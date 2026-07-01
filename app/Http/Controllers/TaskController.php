<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process; 
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * Windows scheduled task name
     */
    private $taskName;
    
    /**
     * Process timeout in seconds
     */
    private $processTimeout = 60;
    
    /**
     * Constructor with middleware setup and configuration loading
     */
    public function __construct()
    {
        $this->middleware('permission:manage-tasks');
        $this->taskName = env('WINDOWSTASKZEROOP', null);
    }
    
    /**
     * Check the status of a Windows scheduled task
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTaskStatus() 
    { 
        if (empty($this->taskName)) {
            return response()->json(['error' => 'Task name not configured'], 500);
        }
        
        try { 
            // Create a process to query the task status
            $process = new Process(['schtasks', '/query', '/tn', $this->taskName, '/fo', 'LIST']);
            $process->setTimeout($this->processTimeout);
            $process->run();
            
            if (!$process->isSuccessful()) { 
                throw new ProcessFailedException($process); 
            }
            
            // Capture and analyze the output
            $output = $process->getOutput();
            $status = $this->parseTaskStatus($output);
            
            // Log successful status check
            Log::info("Task status checked", [
                'task' => $this->taskName,
                'status' => $status
            ]);
            
            return response()->json([
                'status' => $status, 
                'task_name' => $this->taskName,
                'output' => $output
            ]); 
        } catch (\Exception $e) {
            Log::error("Error checking task status", [
                'task' => $this->taskName,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => $e->getMessage()], 500); 
        } 
    }

    /**
     * Enable or disable a Windows scheduled task
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function manageTask(Request $request) 
    { 
        if (empty($this->taskName)) {
            return response()->json(['error' => 'Task name not configured'], 500);
        }
        
        // Validate the input
        $request->validate([
            'action' => 'required|in:enable,disable'
        ]);
        
        $action = $request->input('action');
        
        try {
            // Create a process to change the task status
            $process = new Process(['schtasks', '/change', '/tn', $this->taskName, '/'. $action]);
            $process->setTimeout($this->processTimeout);
            $process->run();
            
            if (!$process->isSuccessful()) { 
                throw new ProcessFailedException($process); 
            }
            
            // Log successful action
            Log::info("Task {$action}d", [
                'task' => $this->taskName,
                'action' => $action,
                'user' => auth()->user()->name
            ]);
            
            // If enabling the task, also run it
            if ($action == 'enable') {
                $this->runTask();
            }
            
            return response()->json([
                'message' => "Task {$action}d successfully",
                'task_name' => $this->taskName
            ]); 
        } catch (\Exception $e) {
            Log::error("Error {$action}ing task", [
                'task' => $this->taskName,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => $e->getMessage()], 500); 
        } 
    }
    
    /**
     * Run the scheduled task
     * 
     * @return bool
     * @throws ProcessFailedException
     */
    private function runTask()
    {
        $process = new Process(['schtasks', '/run', '/tn', $this->taskName]);
        $process->setTimeout($this->processTimeout);
        $process->run();
        
        if (!$process->isSuccessful()) {
            Log::error("Error running task", [
                'task' => $this->taskName,
                'error' => $process->getErrorOutput()
            ]);
            throw new ProcessFailedException($process);
        }
        
        Log::info("Task run initiated", [
            'task' => $this->taskName,
            'output' => $process->getOutput()
        ]);
        
        return true;
    }
    
    /**
     * Parse the task status from the command output
     * 
     * @param string $output
     * @return string
     */
    private function parseTaskStatus($output)
    {
        $status = 'Unknown';
        
        // Check if the task is disabled
        if (preg_match('/Status:\s+Disabled/', $output)) {
            $status = 'Disabled';
        }
        // Check if the task is ready and/or enabled
        elseif (preg_match('/Status:\s+Ready/', $output) || preg_match('/Status:\s+Enabled/', $output)) {
            $status = 'Enabled';
        }
        // Check if the task is running
        elseif (preg_match('/Status:\s+Running/', $output)) {
            $status = 'Running';
        }
        
        return $status;
    }
}
