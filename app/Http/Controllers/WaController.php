<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WaController extends Controller
{
    /**
     * WhatsApp API configuration
     */
    private $apiUrl;
    private $instanceId;
    private $accessToken;
    
    /**
     * Constructor with middleware setup and configuration loading
     */
    public function __construct()
    {
        // $this->middleware('permission:send-whatsapp', ['only' => ['sendtext', 'sendImage']]);
        
        // Load WhatsApp API configuration
        $this->apiUrl = env('WHATS_APP_URL', null);
        $this->instanceId = env('WHATSAPP_INSTANCE', null);
        $this->accessToken = env('WHATS_APPTOKEN', null);
    }
    
    /**
     * Log user activity
     * 
     * @param string $message The message to log
     * @param string $logName The log name
     * @return void
     */
    private function logActivity($message, $logName = 'Whatsapp Message')
    {
        activity()
            ->causedBy(auth()->user())
            ->useLog($logName)
            ->log($message);
    }
    
    /**
     * Get an alert message
     * 
     * @param string $code The alert message code
     * @return object The alert message
     */
    private function getAlertMessage($code)
    {
        return app('App\Http\Controllers\AlertMessageController')->get($code);
    }

    /**
     * Display form and send text message via WhatsApp
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function sendtext(Request $request)
    {
        $title = "Send Text Message";
        
        if ($request->number) {
            try {
                // Build the query string
                $query = http_build_query([
                    'number' => $request->number,
                    'type' => 'text',
                    'message' => $request->msg,
                    'instance_id' => $this->instanceId,
                    'access_token' => $this->accessToken,
                ]);
                
                // Make the API request
                $response = Http::get($this->apiUrl . '?' . $query);
                
                // Check if the response is successful
                if ($response->json('status') == 'success') {
                    $alertMessage = $this->getAlertMessage('whatsapp.success');
                    $this->logActivity($alertMessage->message);
                    session()->flash('success', $alertMessage->message);
                    return view('admin.whatsapp.sendtext', compact('title', 'response'));
                } else {
                    $alertMessage = $this->getAlertMessage('whatsapp.error');
                    $this->logActivity($alertMessage->message);
                    Log::error('WhatsApp API error', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    session()->flash('error', $alertMessage->message);
                    return view('admin.whatsapp.sendtext', compact('title'));
                }
            } catch (\Exception $e) {
                Log::error('WhatsApp API exception', ['error' => $e->getMessage()]);
                $alertMessage = $this->getAlertMessage('whatsapp.error');
                $this->logActivity($alertMessage->message);
                session()->flash('error', $alertMessage->message);
                return view('admin.whatsapp.sendtext', compact('title'));
            }
        }
        
        return view('whatsapp.sendtext', compact('title'));
    }

    /**
     * Send an image via WhatsApp
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendImage(Request $request)
    {
        if ($request->number && $request->image_url) {
            try {
                // Ensure image_url is a string
                $imageUrl = is_array($request->image_url) ? $request->image_url['imageUrl'] : $request->image_url;
                
                // Convert local path to full URL
                if (strpos($imageUrl, '/storage/') === 0) {
                    $imageUrl = asset($imageUrl);
                }
                
                // Extract just the filename from the URL path
                $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
                
                // Log the information
                Log::info('Image details', [
                    'full_url' => $imageUrl,
                    'filename' => $filename
                ]);
                
                // Create parameters array for the uniwebmedia WhatsApp API
                $params = [
                    'number' => $request->number,
                    'type' => 'media',
                    'message' => $request->caption ?? 'Live Chart',
                    'media_url' => $imageUrl, // Direct full image URL
                    'filename' => $filename, // Just the filename
                    'instance_id' => $this->instanceId,
                    'access_token' => $this->accessToken,
                ];
                
                // Log the request data with redacted access token
                $logParams = $params;
                $logParams['access_token'] = '[REDACTED]';
                Log::info('Sending image to WhatsApp', array_merge(
                    ['url' => $this->apiUrl],
                    $logParams
                ));
                
                // Build the query string
                $query = http_build_query($params);
                
                // Log the full URL with query string (redacted)
                $logUrl = preg_replace('/access_token=([^&]*)/', 'access_token=[REDACTED]', $this->apiUrl . '?' . $query);
                Log::info('Full URL with query string', ['url' => $logUrl]);
                
                // Make the API request
                $response = Http::timeout(30)->get($this->apiUrl . '?' . $query);
                
                // Log the response
                Log::info('WhatsApp API response (image)', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                
                // Check if the response indicates success
                if ($response->successful() && isset($response->json()['status']) && $response->json()['status'] === 'success') {
                    $this->logActivity('Image sent successfully to ' . $request->number);
                    return response()->json($response->json());
                } else {
                    Log::error('Error sending image to WhatsApp', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to send image: ' . ($response->json()['message'] ?? 'Unknown error'),
                        'api_response' => $response->json()
                    ], $response->successful() ? 200 : 500);
                }
            } catch (\Exception $e) {
                Log::error('Exception sending image to WhatsApp', ['error' => $e->getMessage()]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }
        }
        
        Log::error('Invalid request: missing number or image_url');
        return response()->json([
            'status' => 'error', 
            'message' => 'Missing required parameters: number and image_url'
        ], 400);
    }
}