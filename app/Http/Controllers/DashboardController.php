<?php

namespace App\Http\Controllers;
use App\Models\RouterosAPI;
use App\Models\Server;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        $title = 'Dashboard';

        activity()->causedBy(auth()->user())->useLog('Dashboard')->log('Dashboard Viewed');

        $total_servers = Server::where('enable', 1)->count();
        $total_locations = Location::where('enable', 1)->count();

        $userss = 0;
        $servers = Server::where('enable','1')->get();
       
        

        // app('App\Http\Controllers\PrtgApiController')->getfirstchart();
        // app('App\Http\Controllers\PrtgApiController')->getsecondchart();

        $totalactiveuserc = app('App\Http\Controllers\FetchapiController')->allactiveusers();

        $upsensorcount =app('App\Http\Controllers\PrtgApiController')->upsensors();
        $downsensorcount =app('App\Http\Controllers\PrtgApiController')->downsensors();
        $totalsensorcount =app('App\Http\Controllers\PrtgApiController')->totalsensors();
        $msebstatus = app('App\Http\Controllers\PrtgApiController')->getMsebStatusData();
        $disabledsubscribers = app('App\Http\Controllers\FetchapiController')->getdisablesubscribers();

        foreach($servers as $server){

            $ip = $server->mip;
            $user = $server->username;
            $password = $server->password;

            $API = new RouterosAPI();
            $API->debug = false;

            if ($API->connect($ip, $user, $password)) {

                $username = auth()->user()->name; // Get the logged-in username

                // Log the username to MikroTik
                $API->write('/log/error', false);
                $API->write('=message=User ' . $username . ' logged to ' . env('APP_NAME'), true);
                $API->read();

                $activeuserss = $API->comm('/ppp/active/print');

                $userss = $userss + count($activeuserss);

            }

        }
        $temp = $this->getTempInfoAjax();

        return view('dashboard',compact('title','userss','total_servers','total_locations','upsensorcount','downsensorcount','totalsensorcount','totalactiveuserc','msebstatus','temp','disabledsubscribers'));

        // return view('dashboard',compact('title'));
    }


    public function getTempInfoAjax(){

        $main_server = Server::where('name','Main Server')->first();

        if($main_server){

            $main_server_ip = $main_server->mip;
            $main_server_user = $main_server->username;
            $main_server_password = $main_server->password;

            $API = new RouterosAPI();
            $API->debug = false;

            $health = [];

            if ($API->connect($main_server_ip, $main_server_user, $main_server_password)) {

                    // $username = auth()->user()->name; // Get the logged-in username

                    // // Log the username to MikroTik
                    // $API->write('/log/error', false);
                    // $API->write('=message=User ' . $username . ' logged to ' . env('APP_NAME'), true);
                    // $API->read();

                    $health = $API->comm('/system/health/print');
                }
                
                // $temp = $health[0]["temperature"];
                $temp = "N/A";

        }else{
            $temp = 'N/A';
        }

        // return $temp;
        $data = [
            'temp' => $temp
        ];
        return response()->json($data);
        
    }
}