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


        return view('dashboard',compact('title'));
    }
}