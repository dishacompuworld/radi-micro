<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OltLogController extends Controller
{
    protected $listenerProcessIdFile = null;

    public function __construct()
    {
        $this->listenerProcessIdFile = storage_path('app/olt_udp_listener.pid');
    }

    public function index()
    {
        $title = 'OLT Logs';
        $running = $this->isListenerRunning();

        return view('olt.logs', compact('title', 'running'));
    }

    public function status()
    {
        return response()->json([
            'running' => $this->isListenerRunning(),
            'port' => 514,
            'pid' => $this->readPid(),
        ]);
    }

    public function recent()
    {
        $logFile = storage_path('logs/olt-udp.log');
        $logs = [];

        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lines = array_slice(array_reverse($lines), 0, 200);

            foreach ($lines as $line) {
                $entry = [
                    'raw' => $line,
                    'time' => '',
                    'device' => '',
                    'serial' => '',
                    'ont' => '',
                    'pon' => '',
                    'oid' => '',
                    'alarm' => '',
                    'count' => '',
                    'name' => '',
                    'power' => '',
                ];

                $prefixMatch = preg_match('/^\[[^\]]+\]\s*[^\s]+:\d+\s*(.*)$/', $line, $matches);
                $payload = $prefixMatch ? $matches[1] : $line;

                $patterns = [
                    '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+ONU\s+\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+in\s+PON\s+(?P<pon>\d+)\s+(?P<alarm>.+?)\s+(?P<count>\d+)\s+reported\.?\s*$/i',
                    '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+(?:\[[^\]]+\]\s*)?PON\s+(?P<pon>\d+)\s+ONU\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+(?P<alarm>.+?)\s*$/i',
                    '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+Signals\s+were\s+lost\s+for\s+ONU\s+\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+in\s+PON\s+(?P<pon>\d+)\.?\s*$/i',
                    '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+ONU\s+\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+in\s+PON\s+(?P<pon>\d+)\s+last\s+down\s+causes?\s*:\s*(?P<alarm>.+?)\.?\s*$/i',
                    '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+ONU\s+\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+in\s+PON\s+(?P<pon>\d+)\s+was\s+disconnected\.?\s*$/i',
                    '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+Warn\s+of\s+dying-gasp\s+for\s+ONU\s+\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+in\s+PON\s+(?P<pon>\d+)\.?\s*$/i',
                    '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+ONU\s+\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+in\s+PON\s+(?P<pon>\d+)\s+was\s+connected\.?\s*$/i',
                    '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+PON\s+(?P<pon>\d+)\s+ONU\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+ethernet\s+port\s+\d+\s+link\s+was\s+up\.?\s*$/i',
                    '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+ONU\s+(?P<ont>\d+)\s+in\s+PON\s+(?P<pon>\d+)\s+was\s+activated\s+successfully\.?\s*$/i',
                ];

                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $payload, $matches)) {
                        $entry['time'] = $matches['timestamp'] ?? '';
                        $entry['device'] = $matches['device'] ?? '';
                        $entry['serial'] = $matches['serial'] ?? '';
                        $entry['ont'] = $matches['ont'] ?? '';
                        $entry['pon'] = $matches['pon'] ?? '';
                        $entry['count'] = $matches['count'] ?? '';
                        $entry['alarm'] = trim($matches['alarm'] ?? '');

                        if ($entry['alarm'] === '') {
                            if (stripos($payload, 'link was up') !== false || stripos($payload, 'was connected') !== false) {
                                $entry['alarm'] = 'Connected';
                            } elseif (stripos($payload, 'was activated successfully') !== false) {
                                $entry['alarm'] = 'Activated successfully';
                            } elseif (stripos($payload, 'dying-gasp') !== false) {
                                $entry['alarm'] = 'Dying-gasp';
                            } elseif (stripos($payload, 'Signals were lost') !== false) {
                                $entry['alarm'] = 'Signals were lost';
                            } elseif (stripos($payload, 'was disconnected') !== false) {
                                $entry['alarm'] = 'ONU was disconnected';
                            } elseif (stripos($payload, 'last down causes') !== false) {
                                $entry['alarm'] = 'Last down causes';
                            }
                        }

                        $oid = $entry['pon'] !== '' && $entry['ont'] !== '' ? $entry['pon'] . '.' . $entry['ont'] : '';
                        $entry['oid'] = $oid;

                        if ($oid !== '') {
                            $lookup = DB::table('opticalpowers')->where('oid', $oid)->select('name', 'powers')->first();
                            $entry['name'] = $lookup->name ?? '';
                            $entry['power'] = $lookup->powers ?? '';
                        }

                        break;
                    }
                }

                $logs[] = $entry;
            }
        }

        return response()->json([
            'logs' => $logs,
            'running' => $this->isListenerRunning(),
        ]);
    }

    public function start(Request $request)
    {
        if ($this->isListenerRunning()) {
            return response()->json([
                'success' => true,
                'message' => 'OLT UDP listener is already running.',
                'running' => true,
            ]);
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $output = [];
            $code = 0;
            exec('sc start OLTListener 2>&1', $output, $code);
            $message = trim(implode("\n", $output));

            if ($code === 0 || str_contains($message, 'STARTED') || str_contains(strtolower($message), 'already')) {
                return response()->json([
                    'success' => true,
                    'message' => 'OLT service started successfully.',
                    'running' => true,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $message ?: 'Failed to start OLT service via Windows Service Control Manager.',
            ], 500);
        }

        $pid = $this->startListenerProcess();

        if (! $pid) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start OLT UDP listener. Check PHP permissions and port availability.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OLT UDP listener started successfully on port 514.',
            'running' => true,
            'pid' => $pid,
        ]);
    }

    public function stop(Request $request)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = [];
            $code = 0;
            exec('sc stop OLTListener 2>&1', $output, $code);
            $message = trim(implode("\n", $output));

            if ($code === 0 || str_contains($message, 'STOPPED') || str_contains(strtolower($message), 'not running')) {
                if (file_exists($this->listenerProcessIdFile)) {
                    @unlink($this->listenerProcessIdFile);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'OLT service stopped successfully.',
                    'running' => false,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $message ?: 'Failed to stop OLT service via Windows Service Control Manager.',
            ], 500);
        }

        $pid = $this->readPid();

        if (! $pid && PHP_OS_FAMILY === 'Windows') {
            $pid = $this->findWindowsUdpListenerPid();
        }

        if (! $pid) {
            return response()->json([
                'success' => true,
                'message' => 'OLT UDP listener is not running.',
                'running' => false,
            ]);
        }

        if (function_exists('proc_open')) {
            $this->killProcess((int) $pid);
        }

        if (file_exists($this->listenerProcessIdFile)) {
            @unlink($this->listenerProcessIdFile);
        }

        return response()->json([
            'success' => true,
            'message' => 'OLT UDP listener stopped.',
            'running' => false,
        ]);
    }

    public function deleteLogs()
    {
        $logFile = storage_path('logs/olt-udp.log');

        if (file_exists($logFile)) {
            @unlink($logFile);
        }

        file_put_contents($logFile, '');

        return response()->json([
            'success' => true,
            'message' => 'All OLT logs deleted.',
        ]);
    }

    protected function startListenerProcess()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = sprintf(
                'powershell -NoProfile -Command "Start-Process -FilePath %s -ArgumentList @(%s, %s) -PassThru -WindowStyle Hidden -RedirectStandardOutput NUL -RedirectStandardError NUL | Select-Object -ExpandProperty Id"',
                escapeshellarg(PHP_BINARY),
                escapeshellarg(base_path('artisan')),
                escapeshellarg('olt:listen')
            );

            exec($cmd, $output, $returnVar);

            $pid = null;
            if (! empty($output)) {
                $pid = trim((string) $output[0]);
            }

            if ($pid !== '' && is_numeric($pid)) {
                file_put_contents($this->listenerProcessIdFile, (int) $pid);
                return (int) $pid;
            }

            return null;
        }

        $cmd = sprintf(
            'php %s %s > /dev/null 2>&1 & echo $! ',
            escapeshellarg(base_path('artisan')),
            escapeshellarg('olt:listen')
        );

        $output = [];
        exec($cmd, $output, $returnVar);

        $pid = null;
        if (! empty($output)) {
            $pid = trim((string) $output[0]);
        }

        if (! $pid || ! is_numeric($pid)) {
            return null;
        }

        file_put_contents($this->listenerProcessIdFile, (int) $pid);

        return (int) $pid;
    }

    protected function isListenerRunning()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $pid = $this->readPid();
            if ($pid) {
                $output = shell_exec(sprintf('powershell -NoProfile -Command "Get-Process -Id %d -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Id" 2>$null', (int) $pid));
                if (is_string($output) && trim($output) !== '') {
                    return true;
                }
            }

            $output = shell_exec('powershell -NoProfile -Command "Get-NetUDPEndpoint -ErrorAction SilentlyContinue | Where-Object { $_.LocalPort -eq 514 } | Select-Object -ExpandProperty LocalPort" 2>$null');
            return is_string($output) && preg_match('/514/', trim($output)) === 1;
        }

        $pid = $this->readPid();
        if (! $pid) {
            return false;
        }

        return file_exists('/proc/' . $pid);
    }

    protected function readPid()
    {
        if (! file_exists($this->listenerProcessIdFile)) {
            return null;
        }

        $pid = trim((string) file_get_contents($this->listenerProcessIdFile));

        return $pid !== '' && is_numeric($pid) ? (int) $pid : null;
    }

    protected function findWindowsUdpListenerPid()
    {
        $output = shell_exec('powershell -NoProfile -Command "(Get-NetUDPEndpoint -ErrorAction SilentlyContinue | Where-Object { $_.LocalPort -eq 514 } | Select-Object -ExpandProperty OwningProcess -Unique) 2>$null"');

        if (! is_string($output)) {
            return null;
        }

        $pid = trim($output);

        return $pid !== '' && is_numeric($pid) ? (int) $pid : null;
    }

    protected function killProcess($pid)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            @exec(sprintf('taskkill /PID %d /F > NUL 2>&1', (int) $pid));
            return;
        }

        @exec(sprintf('kill %d > /dev/null 2>&1', (int) $pid));
    }
}
