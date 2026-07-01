<?php

namespace App\Http\Controllers;

use App\Models\RouterosAPI;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\Server;
use Yajra\DataTables\DataTables;
use App\Events\PingResult;
use App\Http\Controllers\MicrotikController;
use Illuminate\Support\Facades\Log;

class PPPoEController extends Controller
{
    private $api;
    private $connectionTimeout = 5; // seconds

    public function __construct()
    {
        // $this->middleware('role:super-admin','permission:add-server',['only' => ['create','store']]); role example
        $this->middleware('permission:view-server',['only' => ['deleted','newactiveserver','ping','allactivenew', 'delet']]);
        $this->api = new RouterosAPI();
        $this->api->debug = false;
    }

    /**
     * Connect to a MikroTik server
     * 
     * @param int $serverId
     * @return Server
     * @throws \Exception
     */
    private function connectToServer($serverId)
    {
        try {
            $server = Server::find($serverId);
            
            if (!$server) {
                throw new \Exception("Server with ID {$serverId} not found");
            }

            if (!$this->api->connect($server->mip, $server->username, $server->password, $this->connectionTimeout)) {
                throw new \Exception("Unable to connect to router {$server->name} ({$server->mip})");
            }

            return $server;
        } catch (\Exception $e) {
            Log::error("Connection error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute API command with error handling
     * 
     * @param string $command
     * @param array $params
     * @return mixed
     */
    private function executeCommand($command, $params = [])
    {
        try {
            return $this->api->comm($command, $params);
        } catch (\Exception $e) {
            Log::error("API command error: {$command} - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Log activity to MikroTik router
     * 
     * @param string $message
     */
    private function logToMikrotik($message)
    {
        try {
            $this->api->write('/log/error', false);
            $this->api->write('=message=' . $message, true);
            $this->api->read();
        } catch (\Exception $e) {
            Log::error("Error logging to MikroTik: " . $e->getMessage());
        }
    }

    public function deleted(Request $request)
    {
        try {
            $server = $this->connectToServer($request->get('server'));
            $this->executeCommand('/ppp/active/remove', ['.id' => $request->id]);
            
            activity()
                ->causedBy(auth()->user())
                ->useLog('Active Users')
                ->log('PPPoE User ' . $request->cname . ' Deleted from ' . $server->name);
            
            return response()->json(['success' => true, 'cname' => $request->cname]);
        } catch (\Exception $e) {
            Log::error("Error in deleted method: " . $e->getMessage());
            return response()->json(['success' => false, 'cname' => $request->cname, 'error' => $e->getMessage()]);
        } finally {
            if ($this->api && $this->api->connected) {
                $this->api->disconnect();
            }
        }
    }

    public function delet(Request $request)
    {
        try {
            $server = $this->connectToServer($request->get('server'));
            $this->executeCommand('/ppp/active/remove', ['.id' => $request->id]);
            
            activity()
                ->causedBy(auth()->user())
                ->useLog('Active Users')
                ->log('PPPoE User ' . $request->cname . ' Deleted from All Servers');
            
            return response()->json([
                'success' => true, 
                'cname' => $request->cname, 
                'server' => $server->name
            ]);
        } catch (\Exception $e) {
            Log::error("Error in delet method: " . $e->getMessage());
            return response()->json(['success' => false, 'cname' => $request->cname, 'error' => $e->getMessage()]);
        } finally {
            if ($this->api && $this->api->connected) {
                $this->api->disconnect();
            }
        }
    }

    public function deletapi(Request $request)
    {

        try {
            $server = $this->connectToServer($request->get('server'));
            $this->executeCommand('/ppp/active/remove', ['.id' => $request->id]);
            
            activity()
                ->causedBy(auth()->user())
                ->useLog('Active Users - API')
                ->log('PPPoE User ' . $request->cname . ' Deleted from All Servers');
                
            
            return response()->json([
                'success' => true, 
                'cname' => $request->cname, 
                'server' => $server->name
            ]);
        } catch (\Exception $e) {
            Log::error("Error in delet method: " . $e->getMessage());
            activity()
                ->causedBy(auth()->user())
                ->useLog('Active Users - API')
                ->log('PPPoE User ' . $request->cname . ' Deletion Failed');
            return response()->json(['success' => false, 'cname' => $request->cname, 'error' => $e->getMessage()]);
        } finally {
            if ($this->api && $this->api->connected) {
                $this->api->disconnect();
            }
        }
    }

    public function newactiveserver(Request $request)
    {
        $title = 'Active pppoe Users';

        $iid = $request->get('server');
        $search = $request->get('name');
        $servers = Server::where('enable','1')->get();

        $serveriid = Server::find($iid);
        $message = auth()->user()->name." checking active users.";

        // app('App\Http\Controllers\MicrotikController')->addtolog($serveriid);

        // return $serveriidd;
        if($request->ajax()){

            $activeu=[];

            if($iid){

                $ip = $serveriid->mip;
                $user = $serveriid->username;
                $password = $serveriid->password;

                $API = new RouterosAPI();
                $API->debug = false;

                if ($API->connect($ip, $user, $password)) {

                    // Log the username to MikroTik
                    $API->write('/log/error', false);
                    $API->write('=message= ' . $message, true);
                    $API->read();

                    $activeu = array_reverse($API->comm('/ppp/active/print'));
                    // return $iid;

                    return DataTables::of($activeu)
                            ->addIndexColumn()
                            ->addColumn('namel',function ($data){
                                $namee = $data['name'];
                                // $namelink = "<a href='#'>. $namee . </a>";
                                $namelink = '<a href="'.route('subscriber.microtik', ['name' => $namee]).'">'. $namee .'</a>';

                                // Just display the link always since we can't determine permissions
                                return $namelink;
                            })
                            ->addColumn('addressnew',function ($data){
                                // $namee = $data['name'];
                                $tenip = explode(".", $data['address']) ;
                                if($tenip[0]=="10"){
                                    $adddd = '<a href="http://'. $data['address'] .':8080" target="_new" class="text-danger">'. $data['address'] .'</a>';
                                }else{
                                    $adddd = '<a href="http://'. $data['address'] .':8080" target="_new">'. $data['address'] .'</a>';
                                }
                                return $adddd;
                            })
                            ->addColumn('remove',function ($data)use ($iid){
                                // $namee = $data['name'];
                                // $removebtn = '<a href="'. route('pppoe.deleted', ['server' => $iid, 'cname'=> $data['name'], 'id'=>$data['.id']]) . '" class="btn btn-danger btn-sm" id="removebtn">Remove</a>';
                                $removebtn = '<a href="javascript:void(0)" class="btn btn-danger" data-route="'.route('pppoe.deleted').'" data-cname="'. $data['name'] .'" data-server="' . $iid . '" data-id="' . $data['.id'] . '" id="removebtn">Remove</a>';
                                //  $namelink = '<a href="' . $iid . '" class="editbtn">UserName</a>';
                                $chartbtn = '<a href="'. route('traffic.chart', ['serverId' => $iid, 'username'=> $data['name']]) . '" class="btn btn-success btn-sm">Live Graph</a>';
                                return $chartbtn . " " . $removebtn;
                            })->addColumn('newmac',function ($data){
                                // $removebtn = '<a href="'. route('pppoe.delet', ['server' => $data['serverid'], 'cname'=> $data['name'], 'id'=>$data['.id'], 'checked'=>$chk]) . '" class="btn btn-danger btn-sm">Remove</a>';
                                    // $namelink = '<a href="' . $iid . '" class="editbtn">UserName</a>';
                                    $newmac = str_replace(':', '-', $data['caller-id']);
                                    // $newmac = '<a href="'. route('find.mac.vendor', ['mac' => $newmac]) . '">'. $newmac .'</a>';
                                return $newmac;
                            })
                            ->rawColumns(['namel','remove','addressnew','newmac'])
                            ->make(true);

                    $API->disconnect(); 
                    }
            }

        }

        if($serveriid){
            activity()->causedBy(auth()->user())->useLog('Active Users')->log('Active users checked from ' . $serveriid->name);
        }
        
        return view('admin.PPPoE.newactive',compact('title','iid','servers','search'));
    }

    public function allactivenew(Request $request)
    {
        $title = 'All Active pppoe Users';
        $activeu=[];
        $finalarray=[];
        $search = $request->name;
        $servers = Server::where('enable','1')->get();

        $checked = $request->checked;

        // return $checked;

        if($checked = 1){
            $chk = $checked;
        }else{
            $chk = 0;
        }

        if($request->ajax()){

            foreach($servers as $server)
            {
                    $ip = $server->mip;
                    $user = $server->username;
                    $password = $server->password;
                    $servername = $server->name;
                    $serverid = $server->id;

                    $message = auth()->user()->name." checking active users.";

                    $API = new RouterosAPI();
                    $API->debug = false;

                    if ($API->connect($ip, $user, $password)) {

                        // Log the username to MikroTik
                        $API->write('/log/error', false);
                        $API->write('=message='. $message , true);
                        $API->read();

                        $activeu = array_reverse($API->comm('/ppp/active/print'));

                        foreach($activeu as &$usrs)
                        {
                            $strtotime_input = strtr($usrs['uptime'], [
                                'w' => ' week ',
                                'd' => ' day ',
                                'h' => ' hour ',
                                'm' => ' minute ',
                                's' => ' second '
                            ]);
                            $timestamp = strtotime($strtotime_input);

                            $usrs['server'] = $servername;
                            $usrs['time'] = time() - $timestamp;
                            $usrs['serverid'] = $serverid;
                        }

                    $API->disconnect();
                    }
                    $finalarray = array_merge($finalarray, $activeu);
            }
            return DataTables::of($finalarray)
                ->addIndexColumn()
                ->addColumn('namel',function ($data){
                    $namee = $data['name'];
                    $namelink = '<a href="'.route('subscriber.microtik', ['name' => $namee]).'">'. $namee .'</a>';

                    // Just display the link always since we can't determine permissions
                    return $namelink;
                })
                ->addColumn('addressnew',function ($data){
                    $tenip = explode(".", $data['address']) ;
                    if($tenip[0]=="10"){
                        $adddd = '<a href="http://'. $data['address'] .':8080" target="_new" class="text-danger">'. $data['address'] .'</a>';
                    }else{
                        $adddd = '<a href="http://'. $data['address'] .':8080" target="_new">'. $data['address'] .'</a>';
                    }
                    return $adddd;
                })
                ->addColumn('ping',function ($data){
                    $pinglink = '<a href="ping?ip='. $data['address'] .'&username=' . $data['name'] . '&server=' . $data['serverid'] . '">Ping</a>';

                    return $pinglink;
                })
                ->addColumn('remove',function ($data) use ($chk){
                    $chartbtn = '<a href="'. route('traffic.chart', ['serverId' => $data['serverid'], 'username'=> $data['name']]) . '" class="btn btn-success btn-sm">Live Graph</a>';
                        // $namelink = '<a href="' . $iid . '" class="editbtn">UserName</a>';
                    // $removebtn = '<a href="'. route('pppoe.delet', ['server' => $data['serverid'], 'cname'=> $data['name'], 'id'=>$data['.id'], 'checked'=>$chk]) . '" class="btn btn-danger btn-sm">Remove</a>';
                    $removebtn = '<a href="javascript:void(0)" class="btn btn-danger" data-route="'.route('pppoe.delet').'" data-cname="'. $data['name'] .'" data-server="' . $data['serverid'] . '" data-id="' . $data['.id'] . '" id="removebtn">Remove</a>';
                    return $chartbtn . " " . $removebtn;
                })
                ->addColumn('newmac',function ($data){
                    // $removebtn = '<a href="'. route('pppoe.delet', ['server' => $data['serverid'], 'cname'=> $data['name'], 'id'=>$data['.id'], 'checked'=>$chk]) . '" class="btn btn-danger btn-sm">Remove</a>';
                        // $namelink = '<a href="' . $iid . '" class="editbtn">UserName</a>';
                        $newmac = str_replace(':', '-', $data['caller-id']);
                        // $newmac = '<a href="'. route('find.mac.vendor', ['mac' => $newmac]) . '">'. $newmac .'</a>';
                    return $newmac;
                })
                ->addColumn('newuptime',function ($data){
                    // $removebtn = '<a href="'. route('pppoe.delet', ['server' => $data['serverid'], 'cname'=> $data['name'], 'id'=>$data['.id'], 'checked'=>$chk]) . '" class="btn btn-danger btn-sm">Remove</a>';
                        // $namelink = '<a href="' . $iid . '" class="editbtn">UserName</a>';
                        $newtime = 0 - $data['time'];
                        // $newtime = '<a href="'. route('find.mac.vendor', ['mac' => $newmac]) . '">'. $newmac .'</a>';
                        $newtime = $this->convertSeconds($newtime);
                    return $newtime;
                })
                ->rawColumns(['namel','remove','addressnew','ping','newmac','newuptime'])
                ->make(true);

        }

        activity()->causedBy(auth()->user())->useLog('Active Users')->log('All Active Users cheked.');

        return view('PPPoE.newactivenew', compact('title','servers','checked'));
    }

    //activeUsersApi
    public function allactivenewapi(Request $request)
    {
        // $title = 'All Active pppoe Users';
        $activeu=[];
        $finalarray=[];
        $search = $request->name;
        $servers = Server::where('enable','1')->get();

        $checked = $request->checked;

        // return $checked;

        if($checked = 1){
            $chk = $checked;
        }else{
            $chk = 0;
        }

        // if($request->ajax()){

            foreach($servers as $server)
            {
                    $ip = $server->mip;
                    $user = $server->username;
                    $password = $server->password;
                    $servername = $server->name;
                    $serverid = $server->id;

                    $message = auth()->user()->name." checking active users using API";

                    $API = new RouterosAPI();
                    $API->debug = false;

                    if ($API->connect($ip, $user, $password)) {

                        // Log the username to MikroTik
                        $API->write('/log/error', false);
                        $API->write('=message='. $message , true);
                        $API->read();

                        $activeu = array_reverse($API->comm('/ppp/active/print'));

                        foreach($activeu as &$usrs)
                        {
                            $strtotime_input = strtr($usrs['uptime'], [
                                'w' => ' week ',
                                'd' => ' day ',
                                'h' => ' hour ',
                                'm' => ' minute ',
                                's' => ' second '
                            ]);
                            $timestamp = strtotime($strtotime_input);

                            $usrs['server'] = $servername;
                            $usrs['time'] = time() - $timestamp;
                            $usrs['serverid'] = $serverid;
                        }

                    $API->disconnect();
                    }
                    $finalarray = array_merge($finalarray, $activeu);
                    $sortColumn = array_column($finalarray, 'time'); // Replace 'column_name' with your actual column name

                    // Sort the array
                    array_multisort($sortColumn, SORT_DESC, $finalarray);

            }


        activity()->causedBy(auth()->user())->useLog('Active Users - api')->log('All Active Users checked');

        // return view('admin.PPPoE.newactivenew', compact('title','servers','checked'));
        return response()->json($finalarray, 200);
    }

    public function pingold(Request $request)
    {
        $title = "User Ping";
        $iid = $request->get('server');
        $iiip = $request->ip;

        if($request->time){
            $time = $request->get('time');
        }else{
            $time = 5;
        }

        $subscriber=$request->username;

        $serveriid = Server::find($iid);

        $ip = $serveriid->mip;
        $user = $serveriid->username;
        $password = $serveriid->password;
        $sname = $serveriid->name;

        // return $serveriid;

        $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {

                $PING = $API->comm("/ping", array(
                    "address" => "$iiip",
                    "count" => $time
                ));
            $length = count($PING);

                // return $PING;

                activity()->causedBy(auth()->user())->useLog('Ping')->log('Check for user '. $subscriber . ' for ' . $time . ' of ' . $sname);
                return view('admin.PPPoE.ping', compact('title', 'PING','iid','iiip','sname','subscriber','time'));

            }
    }

    public function ping(Request $request)
    {
        $title = "User Ping";
        $iid = $request->get('server');
        $iiip = $request->ip;

        if($request->time){
            $time = $request->get('time');
        }else{
            $time = 5;
        }

        $subscriber=$request->username;

        $serveriid = Server::find($iid);

        $ip = $serveriid->mip;
        $user = $serveriid->username;
        $password = $serveriid->password;
        $sname = $serveriid->name;
        // return $serveriid;

        $API = new RouterosAPI();
            $API->debug = false;

            // if ($API->connect($ip, $user, $password)) {

            //     $PING = $API->comm("/ping", array(
            //         "address" => "$iiip",
            //         "count" => $time
            //     ));
            // $length = count($PING);

                // return $PING;

                activity()->causedBy(auth()->user())->useLog('Ping')->log('Check for user '. $subscriber . ' for ' . $time . ' of ' . $sname);
                return view('admin.PPPoE.pingnew', compact('title', 'iid','iiip','sname','subscriber','time'));

            
    }

    public function realTimePing(Request $request)
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        
        try {
            $server = $this->connectToServer($request->get('server'));
            $username = $request->get('username', 'unknown');
            $ip = $request->get('ip');
            $pingCount = min(intval($request->get('time', 5)), 30); // Limit to 30 iterations
            
            $message = auth()->user()->name . " checking ping for user " . $username . ".";
            $this->logToMikrotik($message);
            
            $iterations = 0;
            while ($iterations < $pingCount) {
                $pingResult = $this->executeCommand('/ping', [
                    'address' => $ip, 
                    'count' => 1
                ]);
                
                if ($pingResult) {
                    echo "data: " . json_encode($pingResult) . "\n\n";
                    ob_flush();
                    flush();
                }
                
                $iterations++;
            }
        } catch (\Exception $e) {
            Log::error("Error in realTimePing: " . $e->getMessage());
            echo "data: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
            ob_flush();
            flush();
        } finally {
            if ($this->api && $this->api->connected) {
                $this->api->disconnect();
            }
        }
    }

    public function realTimePingApi(Request $request)
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        
        // try {
            $server = $this->connectToServer($request->get('server'));
            $username = $request->get('username', 'unknown');
            $ip = $request->get('ip');
            $pingCount = min(intval($request->get('time', 5)), 30); // Limit to 30 iterations
            
            $message = auth()->user()->name . " checking ping for user " . $username . " from API";
            activity()->causedBy(auth()->user())->useLog('Ping - api')->log('Checking ping for user '. $username . ' of ' . $ip);
            $this->logToMikrotik($message);
            
            $iterations = 0;
            // while ($iterations < $pingCount) {
                $pingResult = $this->executeCommand('/ping', [
                    'address' => $ip, 
                    'count' => 1
                ]);
                
                if ($pingResult) {
                    ob_flush();
                    flush();
                    return response()->json($pingResult[0], 200); // return jsonencode($pingResult); // return $pingResult;
                }
    }

    /**
     * Convert seconds to a human-readable format
     * 
     * @param int $seconds
     * @return string
     */
    function convertSeconds($seconds) {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
    
        $result = '';
    
        if ($days > 0) {
            $result .= $days . 'day ';
        }
    
        if ($hours > 0) {
            $result .= $hours . 'hr ';
        }
    
        if ($minutes > 0) {
            $result .= $minutes . 'min ';
        }
    
        if ($seconds > 0 || $result === '') {
            $result .= $seconds . 'sec';
        }
    
        return trim($result);
    }
}

// error_reporting(0);
