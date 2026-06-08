<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RouterosAPI;
use Illuminate\Support\Facades\Session;
use App\Models\Server;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class MicrotikController extends Controller
{
    private $api;
    private $server;
    private $connectionTimeout = 5;
    private $maxRetries = 3;

    public function __construct()
    {
        // $this->middleware('role:super-admin','permission:add-server',['only' => ['create','store']]); role example
        // $this->middleware('permission:view-sheduler',['only' => ['shedule']]);
        // $this->middleware('permission:view-script',['only' => ['script']]);
        // // $this->middleware('permission:view-microtik-logs',['only' => ['viewLogs']);

        // $this->middleware('permission:view-sheduler',['only' => ['shedule']]);
        // $this->middleware('permission:view-script',['only' => ['script']]);
        // $this->middleware('permission:view-microtik-logs',['only' => ['viewLog']]);
        // $this->middleware('permission:view-system-health',['only' => ['getSystemHealth']]);
        // $this->middleware('permission:view-neighbors',['only' => ['getIpNeighbors']]);
        // $this->middleware('permission:view-services',['only' => ['showServices','showServiceStatus','updatePptp','updateL2tp','updateTelnet','updateWwwssl','updateWww','updateSsh','updateWinbox']]);
        //
    }

    private function connectToServer($serverId)
    {
        try {
            $this->server = Server::findOrFail($serverId);
            $this->api = new RouterosAPI();
            $this->api->debug = false;
            
            if (!$this->api->connect($this->server->mip, $this->server->username, $this->server->password)) {
                throw new \Exception("Failed to connect to server");
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to connect to server {$serverId}: " . $e->getMessage());
            return false;
        }
    }

    private function executeCommand($command, $params = [])
    {
        try {
            if (!$this->api) {
                throw new \Exception("No active API connection");
            }

            $this->api->write($command, false);
            foreach ($params as $key => $value) {
                $this->api->write("={$key}={$value}");
            }
            
            return $this->api->read();
        } catch (\Exception $e) {
            Log::error("Command execution failed: " . $e->getMessage());
            return false;
        }
    }

    public function shedule(Request $request){

        $title = "Sheduler";

        $chk = $request->checkbox;
        $id= $request->id;

        $chkvalue = $request->state;

        $seletedserver = $request->get('sserver');
        $servers = Server::where('enable','1')->get();

        if($chk=="pressed")
        {
            $serveriid = Server::find($seletedserver);

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                $srt1= "/system/scheduler/disable";
                $srt= "/system/scheduler/enable";
               //. $selected;
                if($chkvalue=="on"){

                    $API->write($srt,false);
                    $API->write("=.id=". $id);
                    $API->read();

                    activity()->causedBy(auth()->user())->useLog('Shedule')->log('Sheduled Enabled');

                }else{

                    $API->write($srt1,false);
                    $API->write("=.id=". $id);
                    $API->read();

                    activity()->causedBy(auth()->user())->useLog('Shedule')->log('Sheduled Disabled');
                }

            }


        }

        if($seletedserver){

            $serveriid = Server::find($seletedserver);

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API1 = new RouterosAPI();
            $API1->debug = false;

            if ($API1->connect($ip, $user, $password)) {

                $shedules = $API1->comm('/system/scheduler/print');

                // return $shedules;

                return view('admin.microtik.shedule', compact('title','servers','shedules','seletedserver'));
            }
        }



        return view('microtik.shedule', compact('title','servers'));
    }

    public function script(Request $request){
        $title = "Scripts";

        $chk = $request->checkbox;
        $id= $request->id;

        //$chkvalue = $request->state;

        $seletedserver = $request->get('sserver');
        $servers = Server::where('enable','1')->get();

        if($chk=="pressed")
        {
            $serveriid = Server::find($seletedserver);

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                // $srt1= "/system/script/disable";
                // $srt= "/system/script/enable";
               //. $selected;
                $API->write("/system/script/run",false);
                $API->write("=.id=". $id);
                $API->read();

                // routeros->write("/system/script/run",1);
                // routeros->write("=.id=*3",1);

                activity()->causedBy(auth()->user())->useLog('Scripts')->log('Script Executed');

            }
        }

        if($seletedserver){

            $serveriid = Server::find($seletedserver);

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API1 = new RouterosAPI();
            $API1->debug = false;

            if ($API1->connect($ip, $user, $password)) {

                $scripts = $API1->comm('/system/script/print');

                // return $scripts;

                return view('admin.microtik.script', compact('title','servers','scripts','seletedserver'));
            }
        }
        return view('admin.microtik.script', compact('title','servers'));
    }

    public function addtolog($serveriid){

            // $seletedserver = $request->get('sserver');
            $message = auth()->user()->name." Logged in from microtik-radius.";
            $serveriid = $serveriid;
            // $serveriid = Server::find($seletedserver);

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;
            
            
            if ($API->connect($ip, $user, $password)) { // Command to add log 
                $command = '/log/error'; 
                $params = [ 'message' => $message, ]; 
                $API->comm($command, $params); 
                $API->disconnect(); 
                // return response()->json(['status' => 'success', 'message' => 'Log written successfully']); 
            } else { 
                // return response()->json(['status' => 'error', 'message' => 'Unable to connect to MikroTik'], 500); 
            }
            
            
    // (auth()->user())->useLog('Scripts')->log('Script Executed');

    }

    public function login(Request $request)
    {
        try {
            $serveriid = Server::find(1);
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();

            if ($API->connect($ip, $user, $password)) {
                Session::put('mikrotik_ip', $ip);
                Session::put('mikrotik_user', $user);
                Session::put('mikrotik_password', $password);
                Session::put('isLoggedIn', true);
                // Log::info('MikroTik login successful', ['ip' => $ip, 'user' => $user]);
                return response()->json(['success' => true]);
            } else {
                Log::error('MikroTik login failed', ['ip' => $ip, 'user' => $user]);
                return response()->json(['success' => false, 'message' => 'Login failed'], 401);
            }
        } catch (\Exception $e) {
            Log::error('Exception during MikroTik login', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Internal Server Error'], 500);
        }
    }

    public function getRealTimeTraffic(Request $request)
    {
        if (!Session::get('isLoggedIn')) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        try {
            $ip = Session::get('mikrotik_ip');
            $user = Session::get('mikrotik_user');
            $password = Session::get('mikrotik_password');

            $API = new RouterosAPI();

            if ($API->connect($ip, $user, $password)) {
                $interfaces = [env('MICROTIK_INTERFACE1'), env('MICROTIK_INTERFACE2')];
                $trafficData = [];

                $username = auth()->user()->name; // Get the logged-in username

                // Log the username to MikroTik
                $API->write('/log/error', false);
                $API->write('=message=User ' . $username . ' fetching traffic data', true);
                $API->read();

                foreach ($interfaces as $interface) {
                    $API->write('/interface/monitor-traffic', false);
                    $API->write('=interface=' . $interface, false);
                    $API->write('=once=', true);
                    $READ = $API->read(false);
                    $ARRAY = $API->parseResponse($READ);
                    $trafficData[$interface] = $ARRAY;
                }
                // $API->disconnect();

                return response()->json($trafficData);
            } else {
                Log::error('Unable to connect to MikroTik device', ['ip' => $ip, 'user' => $user]);
                return response()->json(['error' => 'Unable to connect to MikroTik device'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception during traffic data fetching', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function readScripts(Request $request)
    {
        $title = "Scripts";
        $seletedserver = $request->get('sserver');
        $servers = Server::where('enable', '1')->get();

        if ($seletedserver) {
            $serveriid = Server::find($seletedserver);

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                $scripts = $API->comm('/system/script/print');
                return view('admin.microtik.scripts', compact('title', 'servers', 'scripts', 'seletedserver'));
            }
        }

        return view('microtik.scripts', compact('title', 'servers'));
    }

    public function createScript()
    {
        $title = "Add New Script";
        $servers = Server::where('enable', '1')->get();
        return view('admin.microtik.create_script', compact('title', 'servers'));
    }

    public function storeScript(Request $request)
    {
        $seletedserver = $request->get('sserver');
        $scriptName = $request->input('script_name');
        $scriptSource = $request->input('script_source');

        if ($seletedserver) {
            $serveriid = Server::find($seletedserver);

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                $API->comm('/system/script/add', [
                    'name' => $scriptName,
                    'source' => $scriptSource
                ]);

                activity()->causedBy(auth()->user())->useLog('Script')->log('Script Added: ' . $scriptName);
            }
        }

        return redirect()->route('microtik.scripts');
    }

    public function editScript($id, Request $request)
    {
        $title = "Edit Script";
        $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->query('sserver');

        if ($seletedserver) {
            $serveriid = Server::find($seletedserver);


            // return $serveriid;
            if (!$serveriid) {
                return redirect()->route('microtik.scripts')->with('error', 'Selected server not found.');
            }

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                // Fetch all scripts to check if the connection and command are working
                $scripts = $API->comm('/system/script/print');
            
                // Log::info('Scripts response: ', $scripts);
            
                // if (empty($scripts)) {
                //     return redirect()->route('microtik.scripts')->with('error', 'No scripts found.');
                // }
            
                // Log all script IDs for debugging
                // foreach ($scripts as $scriptItem) {
                //     Log::info('Script ID: ' . $scriptItem['.id']);
                // }
            
                
                // Log::info('Selected ID: ' . $id);
                // Log::info('Found script: ', $script);
                // Log::info('Found script: ', $script);
                // $script['source'] = implode("\n", explode("\n", $script['source']));

                // Ensure the entire script source is retrieved
                // $scriptSource = $script['source'];

                // $script = $API->comm('/system/script/print', [
                //     '.proplist' => 'name,source',
                //     '?id' => $id
                // ]);
            

                // Find the specific script by ID
                $script = collect($scripts)->first(function ($scriptItem) use ($id) {
                    return (string) $scriptItem['.id'] === (string) $id;
                });


                Log::info('Found script: ', $script);
            
                // if (empty($script)) {
                //     return redirect()->route('microtik.scripts')->with('error', 'Script not found.');
                // }
            
                // Ensure the entire script source is retrieved
                $scriptSource = $script['source'];
            
                Log::info('Script source: ' . json_encode($scriptSource));

                Log::info('Script source: ' . $scriptSource);
            
                return view('admin.microtik.edit_script', compact('title', 'servers', 'script', 'seletedserver'));
            }
        }

        // return redirect()->route('microtik.scripts')->with('error', 'No server selected.');
    }

    public function updateScript(Request $request, $id)
    {
        $seletedserver = $request->get('sserver');
        $scriptSource = $request->input('script_source');

        if ($seletedserver) {
            $serveriid = Server::find($seletedserver);

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                $API->comm('/system/script/set', [
                    '.id' => $id,
                    'source' => $scriptSource
                ]);

                activity()->causedBy(auth()->user())->useLog('Script')->log('Script Edited: ' . $id);
            }
        }

        return redirect()->route('microtik.scripts');
    }

    public function deleteScript($id)
    {
        $seletedserver = request()->get('sserver');

        if ($seletedserver) {
            $serveriid = Server::find($seletedserver);

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                $API->comm('/system/script/remove', [
                    '.id' => $id
                ]);

                activity()->causedBy(auth()->user())->useLog('Script')->log('Script Deleted: ' . $id);
            }
        }

        return redirect()->route('microtik.scripts');
    }

    public function runScript($id)
    {
        $seletedserver = request()->get('sserver');

        if ($seletedserver) {
            $serveriid = Server::find($seletedserver);

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                $API->comm('/system/script/run', [
                    '.id' => $id
                ]);

                activity()->causedBy(auth()->user())->useLog('Script')->log('Script Executed: ' . $id);
            }
        }

        return redirect()->route('microtik.scripts');
    }

    public function viewLogs(Request $request)
    {
        $title = "View Logs";
        $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->query('sserver');

        if ($seletedserver) {
            $serveriid = Server::find($seletedserver);

            if (!$serveriid) {
                return response()->json(['error' => 'Selected server not found'], 404);
            }

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                // Fetch logs
                $logs = $API->comm('/log/print');
                $logs = array_reverse($logs);

                Log::info('API Response: ' . json_encode($logs));

                return response()->json($logs);
            } else {
                Log::error('Failed to connect to the MikroTik router.');
                return response()->json(['error' => 'Failed to connect to the MikroTik router'], 500);
            }
        }

        return view('admin.microtik.logs', compact('title', 'servers', 'seletedserver'));
    }

    public function viewLog(Request $request)
    {
        $title = "View Microtik Logs";
        $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->query('sserver');
        $serveriid = Server::find($seletedserver);
        // Log::info('Server ID: ' . $serveriid);
        if ($serveriid) {
            // $serveriid = Server::find($seletedserver);

            if (!$serveriid) {
                return response()->json(['error' => 'Selected server not found'], 404);
            }

            if($request->ajax()){
                // Log::info('Entered Ajax');
                $logs=[];
    
                if($serveriid){
    
                    $ip = $serveriid->mip;
                    $user = $serveriid->username;
                    $password = $serveriid->password;
    
                    $API = new RouterosAPI();
                    $API->debug = false;
    
                    if ($API->connect($ip, $user, $password)) {
    
                        // Log the username to MikroTik
                        $logs = $API->comm('/log/print');
                        $logs = array_reverse($logs);

                        $logs = array_filter($logs, function ($log) {
                            return !preg_match('/system.*info.*account/i', $log['topics']);
                        });
                        return DataTables::of($logs)
                                ->addIndexColumn()
                                ->addColumn('time1',function ($data){
                                    $time = $data['time'];
                                    return $time;
                                })
                                ->addColumn('topics1',function ($data){
                                    $topics = $data['topics'];
                                    return $topics;
                                })
                            
                                ->rawColumns(['time1','topics1'])
                                ->make(true);
    
                        $API->disconnect(); 
                        }
                }
    
            }

        }

        return view('microtik.log', compact('title', 'seletedserver','servers'));
    }

    public function getSystemHealth(Request $request)
    {
        $title = "System Health";
        // $servers = Server::where('enable', '1')->get();
        // $seletedserver = $request->query('sserver');

        // if ($seletedserver) {
        //     $server = Server::find($seletedserver);
        //     if (!$server) {
        //         return response()->json(['error' => 'Selected server not found'], 404);
        //     }

        //     $ip = $server->mip;
        //     $user = $server->username;
        //     $password = $server->password;

        //     $API = new RouterosAPI();
        //     $API->debug = false;

        //     if ($API->connect($ip, $user, $password)) {
        //         // Fetch system health
        //         $health = $API->comm('/system/health/print');
        //         $API->disconnect();

        //         // Log::info('System Health: ' . json_encode($health));

        //         return response()->json($health);
        //     } else {
        //         Log::error('Failed to connect to the MikroTik router.');
        //         return response()->json(['error' => 'Failed to connect to the MikroTik router'], 500);
        //     }
        // }

        // return view('microtik.system_health', compact('title', 'servers', 'seletedserver'));
        return view('microtik.system_health', compact('title'));
        
    }

    public function getIpNeighbors(Request $request)
    {
        $title = "IP Neighbors";
        $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->query('sserver');

        if ($seletedserver) {
            $server = Server::find($seletedserver);
            if (!$server) {
                return response()->json(['error' => 'Selected server not found'], 404);
            }

            $ip = $server->mip;
            $user = $server->username;
            $password = $server->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                // Fetch IP neighbors
                $neighbors = $API->comm('/ip/neighbor/print');
                $API->disconnect();

                Log::info('IP Neighbors: ' . json_encode($neighbors));

                return response()->json($neighbors);
            } else {
                Log::error('Failed to connect to the MikroTik router.');
                return response()->json(['error' => 'Failed to connect to the MikroTik router'], 500);
            }
        }

        return view('microtik.ip_neighbors', compact('title', 'servers', 'seletedserver'));
    }

    public function getPppTraffic(Request $request)
    {
        $serverId = $request->query('sserver');
        $interface = $request->query('interface');

        if ($serverId && $interface) {
            // Fetch live traffic data for the specific interface
            $data = $this->fetchLiveTrafficData($serverId, $interface);

            return response()->json($data);
        } elseif ($serverId) {
            // Fetch PPP interface traffic data from the MikroTik router
            $data = $this->fetchPppTrafficData($serverId);

            return response()->json($data);
        } else {
            $title = "PPP Traffic";
            $servers = Server::where('enable', '1')->get();
            $seletedserver = $request->query('sserver');

            return view('admin.microtik.ppp_traffic', compact('title', 'servers', 'seletedserver'));
        }
    }

    private function fetchPppTrafficData($serverId)
    {
        $server = Server::find($serverId);
        if (!$server) {
            return ['error' => 'Selected server not found'];
        }

        $ip = $server->mip;
        $user = $server->username;
        $password = $server->password;

        $API = new RouterosAPI();
        $API->debug = false;

        try {
            if ($API->connect($ip, $user, $password)) {
                // Fetch PPP interface data
                $responses = $API->comm('/interface/print');
                $API->disconnect();

                $data = [];
                foreach ($responses as $response) {
                    $rxBytes = $response['rx-byte'] ?? 0;
                    $txBytes = $response['tx-byte'] ?? 0;

                    // Convert bytes to Megabits per second (Mbps)
                    $rxMbps = ($rxBytes * 8) / (1024 * 1024);
                    $txMbps = ($txBytes * 8) / (1024 * 1024);

                    $data[] = [
                        'name' => $response['name'] ?? '',
                        'type' => $response['type'] ?? '',
                        'rx-mbps' => round($rxMbps, 2),
                        'tx-mbps' => round($txMbps, 2),
                        'running' => $response['running'] ?? '',
                    ];
                }

                return $data;
            } else {
                Log::error('Failed to connect to the MikroTik router.');
                return ['error' => 'Failed to connect to the MikroTik router'];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching PPP interface traffic: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    private function fetchLiveTrafficData($serverId, $interface)
    {
        $server = Server::find($serverId);
        if (!$server) {
            return ['error' => 'Selected server not found'];
        }

        $ip = $server->mip;
        $user = $server->username;
        $password = $server->password;

        $API = new RouterosAPI();
        $API->debug = false;

        try {
            if ($API->connect($ip, $user, $password)) {
                // Fetch live traffic data for the specific interface
                $responses = $API->comm('/interface/monitor-traffic', [
                    'interface' => $interface,
                    'once' => true
                ]);
                $API->disconnect();

                if (empty($responses)) {
                    return ['error' => 'No data received from the MikroTik router'];
                }

                $response = $responses[0];
                $rxBytes = $response['rx-bits-per-second'] ?? 0;
                $txBytes = $response['tx-bits-per-second'] ?? 0;

                // Convert bits per second to Megabits per second (Mbps)
                $rxMbps = $rxBytes / (1024 * 1024);
                $txMbps = $txBytes / (1024 * 1024);

                return [
                    'rx-mbps' => round($rxMbps, 2),
                    'tx-mbps' => round($txMbps, 2)
                ];
            } else {
                Log::error('Failed to connect to the MikroTik router.');
                return ['error' => 'Failed to connect to the MikroTik router'];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching live traffic data: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function showTrafficChart(Request $request)
    {
        $serverId = $request->query('serverId');
        $username = $request->query('username');
        $title = "Traffic Chart - $username";

        return view('admin.microtik.traffic-chart', compact('serverId', 'username','title'));
    }

    public function showServices(Request $request)
    {
        $servers = Server::where('enable', '1')->get();
        $serverId = $request->query('serverId');
        $username = $request->query('username');
        $title = "Services";

        return view('microtik.services', compact('servers','serverId', 'username','title'));
    }

    public function showServiceStatus(Request $request)
    {
        $pptpStatus = 'disabled';
        $l2tpStatus = 'disabled';
        $telnetStatus = 'disabled';
        $wwwsslStatus = 'disabled';
        $wwwStatus = 'disabled';
        $sshStatus = 'disabled';
        $winboxStatus = 'disabled';

        $seletedserver = $request->query('sserver');
        if (!$seletedserver) {
            return ['error' => 'No server selected'];
        }
        // Log::info('Selected server: '. $seletedserver);
        $serveriid = Server::find($seletedserver);

        $ip = $serveriid->mip;
        $user = $serveriid->username;
        $password = $serveriid->password;

        // Log::info('Ip '. $ip . ' User: '. $user . ' Password: '. $password);
        $API = new RouterosAPI();
        $API->debug = false;
        if ($API->connect($ip, $user, $password)){
            // $pptpStatus = 'disabled';
            $response = $API->comm('/interface/pptp-server/server/print');
            if (isset($response[0]['enabled']) && $response[0]['enabled'] === 'true') {
                $pptpStatus = 'enabled';
            }

            $response1 = $API->comm('/interface/l2tp-server/server/print');
            if (isset($response1[0]['enabled']) && $response1[0]['enabled'] === 'true') {
                $l2tpStatus = 'enabled';
            }

            $response3 = $API->comm('/ip/service/print');
            $telnet = array_filter($response3, function ($item) {
                return $item['name'] === 'telnet';
            });
            // Log::info('Ip Services : '. json_encode($response3));
            // Log::info('Telnet: '. json_encode($telnet));
            if (isset($telnet[0]['disabled']) && $telnet[0]['disabled'] === 'false') {
                $telnetStatus = 'enabled';
            }
            // Log::info('Status of Telnet: '. $telnet[0]['disabled']);

            // $response2 = $API->comm('/ip/service/print');
            $wwwssl = array_filter($response3, function ($item) {
                return $item['name'] === 'www-ssl';
            });
            // Log::info('Ip Services : '. json_encode($response3));
            // Log::info('wwwssl: '. json_encode($wwwssl));
            if (isset($wwwssl['4']['disabled']) && $wwwssl['4']['disabled'] === 'false') {
                $wwwsslStatus = 'enabled';
            }
            // Log::info('Status of wwwssl: '. $wwwssl[4]['disabled']);

            // $response4 = $API->comm('/ip/service/print');
            $www = array_filter($response3, function ($item) {
                return $item['name'] === 'www';
            });
            // Log::info('Ip Services : '. json_?encode($response3));
            // Log::info('wwwssl: '. json_encode($wwwssl));
            if (isset($www['2']['disabled']) && $www['2']['disabled'] === 'false') {
                $wwwStatus = 'enabled';
            }
            // Log::info('Status of wwwssl: '. $wwwssl[4]['disabled']);

            // $response5 = $API->comm('/ip/service/print');
            $ssh = array_filter($response3, function ($item) {
                return $item['name'] === 'ssh';
            });
            // Log::info('Ip Services : '. json_encode($response3));
            // Log::info('wwwssl: '. json_encode($wwwssl));
            if (isset($ssh['3']['disabled']) && $ssh['3']['disabled'] === 'false') {
                $sshStatus = 'enabled';
            }
            // Log::info('Status of wwwssl: '. $wwwssl[4]['disabled']);

            // $response6 = $API->comm('/ip/service/print');
            $winbox = array_filter($response3, function ($item) {
                return $item['name'] === 'winbox';
            });
            // Log::info('Ip Services : '. json_encode($response6));
            // Log::info('wwwssl: '. json_encode($wwwssl));
            if (isset($winbox['6']['disabled']) && $winbox['6']['disabled'] === 'false') {
                $winboxStatus = 'enabled';
            }
            // Log::info('Status of wwwssl: '. $wwwssl[4]['disabled']);
        }
        // Log::info('Pptp Status: '. $pptpStatus);
        return ['pptpStatus' => $pptpStatus, 'l2tpStatus' => $l2tpStatus, 'telnetStatus' => $telnetStatus, 'wwwsslStatus' => $wwwsslStatus, 'sshStatus' => $sshStatus, 'wwwStatus' => $wwwStatus, 'winboxStatus' => $winboxStatus];
        
    }

    public function updatePptp(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $pptpEnabled = $request->input('pptp_enabled');
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        // Log::info('Pptp Status: '. $pptpEnabled);
        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;
            if ($API->connect($ip, $user, $password)) {
            // $routerosAPI = new RouterosAPI($ip, $user, $password);
                
                if ($pptpEnabled == 'true') {
                    $API->write('/interface/pptp-server/server/set', false);
                    $API->write('=enabled=yes');
                    $API->read();
                    // Log::info('Pptp enabled for Selected Server:'. $seletedserver);
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('pptp.enable');

                } else {
                    $API->write('/interface/pptp-server/server/set', false);
                    $API->write('=enabled=no');
                    $API->read();
                    // Log::info('Pptp disabled for Selected Server:'. $seletedserver);
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('pptp.disable');
                }
                return $alertMessage;
            }
        }
    }

    public function updateL2tp(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $l2tpEnabled = $request->input('l2tp_enabled');
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        // Log::info('Pptp Status: '. $pptpEnabled);
        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;
            if ($API->connect($ip, $user, $password)) {
            // $routerosAPI = new RouterosAPI($ip, $user, $password);
                
                if ($l2tpEnabled == 'true') {
                    $API->write('/interface/l2tp-server/server/set', false);
                    $API->write('=enabled=yes');
                    $API->read();
                    // Log::info('Pptp enabled for Selected Server:'. $seletedserver);
                    $message = 'L2TP service enabled successfully!';

                } else {
                    $API->write('/interface/l2tp-server/server/set', false);
                    $API->write('=enabled=no');
                    $API->read();
                    // Log::info('Pptp disabled for Selected Server:'. $seletedserver);
                    $message = 'L2TP service disabled successfully!';
                }
                return response()->json(['message' => $message]);
            }
        }
    }

    public function updateTelnet(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $telnetEnabled = $request->input('telnet_enabled');
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        // Log::info('Telnet Status: '. $telnetEnabled);
        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;


            if ($API->connect($ip, $user, $password)) {

                if ($telnetEnabled == 'true') {
                    $API->write('/ip/service/set', false);
                    $API->write('=numbers=telnet', false);
                    $API->write('=disabled=no');
                    $API->read();
                    // Log::info('Telnet enabled for Selected Server:'. $seletedserver);
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('telnet.enable');
                    // $message = $alertMessage;

                } else {
                    $API->write('/ip/service/set', false);
                    $API->write('=numbers=telnet', false);
                    $API->write('=disabled=yes');
                    $API->read();
                    // Log::info('Telnet disabled for Selected Server:'. $seletedserver);
                    // $message = 'Telnet service disabled successfully!';
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('telnet.disable');
                    // $message = $alertMessage;
                }
                return $alertMessage;
            }
        }

    }

    public function updateWwwssl(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $wwwsslEnabled = $request->input('wwwssl_enabled');
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {

                if ($wwwsslEnabled == 'true') {
                    $API->write('/ip/service/set', false);
                    $API->write('=numbers=www-ssl', false);
                    $API->write('=disabled=no');
                    $API->read();
                    // Log::info('Telnet enabled for Selected Server:'. $seletedserver);
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('wwwssl.enable');

                } else {
                    $API->write('/ip/service/set', false);
                    $API->write('=numbers=www-ssl', false);
                    $API->write('=disabled=yes');
                    $API->read();
                    // Log::info('Telnet disabled for Selected Server:'. $seletedserver);
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('wwwssl.disable');
                }
                return $alertMessage;
            }
        }

    }

    public function updateWww(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $wwwEnabled = $request->input('www_enabled');
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        // Log::info('Telnet Status: '. $telnetEnabled);
        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {

                if ($wwwEnabled == 'true') {
                    $API->write('/ip/service/set', false);
                    $API->write('=numbers=www', false);
                    $API->write('=disabled=no');
                    $API->read();
                    // Log::info('Telnet enabled for Selected Server:'. $seletedserver);
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('www.enable');

                } else {
                    $API->write('/ip/service/set', false);
                    $API->write('=numbers=www', false);
                    $API->write('=disabled=yes');
                    $API->read();
                    // Log::info('Telnet disabled for Selected Server:'. $seletedserver);
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('www.disable');
                }
                return $alertMessage;
            }
        }

    }

    public function updateSsh(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $sshEnabled = $request->input('ssh_enabled');
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                // Log::info('Connected to Router IP: '. $ip);

                if ($sshEnabled == 'true') {
                    $API->write('/ip/service/set', false);
                    $API->write('=numbers=ssh', false);
                    $API->write('=disabled=no');
                    $API->read();
                    // Log::info('Telnet enabled for Selected Server:'. $seletedserver);
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('ssh.enable');

                } else {
                    $API->write('/ip/service/set', false);
                    $API->write('=numbers=ssh', false);
                    $API->write('=disabled=yes');
                    $API->read();
                    // Log::info('Telnet disabled for Selected Server:'. $seletedserver);
                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('ssh.disable');
                }
                return $alertMessage;
            }
        }
    }

    public function updateWinbox(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $wwwsslEnabled = $request->input('winbox_enabled');
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        // Log::info('Telnet Status: '. $telnetEnabled);
        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {

                if ($wwwsslEnabled == 'true') {
                    $API->write('/ip/service/set', false);
                    $API->write('=numbers=winbox', false);
                    $API->write('=disabled=no');
                    $API->read();

                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('winbox.enable');
                } else {
                    $API->write('/ip/service/set', false);
                    $API->write('=numbers=winbox', false);
                    $API->write('=disabled=yes');
                    $API->read();

                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('winbox.disable');
                }
                return $alertMessage;
            }
        }

    }

    public function getSystemHistory(Request $request)
    {
        $title = "System History";
        $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->query('sserver');

        if ($seletedserver) {
            $server = Server::find($seletedserver);
            if (!$server) {
                return response()->json(['error' => 'Selected server not found'], 404);
            }

            $ip = $server->mip;
            $user = $server->username;
            $password = $server->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                // Fetch IP neighbors
                $systemHistory = $API->comm('/system/history/print');
                $API->disconnect();

                // Log::info('System History: ' . json_encode($systemHistory));

                return response()->json($systemHistory);
            } else {
                Log::error('Failed to connect to the MikroTik router.');
                return response()->json(['error' => 'Failed to connect to the MikroTik router'], 500);
            }
        }

        return view('microtik.history', compact('title', 'servers', 'seletedserver'));
        
        // $api = new RouterOSAPI();
        // $api->connect('192.168.88.1', 'admin', 'password');

        // $systemHistory = $api->comm('/system/history/print');

        // return response()->json($systemHistory);
    }

    public function getSystemHistoryApi(Request $request)
    {
        // $title = "System History";
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->serverid;

        if ($seletedserver) {
            $server = Server::find($seletedserver);
            if (!$server) {
                return response()->json(['error' => 'Selected server not found'], 404);
            }

            $ip = $server->mip;
            $user = $server->username;
            $password = $server->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                // Fetch IP neighbors
                $systemHistory = $API->comm('/system/history/print');
                $API->disconnect();

                // Log::info('System History: ' . json_encode($systemHistory));
                activity()
                ->causedBy(auth()->user())
                ->useLog('System History - API')
                ->log('System History fetched for server: ' . $server->name);

                return response()->json($systemHistory);
            } else {
                Log::error('Failed to connect to the MikroTik router.');
                return response()->json(['error' => 'Failed to connect to the MikroTik router'], 500);
            }
        }
    }

    public function viewCommand(Request $request)
    {
        $title = "System Command";
        $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->query('sserver');

        
        return response()->view('microtik.command', compact('title', 'servers', 'seletedserver'));
    }

    public function runCommand(Request $request){
        try{
            $seletedserver = $request->input('sserver');
            if ($seletedserver) {
                $server = Server::find($seletedserver);
                if (!$server) {
                    return response()->json(['error' => 'Selected server not found'], 404);
                }

                $ip = $server->mip;
                $user = $server->username;
                $password = $server->password;
            
                $API = new RouterosAPI();
                $API->debug = true;
                if ($API->connect($ip, $user, $password)) {
                    $command = trim($request->input('command'));
                    if (!empty($command)) {
                        // $response = $API->comm($command);
                        $command = strval($command);
                        // $commandArray = explode(" ", $command);
                        $API->write($command);
                        $response = $API->read();
                        // if (isset($response['!trap'])) {
                        //     return response()->json(['error' => 'Invalid command', 'details' => $response['!trap']], 400);
                        // }
                    }
                    // $API->disconnect();
                    // return response()->json(['success' => true, 'command' => $command, 'response' => $response]);
                    if (isset($response['!trap'])) { //check if the router returned an error.
                        return response()->json([
                            'success' => false,
                            'error' => $response['!trap'][0]['message'],
                        ], 500);
                    }
    
                    return response()->json([
                        'success' => true,
                        'results' => $response,
                    ]);
                }
            } else {
                return response()->json(['error' => 'Selected server not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}