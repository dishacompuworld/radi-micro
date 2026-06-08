<?php

namespace App\Http\Controllers;

// use App\Http\Controllers\Http;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use PhpParser\Node\Stmt\Return_;
use Userlocation;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\returnSelf;

class FetchapiController extends Controller
{
    public function __construct(){
        // // $this->middleware('role:super-admin','permission:add-server',['only' => ['create','store']]); role example
        // $this->middleware('permission:view-packages',['only' => ['getlocationpackages']]);
        // // $this->middleware('permission:view-radius-logs',['only' => ['accessrequest']]);
        // $this->middleware('permission:reset-mac',['only' => ['resetmac']]);
        // $this->middleware('permission:enable-disable',['only' => ['enablesubscriber','disablesubscriber']]);
        // $this->middleware('permission:overright-bandwidth',['only' => ['overrightspeed']]);
        // $this->middleware('permission:view-admin-radius-logs',['only' => ['adminaccesslog']]);

        // $this->middleware('permission:view-radius-logs',['only' => ['accessrequest']]);
        // $this->middleware('permission:reset-mac',['only' => ['macreset']]);
        // $this->middleware('permission:enable-disable',['only' => ['enablesubscriber','disablesubscriber']]);
        // $this->middleware('permission:overright-bandwidth',['only' => ['overrightspeed']]);
        // //
    }

    public function login(){

        // phpinfo();
        /**@var App\Http\Controllers\App\Http\Request */
        $response = Http::asForm()->post('https://disha.xceednet.com/api/v2/sessions/user_login', [
            'email' => env('XCEEDNET_LOGIN',null),
            'password' => env('XCEEDNET_PASS',null)
        ]);
        $tokan =  $this->token = json_decode($response, true)['auth_token'];

        // return $tokan;

        $data = [
            'tokan' => $tokan,
        ];
        session()->put($data);
    }

    public function subscriberDetails(Request $request){

        $title = "Subscriber Details";
        // return $request;
        // $url = "disha.xceednet.com";
        $loca = $request->loca;

        $location = DB::table('locations')
                    ->where('enable', 1)
                    ->get();

        // return $location;

        if($request->loca){

            $this->login();

            $tokan = session()->get('tokan');


            // if($request->loca){

                $locationss = DB::table('locations')
                    ->where('name', $request->loca)
                    ->first();

                    // return $locationss;
                $url = $locationss->url;

            // }

            $response1 = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authentication' => $tokan
            ])->get('https://' . $url . '/subscribers/search_subscriber/?username='. $request->username);

            $locationid = DB::table('packages')
            ->select('packages.name','packages.radiusid')
            ->leftJoin('locations', 'packages.locationid', '=', 'locations.id')
            ->where('locations.name', $loca)
            ->get();

            if(!isset($response1['error_status'])){
                $subscriber = DB::table('subscribers')
                ->insertOrIgnore([
                    'name' => $request->username,
                    'domain' => $request->loca,
                    'enable' => 1

                ]);
            }


            // return $response1;
            return view('admin.radius.show', compact('title','response1', 'location','loca','locationid'));

        }else{

            return view('admin.radius.show', compact('title','location'));
        }

    }

    public function subscriberDtlFromMicrotik(Request $request){

        $this->login();
        $tokan = session()->get('tokan');

        $title = "Subscriber Details";
        $opticalpower ="Ont Not assign";
        $opticaltxpower = "Ont Not assign";
        $ontuptime="Not Available";
        $ontserial="Not Available";
        $onttemp="Not Available";
        $onteth = "Not Available";
        $ontmodel = "Not Available";
        $ontdist = "Not Available";
        $ontstatus = "Not Available";

        $location = DB::table('locations')
                    ->where('enable', 1)
                    ->get();

        $userdomain = DB::table('subscribers')
        ->where('name', $request->name)
        ->first();

        $userlocations = DB::table('locations')
            ->select('locations.name')
            ->leftJoin('userlocation', 'userlocation.locationid', '=', 'locations.id')
            ->where('userlocation.userid', auth()->user()->id)
            ->get();


        // if($oid->oid===null){
            $doid = "";
        // }else{
        //     $doid = $oid->oid;
        // }
        // return $userdomain;

        if($userdomain){

            $urll=$userdomain->domain . ".xceednet.com";
            $fromd = "From Database";

           if($userdomain->oid){
                $doid = $userdomain->oid;
           }else{
                $doid = "";
           }

            $response1 = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authentication' => $tokan
            ])->get('https://' . $urll . '/subscribers/search_subscriber/?username='. $request->name);

            // return $response1;

            $locationid = DB::table('packages')
            ->select('packages.name','packages.radiusid')
            ->leftJoin('locations', 'packages.locationid', '=', 'locations.id')
            ->where('locations.name', $userdomain->domain)
            ->get();

            

            if($doid){
                $opticalpower =  app('App\Http\Controllers\FetchSNMP')->getontpower($userdomain->oid);
                // return $opticalpower;
                if ($opticalpower == "SNMP Not Available"){
                    $opticaltxpower = "Snmp Not Available";
                    $ontuptime = "Snmp Not Available";
                    $ontserial = "Snmp Not Available";
                    $onttemp = "Snmp Not Available";
                    $onteth = "Snmp Not Available";
                    $ontmodel = "Snmp Not Available";
                    $ontdist = "Snmp Not Available";
                    $ontstatus = "Snmp Not Available";
                }else{
                    $opticaltxpower =  app('App\Http\Controllers\FetchSNMP')->getonttxpower($userdomain->oid);
                    $ontuptime = app('App\Http\Controllers\FetchSNMP')->getontuptime($userdomain->oid);
                    $ontserial = app('App\Http\Controllers\FetchSNMP')->getontserial($userdomain->oid);
                    $onttemp = app('App\Http\Controllers\FetchSNMP')->getonttemp($userdomain->oid);
                    $onteth = app('App\Http\Controllers\FetchSNMP')->getonteth($userdomain->oid);
                    $ontmodel = app('App\Http\Controllers\FetchSNMP')->getontmodel($userdomain->oid);
                    $ontdist = app('App\Http\Controllers\FetchSNMP')->getontdist($userdomain->oid);
                    $ontstatus = trim(app('App\Http\Controllers\FetchSNMP')->getontstatus($userdomain->oid),'"');
                }
            }else{
                $opticaltxpower = "Ont Not assign";
                $opticalpower = "Ont Not assign";
                $ontuptime = "Ont Not assign";
                $ontserial = "Ont Not assign";
                $onttemp = "Ont Not assign";
                $onteth = "Ont Not assign";
                $ontmodel = "Ont Not assign";
                $ontdist = "Ont Not assign";
                $ontstatus = "Ont Not assign";
            }
        }else{

            foreach($location as $loc){
                $urll = $loc-> url;
                $uriii = $loc-> name;
                $fromd = "From Radius";
                $response1 = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authentication' => $tokan
                ])->get('https://' . $urll . '/subscribers/search_subscriber/?username='. $request->name);

                if(isset($response1['data']['username']) && $response1['data']['username'] === $request->name) { 
                    break; 
                }
            }
            // return $response1['data']['username'];

            $subscriber = DB::table('subscribers')
                        ->insertOrIgnore([
                            'name' => $request->name,
                            'domain' => $uriii,
                            'enable' => 1

                        ]);

            $locationid = DB::table('packages')
            ->select('packages.name','packages.radiusid')
            ->leftJoin('locations', 'packages.locationid', '=', 'locations.id')
            ->where('locations.name', $uriii)
            ->get();

            $doid = "";

            // return $locationid;
        }

        //  return $response1;

        if(isset($uriii)){
            $logtext = 'Fetch User Details of '. $request->name . ' '. $fromd . ' of ' . $uriii;
        }else{
            $locn = explode(".",$urll);
            $logtext = 'Fetch User Details of '. $request->name . ' '. $fromd . ' of ' . $locn[0];
        }

        // return gmdate('d H:i:s', $ontuptime);
        // return $this->toDateInterval(1640467)->format('%a days %h hours %i minutes'));

        activity()->causedBy(auth()->user())->useLog('Location')->log($logtext);
        return view('admin.radius.microtik', compact('title','response1','urll','fromd','locationid','userlocations','opticalpower','doid','ontuptime','ontserial','onttemp','onteth','ontmodel', 'ontdist','opticaltxpower','ontstatus'));

    }


    public function subscriberDtlFromMicrotikapi(Request $request){

        // return $request->name;
        $this->login();
        $tokan = session()->get('tokan');

        // $title = "Subscriber Details";
        $opticalpower = "Ont Not assign";
        $opticaltxpower = "Ont Not assign";
        $ontuptime= "Not Available";
        $ontserial= "Not Available";
        $onttemp= "Not Available";
        $onteth = "Not Available";
        $ontmodel = "Not Available";
        $ontdist = "Not Available";
        $ontstatus = "Not Available";

        $location = DB::table('locations')
                    ->where('enable', 1)
                    ->get();

        $userdomain = DB::table('subscribers')
        ->where('name', $request->name)
        ->first();

        $userlocations = DB::table('locations')
            ->select('locations.name')
            ->leftJoin('userlocation', 'userlocation.locationid', '=', 'locations.id')
            ->where('userlocation.userid', auth()->user()->id)
            ->get();

            $doid = "";

        if($userdomain){

            $urll=$userdomain->domain . ".xceednet.com";
            $fromd = "From Database";

           if($userdomain->oid){
                $doid = $userdomain->oid;
           }else{
                $doid = "";
           }

            $response1 = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authentication' => $tokan
            ])->get('https://' . $urll . '/subscribers/search_subscriber/?username='. $request->name);

            // return $response1;

            $locationid = DB::table('packages')
            ->select('packages.name','packages.radiusid')
            ->leftJoin('locations', 'packages.locationid', '=', 'locations.id')
            ->where('locations.name', $userdomain->domain)
            ->get();

            if($doid){
                $opticalpower =  app('App\Http\Controllers\FetchSNMP')->getontpowerapi($userdomain->oid);
                if ($opticalpower == "SNMP Not Available"){
                    $opticaltxpower = "Snmp Not Available";
                    $ontuptime = "Snmp Not Available";
                    $ontserial = "Snmp Not Available";
                    $onttemp = "Snmp Not Available";
                    $onteth = "Snmp Not Available";
                    $ontmodel = "Snmp Not Available";
                    $ontdist = "Snmp Not Available";
                    $ontstatus = "Snmp Not Available";
                }else{
                    $opticaltxpower =  app('App\Http\Controllers\FetchSNMP')->getonttxpower($userdomain->oid);
                    $ontuptime = app('App\Http\Controllers\FetchSNMP')->getontuptime($userdomain->oid);
                    $ontserial = app('App\Http\Controllers\FetchSNMP')->getontserial($userdomain->oid);
                    $onttemp = app('App\Http\Controllers\FetchSNMP')->getonttemp($userdomain->oid);
                    $onteth = app('App\Http\Controllers\FetchSNMP')->getonteth($userdomain->oid);
                    $ontmodel = app('App\Http\Controllers\FetchSNMP')->getontmodel($userdomain->oid);
                    $ontdist = app('App\Http\Controllers\FetchSNMP')->getontdist($userdomain->oid);
                    $ontstatus = trim(app('App\Http\Controllers\FetchSNMP')->getontstatus($userdomain->oid),'"');
                }
            }else{
                $opticaltxpower = "Ont Not assign";
                $opticalpower = "Ont Not assign";
                $ontuptime = "Ont Not assign";
                $ontserial = "Ont Not assign";
                $onttemp = "Ont Not assign";
                $onteth = "Ont Not assign";
                $ontmodel = "Ont Not assign";
                $ontdist = "Ont Not assign";
                $ontstatus = "Ont Not assign";
            }
        }else{

            foreach($location as $loc){
                $urll = $loc-> url;
                $uriii = $loc-> name;
                $fromd = "From Radius";
                $response1 = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authentication' => $tokan
                ])->get('https://' . $urll . '/subscribers/search_subscriber/?username='. $request->name);

                if(isset($response1['data']['username']) && $response1['data']['username'] === $request->name) { 
                    break; 
                }
            }
            // return $response1['data']['username'];

            $subscriber = DB::table('subscribers')
                        ->insertOrIgnore([
                            'name' => $request->name,
                            'domain' => $uriii,
                            'enable' => 1

                        ]);

            $locationid = DB::table('packages')
            ->select('packages.name','packages.radiusid')
            ->leftJoin('locations', 'packages.locationid', '=', 'locations.id')
            ->where('locations.name', $uriii)
            ->get();

            $doid = "";

            // return $locationid;
        }

        //  return $response1;

        if(isset($uriii)){
            $logtext = 'Fetch User Details of '. $request->name . ' '. $fromd . ' of ' . $uriii;
        }else{
            $locn = explode(".",$urll);
            $logtext = 'Fetch User Details of '. $request->name . ' '. $fromd . ' of ' . $locn[0];
        }

        activity()->causedBy(auth()->user())->useLog('FetchUserDetails - API' )->log($logtext);
        // return view('admin.radius.microtik', compact('title','response1','urll','fromd','locationid','userlocations','opticalpower','doid','ontuptime','ontserial','onttemp','onteth','ontmodel', 'ontdist','opticaltxpower','ontstatus'));

        return response()->json([
            'status' => 'success',
            'id'=> $response1['data']['id'],
            'username' => $request->name,
            'name' => $response1['data']['name'],
            'address' => $response1['data']['address1'],
            'email' => $response1['data']['email'],
            'phone' => $response1['data']['mobile1'],
            'online' => $response1['data']['is_online'],
            'renew_date' => $response1['data']['renewed_at'],
            'package_name' => $response1['data']['location_package_name'],
            'expire_date' => $response1['data']['expires_at'],
            'last_login' => $response1['data']['last_login_at'],
            'override_bandwidth' => $response1['data']['override_package_bandwidth'],
            'download_today' => $response1['data']['bytes_uploaded_in_24_hours'],
            'upload_today' => $response1['data']['bytes_downloaded_in_24_hours'],
            'total_upload' => $response1['data']['bytes_uploaded_total_human'],
            'total_download' => $response1['data']['bytes_downloaded_total_human'],
            'enable' => $response1['data']['status'],
            'location' => $urll, 
            'opticalpower' => $opticalpower, 
            'doid' => $doid, 
            'ontuptime' => $ontuptime, 
            'ontserial' => $ontserial, 
            'onttemp' => $onttemp, 
            'onteth' => $onteth, 
            'ontmodel' => $ontmodel, 
            'ontdist' => $ontdist, 
            'opticaltxpower' => $opticaltxpower, 
            'ontstatus' => $ontstatus
        ]);
    }


    public function locationdetails(Request $request){
        $this->login();
        $tokan = session()->get('tokan');

        $locationshort =$request->location;

        $title = "Location Details";
        $useridd=auth()->id();

        if($request->location){
            $location = $request->location . ".xceednet.com";

            // return $location;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authentication' => $tokan
            ])->get('https://' . $location . '/location_dashboard');

        }else{
            $location="";
            $response=[];

        }

        $slocations = DB::table('locations')
        ->select('locations.name')
        ->leftJoin('userlocation', 'userlocation.locationid', '=', 'locations.id')
        ->leftJoin('users', 'userlocation.userid', '=', 'users.id')
        ->where('users.id', '=', $useridd)
        ->get();

        // return ($slocations);
        return view('location.location', compact('title', 'response','locationshort','slocations'));

    }

    public function getlocationpackages(Request $request){

        $this->login();
        $tokan = session()->get('tokan');
        $title = "Location Packages";
        $userid=auth()->id();
        $slocation=$request->location;



        $locations = DB::table('locations')
        ->select('locations.name')
        ->leftJoin('userlocation', 'userlocation.locationid', '=', 'locations.id')
        ->leftJoin('users', 'userlocation.userid', '=', 'users.id')
        ->where('users.id', '=', $userid)
        ->get();


        if($request->location){
            $url = "https://". $request->location . ".xceednet.com/location_packages";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authentication' => $tokan
            ])->get($url);
            return view('admin.location.packages', compact('title', 'response','locations','slocation'));

        }

            // return $response;

        return view('admin.location.packages', compact('title','locations'));
    }

    public function accessrequest(Request $request){
        $this->login();
        $tokan = session()->get('tokan');
        $title = "Radius-Access Logs";


        $location =$request->location;
        $locationurl = $request->location . ".xceednet.com";

        $useridd=auth()->id();

        $slocations = DB::table('locations')
        ->select('locations.name')
        ->leftJoin('userlocation', 'userlocation.locationid', '=', 'locations.id')
        ->leftJoin('users', 'userlocation.userid', '=', 'users.id')
        ->where('users.id', '=', $useridd)
        ->get();

        if($location){
            if($request->ajax()){

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Version' => 'HTTP/1.0',
                    'Accept' => 'application/json',
                    'Authentication' => $tokan
                ])->post('https://' . $location . '.xceednet.com/subscriber_access_requests/search',['start'=> '0', 'length'=> '100']);

                return DataTables::of($response['data'])
                        ->addIndexColumn()
                        ->addColumn('usern',function ($data){
                            $namelink = '<a href="'.route('subscriber.microtik', ['name' => $data[3]]).'">'. $data[3] .'</a>';
                            return $namelink;
                        })
                        ->addColumn('msgg',function ($data){
                            $msggg = $data[6];
                            return $msggg;
                        })
                        ->addColumn('msggg',function ($data){
                            $msggg = $data[7];
                            return $msggg;
                        })
                        ->addColumn('newmac',function ($data){
                                $newmac = str_replace(':', '-', $data[4]);
                                $newmac = '<a href="'. route('find.mac.vendor', ['mac' => $newmac]) . '">'. $newmac .'</a>';
                            return $newmac;
                        })
                        ->rawColumns(['usern','msgg','msggg','newmac'])
                        ->make(true);
                return view('admin.location.access', compact('title', 'slocations', 'location'));
            }
            activity()->causedBy(auth()->user())->useLog('Access Log')->log('Checked Access Log for Location '. $location);
        }

        return view('location.access', compact('title','slocations','location'));
    }

    public function subscriberaccessrequest(Request $request){

        $this->login();
        $tokan = session()->get('tokan');
        $title = "Subscriber-Access Logs";


        $location = $request->location;
        $id = $request->id;
        $name = $request->name;
        $url = $request->location . ".xceednet.com";

        // if($name){
            if($request->ajax()){
                $api_url = 'https://' . $location . '.xceednet.com/subscriber_access_requests/search';

                $headers = [
                    'Content-Type' => 'application/json',
                    'Version' => 'HTTP/1.0',
                    'Accept' => 'application/json',
                    'Authentication' => $tokan
                ];
                $array = ['columns'=>['3'=>['search'=>['value'=> $name]]]];

                $response = Http::withHeaders($headers)->post($api_url, $array);

                return DataTables::of($response['data'])
                        ->addIndexColumn()
                        ->make(true);

                        return view('admin.radius.subscriberaccess', compact('title','name','location'));

            }
        // }

        return view('radius.subscriberaccess', compact('title','name','location'));

    }

    public function subscriberaccessrequestdelete(Request $request){

        $this->login();
        $tokan = session()->get('tokan');
        $title = "Subscriber-Access Logs";
        
        $id = $request->input('id');
        $loc = $request->input('location');
        $name = $request->input('name');

        $api_url = 'https://' . $loc . '.xceednet.com/subscriber_access_requests/delete_all';

        $headers = [
            'Content-Type' => 'application/json',
            'Version' => 'HTTP/1.0',
            'Accept' => 'application/json',
            'Authentication' => $tokan
        ];
        $array = ['subscriber_id'=> $id ];

        $response = Http::withHeaders($headers)->delete($api_url, $array);

        $msg = 'Access Log cleared for user '.$name;
        activity()->causedBy(auth()->user())->useLog('Radius')->log($msg);
        // return $response;
        return response()->json(['success' => true]);

    }

    public function adminaccesslog(Request $request){
        $this->login();
        $tokan = session()->get('tokan');
        $title = "Admin Radius-Access Logs";

        if($request->ajax()){

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Version' => 'HTTP/1.0',
                'Accept' => 'application/json',
                'Authentication' => $tokan
            ])->post('https://admin.xceednet.com/operator_subscriber_access_requests/search',['start'=> '0', 'length'=> '100']);

            return DataTables::of($response['data'])
                    ->addIndexColumn()
                    ->addColumn('usern',function ($data){
                        $namelink = '<a href="'.route('subscriber.microtik', ['name' => $data[3]]).'">'. $data[3] .'</a>';
                        return $namelink;
                    })
                    ->addColumn('newmac',function ($data){
                        // $removebtn = '<a href="'. route('pppoe.delet', ['server' => $data['serverid'], 'cname'=> $data['name'], 'id'=>$data['.id'], 'checked'=>$chk]) . '" class="btn btn-danger btn-sm">Remove</a>';
                            // $namelink = '<a href="' . $iid . '" class="editbtn">UserName</a>';
                            $newmac = str_replace(':', '-', $data[4]);
                            $newmac = '<a href="'. route('find.mac.vendor', ['mac' => $newmac]) . '">'. $newmac .'</a>';
                        return $newmac;
                    })
                    ->rawColumns(['usern','newmac'])
                    ->make(true);

            return view('admin.location.adminaccess', compact('title','response'));
        }
        return view('location.adminaccess', compact('title'));

    }

    public function adminaccesslogapi(Request $request){
        $this->login();
        $tokan = session()->get('tokan');
        // $title = "Admin Radius-Access Logs";

        // if($request->ajax()){

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Version' => 'HTTP/1.0',
                'Accept' => 'application/json',
                'Authentication' => $tokan
            ])->post('https://admin.xceednet.com/operator_subscriber_access_requests/search',['start'=> '0', 'length'=> '100']);


            $formattedData = [];
            foreach ($response['data'] as $row) {
                // Define field names based on the data structure
                $formattedRow = [
                    'timestamp' => html_entity_decode(strip_tags($row[0])),
                    'provider' => html_entity_decode(strip_tags($row[1])),
                    'ip_address' => html_entity_decode(strip_tags($row[2])),
                    'username' => html_entity_decode(strip_tags($row[3])),
                    'mac_address' => html_entity_decode(strip_tags($row[4])),
                    'additional_info' => html_entity_decode(strip_tags($row[5])),
                    'status' => html_entity_decode(strip_tags($row[6])),
                    'message' => html_entity_decode(strip_tags($row[7]))
                ];
                $formattedData[] = $formattedRow;
            }

            activity()->causedBy(auth()->user())->useLog('Admin Access request - api')->log('Checked Access request.');
            return response()->json($formattedData, 200, [], JSON_PRETTY_PRINT);

        // }
        // return view('admin.location.adminaccess', compact('title'));

    }

    public function onlineradius(Request $request){

        $this->login();
        $tokan = session()->get('tokan');
        $title = "Radius-Access Logs";

        $locationshort =$request->location;
        $location = $request->location . ".xceednet.com";

        $useridd=auth()->id();

        $slocations = DB::table('locations')
        ->select('locations.name')
        ->leftJoin('userlocation', 'userlocation.locationid', '=', 'locations.id')
        ->leftJoin('users', 'userlocation.userid', '=', 'users.id')
        ->where('users.id', '=', $useridd)
        ->get();

        if($locationshort){
            if($request->ajax()){
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authentication' => $tokan
                ])->post('https://' . $location . '/subscribers/search_online',['start'=> '0', 'length'=> '100']);

                return DataTables::of($response['data'])
                        ->addIndexColumn()
                        ->addColumn('usern',function ($data){
                            $namelink = '<a href="'.route('subscriber.microtik', ['name' => $data[1]]).'">'. $data[1] .'</a>';
                            return $namelink;
                        })
                        ->addColumn('newmac',function ($data){
                            $newmac = str_replace(':', '-', $data[7]);
                            $newmac = '<a href="'. route('find.mac.vendor', ['mac' => $newmac]) . '">'. $newmac .'</a>';
                        return $newmac;
                        })
                        ->addColumn('newip',function ($data){
                            $tenip = explode(".", $data[8]) ;
                            if($tenip[0]=="10"){
                                $adddd = '<a href="http://'. $data[8] .':8080" target="_new" class="text-danger">'. $data[8] .'</a>';
                            }else{
                                $adddd = '<a href="http://'.$data[8] .':8080" target="_new">'. $data[8] .'</a>';
                            }
                            return $adddd;
                        })
                        ->rawColumns(['usern','newmac','newip'])
                        ->make(true);

                activity()->causedBy(auth()->user())->useLog('Access Log')->log('Checked Access Log for Location '. $locationshort);
                // return view('admin.location.online', compact('title', 'slocations', 'locationshort'));
            }

        }
        return view('location.online', compact('title','slocations','locationshort'));

    }

    public function resetmac(Request $request){
        $this->login();
        $tokan = session()->get('tokan');
        $title = "Subscriber Details";
        $name = $request->name;
        $idd = $request->id;
        $loca = $request->location;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authentication' => $tokan
        ])->post('https://'. $loca . '.xceednet.com/subscribers/update_multiple',['button'=>'Reset MAC','subscriber_ids'=>$idd]);

        // return $response;
        // return view('admin.location.testapi', compact('title', 'response'));


        if($response['status'] =="ok"){
            $msg = "Mac Reseted successfully for user " . $name;
        }else{
            $msg = "Mac not reseted for user " . $name;
        }
        activity()->causedBy(auth()->user())->useLog('MAC Reset')->log($msg);
        return redirect()->route('subscriber.microtik', compact('name','title'))->with('msg', $msg);

    }

    public function macreset(Request $request){
        $this->login();
        $tokan = session()->get('tokan');
        $name = $request->name;
        $idd = $request->id;
        $loca = $request->location;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authentication' => $tokan
        ])->post('https://'. $loca . '.xceednet.com/subscribers/update_multiple',['button'=>'Reset MAC','subscriber_ids'=>$idd]);


        if($response['status'] =="ok"){
            $msg = "Mac Reseted successfully for user " . $name;
            activity()->causedBy(auth()->user())->useLog('MAC Reset')->log($msg);

            return response()->json(['success' => true]);
        }else{
            $msg = "Mac reset failed for user " . $name;
            activity()->causedBy(auth()->user())->useLog('MAC Reset')->log($msg);

            return response()->json(['success' => false]);
        }
        
        // return redirect()->route('subscriber.microtik', compact('name','title'))->with('msg', $msg);

    }

    public function macresetapi(Request $request){
        $this->login();
        $tokan = session()->get('tokan');
        $name = $request->name;
        $idd = $request->id;
        $loca = $request->location;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authentication' => $tokan
        ])->post('https://'. $loca . '.xceednet.com/subscribers/update_multiple',['button'=>'Reset MAC','subscriber_ids'=>$idd]);


        if($response['status'] =="ok"){
            $msg = "Mac Reseted successfully for user " . $name;
            activity()->causedBy(auth()->user())->useLog('MAC Reset - API')->log($msg);

            return response()->json(['success' => true], 200);
        }else{
            $msg = "Mac reset failed for user " . $name;
            activity()->causedBy(auth()->user())->useLog('MAC Reset- API')->log($msg);

            return response()->json(['success' => false], 200);
        }
        
        // return redirect()->route('subscriber.microtik', compact('name','title'))->with('msg', $msg);

    }

    public function enablesubscriber(Request $request){
        $this->login();
        $tokan = session()->get('tokan');
        $title = "Subscriber Details";
        $name = $request->name;
        $idd = $request->id;
        $loca = $request->location;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authentication' => $tokan
        ])->post('https://'. $loca . '.xceednet.com/subscribers/update_multiple',['button'=>'Enable','subscriber_ids'=>$idd]);

        // return $response;
        // return view('admin.location.testapi', compact('title', 'response'));


        if($response['status'] == "ok"){
            $msg = "Enabled successfully for user " . $name;
        }else{
            $msg = "Enable failed for user " . $name;
        }
        activity()->causedBy(auth()->user())->useLog('Enable User')->log($msg);
        return redirect()->route('subscriber.microtik', compact('name','title'))->with('msg', $msg);
    }

    public function enablesubscriberapi(Request $request){
        $this->login();
        $tokan = session()->get('tokan');
        // $title = "Subscriber Details";
        $name = $request->name;
        $idd = $request->id;
        $loca = $request->location;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authentication' => $tokan
        ])->post('https://'. $loca . '.xceednet.com/subscribers/update_multiple',['button'=>'Enable','subscriber_ids'=>$idd]);

        // return $response;
        // return view('admin.location.testapi', compact('title', 'response'));


        if($response['status'] == "ok"){
            $msg = "Enabled successfully for user " . $name;
            activity()->causedBy(auth()->user())->useLog('Enable User - API')->log($msg);
            return response()->json(['success' => true, 'message' => $msg], 200);
        }else{
            $msg = "Enable failed for user " . $name;
            activity()->causedBy(auth()->user())->useLog('Enable User - API')->log($msg);
            return response()->json(['success' => false, 'message' => $msg], 200);
        }
        
        // return redirect()->route('subscriber.microtik', compact('name','title'))->with('msg', $msg);
    }

    public function disablesubscriber(Request $request){
        $this->login();
        $tokan = session()->get('tokan');
        $title = "Subscriber Details";
        $name = $request->name;
        $idd = $request->id;
        $loca = $request->location;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authentication' => $tokan
        ])->post('https://'. $loca . '.xceednet.com/subscribers/update_multiple',['button'=>'Disable','subscriber_ids'=>$idd]);

        if($response['status'] =="ok"){
            $msg = $name . " user successfully Disabled";
        }else{
            $msg = "Disabled failed for user " . $name;
        }
        activity()->causedBy(auth()->user())->useLog('Disable User')->log($msg);

        // return response()->json(['success' => true, 'message' => $msg]);
        return redirect()->route('subscriber.microtik', compact('name','title'))->with('msg', $msg);
    }

    public function disablesubscriberapi(Request $request){
        $this->login();
        $tokan = session()->get('tokan');
        $title = "Subscriber Details";
        $name = $request->name;
        $idd = $request->id;
        $loca = $request->location;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authentication' => $tokan
        ])->post('https://'. $loca . '.xceednet.com/subscribers/update_multiple',['button'=>'Disable','subscriber_ids'=>$idd]);

        if($response['status'] =="ok"){
            $msg = $name . " user successfully Disabled";

            activity()->causedBy(auth()->user())->useLog('Disable User - API')->log($msg);
        
            return response()->json(['success' => true, 'message' => $msg]);
        }else{
            $msg = "Disabled failed for user " . $name;

            activity()->causedBy(auth()->user())->useLog('Disable User - API')->log($msg);
        
            return response()->json(['success' => false, 'message' => $msg]);
        }
        
        // return redirect()->route('subscriber.microtik', compact('name','title'))->with('msg', $msg);
    }

    public function overrightspeed(Request $request){

        $this->login();
        $tokan = session()->get('tokan');
        $title = "OverRightSpeed";
        $id = $request->id;
        $name = $request->name;
        $location = $request->location;
        $dn = $request->dn;
        $up = $request->up;
        $action = $request->action;

        if($action=='yes'){

            if($request->yes=='on'){
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authentication' => $tokan
                ])->patch('https://' . $location . '.xceednet.com/subscribers/'. $id ,['subscriber'=>['override_package_bandwidth'=> '1', 'overridden_bandwidth_upload'=>$up, 'overridden_bandwidth_upload_unit'=> 'M', 'overridden_bandwidth_download'=>$dn, 'overridden_bandwidth_download_unit'=>'M','status_event'=>'override_package_speed']]);

                // return $response;
                $msg = 'Bandwidth Overright Successfully for user ' . $name;

                activity()->causedBy(auth()->user())->useLog('Overright Speed')->log($msg);
                return redirect()->route('subscriber.microtik', compact('name'))->with('msg', $msg);
            }else{
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authentication' => $tokan
                ])->patch('https://' . $location . '.xceednet.com/subscribers/'. $id ,['subscriber'=>['override_package_bandwidth'=> '0', 'overridden_bandwidth_upload'=>$up, 'overridden_bandwidth_upload_unit'=> 'M', 'overridden_bandwidth_download'=>$dn, 'overridden_bandwidth_download_unit'=>'M','status_event'=>'override_package_speed']]);

                // return $response;
                $msg = 'Bandwidth Overright disbled for user ' . $name;

                activity()->causedBy(auth()->user())->useLog('Overright Speed')->log($msg);
                return redirect()->route('subscriber.microtik', compact('name'))->with('msg', $msg);

            }
        }
        return view('admin.radius.speed', compact('title','name','location','id'));
    }

    public function searchsubscriber(Request $request){

        $this->login();
        $tokan = session()->get('tokan');

        $title = "Search Subscriber";
        $idd = $request->id;

        $loca = $request->loca;

        $name = $request->name;

        $location = DB::table('locations')
        ->select('locations.name')
        ->leftJoin('userlocation', 'userlocation.locationid', '=', 'locations.id')
        ->where('userlocation.userid', auth()->user()->id)
        ->get();

        // $linkk = "https://'. $loca . '.xceednet.com/subscribers/search",["columns"=> ["2"=>["search"=>["value"=>"' . $name ]]]];
        // $response1 = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        //     'Version' => 'HTTP/1.0',
        //     'Accept' => 'application/json',
        //     'Authentication' => $tokan
        // ])->post('https://'. $loca . '.xceednet.com/subscribers/search',['columns'=> ['2'=>['search'=>['value'=>$name]]]]);

        // return $response1;

        // if($name){
            if($request->ajax()){
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Version' => 'HTTP/1.0',
                    'Accept' => 'application/json',
                    'Authentication' => $tokan
                ])->post('https://'. $loca . '.xceednet.com/subscribers/search',['search'=>['value'=>$name]]);

                // $daata = $response['data'];

                return DataTables::of($response['data'])
                ->addIndexColumn()
                ->addColumn('usern',function ($data){
                    $namelink = '<a href="'.route('subscriber.microtik', ['name' => $data[1]]).'">'. $data[1] .'</a>';
                    // $namelink = $data[1];
                    return $namelink;
                })
                ->addColumn('online',function ($data){

                    if($data[8]==true){
                        $status = '<p class="text-success">Online</p>';
                    }else{
                        $status = '<p class="text-danger">Offiline</p>';
                    }
                    // $namelink = '<a href="'.route('subscriber.microtik', ['name' => $data[1]]).'">'. $data[1] .'</a>';
                    // $namelink = $data[1];
                    // $status = $data[8];
                    return $status;
                })
                ->addColumn('renewaldt',function ($data){

                    
                    $renew_dt = date('D, d M Y g:i:s A', strtotime($data[9]));
                    
                    // $namelink = '<a href="'.route('subscriber.microtik', ['name' => $data[1]]).'">'. $data[1] .'</a>';
                    // $namelink = $data[1];
                    // $status = $data[8];
                    return $renew_dt;
                })
                ->addColumn('expirydt',function ($data){

                    
                    $expiry_dt = date('D, d M Y g:i:s A', strtotime($data[10]));
                    
                    // $namelink = '<a href="'.route('subscriber.microtik', ['name' => $data[1]]).'">'. $data[1] .'</a>';
                    // $namelink = $data[1];
                    // $status = $data[8];
                    return $expiry_dt;
                })
                ->rawColumns(['usern','online','renewaldt','expirydt'])
                ->make(true);

                // return view('admin.radius.subscriber', compact('title', 'location', 'name', 'loca'));
            }
        // }
        return view('radius.subscriber', compact('title', 'location', 'loca', 'name'));

    }

    public function searchsubscriberall(Request $request){

        $this->login();
        $tokan = session()->get('tokan');

        $title = "Search in All";
        $name = $request->name;

        // $response = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        //     'Version' => 'HTTP/1.0',
        //     'Accept' => 'application/json',
        //     'Authentication' => $tokan
        // ])->post('admin.xceednet.com/operator_subscribers/search',['search'=>['value'=>$name]]); ['start'=> '0', 'length'=> '100']



        if($request->ajax()){
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Version' => 'HTTP/1.0',
                'Accept' => 'application/json',
                'Authentication' => $tokan
            ])->post('admin.xceednet.com/operator_subscribers/search',['search'=>['value'=>$name],'start'=> '0', 'length'=> '99']);

            // $daata = $response['data'];

            return DataTables::of($response['data'])
            ->addIndexColumn()
            ->addColumn('namen',function ($data){
                $namelink = strip_tags($data[1]);
                // $namelink1 = strip_tags($data[1]);
                // $namelink = '<a href="'.route('subscriber.microtik', ['name' => $namelink1 ]).'">'. $namelink1 .'</a>';
                return $namelink;
            })
            ->addColumn('usern',function ($data){
                $user = strip_tags($data[2]);
                return $user;
            })
            ->addColumn('isp',function ($data){
                $ispn = strip_tags($data[3]);
                return $ispn;
            })
            ->addColumn('online',function ($data){
                $stat = strip_tags($data[4]);
                if($stat=="Online"){
                    $status = "Online";
                }else{
                    $status = "Offline";
                }
                // $namelink = '<a href="'.route('subscriber.microtik', ['name' => $data[1]]).'">'. $data[1] .'</a>';
                // $namelink = $data[1];
                return $status;
            })
            ->addColumn('static',function ($data){
                $staticip = strip_tags($data[5]);
                return $staticip;
            })
            ->addColumn('status',function ($data){
                $statusnn = strip_tags($data[8]);
                return $statusnn;
            })
            ->rawColumns(['name','usern','isp','static','online','statusnn'])
            ->make(true);

            // return view('admin.radius.subscriber', compact('title', 'location', 'name', 'loca'));
        }


        // $test =  strip_tags($response['data'][0][0]);
        // return $response;
        return view('radius.subscriberall', compact('title','name'));

    }

    public function searchsubscriberallapi(Request $request)
    {
        $this->login();
        $token = session()->get('tokan');

        // $title = "Search in All";
        $name = $request->name;

        // if($request->ajax()){
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Version' => 'HTTP/1.0',
            'Accept' => 'application/json',
            'Authentication' => $token
        ])->post('admin.xceednet.com/operator_subscribers/search', ['search' => ['value' => $name], 'start' => '0', 'length' => '99']);


        $cleanData = [];

        foreach ($response['data'] as $row) {
            $cleanRow = [];
            foreach ($row as $cell) {
                // Strip HTML tags and decode HTML entities
                $cleanCell = html_entity_decode(strip_tags($cell));
                $cleanRow[] = $cleanCell;
            }
            $cleanData[] = $cleanRow;
        }

        // Convert the array of arrays to an array of objects with meaningful keys
        $result = [];
        foreach ($cleanData as $item) {
            $result[] = [
                'id' => $item[0],
                'username' => $item[1],
                'fullName' => $item[2],
                'company' => $item[3],
                'online' => $item[4],
                'staticip' => $item[5],
                'startDate' => $item[6],
                'endDate' => $item[7],
                'status' => $item[8],
            ];
        }

        activity()
            ->causedBy(auth()->user())
            ->useLog('All Location Users - api')
            ->log('Search user using query ' . $name);

            $sortColumn = array_column($result, 'online'); // Replace 'column_name' with your actual column name

        // Sort the array
        array_multisort($sortColumn, SORT_DESC, $result);
        return response()->json($result, 200, [], JSON_PRETTY_PRINT);
    }


    public function allactiveusers(){

        $this->login();
        $tokan = session()->get('tokan');

        // $title = "Subscriber Details";

        $location = DB::table('locations')
                    ->where('enable', 1)
                    ->get();

        $activeusercount = 0;

        $dattta = DB::table('subscribercount')
        ->where('datee', now()->format('Y-m-d'))
        ->get();

        // return $dattta;

        if(!$dattta->isEmpty()){
            $activeusercount=$dattta[0]->subcount;
        }else{
            foreach($location as $loc){
                $urll = $loc-> url;
                // $uriii = $loc-> name;
                $response1 = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authentication' => $tokan
                ])->get('https://' . $urll . '/location_dashboard');

                if(isset($response1['active_subscribers_count'])){
                    $activeusercount += $response1['active_subscribers_count'];
                }
            }
            DB::table('subscribercount')
                        ->insertOrIgnore([
                            'datee' => now(),
                            'subcount' => $activeusercount
                        ]);
        }
        return $activeusercount;
    }

    public function findmacvendor(Request $request){
        $title="Find MAC Details";
       $mac = $request->mac;
        if($mac){
            $url = env('MACURL',null) . urlencode($mac);
            $token = env('MACTOKEN',null);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token]);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            $response = json_decode($response);

            // return $response;

            // if (empty($response)) {
            //     return "Not Found";
            // } elseif ($data = $response->data) {
                // return $response;
                return view('location.mac', compact('title','response','mac'));
            // } elseif ($errors = $response->errors) {
            //     return $errors;
            // }
        }

        return view('location.mac', compact('title'));
    }

    public function findmac(Request $request)
    {
        $title = "Find MAC Details";
        // Log::info('findmac function called');

        $mac = $request->mac;
        // Log::info('MAC address:', ['mac' => $mac]);

        if ($mac) {
            $url = env('MACURL', null) . urlencode($mac);
            $token = env('MACTOKEN', null);

            // Log::info('URL and Token:', ['url' => $url, 'token' => $token]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);

            if ($response === false) {
                // Log::error('cURL Error:', ['error' => curl_error($ch)]);
            } else {
                // Log::info('cURL Response:', ['response' => $response]);
            }

            curl_close($ch);
            $response = json_decode($response);

            // Create a tooltip string
            $tooltip = $response->data->organization_name;

            // Return the tooltip string
            return response()->json(['tooltip_data' => $tooltip]);
        }

        // Log::info('No MAC address provided');
        return response()->json(['tooltip_data' => 'No MAC address provided']);
    }

    public function testapi(Request $request){

        // $this->login();
        // $tokan = session()->get('tokan');

        // $title = "Test API";
        // // $idd = $request->id;

        // // $response = Http::withHeaders([
        // //     'Content-Type' => 'application/json',
        // //     'Version' => 'HTTP/1.0',
        // //     'Accept' => 'application/json',
        // //     'Authentication' => $tokan
        // // ])->get('disha.xceednet.com/location_dashboard');


        // $api_url = 'https://disha.xceednet.com/subscriber_access_requests/delete_all';

        //         $headers = [
        //             'Content-Type' => 'application/json',
        //             'Version' => 'HTTP/1.0',
        //             'Accept' => 'application/json',
        //             'Authentication' => $tokan
        //         ];
        //         $array = ['subscriber_id'=> '2892312' ];

        //         $response = Http::withHeaders($headers)->DELETE($api_url, $array);

        // // $test =  strip_tags($response['data'][0][0]);
        // return $response;
        // return view('https://admin.location.testapi', compact('title', 'response'));
        $variable = 'telnet.disable';
        $alertMessage = app('App\Http\Controllers\AlertMessageController')->get($variable);
        return $alertMessage;
    }

}

            //get fetch location packages https://disha.xceednet.com/location_packages
            //post fetch access request https://disha.xceednet.com/subscriber_access_requests/search

            //post fetch online users https://disha.xceednet.com/subscribers/search_online?username=hasibur_raheman_mhada

            //get fetch locattions https://disha.xceednet.com/location_subdomain_and_domains

            //get location dashboard https://disha.xceednet.com/location_dashboard

            //get location login users https://disha.xceednet.com/location_users

            //post search user https://disha.xceednet.com/subscribers/search

            //search user with id https://disha.xceednet.com/subscribers/id

            //searchUrl https://disha.xceednet.com/subscribers/search_subscriber/?username=oltstatic

            //get https://disha.xceednet.com/api/v2/subscribers/dashboard

            //get https://disha.xceednet.com/location_dashboard


            // $response = Http::::withHeaders($headers)->get($apiURL, [
            //     'id' => $id,
            //     'some_another_parameter' => $param
            // ]);


    //         $array = [
    //             'key1' => 'value1',
    //             'key2' => 'value2',
    //             // etc...
    // ];
    // $headers = [
    //             'Content-Type' => 'application/x-www-form-urlencoded',
    //             'HMAC' => $hmac
    // ];

    // $data = http_build_query($array, '', '&');

    //    $response = Http::withHeaders($headers)
    //   ->withBody($data)
    //   ->asForm()
    //   ->post($api_url);


    //git rm --cached public/hgraph.svg


    // http://ip-api.com/json/103.91.123.3

//MACURL = https://api.macvendors.com/v1/lookup
// MACTOKEN = eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiIsImp0aSI6ImM1MGQ2N2U2LTU0MDUtNGRmZS1hZTFhLWY0Zjk1ZDJiYjA1ZSJ9.eyJpc3MiOiJtYWN2ZW5kb3JzIiwiYXVkIjoibWFjdmVuZG9ycyIsImp0aSI6ImM1MGQ2N2U2LTU0MDUtNGRmZS1hZTFhLWY0Zjk1ZDJiYjA1ZSIsImlhdCI6MTcyMjc5OTk3MSwiZXhwIjoyMDM3Mjk1OTcxLCJzdWIiOiIxNDc5NiIsInR5cCI6ImFjY2VzcyJ9.6PORyrKPiaKUJG8Gm7eP3KgSwHo-x7zLLIRm_bh5t6tmJ3JExQE9EkIuxbBafN0SzG288myTOxBHaJFi07ATjg


// https://ipapi.co/8.8.8.8/json/ //for ip details

//https://ipapi.co/ip/ for customer ip
