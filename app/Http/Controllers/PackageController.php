<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\FetchapiController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;


class PackageController extends Controller
{
    public function __construct()
    {
        // $this->middleware('role:super-admin','permission:add-server',['only' => ['create','store']]); role example
        // $this->middleware('permission:view-packages',['only' => ['show','insertdelete']]);

    }

    public function show(Request $request){

        $newVar = new FetchapiController();
        $newVar->login();
        $tokan = session()->get('tokan');
        $title = "Location Packages";
        $userid=auth()->id();
        $slocation=$request->location;

        //
            // return $slocation;
        $locations = DB::table('locations')
        ->select('locations.name', 'locations.id')
        ->leftJoin('userlocation', 'userlocation.locationid', '=', 'locations.id')
        ->leftJoin('users', 'userlocation.userid', '=', 'users.id')
        ->where('users.id', '=', $userid)
        ->get();

        if($slocation){

            $slocationname=DB::table('locations')
            ->select('name')
            ->where('id', '=', $slocation)
            ->get();

            $packages = DB::table('packages')
            ->where('locationid', '=', $slocation)
            ->get();

            // return $packages;

            // return $slocationname;
            $locationname = $slocationname[0]->name;
            $url = "https://". $locationname . ".xceednet.com/location_packages";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authentication' => $tokan
            ])->get($url);

            activity()->causedBy(auth()->user())->useLog('Location')->log('Retrive Location packages of ' . $locationname);

            return view('package.show', compact('title', 'response','locations','locationname','packages'));
        }


        return view('package.show', compact('title','locations'));

    }

    public function insertdelete(Request $request){

        // return $request;
        $locationnm = $request->locationname;
        $idd = $request->rid;

        $locaionid= DB::table('locations')
        ->select('id')
        ->where('name', '=', $locationnm)
        ->get();

        $location = $locaionid[0]->id;
        // return $locaionid[0]->id;

        if($request->btn=="Insert"){
        DB::table('packages')
        ->insert([
            'radiusid' => $request->rid,
            'name' => $request->name,
            'desc' => $request->description,
            'locationid' =>$locaionid[0]->id

        ]);
        $msg = $request->name . ' added Successfully';

    }else{
        $pkg = DB::table('packages')
                    ->where('radiusid', $idd)
                    ->delete();

        if($pkg){
            $msg = $request->name . ' deleted Successfully';
        }else{
            $msg = $request->name . ' not deleted Successfully';
        }

    }

    activity()->causedBy(auth()->user())->useLog('Location Package')->log($msg . ' for location ' . $locationnm);

        return redirect()->route('packages.show', compact('location'))
        ->with('msg', $msg);

    }
}
