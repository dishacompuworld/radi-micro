@php
   if(isset($location)){$location;}else{$location="";}
   if(isset($name)){$name;}else{$name="";}
   if(isset($id)){$id;}else{$id="";}

//    if(isset($urll)){ $subdomain = strtok($urll, '.');}

//    if(isset($response1['data']['online'])){
//         if($response1['data']['online']=='true'){
//             $online="Yes";
//         }else{
//             $online="No";
//     }
//     $urll = $loca . ".xceednet.com";

//     if($loca=='disha' & Auth::user()->hasRole('super-admin')){

//         $radiuslable = "Xceednet Link / UniCRM Link";
//         $radiuslink = "<a href='https://". $urll . "/subscribers/" . $response1['data']['id'] . "' target='_new'>Xceednet</a> / <a href='https://superclick.dishacompuworld.com/net/index.php?page=view_customer&q=" . $response1['data']['username']. "' target='_new'>UniCRM</a>";

//         }else {
//             $radiuslable = "Xceednet Link";
//             $radiuslink = "<a href='https://". $urll . "/subscribers/" . $response1['data']['id'] . "' target='_new'>Xceednet</a>";
//         }
//     }

@endphp
@extends('layouts.admin')


@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Subscriber Details</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Subscriber Details</li>
                        <li class="breadcrumb-item active">Overright Speed</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
            <!-- Subscribers -->
            <form name='speed' action="{{ route('speed.change')}}" method="GET">
            <table class="table-striped">
                <tr><th>User Name </th><td>{{ $name }}</td></tr>
                <tr><th>Location Name </th><td>{{ $location }}.xceednet.com</td></tr>
                <tr><th>Overtight Speed</th><td><input type="checkbox" name="yes"></td></tr>
                <tr>
                    <th>Overright Download Speed</th>
                    <td>
                        <select name='dn'>
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                        </select> Mb
                    </td>
                </tr>
                <tr>
                    <th>Overright Upload Speed</th>
                    <td>
                        <select name='up'>
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                        </select> Mb
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="left">
                        <input type="hidden" name="action" value="yes">
                        <input type="hidden" name="location" value="{{ $location }}">
                        <input type="hidden" name="id" value="{{ $id }}">
                        <input type="hidden" name="name" value="{{ $name }}">
                        <input type="submit" class="btn btn-success btn-sm">
                    </td>
                </tr>
            </table>
            </form>
            <!-- /Subscribers -->
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-start mt-3">
        <a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
    </div>
    </div>
</div>
@endsection
