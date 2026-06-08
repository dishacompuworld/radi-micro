<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RouterosAPI;
use App\Models\Server;

class ServerStats extends Controller
{
    public function __construct()
    {
        // $this->middleware('role:super-admin','permission:add-server',['only' => ['create','store']]); role example
        // $this->middleware('permission:view-serverstats',['only' => ['index']]);

    }

    public function index(Request $request)
    {
        $title = "Microtik Stats";
        $iid = $request->get('server');
        $servers = Server::where('enable','1')->get();
        $data=[];

        if($iid){

            $serveriid = Server::find($iid);

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {

                // $hotspotactive = $API->comm('/ip/hotspot/active/print');
                $resource = $API->comm('/system/resource/print');
                // $secret = $API->comm('/ppp/secret/print');
                $active = $API->comm('/ppp/active/print');
                // $interface = $API->comm('/interface/ethernet/print');
                $routerboard = $API->comm('/system/routerboard/print');
                $identity = $API->comm('/system/identity/print');

                $data = [
                    // 'totalsecret' => count($secret),
                    // 'totalhotspot' => count($hotspotactive),
                    // 'hotspotactive' => count($hotspotactive),
                    'active' => count($active),
                    'cpu' => $resource[0]['cpu-load'],
                    'uptime' => $resource[0]['uptime'],
                    'version' => $resource[0]['version'],
                    'boardname' => $resource[0]['board-name'],
                    'freememory' => $resource[0]['free-memory'],
                    'totalmemory' => $resource[0]['total-memory'],
                    'freehdd' => $resource[0]['free-hdd-space'],
                    'totalhdd' => $resource[0]['total-hdd-space'],
                    'model' => $routerboard[0]['model'],
                    'identity' => $identity[0]['name'],
                    'buildtime' => $resource[0]['build-time'],
                    'factorysoftware' => $resource[0]['factory-software'],
                ];

                // return $data;
                activity()->causedBy(auth()->user())->useLog('Microtik Stats')->log('Checked stats for ' . $serveriid->name);
                return view('stats.index', compact('title','servers','iid','data'));
            } else {

                return redirect('failed');
            }
        }

        return view('stats.index', compact('title','servers','iid','data'));
    }

    public function api($id)
    {
        // $title = "Microtik api";
        $iid = $id;
        // $servers = Server::where('enable','1')->get();
        $data=[];

        if($iid){

            $serveriid = Server::find($iid);

            $ip = $serveriid->mip;
            $user = $serveriid->username;
            $password = $serveriid->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {

                // $hotspotactive = $API->comm('/ip/hotspot/active/print');
                $resource = $API->comm('/system/resource/print');
                // $secret = $API->comm('/ppp/secret/print');
                $active = $API->comm('/ppp/active/print');
                // $interface = $API->comm('/interface/ethernet/print');
                $routerboard = $API->comm('/system/routerboard/print');
                $identity = $API->comm('/system/identity/print');

                $data = [
                    // 'totalsecret' => count($secret),
                    // 'totalhotspot' => count($hotspotactive),
                    // 'hotspotactive' => count($hotspotactive),
                    'active' => count($active),
                    'cpu' => $resource[0]['cpu-load'],
                    'uptime' => $resource[0]['uptime'],
                    'version' => $resource[0]['version'],
                    'boardname' => $resource[0]['board-name'],
                    'freememory' => $resource[0]['free-memory'],
                    'totalmemory' => $resource[0]['total-memory'],
                    'freehdd' => $resource[0]['free-hdd-space'],
                    'totalhdd' => $resource[0]['total-hdd-space'],
                    'model' => $routerboard[0]['model'],
                    'identity' => $identity[0]['name'],
                    'buildtime' => $resource[0]['build-time'],
                    'factorysoftware' => $resource[0]['factory-software'],
                ];

                // return $data;
                activity()->causedBy(auth()->user())->useLog('Microtik Stats from api')->log('Checked stats for ' . $serveriid->name);
                return response()->json($data,200); // json_encode($data,200);
            } else {

                return response()->json('{"status":"failed"}',200);
            }
        }

        // return json_encode($data,200);
    }


}
