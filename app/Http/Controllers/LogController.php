<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
// use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\User;

class LogController extends Controller
{

    public function __construct()
     {
        //  // $this->middleware('role:super-admin','permission:add-server',['only' => ['create','store']]); role example
         $this->middleware('permission:all-logs',['only' => ['show']]);
         $this->middleware('permission:delete-all-logs',['only' => ['delete']]);
     }


    public function show(Request $request){

        // $allLogs = DB::table('activity_log')->get();
        // return $allLogs;

        $title = 'All Logs';
        if($request->ajax()){
            $allLogs = DB::table('activity_log')->orderBy('updated_at', 'desc')->get();
            return DataTables::of($allLogs)
                ->addIndexColumn()
                ->addColumn('user',function ($allLogs){
                    $userdt = User::find($allLogs->causer_id);
                    return $userdt->name;
                })
                ->rawColumns(['user'])
                ->make(true);
        }
        return view('logs.all',compact('title'));
    }

    public function alllogsapi(Request $request){

            $allLogs = DB::table('activity_log')->orderBy('updated_at', 'desc')->get();

            $allLogs->map(function ($log) {
                if ($log->causer_id) {
                    $user = User::find($log->causer_id);
                    $log->user_name = $user ? $user->name : null;
                } else {
                    $log->user_name = null;
                }
                return $log;
            });
        
            activity()->causedBy(auth()->user())->useLog('Logs - Api')->log('All Logs Viewed');

            return response()->json($allLogs);

    }

    public function showlogs(Request $request){

        // $allLogs = DB::table('activity_log')->where('causer_id', auth()->id())->orderBy('updated_at', 'desc')->get();
        // return $allLogs;

        $title = 'Logs';
        if($request->ajax()){
            $allLogs = DB::table('activity_log')->where('causer_id', auth()->id())->orderBy('updated_at', 'desc')->get();
            return DataTables::of($allLogs)
                ->addIndexColumn()
                ->make(true);
        }
        activity()->causedBy(auth()->user())->useLog('Logs')->log('Logs Viewed');
        return view('logs.user',compact('title'));
    }

    public function showlogsapi(Request $request){

        activity()->causedBy(auth()->user())->useLog('Logs - Api')->log('Logs Viewed');
        $allLogs = DB::table('activity_log')->where('causer_id', auth()->id())->orderBy('updated_at', 'desc')->get();
        
        return response()->json($allLogs);

    }

    public function delete()
    {
        DB::table('activity_log')->delete();
        DB::statement('ALTER TABLE activity_log AUTO_INCREMENT = 1');

        $msg = 'All Logs Cleared';
        activity()->causedBy(auth()->user())->useLog('Logs')->log($msg);

        return redirect()->route('show.alllogs')->with('msg',$msg);
    }
}
