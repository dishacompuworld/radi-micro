<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use Illuminate\Support\Facades\Session;
use App\Models\Server;
use App\Models\RouterosAPI as LegacyRouterosAPI;
use App\Services\RouterosClientAdapter as RouterosAPI;
use App\Support\RouterosServiceStatus;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class NewMicrotikController extends Controller
{
    private $api;
    private $server;
    private $connectionTimeout = 15;
    private $maxRetries = 3;

    public function __construct()
    {
        // $this->middleware('role:super-admin','permission:add-server',['only' => ['create','store']]); role example
        $this->middleware('permission:view-sheduler',['only' => ['shedule']]);
        $this->middleware('permission:view-script',['only' => ['script']]);
        // $this->middleware('permission:view-microtik-logs',['only' => ['viewLogs']);

        $this->middleware('permission:view-sheduler',['only' => ['shedule']]);
        $this->middleware('permission:view-script',['only' => ['script']]);
        $this->middleware('permission:view-microtik-logs',['only' => ['viewLog']]);
        $this->middleware('permission:view-system-health',['only' => ['getSystemHealth']]);
        $this->middleware('permission:view-neighbors',['only' => ['getIpNeighbors']]);
        $this->middleware('permission:view-services',['only' => ['showServices','showServiceStatus','updatePptp','updateL2tp','updateTelnet','updateWwwssl','updateWww','updateSsh','updateWinbox']]);
        //
    }

    /**
     * Create and run actions with an evilfreelancer RouterOS client.
     * The callback receives the created client and should return any value needed.
     */
    private function withClient(string $ip, string $user, string $password, callable $callback)
    {
        $config = (new Config())
            ->set('host', $ip)
            ->set('user', $user)
            ->set('pass', $password)
            ->set('port', 8728)
            ->set('timeout', $this->connectionTimeout)
            ->set('socket_timeout', max(15, $this->connectionTimeout * 2))
            ->set('ssl', false);

        $client = new Client($config);
        try {
            return $callback($client);
        } finally {
            if (method_exists($client, 'disconnect')) {
                try { $client->disconnect(); } catch (\Throwable $e) { Log::warning($e->getMessage()); }
            }
        }
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

    private function normalizeLogItems(array $rawLogs): array
    {
        $normalized = [];

        foreach ($rawLogs as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (isset($item['message'])) {
                $message = $item['message'];
            } elseif (isset($item['msg'])) {
                $message = $item['msg'];
            } elseif (isset($item['log'])) {
                $message = $item['log'];
            } else {
                $message = '';
            }

            if (isset($item['topics'])) {
                $topics = $item['topics'];
                if (is_array($topics)) {
                    $topics = implode(', ', $topics);
                }
            } elseif (isset($item['topic'])) {
                $topics = $item['topic'];
            } else {
                $topics = '';
            }

            if (isset($item['time'])) {
                $time = $item['time'];
            } elseif (isset($item['timestamp'])) {
                $ts = $item['timestamp'];
                if (is_numeric($ts)) {
                    $time = date('Y-m-d H:i:s', (int)$ts);
                } else {
                    $time = $ts;
                }
            } else {
                $time = '';
            }

            if (preg_match('/system.*info.*account/i', $topics)) {
                continue;
            }

            $normalized[] = array_merge($item, [
                'message' => $message,
                'topics' => $topics,
                'time' => $time,
            ]);
        }

        return $normalized;
    }

    public function streamLog(Request $request)
    {
        $serverId = $request->query('sserver');
        $server = Server::find($serverId);

        if (!$server) {
            return response()->json(['error' => 'Selected server not found'], 404);
        }

        return response()->stream(function () use ($server) {
            try {
                Log::info('NewMicrotikController streamLog started', ['server_id' => $server->id]);

                ignore_user_abort(true);
                set_time_limit(0);
                session_write_close();

                if (function_exists('apache_setenv')) {
                    @apache_setenv('no-gzip', '1');
                }
                @ini_set('zlib.output_compression', '0');
                @ini_set('output_buffering', 'off');
                @ini_set('implicit_flush', '1');
                while (ob_get_level() > 0) {
                    @ob_end_flush();
                }
                ob_implicit_flush(true);
                echo str_repeat(' ', 4096);
                @flush();

                $legacy = new LegacyRouterosAPI();
                $legacy->debug = false;

                if (!$legacy->connect($server->mip, $server->username, $server->password)) {
                    echo json_encode(['type' => 'error', 'message' => 'Unable to login to MikroTik']) . "\n";
                    @flush();
                    return;
                }

                echo json_encode(['type' => 'status', 'status' => 'logged_in']) . "\n";
                @flush();

                while (!connection_aborted()) {
                    try {
                        $rawLogs = $legacy->comm('/log/print');
                        $normalized = $this->normalizeLogItems(is_array($rawLogs) ? array_reverse($rawLogs) : []);
                        echo json_encode(['type' => 'logs', 'logs' => $normalized]) . "\n";
                        @flush();
                    } catch (\Throwable $e) {
                        echo json_encode(['type' => 'error', 'message' => $e->getMessage()]) . "\n";
                        @flush();
                    }

                    sleep(5);
                }

                try {
                    $legacy->disconnect();
                    echo json_encode(['type' => 'status', 'status' => 'logged_out']) . "\n";
                    @flush();
                } catch (\Throwable $e) {
                    Log::warning('Error disconnecting MikroTik stream: ' . $e->getMessage());
                }
            } catch (\Throwable $e) {
                Log::error('NewMicrotikController streamLog failure', ['server_id' => $server->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                echo json_encode(['type' => 'error', 'message' => 'Stream failed: ' . $e->getMessage()]) . "\n";
                @flush();
            }
        }, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
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

                return view('microtik.shedule', compact('title','servers','shedules','seletedserver'));
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

    public function microtikLogin(Request $request)
    {
        $serverId = $request->input('sserver');
        if (!$serverId) {
            return response()->json(['success' => false, 'message' => 'Server selection required'], 400);
        }

        $server = Server::find($serverId);
        if (!$server) {
            return response()->json(['success' => false, 'message' => 'Selected server not found'], 404);
        }

        try {
            $ip = $server->mip;
            $user = $server->username;
            $password = $server->password;

            $API = new RouterosAPI();
            if ($API->connect($ip, $user, $password)) {
                $API->disconnect();
                Session::put('mikrotik_ip', $ip);
                Session::put('mikrotik_user', $user);
                Session::put('mikrotik_password', $password);
                Session::put('mikrotik_server_id', $serverId);
                Session::put('isLoggedIn', true);
                return response()->json(['success' => true]);
            }

            Log::error('MikroTik login failed', ['server_id' => $serverId, 'ip' => $ip, 'user' => $user]);
            return response()->json(['success' => false, 'message' => 'Unable to connect to MikroTik'], 401);
        } catch (\Exception $e) {
            Log::error('Exception during MikroTik login', ['error' => $e->getMessage(), 'server_id' => $serverId]);
            return response()->json(['success' => false, 'message' => 'Internal Server Error'], 500);
        }
    }

    public function microtikLogout(Request $request)
    {
        Session::forget(['mikrotik_ip', 'mikrotik_user', 'mikrotik_password', 'mikrotik_server_id', 'isLoggedIn']);
        return response()->json(['success' => true]);
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
                return view('microtik.scripts', compact('title', 'servers', 'scripts', 'seletedserver'));
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

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                // Log::info('Entered Ajax/JSON request');
                $logs=[];
    
                if($serveriid){
    
                    $ip = $serveriid->mip;
                    $user = $serveriid->username;
                    $password = $serveriid->password;
    
                    // Primary: attempt legacy socket adapter first (more reliable historically)
                    $rawLogs = [];
                    $rawSource = 'none';

                    try {
                        $legacy = new LegacyRouterosAPI();
                        $legacy->debug = false;
                        if ($legacy->connect($ip, $user, $password)) {
                            Log::info('NewMicrotikController: fetched logs using legacy adapter');
                            $rawLogs = $legacy->comm('/log/print');
                            Log::info('NewMicrotikController legacy raw type: ' . gettype($rawLogs) . ' count: ' . (is_array($rawLogs) ? count($rawLogs) : 0));
                            Log::info('NewMicrotikController legacy sample: ' . json_encode(is_array($rawLogs) ? array_slice($rawLogs, 0, 5) : $rawLogs));
                            $legacy->disconnect();
                            $rawSource = 'legacy';
                        }
                    } catch (\Throwable $e) {
                        Log::warning('NewMicrotikController legacy primary attempt failed: ' . $e->getMessage());
                        $rawLogs = [];
                    }

                    // If legacy returned nothing, try the new client as a fallback
                    $alternate = null;
                    $API = null;
                    if (empty($rawLogs)) {
                        $API = new RouterosAPI();
                        $API->debug = false;
                        if ($API->connect($ip, $user, $password)) {
                            Log::info("NewMicrotikController: connected to {$ip} (new client fallback)");
                            $rawLogs = $API->comm('/log/print');
                            Log::info('NewMicrotikController viewLog raw type: ' . gettype($rawLogs) . ' count: ' . (is_array($rawLogs) ? count($rawLogs) : 0));
                            Log::info('NewMicrotikController viewLog raw sample: ' . json_encode(is_array($rawLogs) ? array_slice($rawLogs, 0, 5) : $rawLogs));
                            $rawLogs = is_array($rawLogs) ? array_reverse($rawLogs) : [];

                            if (empty($rawLogs)) {
                                try {
                                    $alternate = $API->comm('/log/print', ['.proplist' => 'time,topics,message', 'limit' => 20]);
                                    Log::info('NewMicrotikController viewLog alternate raw type: ' . gettype($alternate) . ' count: ' . (is_array($alternate) ? count($alternate) : 0));
                                    Log::info('NewMicrotikController viewLog alternate sample: ' . json_encode(is_array($alternate) ? array_slice($alternate, 0, 5) : $alternate));
                                    $alternate = is_array($alternate) ? array_reverse($alternate) : [];
                                } catch (\Throwable $e) {
                                    Log::warning('NewMicrotikController alternate log fetch failed: ' . $e->getMessage());
                                    $alternate = null;
                                }
                            }

                            $API->disconnect();
                            $rawSource = 'new';
                        }
                    }

                        $normalized = [];
                        foreach ($rawLogs as $item) {
                            if (!is_array($item)) continue;

                            if (isset($item['message'])) {
                                $message = $item['message'];
                            } elseif (isset($item['msg'])) {
                                $message = $item['msg'];
                            } elseif (isset($item['log'])) {
                                $message = $item['log'];
                            } else {
                                $message = '';
                            }

                            if (isset($item['topics'])) {
                                $topics = $item['topics'];
                                if (is_array($topics)) {
                                    $topics = implode(', ', $topics);
                                }
                            } elseif (isset($item['topic'])) {
                                $topics = $item['topic'];
                            } else {
                                $topics = '';
                            }

                            if (isset($item['time'])) {
                                $time = $item['time'];
                            } elseif (isset($item['timestamp'])) {
                                $ts = $item['timestamp'];
                                if (is_numeric($ts)) {
                                    $time = date('Y-m-d H:i:s', (int)$ts);
                                } else {
                                    $time = $ts;
                                }
                            } else {
                                $time = '';
                            }

                            if (preg_match('/system.*info.*account/i', $topics)) {
                                continue;
                            }

                            $normalized[] = array_merge($item, [
                                'message' => $message,
                                'topics' => $topics,
                                'time' => $time,
                            ]);
                        }

                        Log::info('NewMicrotikController viewLog normalized count: ' . count($normalized));

                        $normalized = array_reverse($normalized);

                        // If debug_raw param present, return raw, alternate and normalized JSON for easier troubleshooting
                        if ($request->query('debug_raw')) {
                            if ($API !== null) {
                                $API->disconnect();
                            }
                            return response()->json(['raw' => $rawLogs, 'alternate' => $alternate, 'normalized' => $normalized]);
                        }

                        $response = DataTables::of($normalized)
                                ->addIndexColumn()
                                ->addColumn('time1', function ($data) {
                                    return $data['time'] ?? '';
                                })
                                ->addColumn('topics1', function ($data) {
                                    return $data['topics'] ?? '';
                                })
                                ->rawColumns(['time1','topics1'])
                                ->make(true);

                        if ($API !== null) {
                            $API->disconnect();
                        }
                        return $response;
                    }
                }
    
            }

        
        return view('microtik.log', compact('title', 'seletedserver','servers'));
    }

    public function getSystemHealth(Request $request)
    {
        $title = 'System Health';
        $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->query('sserver');

        if ($seletedserver) {
            $server = Server::find($seletedserver);
            if (!$server) {
                return response()->json(['error' => 'Selected server not found'], 404);
            }

            try {
                $health = $this->withClient(
                    $server->mip,
                    $server->username,
                    $server->password,
                    function (Client $client) {
                        return $client->query('/system/health/print')->read();
                    }
                );

                return response()->json($health ?: []);
            } catch (\Throwable $e) {
                Log::error('Failed to fetch MikroTik system health.', [
                    'server_id' => $seletedserver,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'error' => 'Failed to connect to the MikroTik router'
                ], 500);
            }
        }

        return view('microtik.system_health', compact('title', 'servers', 'seletedserver'));
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
        $serverId = $request->query('sserver', $request->query('serverId'));
        $interface = $request->query('interface', $request->query('interfaceName'));

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
            $seletedserver = $request->query('sserver', $request->query('serverId'));

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
        $normalizedInterface = trim((string) str_replace(['<', '>'], '', $interface));
        $rawInterface = trim((string) $interface);

        Log::info('Fetching live traffic', [
            'server_id' => $serverId,
            'original_interface' => $rawInterface,
            'normalized_interface' => $normalizedInterface,
        ]);

        $attempts = array_values(array_unique([
            $rawInterface,
            $normalizedInterface,
            "<$normalizedInterface>",
            "pppoe-" . ltrim($normalizedInterface, 'pppoe-'),
            ltrim($normalizedInterface, 'pppoe-'),
            "<$rawInterface>",
        ], SORT_STRING));

        $config = (new Config())
            ->set('host', $ip)
            ->set('user', $user)
            ->set('pass', $password)
            ->set('port', 8728)
            ->set('timeout', $this->connectionTimeout)
            ->set('socket_timeout', max(15, $this->connectionTimeout * 2))
            ->set('ssl', false);

        $client = new Client($config);

        try {
            foreach ($attempts as $attempt) {
                $query = (new Query('/interface/monitor-traffic'))
                    ->equal('interface', $attempt)
                    ->equal('once');

                $responses = $client->query($query)->read();

                if (!empty($responses)) {
                    $response = is_array($responses[0] ?? null) ? $responses[0] : [];
                    $rxBytes = (float) ($response['rx-bits-per-second'] ?? 0);
                    $txBytes = (float) ($response['tx-bits-per-second'] ?? 0);

                    Log::info('Live traffic response', [
                        'attempt' => $attempt,
                        'response' => $response,
                        'raw_responses' => $responses,
                    ]);

                    return [
                        'rx-mbps' => round(($rxBytes / 1000 / 1000), 4),
                        'tx-mbps' => round(($txBytes / 1000 / 1000), 4),
                        'rx-bps' => $rxBytes,
                        'tx-bps' => $txBytes,
                    ];
                }
            }

            return ['error' => 'No live traffic data received for this interface'];
        } catch (\Throwable $e) {
            Log::error('Error fetching live traffic data: ' . $e->getMessage(), [
                'server_id' => $serverId,
                'interface' => $interface,
            ]);
            return ['error' => 'Socket timeout or router query failed: ' . $e->getMessage()];
        } finally {
            if (method_exists($client, 'disconnect')) {
                try {
                    $client->disconnect();
                } catch (\Throwable $e) {
                    Log::warning('Failed to disconnect RouterOS client: ' . $e->getMessage());
                }
            }
        }
    }

    public function showTrafficChart($serverId, $username)
    {
        $title = "Traffic Chart - $username";

        return view('microtik.traffic-chart', compact('serverId', 'username', 'title'));
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
            $services = is_array($response3) ? $response3 : [];

            $telnetStatus = RouterosServiceStatus::getServiceStatus($services, 'telnet');
            $wwwsslStatus = RouterosServiceStatus::getServiceStatus($services, 'www-ssl');
            $wwwStatus = RouterosServiceStatus::getServiceStatus($services, 'www');
            $sshStatus = RouterosServiceStatus::getServiceStatus($services, 'ssh');
            $winboxStatus = RouterosServiceStatus::getServiceStatus($services, 'winbox');
        }
        // Log::info('Pptp Status: '. $pptpStatus);
        return ['pptpStatus' => $pptpStatus, 'l2tpStatus' => $l2tpStatus, 'telnetStatus' => $telnetStatus, 'wwwsslStatus' => $wwwsslStatus, 'sshStatus' => $sshStatus, 'wwwStatus' => $wwwStatus, 'winboxStatus' => $winboxStatus];
        
    }

    public function updatePptp(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $pptpEnabled = RouterosServiceStatus::isEnabled($request->input('pptp_enabled'));
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        // Log::info('Pptp Status: '. $pptpEnabled);
        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new LegacyRouterosAPI();
            $API->debug = false;
            if ($API->connect($ip, $user, $password)) {
                if ($pptpEnabled) {
                    $API->comm('/interface/pptp-server/server/set', [
                        'enabled' => 'yes',
                    ]);

                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('pptp.enable');
                } else {
                    $API->comm('/interface/pptp-server/server/set', [
                        'enabled' => 'no',
                    ]);

                    $alertMessage = app('App\Http\Controllers\AlertMessageController')->get('pptp.disable');
                }

                $API->disconnect();
                return $alertMessage;
            }
        }
    }

    public function updateL2tp(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $l2tpEnabled = RouterosServiceStatus::isEnabled($request->input('l2tp_enabled'));
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        // Log::info('Pptp Status: '. $pptpEnabled);
        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new LegacyRouterosAPI();
            $API->debug = false;
            if ($API->connect($ip, $user, $password)) {
                if ($l2tpEnabled) {
                    $API->comm('/interface/l2tp-server/server/set', [
                        'enabled' => 'yes',
                    ]);

                    $message = 'L2TP service enabled successfully!';
                } else {
                    $API->comm('/interface/l2tp-server/server/set', [
                        'enabled' => 'no',
                    ]);

                    $message = 'L2TP service disabled successfully!';
                }

                $API->disconnect();
                return response()->json(['message' => $message]);
            }
        }
    }

    public function updateTelnet(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $telnetEnabled = RouterosServiceStatus::isEnabled($request->input('telnet_enabled'));
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        // Log::info('Telnet Status: '. $telnetEnabled);
        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new LegacyRouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                $API->comm('/ip/service/set', [
                    'numbers' => 'telnet',
                    'disabled' => $telnetEnabled ? 'no' : 'yes',
                ]);

                $alertMessage = $telnetEnabled
                    ? app('App\Http\Controllers\AlertMessageController')->get('telnet.enable')
                    : app('App\Http\Controllers\AlertMessageController')->get('telnet.disable');

                $API->disconnect();
                return $alertMessage;
            }
        }

    }

    public function updateWwwssl(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $wwwsslEnabled = RouterosServiceStatus::isEnabled($request->input('wwwssl_enabled'));
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new LegacyRouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                $API->comm('/ip/service/set', [
                    'numbers' => 'www-ssl',
                    'disabled' => $wwwsslEnabled ? 'no' : 'yes',
                ]);

                $alertMessage = $wwwsslEnabled
                    ? app('App\Http\Controllers\AlertMessageController')->get('wwwssl.enable')
                    : app('App\Http\Controllers\AlertMessageController')->get('wwwssl.disable');

                $API->disconnect();
                return $alertMessage;
            }
        }

    }

    public function updateWww(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $wwwEnabled = RouterosServiceStatus::isEnabled($request->input('www_enabled'));
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        // Log::info('Telnet Status: '. $telnetEnabled);
        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new LegacyRouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                $API->comm('/ip/service/set', [
                    'numbers' => 'www',
                    'disabled' => $wwwEnabled ? 'no' : 'yes',
                ]);

                $alertMessage = $wwwEnabled
                    ? app('App\Http\Controllers\AlertMessageController')->get('www.enable')
                    : app('App\Http\Controllers\AlertMessageController')->get('www.disable');

                $API->disconnect();
                return $alertMessage;
            }
        }

    }

    public function updateSsh(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $sshEnabled = RouterosServiceStatus::isEnabled($request->input('ssh_enabled'));
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new LegacyRouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                $API->comm('/ip/service/set', [
                    'numbers' => 'ssh',
                    'disabled' => $sshEnabled ? 'no' : 'yes',
                ]);

                $alertMessage = $sshEnabled
                    ? app('App\Http\Controllers\AlertMessageController')->get('ssh.enable')
                    : app('App\Http\Controllers\AlertMessageController')->get('ssh.disable');

                $API->disconnect();
                return $alertMessage;
            }
        }
    }

    public function updateWinbox(Request $request)
    {
        // $servers = Server::where('enable', '1')->get();
        $seletedserver = $request->input('sserver');
        $wwwsslEnabled = RouterosServiceStatus::isEnabled($request->input('winbox_enabled'));
        $serveriid = Server::find($seletedserver);
        $seletedserver = $seletedserver ?? '';

        // Log::info('Telnet Status: '. $telnetEnabled);
        if ($seletedserver) {
            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new LegacyRouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {
                $API->comm('/ip/service/set', [
                    'numbers' => 'winbox',
                    'disabled' => $wwwsslEnabled ? 'no' : 'yes',
                ]);

                $alertMessage = $wwwsslEnabled
                    ? app('App\Http\Controllers\AlertMessageController')->get('winbox.enable')
                    : app('App\Http\Controllers\AlertMessageController')->get('winbox.disable');

                $API->disconnect();
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

