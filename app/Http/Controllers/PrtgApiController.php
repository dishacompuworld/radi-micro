<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Yajra\DataTables\DataTables;
// use App\Http\Controllers\Storage;
use Illuminate\Http\File;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;
use SimpleXMLElement;
use App\Http\Controllers\DateTime;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PrtgApiController extends Controller
{
     /**
     * PRTG API configuration
     */
    private $apiUrl;
    private $apiKey;
    private $allTrafficGraphId;
    private $mainProbId;
    private $msebProbId;
    
    /**
     * Constructor with middleware setup and configuration loading
     */
    public function __construct()
    {
        $this->middleware('permission:view-prtg', ['only' => [
            'getfirstchart', 'getsecondchart', 
            'generateSVG1', 'downsensors',
            'generateSVG2', 'historygraph', 'showLiveGraph', 'getLiveGraphImage',
            'allsensors', 'upsensors', 
            'getMsebInfoAjax', 'getProcessedMsebData'
            ]]);
        // // $this->middleware('permission:test-api', ['only' => ['apitest']]);
        
        // Load PRTG API configuration
        $this->apiUrl = env('PRTG_URL', null);
        $this->apiKey = env('PRTG_API_KEY', null);
        $this->allTrafficGraphId = env('PRTG_ALL_TRAFFIC_GRAPH_ID', null);
        $this->mainProbId = env('PRTG_MAIN_PROB_ID', null);
        $this->msebProbId = env('PRTG_MSEB');
    }

    /**
     * Make an API request to PRTG
     * 
     * @param string $endpoint The API endpoint
     * @param array $params The query parameters
     * @return \Illuminate\Http\Client\Response
     * @throws \Exception If API configuration is missing
     */
    private function makeApiRequest($endpoint, $params = [])
    {
        if (empty($this->apiUrl) || empty($this->apiKey)) {
            throw new \Exception('PRTG API configuration is missing');
        }
        
        // Add API token to parameters
        $params['apitoken'] = $this->apiKey;
        
        // Build the URL
        $url = $this->apiUrl . $endpoint;
        
        // Log the request (excluding the API token)
        $logParams = $params;
        $logParams['apitoken'] = '[REDACTED]';
        Log::info('PRTG API request', [
            'url' => $url,
            'params' => $logParams
        ]);
        
        try {
            $response = Http::timeout(10)->get($url, $params);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('PRTG API request failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getfirstchart(){
        $prtg_link = env('PRTG_URL',null);
        $prtg_apikey = env('PRTG_API_KEY',null);
        $graph_id = env('PRTG_ALL_TRAFFIC_GRAPH_ID',null);

        $svg_file = Http::get($prtg_link . '/chart.svg?type=graph&width=800&height=350&graphid=0&id=' . $graph_id .'&graphstyling=showLegend%3D%270%27+baseFontSize%3D%276%27&apitoken=' . $prtg_apikey);

        file_put_contents("graph1.svg", $svg_file);

    }

    public function getsecondchart(){
        $prtg_link = env('PRTG_URL',null);
        $prtg_apikey = env('PRTG_API_KEY',null);
        $graph_id = env('PRTG_ALL_TRAFFIC_GRAPH_ID',null);

        $svg_file1 = Http::get($prtg_link . '/chart.svg?type=graph&width=800&height=350&graphid=1&id=' . $graph_id .'&graphstyling=showLegend%3D%271%27+baseFontSize%3D%275%27&hide=-4&apitoken='. $prtg_apikey);

        file_put_contents("graph2.svg", $svg_file1);

    }


    public function generateSVG1(){
        $prtg_link = env('PRTG_URL',null);
        $prtg_apikey = env('PRTG_API_KEY',null);
        $graph_id = env('PRTG_ALL_TRAFFIC_GRAPH_ID',null);


        // Generate SVG content for the first image 
        // $svgContent = ' <svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"> <rect width="100" height="100" fill="blue" /> <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="white">Image 1</text> </svg>'; 
        $svg_file1 = Http::get($prtg_link . '/chart.svg?type=graph&width=800&height=350&graphid=0&id=' . $graph_id .'&graphstyling=showLegend%3D%271%27+baseFontSize%3D%275%27&hide=-4&apitoken='. $prtg_apikey);
        return Response::make($svg_file1, 200, ['Content-Type' => 'image/svg+xml']);

    }


    public function generateSVG2(){
        $prtg_link = env('PRTG_URL',null);
        $prtg_apikey = env('PRTG_API_KEY',null);
        $graph_id = env('PRTG_ALL_TRAFFIC_GRAPH_ID',null);


        // Generate SVG content for the first image 
        // $svgContent = ' <svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"> <rect width="100" height="100" fill="blue" /> <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="white">Image 1</text> </svg>'; 
        $svg_file1 = Http::get($prtg_link . '/chart.svg?type=graph&width=800&height=350&graphid=1&id=' . $graph_id .'&graphstyling=showLegend%3D%271%27+baseFontSize%3D%275%27&hide=-4&apitoken='. $prtg_apikey);
        return Response::make($svg_file1, 200, ['Content-Type' => 'image/svg+xml']);

    }

    public function downsensors(){

        $prtg_link = env('PRTG_URL',null);
        $prtg_apikey = env('PRTG_API_KEY',null);
        $mainprobid = env('PRTG_MAIN_PROB_ID',null);

        $response = Http::get($prtg_link.'/api/getobjectstatus.htm?id=' . $mainprobid .'&name=downsens&apitoken='. $prtg_apikey);

        $xml = new SimpleXMLElement($response);
        $result = (string) $xml->result;

        if($result){
            return $result;
        }else{
            return 0;
        }

    }

    public function upsensors(){

        $prtg_link = env('PRTG_URL',null);
        $prtg_apikey = env('PRTG_API_KEY',null);
        $mainprobid = env('PRTG_MAIN_PROB_ID',null);

        $response = Http::get($prtg_link.'/api/getobjectstatus.htm?id=' . $mainprobid .'&name=upsens&apitoken='. $prtg_apikey);

        $xml = new SimpleXMLElement($response);
        $result = (string) $xml->result;

        return $result;

    }

     /**
     * Get total sensor count
     * 
     * @return int|string
     */
    public function totalsensors()
    {
        try {
            $response = $this->makeApiRequest('/api/getobjectstatus.htm', [
                'id' => $this->mainProbId,
                'name' => 'totalsens'
            ]);
            
            if ($response->successful()) {
                $xml = new SimpleXMLElement($response->body());
                $result = (string) $xml->result;
                return $result ?: 0;
            } else {
                Log::error('Failed to get total sensors', ['status' => $response->status()]);
                return 0;
            }
        } catch (\Exception $e) {
            Log::error('Exception getting total sensors', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    public function historygraph(Request $request){
        $title = "PRTG History Grabh";
        date_default_timezone_set("Asia/Kolkata");

        $prtg_link = env('PRTG_URL',null);
        $prtg_apikey = env('PRTG_API_KEY',null);
        $graph_id = env('PRTG_ALL_TRAFFIC_GRAPH_ID',null);

        $sdtime = $request->sdtime;

        $edtime = $request->edtime;
        if(isset($edtime)){$edtime;}else{$edtime=now();}
        // return $edtime1;
        // if(isset($edtime)){$edtime;}else{$edtime="";}

        $sdnew = new \DateTime($sdtime);
        if(!$sdtime){
            $sdnew = (new \DateTime())->modify('-1 day');
            $sdtime = $sdnew->format('Y-m-d\TH:i:s'); //date('Y-m-d\TH:i:s');
        }
        $sdnew1 = $sdnew->format('Y-m-d-H-i-s');

        $ednew = new \DateTime($edtime);
        $ednew1 = $ednew->format('Y-m-d-H-i-s');

        //  return $sdnew1 . ' ' . $ednew1;


        $response = Http::get($prtg_link.'/chart.svg?id='. $graph_id . '&avg=10&sdate=' . $sdnew1 . '&edate=' . $ednew1 . '&width=700&height=300&graphid=-1&graphstyling=showLegend%3D%270%27+baseFontSize%3D%276%27&hide=-4&apitoken='. $prtg_apikey);
        // return $response;
        file_put_contents("hgraph.svg", $response);


        activity()->causedBy(auth()->user())->useLog('PRTG')->log('Checked All Traffic from ' . $sdnew1 . ' to ' . $ednew1);
        return view('prtg.historygraph', compact('title','sdtime','edtime'));
    }

    /**
     * Get all sensors and display in DataTable
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function allsensors(Request $request)
    {
        $title = "All Sensors";
        
        try {
            if ($request->ajax()) {
                $response = $this->makeApiRequest('/api/table.xml', [
                    'content' => 'sensors',
                    'output' => 'json',
                    'columns' => 'device,sensor,status,lastvalue'
                ]);
                
                if ($response->successful() && isset($response->json()['sensors'])) {
                    return DataTables::of($response->json()['sensors'])
                        ->addIndexColumn()
                        ->make(true);
                } else {
                    return response()->json(['error' => 'Failed to fetch sensors'], 500);
                }
            }
            
            return view('admin.prtg.allsensors', compact('title'));
        } catch (\Exception $e) {
            Log::error('Exception getting all sensors', ['error' => $e->getMessage()]);
            
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
            return view('prtg.allsensors', compact('title'))
                ->with('error', 'Failed to fetch sensors: ' . $e->getMessage());
        }
    }

    /**
     * Displays the live graph page.
     *
     * @return \Illuminate\View\View
     */
    public function showLiveGraph()
    {
        return view('prtg.livegraph', ['title' => 'Live Graph']);
    }

    /**
     * Fetches and returns the live graph SVG content from PRTG.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function getLiveGraphImage()
    {
        try {
            $response = $this->makeApiRequest('/chart.svg', [
                'type' => 'graph',
                'width' => 1000,
                'height' => 560,
                'graphid' => 0,
                'graphstyling' => 'showLegend%3D%271%27+baseFontSize%3D%279%27',
                'id' => $this->allTrafficGraphId,
            ]);

            if ($response->successful()) {
                $svgContent = $response->body();
                if (!empty($svgContent) && str_contains($svgContent, '<svg')) {
                    return response($svgContent, 200)->header('Content-Type', 'image/svg+xml');
                }
            }
            return response('Failed to fetch graph', 500);
        } catch (\Exception $e) {
            Log::error('Exception in liveGraph', ['error' => $e->getMessage()]);
            return response('Error: ' . $e->getMessage(), 500);
        }
    }


    public function getMessages(Request $request)
    {
        $title = "Messages";
        
        try {
            if ($request->ajax()) {
                $response = $this->makeApiRequest('/api/table.json', [
                    'content' => 'messages',
                    'columns' => 'objid,datetime,parent,type,name,status,message'
                ]);

                if (!$response->successful()) {
                    Log::error('Failed to fetch PRTG messages', ['status' => $response->status()]);
                    return response()->json(['error' => 'Failed to fetch messages'], $response->status());
                }

                $data = $response->json();
                if (!isset($data['messages']) || !is_array($data['messages'])) {
                    Log::error('Invalid response format from PRTG API', ['response' => $data]);
                    return response()->json(['error' => 'Invalid response format'], 500);
                }

                return DataTables::of($data['messages'])
                    ->addIndexColumn()
                    ->addColumn('datetime', function ($row) {
                        return date('Y-m-d H:i:s', strtotime($row['datetime']));
                    })
                    ->make(true);
            }

            return view('admin.prtg.messages', compact('title'));

        } catch (\Exception $e) {
            Log::error('Exception getting PRTG messages', ['error' => $e->getMessage()]);
            
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
            return view('prtg.messages', compact('title'))
                ->with('error', 'Failed to fetch messages: ' . $e->getMessage());
        }
    }

    public function getMessagesapi()
    {
        // $title = "Messages";
        
        // try {
            // if ($request->ajax()) {
                $response = $this->makeApiRequest('/api/table.json', [
                    'content' => 'messages',
                    'columns' => 'objid,datetime,parent,type,name,status,message'
                ]);

                if (!$response->successful()) {
                    Log::error('Failed to fetch PRTG messages', ['status' => $response->status()]);
                    return response()->json(['error' => 'Failed to fetch messages'], $response->status());
                }

                $data = $response->json();
                if (!isset($data['messages']) || !is_array($data['messages'])) {
                    Log::error('Invalid response format from PRTG API', ['response' => $data]);
                    return response()->json(['error' => 'Invalid response format'], 500);
                }

                
            // }
            activity()->causedBy(auth()->user())->useLog('PRTG Messages - api')->log('Checked Access request.');
            return response()->json($data['messages']);

        // } catch (\Exception $e) {
        //     Log::error('Exception getting PRTG messages', ['error' => $e->getMessage()]);
            
        //     if ($request->ajax()) {
        //         return response()->json(['error' => $e->getMessage()], 500);
        //     }
            
        //     return view('admin.prtg.messages', compact('title'))
        //         ->with('error', 'Failed to fetch messages: ' . $e->getMessage());
        // }
    }


    /**
 * Get MSEB (Maharashtra State Electricity Board) sensor up/down status
 * 
 * @return \Illuminate\Http\JsonResponse
 */
/**
 * Get MSEB sensor status data for internal use (returns array)
 * 
 * @return array
 */
    public function getMsebStatusData()
    {
        $sensorId = $this->msebProbId;
        
        try {
            $response = $this->makeApiRequest('/api/getsensordetails.json', [
                'id' => $sensorId
            ]);
            // return $response->json();
            if ($response->successful()) {
                $sensorData = $response->json();
                $sensor = $sensorData['sensordata'];
                
                return [
                    'statustext' => $sensor['statustext'] ?? 'Unknown',
                    'uptime' => $this->parsePrtgDateString($sensor['lastdown']) ?? 'N/A',
                    'downtime' => $this->parsePrtgDateString($sensor['lastup']) ?? 'N/A',
                    'success' => true
                ];
            }
            
            return [
                'statustext' => 'Error',
                'uptime' => 'N/A',
                'downtime' => 'N/A',
                'success' => false
            ];
        } catch (\Exception $e) {
            Log::error('Exception getting MSEB status data', [
                'id' => $sensorId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'statustext' => 'Error',
                'uptime' => 'N/A',
                'downtime' => 'N/A',
                'success' => false
            ];
        }
    }

    public function getMsebStatusDataapi()
    {
        $sensorId = $this->msebProbId;
        
        try {
            $response = $this->makeApiRequest('/api/getsensordetails.json', [
                'id' => $sensorId
            ]);
            // return $response->json();
            if ($response->successful()) {
                $sensorData = $response->json();
                $sensor = $sensorData['sensordata'];
                
                return [
                    'status' => $sensor['statustext'] ?? 'Unknown',
                    'uptime' => $this->parsePrtgDateString($sensor['lastdown']) ?? 'N/A',
                    'downtime' => $this->parsePrtgDateString($sensor['lastup']) ?? 'N/A',
                    'success' => true
                ];
            }
            
            return [
                'status' => 'Error',
                'uptime' => 'N/A',
                'downtime' => 'N/A',
                'success' => false
            ];
        } catch (\Exception $e) {
            Log::error('Exception getting MSEB status data', [
                'id' => $sensorId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'status' => 'Error',
                'uptime' => 'N/A',
                'downtime' => 'N/A',
                'success' => false
            ];
        }
    }


    public function getMsebStatusTest(){
    $apiEndpoint = '/api/getsensordetails.json';
        $url = $this->apiUrl . $apiEndpoint;
        $prtg_apikey = env('PRTG_API_KEY',null);

        // try {
            $response = Http::get($url, [
                'id' => 2233, // Use 'id' parameter for specific sensor lookup
                'output' => 'json',
                'apitoken'=> $prtg_apikey
            ]);

            if ($response->failed()) {
                // \Log::error("PRTG API (getsensordetails.json) request failed for ID {$sensorId}: " . $response->status() . " - " . $response->body());
                return null;
            }

            $data = $response->json();
            // return $data; // Reset the array to ensure we start from the first element

            // The structure for getsensordetails.json is slightly different:
            // The sensor details are usually directly under 'prtg' or 'sensordata' or similar.
            // You might need to inspect the exact JSON response from your PRTG server.
            // A common structure is `data['prtg']['sensordata']['item']` or just `data['sensordata']`.
            // For simplicity, we'll assume it's directly accessible or nested.
            // Let's go with the most common output I've seen, which puts properties directly on a 'sensor' or 'item' object.
            // You might need to adjust this parsing based on your specific PRTG version's output.

            $sensorData = null;
            if (isset($data['sensordata'])) {
                 $sensorData = $data['sensordata'];
            } elseif (isset($data['sensor'])) { // Some versions might just have 'sensor'
                 $sensorData = $data['sensor'];
            } elseif (isset($data['item'])) { // Or even directly 'item'
                 $sensorData = $data['item'];
            }


            if (!$sensorData) {
                //  \Log::warning("PRTG API (getsensordetails.json) did not return expected data structure for ID {$sensorId}.");
                 return 'Error 1';
            }

            // Extract relevant fields, handling potential missing keys
            $sensorName = $sensorData['name'] ?? 'N/A';
            $sensorStatus = $sensorData['statustext'] ?? 'Unknown';
            $uptimeSince = (int)($sensorData['uptimesince'] ?? 0);
            $downtimeSince = (int)($sensorData['downtimesince'] ?? 0);
            $lastUp = isset($sensorData['lastup']) ? Carbon::parse($sensorData['lastup']) : null;
            $lastDown = isset($sensorData['lastdown']) ? Carbon::parse($sensorData['lastdown']) : null;


            $currentStatusDurationSeconds = 0;
            $humanReadableDuration = '';

            if ($sensorStatus === 'Up') {
                $currentStatusDurationSeconds = $uptimeSince;
                $humanReadableDuration = 'Up for: ' . $this->formatDuration($currentStatusDurationSeconds);
            } elseif ($sensorStatus === 'Down') {
                $currentStatusDurationSeconds = $downtimeSince;
                $humanReadableDuration = 'Down for: ' . $this->formatDuration($currentStatusDurationSeconds);
            } else {
                $humanReadableDuration = 'Current state: ' . $sensorStatus;
            }

            return [
                'objid' => 2233,
                'name' => $sensorName,
                'status' => $sensorStatus,
                'current_status_duration_seconds' => $currentStatusDurationSeconds,
                'human_readable_duration' => $humanReadableDuration,
                'lastup' => $lastUp ? $lastUp->toDateTimeString() : 'N/A', // Format as needed
                'lastdown' => $lastDown ? $lastDown->toDateTimeString() : 'N/A', // Format as needed
                // Add any other specific details you need from getsensordetails.json
                // e.g., 'message' => $sensorData['message'] ?? '',
                // 'parentdevicename' => $sensorData['parentdevicename'] ?? '',
            ];

        // } catch (\Exception $e) {
        //     // \Log::error("Error fetching specific PRTG sensor data for ID {$sensorId}: " . $e->getMessage());
        //     return 'Error 2';
        // }
    }



    public function apitest(Request $request){

        $username = "rajesh";
        $pass = "Pass_1234";
        $prtg_link = env('PRTG_URL',null);
        $prtg_apikey = env('PRTG_API_KEY',null);

        $response = Http::get($prtg_link.'/api/table.json?content=sensors&output=json&columns=device,sensor,lastvalue&username=' . $username . '&password=' . $pass);
        // $response = Http::get($prtg_link. '/api/getsensordetails.json?id=2233&apitoken='. $prtg_apikey);
        // $response = Http::get($prtg_link. '/api/table.json?content=messages&columns=objid,datetime,parent,type,name,status,message&username=' . $username . '&password=' . $pass);

        // $result = (string) $xml->result;
        // file_put_contents("graph1.svg", $response);
        // return response()->file($filePath);
        // if($result){
            return $response;
        // }else{
        //     return 0;
        // }
        // return view('admin.location.testapi', compact('title', 'response'));
    }


    private function parsePrtgDateString($prtgDateString)
    {
        if (empty($prtgDateString) || !is_string($prtgDateString)) {
            return null;
        }
        
        // Check if there's a bracketed duration (e.g., "[4 s ago]", "[7 h 52 m ago]")
        if (preg_match('/\[([^\]]+)\]/', $prtgDateString, $matches)) {
            $durationString = $matches[1];
            // Remove "ago" and clean up
            $durationString = str_replace(' ago', '', $durationString);
            return $this->formatPrtgDuration($durationString);
        }
        
        // Extract the numeric OLE Automation date part
        if (preg_match('/^(\d+\.?\d*)/', $prtgDateString, $matches)) {
            $oleAutomationDateValue = (float)$matches[1];
            $unixTimestamp = ($oleAutomationDateValue - 25569) * 86400;
            
            try {
                $carbonDate = Carbon::createFromTimestamp((int)floor($unixTimestamp));
                return $this->formatDuration($carbonDate);
            } catch (\Exception $e) {
                Log::error("Failed to create Carbon instance: " . $e->getMessage());
                return null;
            }
        }
        
        return null;
    }

    private function formatPrtgDuration($durationString)
    {
        // Convert PRTG abbreviations to full format
        $replacements = [
            ' s' => ' sec',
            ' m' => ' min', 
            ' h' => ' hr',
            ' d' => ' day'
        ];
        
        $formatted = $durationString;
        foreach ($replacements as $short => $long) {
            $formatted = str_replace($short, $long, $formatted);
        }
        
        return $formatted;
    }

    private function formatDuration($date)
    {
        $now = Carbon::now();
        $diff = $now->diff($date);
        
        $parts = [];
        
        if ($diff->d > 0) {
            $parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        }
        
        if ($diff->h > 0) {
            $parts[] = $diff->h . ' hr' . ($diff->h > 1 ? 's' : '');
        }
        
        if ($diff->i > 0) {
            $parts[] = $diff->i . ' min' . ($diff->i > 1 ? 's' : '');
        }
        
        if ($diff->s > 0) {
            $parts[] = $diff->s . ' sec' . ($diff->s > 1 ? 's' : '');
        }
        
        if (empty($parts)) {
            return 'just now';
        }
        
        return implode(' ', $parts);
    }


    private function getProcessedMsebData(): array
    {
        $msebstatus = $this->getMsebStatusData();

        // // For this example, let's simulate a dynamic status for testing:
        // $isUp = (time() % 20) < 10; // Changes every 10 seconds for demo
        // if ($isUp) {
        //     $msebstatus = [
        //         'success' => true,
        //         'statustext' => 'Up',
        //         'uptime' => rand(1, 20) . ' days ' . rand(0,23) . ' hours',
        //         'downtime' => 'N/A',
        //     ];
        // } else {
        //     $msebstatus = [
        //         'success' => true, // Assuming API call was successful but status is Down
        //         'statustext' => 'Down',
        //         'uptime' => 'N/A',
        //         'downtime' => rand(1,10) . ' hours ' . rand(0,59) . ' minutes',
        //     ];
        // }
        // // --- END MOCK/PLACEHOLDER DATA ---


        $mseb = 'Unknown';
        $updowntime = 'N/A';
        $statusBgClass = 'bg-secondary'; // Default

        if (empty($msebstatus) || !isset($msebstatus['success'])) {
            return [
                'error' => 'Failed to retrieve MSEB status data.',
                'msebStatus' => 'Error',
                'msebDuration' => 'N/A',
                'statusBgClass' => 'bg-warning',
            ];
        }

        if ($msebstatus['success']) {
            if (isset($msebstatus['statustext']) && $msebstatus['statustext'] == 'Up') {
                $mseb = 'Up';
                $updowntime = $msebstatus['uptime'] ?? 'N/A';
                $statusBgClass = 'bg-success';
            } elseif (isset($msebstatus['statustext'])) { // If not 'Up', consider it 'Down' or other state
                $mseb = $msebstatus['statustext']; // Could be 'Down', 'Warning', etc.
                $updowntime = $msebstatus['downtime'] ?? 'N/A';
                // You might want more specific class handling for other statuses
                $statusBgClass = ($mseb == 'Down') ? 'bg-danger' : 'bg-warning'; // Example
            }
        } else {
            $mseb = 'Error';
            $updowntime = $msebstatus['message'] ?? 'Status unavailable';
            $statusBgClass = 'bg-warning';
        }

        return [
            'msebStatus' => $mseb,
            'msebDuration' => $updowntime,
            'statusBgClass' => $statusBgClass,
        ];
    }

    public function getMsebInfoAjax(): JsonResponse
    {
        $data = $this->getProcessedMsebData();
        return response()->json($data);
    }

}

//get('http://admin.dishacompuworld.com:5000/api/healthstatus.json&username=' . urlencode($username) . '&password=' . urlencode($pass));

//getSenserStatus
//http://admin.dishacompuworld.com:5000/api/getsensordetails.json?id=2067&apitoken=AMXKTK6IORBE6X6SWVAWBEVUYLB6RHJ2BEOAH6FLQA======

//GET /api/getobjectstatus.htm?id=1234&name=lastvalue,downtime HTTP/1.1
// Host: your_prtg_server
// Authorization: YourApiKeyHere
//https://www.paessler.com/manuals/prtg/single_object_status
// http://web.dishacompuworld.com:5000/api/getsensordetails.json?id=2233