<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\FetchapiController;
use Illuminate\Support\Facades\Http;
use App\Models\location;

class LocationController extends Controller
{

    public function __construct()
    {
        // $this->middleware('role:super-admin','permission:add-server',['only' => ['create','store']]); role example
        $this->middleware('permission:add-location',['only' => ['insert']]);
        $this->middleware('permission:view-locations',['only' => ['show']]);
        $this->middleware('permission:delete-location',['only' => ['deleted']]);
    }

    public function show(){

        $title="All Locations";
        $newVar = new FetchapiController();
        $newVar->login();

        $tokan = session()->get('tokan');
        // return $tokan;

        $response1 = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authentication' => $tokan
        ])->get('https://disha.xceednet.com/location_subdomain_and_domains');


        $locations = Location::where('enable','1')->get();




        // return $response1;

        return view('location.show', compact('title','response1','locations'));

    }

    public function insert($name, $url){



        $location = new Location;

        $urll = $name . "." . $url;

        $location->name = $name;
        $location->url = $urll;
        $location->enable = 1;

        $location->save();

        return redirect()->route('location.show')
        ->with('msg', $location->name . ' added Successfully.');

    }

    public function deleted($name){

        // $location = Location::find($name);

        // return $location;

        // $location->delete();


        $location = DB::table('locations')
                    ->where('name', $name)
                    ->delete();

        if($location){
            activity()->causedBy(auth()->user())->useLog('Location')->log('Location ' . $name . ' Deleted Successful');
            return redirect()->route('location.show')
        ->with('msg', 'Location ' . $name . ' Deleted from database Successfully.');
        }else{
            return redirect()->route('location.show')
        ->with('msg', 'Location ' . $name . ' Not Deleted from database.');
        }
    }

    public function showapi(){

        // $title="All Locations";
        $newVar = new FetchapiController();
        $newVar->login();

        $tokan = session()->get('tokan');
        // return $tokan;

        // $response1 = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        //     'Accept' => 'application/json',
        //     'Authentication' => $tokan
        // ])->get('https://disha.xceednet.com/location_subdomain_and_domains');


        $locations = Location::where('enable','1')->get();




        // return $locations;

        return response()->json($locations, 200);

    }
}
