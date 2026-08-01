@php
$info = exec('systeminfo | find /i "Boot Time"');
$timeee = trim(str_replace("System Boot Time:", "", $info));

function format_interval(DateInterval $interval) {
        $result = "";
        if ($interval->y) { $result .= $interval->format("%y years "); }
        if ($interval->m) { $result .= $interval->format("%m months "); }
        if ($interval->d) { $result .= $interval->format("%d days "); }
        if ($interval->h) { $result .= $interval->format("%h hours "); }
        if ($interval->i) { $result .= $interval->format("%i minutes "); }
        if ($interval->s) { $result .= $interval->format("%s seconds "); }

        return $result;
    }

$now = now();
$systemdate = new DateTime($timeee);
$diff = $now->diff($systemdate);
$uptime = format_interval($diff);

// if ($msebstatus['success'] && $msebstatus['statustext'] == 'Up') {
//     $mseb = 'Up';
//     $updowntime = $msebstatus['uptime'];
// } else {
//     $mseb = 'Down';
//     $updowntime = $msebstatus['downtime'];
// }

// echo $temp[];

// $bgClass = $mseb == 'Up' ? 'bg-success' : ($mseb == 'Down' ? 'bg-danger' : 'bg-secondary');
// $bgOpacity = 'bg-opacity-25';

@endphp

@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-7 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex flex-column flex-sm-row align-items-center gap-3">
                        <div>
                            <h5 class="card-title text-primary mb-2">Welcome back {{ auth()->user()->name }} 🎉</h5>
                            <p class="mb-0 text-muted">Have a nice day! Here's what's happening with your network today.</p>
                        </div>
                        <div class="ms-auto text-center">
                            <img src="{{ asset('assets/img/avatar.png') }}" class="img-fluid rounded-circle" width="65" height="65" alt="User avatar" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="bx bx-history fs-2 text-primary"></i>
                        <div>
                            <h6 class="card-title text-primary mb-1">Server Uptime</h6>
                            <p class="mb-0 text-muted">{{ $systemdate->format('d M Y, h:i:s A') }}</p>
                            <span class="fw-semibold">{{ $uptime }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{$total_servers}}</h3>
                            <p class="mb-0 text-muted">Live Servers</p>
                        </div>
                        <i class="bx bx-server fs-2 text-success"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{$total_locations}}</h3>
                            <p class="mb-0 text-muted">Active Locations</p>
                        </div>
                        <i class="bx bx-map fs-2 text-primary"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{$upsensorcount}} / {{$totalsensorcount}}</h3>
                            <p class="mb-0 text-muted">Up Sensors</p>
                        </div>
                        <i class="bx bx-check-circle fs-2 text-warning"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{$totalactiveuserc}}</h3>
                            <p class="mb-0 text-muted">Active Radius Users</p>
                        </div>
                        <i class="bx bx-group fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{$userss}}</h3>
                            <p class="mb-0 text-muted">Microtik Users</p>
                        </div>
                        <i class="bx bx-user fs-2 text-secondary"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{\DB::table('opticalpowers')->count()}}</h3>
                            <p class="mb-0 text-muted">Total ONTs</p>
                        </div>
                        <i class="bx bx-wifi fs-2 text-success"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-danger mb-1">{{$disabledsubscribers}}</h3>
                            <p class="mb-0 text-muted">Disabled Users</p>
                        </div>
                        <i class="bx bx-block fs-2 text-danger"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{$total_servers}}</h3>
                            <p class="mb-0 text-muted">Live Servers</p>
                        </div>
                        <i class="bx bx-server fs-2 text-success"></i>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

@endsection