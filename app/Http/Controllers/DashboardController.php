<?php

namespace App\Http\Controllers;
use App\Models\RouterosAPI;
use App\Models\Server;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request){
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
        $selectedPreset = $request->input('preset', 'current-year');
        $selectedYear = (int) $request->input('year', now()->year);
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $subscriberChartData = $this->getSubscriberChartData($request);
        $availableYears = DB::table('subscribercount')
            ->selectRaw('YEAR(datee) as year')
            ->distinct()
            ->pluck('year')
            ->sort()
            ->values()
            ->all();

        if (empty($availableYears)) {
            $availableYears = [(int) now()->year];
        }

        return view('dashboard', compact('title','userss','total_servers','total_locations','upsensorcount','downsensorcount','totalsensorcount','totalactiveuserc','msebstatus','temp','disabledsubscribers','subscriberChartData','availableYears','selectedPreset','selectedYear','fromDate','toDate'));

        // return view('dashboard',compact('title'));
    }


    private function getSubscriberChartData(Request $request = null)
    {
        $query = DB::table('subscribercount')
            ->select('datee', 'subcount');

        $preset = $request?->input('preset', 'current-year');
        $selectedYear = (int) ($request?->input('year', now()->year) ?? now()->year);
        $fromDate = $request?->input('from_date');
        $toDate = $request?->input('to_date');

        if ($preset === 'previous-year') {
            $query->whereYear('datee', $selectedYear - 1);
        } elseif ($preset === 'last-month') {
            $start = now()->subMonth()->startOfMonth()->toDateString();
            $end = now()->subMonth()->endOfMonth()->toDateString();
            $query->whereBetween('datee', [$start, $end]);
        } elseif ($preset === 'current-month') {
            $start = now()->startOfMonth()->toDateString();
            $end = now()->endOfMonth()->toDateString();
            $query->whereBetween('datee', [$start, $end]);
        } elseif ($preset === 'current-year') {
            $query->whereYear('datee', $selectedYear);
        } elseif ($preset === 'custom') {
            if ($fromDate && $toDate) {
                if ($fromDate > $toDate) {
                    [$fromDate, $toDate] = [$toDate, $fromDate];
                }
                $query->whereBetween('datee', [$fromDate, $toDate]);
            } elseif ($fromDate) {
                $query->whereDate('datee', '>=', $fromDate);
            } elseif ($toDate) {
                $query->whereDate('datee', '<=', $toDate);
            }
        } elseif ($preset === 'all') {
            // keep all records
        }

        $records = $query->orderBy('datee', 'asc')->get()->sortBy(function ($record) {
            return $record->datee;
        })->values();

        $datewiseLabels = [];
        $datewiseSeries = [];
        $monthwiseLabels = [];
        $monthwiseSeries = [];
        $monthlyData = [];

        foreach ($records as $record) {
            $date = \Carbon\Carbon::parse($record->datee);
            $datewiseLabels[] = $date->format('d M Y');
            $datewiseSeries[] = (int) $record->subcount;

            $monthKey = $date->format('Y-M');
            $monthLabel = $date->format('M Y');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [
                    'label' => $monthLabel,
                    'value' => 0,
                    'count' => 0,
                ];
            }

            $monthlyData[$monthKey]['value'] += (int) $record->subcount;
            $monthlyData[$monthKey]['count']++;
        }

        $sortedMonthKeys = array_keys($monthlyData);
        sort($sortedMonthKeys);

        foreach ($sortedMonthKeys as $monthKey) {
            $data = $monthlyData[$monthKey];
            $monthwiseLabels[] = $data['label'];
            $monthwiseSeries[] = round($data['value'] / $data['count']);
        }

        return [
            'datewise' => [
                'labels' => $datewiseLabels,
                'series' => $datewiseSeries,
            ],
            'monthwise' => [
                'labels' => $monthwiseLabels,
                'series' => $monthwiseSeries,
            ],
        ];
    }

    public function getSubscriberChartDataAjax(Request $request)
    {
        $chartData = $this->getSubscriberChartData($request);

        return response()->json($chartData);
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