@php
    if(isset($response1['data']['is_online'])){
        if($response1['data']['is_online']=='true'){
            $online="Yes";

            $cardHeader="background-color: #87ff87;";
            $cardBody="background-color: #eafdea;";
        }else{
            $online="No";
            $cardHeader="background-color: #ec6262;";
            $cardBody="background-color: #f1dbdb;";
    }}else{
            $online="No";
            $cardHeader="background-color: #ec6262;";
            $cardBody="background-color: #f1dbdb;";
    }
    if(isset($loca)){$loca;}else{$loca="";}
    if(isset($doid)){$doid;}else{$doid="";}

    if(isset($urll)){
        $subdomain = strtok($urll, '.');
    }

    if($subdomain=='disha' & Auth::user()->hasRole('super-admin')){
        $radiuslable = "Xceednet Link / UniCRM Link";
        $radiuslink = "<a href='https://". $urll . "/subscribers/" . $response1['data']['id'] . "' target='_new'>Xceednet</a> / <a href='https://superclick.dishacompuworld.com/net/index.php?page=view_customer&q=" . $response1['data']['username']. "' target='_new'>UniCRM</a>";
    }else {
        $radiuslable = "Xceednet Link";
        $radiuslink = "<a href='https://". $urll . "/subscribers/" . $response1['data']['id'] . "' target='_new'>Xceednet</a>";
    }

    function convertData($size){
    if ($size <= 0) {
        return "0 B";
    }
    $base = log($size) / log(1024);
    $suffix = array("", "KB", "MB", "GB", "TB");
    $f_base = floor($base);
    return round(pow(1024, $base - floor($base)), 1) . " " . $suffix[$f_base];
}

    function dateDiff($date)
    {
        $mydate= date("Y-m-d H:i:s");
        $theDiff="";
        //echo $mydate;//2014-06-06 21:35:55
        $datetime1 = date_create($date);
        $datetime2 = date_create($mydate);
        $interval = date_diff($datetime1, $datetime2);
        //echo $interval->format('%s Seconds %i Minutes %h Hours %d days %m Months %y Year    Ago')."<br>";
        $min=$interval->format('%i');
        $sec=$interval->format('%s');
        $hour=$interval->format('%h');
        $mon=$interval->format('%m');
        $day=$interval->format('%d');
        $year=$interval->format('%y');
        if($interval->format('%i%h%d%m%y')=="00000") {
            //echo $interval->format('%i%h%d%m%y')."<br>";
            return $sec." Seconds";
        } else if($interval->format('%h%d%m%y')=="0000"){
            return $min." Minutes";
        } else if($interval->format('%d%m%y')=="000"){
            return $hour." Hours";
        } else if($interval->format('%m%y')=="00"){
            return $day." Days";
        } else if($interval->format('%y')=="0"){
            return $mon." Months";
        } else{
            return $year." Years";
        }
    }


    function format_interval(DateInterval $interval) {
        $result = "";
        if ($interval->y) { $result .= $interval->format("%y years "); }
        if ($interval->m) { $result .= $interval->format("%m months "); }
        if ($interval->d) { $result .= $interval->format("%d days "); }
        if ($interval->h) { $result .= $interval->format("%h hours "); }
        if ($interval->i) { $result .= $interval->format("%i minutes "); }
        if ($interval->s) { $result .= $interval->format("%s seconds"); }

        return $result;
    }

    function secondsToTime($inputSeconds) {
        $secondsInAMinute = 60;
        $secondsInAnHour = 60 * $secondsInAMinute;
        $secondsInADay = 24 * $secondsInAnHour;

        // Extract days
        $days = floor($inputSeconds / $secondsInADay);

        // Extract hours
        $hourSeconds = $inputSeconds % $secondsInADay;
        $hours = floor($hourSeconds / $secondsInAnHour);

        // Extract minutes
        $minuteSeconds = $hourSeconds % $secondsInAnHour;
        $minutes = floor($minuteSeconds / $secondsInAMinute);

        // Extract the remaining seconds
        $remainingSeconds = $minuteSeconds % $secondsInAMinute;
        $seconds = ceil($remainingSeconds);

        // Format and return
        $timeParts = [];
        $sections = [
            'day' => (int)$days,
            'hour' => (int)$hours,
            'minute' => (int)$minutes,
            'second' => (int)$seconds,
        ];

        foreach ($sections as $name => $value){
            if ($value > 0){
                $timeParts[] = $value. ' '.$name.($value == 1 ? '' : 's');
            }
        }

        return implode(', ', $timeParts);
    }


    if($opticalpower==0){
        $opcardHeader="background-color: #ec6262;";
        $opcardBody="background-color: #f1dbdb;";
    }elseif($opticalpower=="Ont Not assign"){
        $opcardHeader="background-color: #d4d4d4;";
        $opcardBody="background-color: #eeeeee;";
    }elseif($opticalpower=="Snmp Not Available"){
        $opcardHeader="background-color: #d4d4d4;";
        $opcardBody="background-color: #eeeeee;";
    }else{
        $opcardHeader="background-color: #87ff87;";
        $opcardBody="background-color: #eafdea;";
    }
@endphp
@extends('admin.layouts.header')

<x-assets.datatables />

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Subscriber Details</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Subscriber Details</li>
	</ul>
</div>
@endpush

@section('content')
<div id="message-container" style="display: none;" class="col-sm-12"></div>
<div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-body">
            <div>
                @if (session('msg'))
                <label class="badge badge-success"> {{ session('msg') }}</lable>
                @endif
            </div>
		<!-- Subscribers -->
		{{-- <h6>Location Name : <b>{{ $urll }}</b> {{ $fromd }}</h6> --}}
            <table class="table-striped">

                @php
                    // if (array_key_exists('error_status', $response1)){
                        // $err = $response1['error_status'];
                    // }else{
                        $err = 'No Error';
                    // }
                @endphp

                    @if ($err=="not_found")
                        <tr><th colspan="2">User Not Found</th></tr>
                    @else
                        @if (isset($response1['data']['id']))
                        @php
                            $renewdate = new DateTime($response1['data']['renewed_at']);
                            $renewdt = $renewdate->format('d M Y, h:i:s A');

                            $expirydate = new DateTime($response1['data']['expires_at']);
                            $expirydt = $expirydate->format('d M Y, h:i:s A');

                            $now = now();

                            $diff = date_diff($now,$expirydate);
                            $remainingdays = $diff->format("%R%a days remaining");

                            $diff2 = date_diff($now, $renewdate);
                            $useddays = $diff2->format("%R%a days used");

                            $lstlogindate = new DateTime($response1['data']['last_login_at']);
                            $lstlogindt = $lstlogindate->format('d M Y,h:i:s A');

                            // $diff_mins = dateDiff($response1['data']['last_login_at']);
                            $diff = $now->diff($lstlogindate);
                            $diff_mins = format_interval($diff);

                            // $downloadtoday = $response1['data']['bytes_uploaded_in_24_hours'] / (1024 * 1024);
                            $downloadtoday = convertData($response1['data']['bytes_uploaded_in_24_hours']);
                            // $uploadtoday = $response1['data']['bytes_downloaded_in_24_hours'] / (1024 * 1024);
                            $uploadtoday = convertData($response1['data']['bytes_downloaded_in_24_hours']);
                            $totaltoday =  convertData($response1['data']['bytes_uploaded_in_24_hours'] + $response1['data']['bytes_downloaded_in_24_hours']);

                            if($response1['data']['advance_renewal']){
                                $adv = 'Yes';
                            }else {
                                $adv ='No';
                            }

                            $mbl = $response1['data']['mobile1'];

                        @endphp
                            {{-- <tr><th>ID</th><td>{{ $response1['data']['id'] }} </td></tr> --}}

                            {{-- <tr><th>SubDomain</th><td>{{ $subdomain }}</td></tr> --}}

                            @foreach ($userlocations as $uLoc)
                                @if ($uLoc->name == $subdomain)

                                    <tr>
                                        <td colspan="2">
                                            @can('reset-mac')
                                                {{-- <a href="{{ route('reset.mac',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$subdomain]) }}" class="btn btn-warning btn-sm">Reset MAC</a> --}}
                                                <a href="javascript:void(0)" class='btn btn-warning btn-sm' id="resetmac" data-name="{{ $response1['data']['username'] }}" data-id="{{ $response1['data']['id'] }}" data-location="{{ $subdomain }}">Reset MAC</a>
                                            @endcan

                                            @can('enable-disable')
                                                @if ($response1['data']['status']=="disabled")
                                                    <a href="{{ route('enable.subscriber',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$subdomain]) }}" class="btn btn-success btn-sm">Enable</a>
                                                @else
                                                    <a href="{{ route('disable.subscriber',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$subdomain]) }}" class="btn btn-danger btn-sm">Disable</a>
                                                @endif
                                            @endcan

                                            @can('overright-bandwidth')
                                                <a href="{{ route('speed.change',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$subdomain]) }}" class="btn btn-warning btn-sm">Overright Speed</a>
                                            @endcan
                                            <a href="{{ route('subscriber.accessrequest',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$subdomain]) }}" class="btn btn-primary btn-sm">Access Request Logs</a>
                                            @can('assign-optical-power')
                                                @if($subdomain=="disha")
                                                    <a href="{{ route('assign.ont',['name'=>$response1['data']['username'], 'oid'=>$doid]) }}" class="btn btn-success btn-sm">Assing/Update ONT</a>
                                                @endif
                                            @endcan
                                            @if($opticalpower!="Ont Not assign")
                                                {{-- <input type='Button' value='Reboot ONT' data-oid='{{ $doid }}' id='rebootont' class='btn btn-danger btn-sm'> --}}
                                                <a href="javascript:void(0)" class='btn btn-danger btn-sm' id="rebootont" data-oid="{{ $doid }}">Reboot ONT</a>
                                                @can('register-ont')
                                                <a href="javascript:void(0)" class='btn btn-danger btn-sm' id="deregist" data-oid="{{ $doid }}">De-Register</a>
                                                <a href="javascript:void(0)" class='btn btn-danger btn-sm' id="regist" data-oid="{{ $doid }}">Register</a>    
                                                @endcan
                                            @endif
                                            
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif

                        {{-- @endforeach --}}
                @endif
            </table>

		<!-- /Subscribers -->
	</div>
</div>
    </div>
</div>


<div class="container-fluid">
    <div class="row">
      <div class="col-sm-6 d-flex justify-content-center">
        <div class="card shadow p-3 mb-5 bg-body rounded" style="width: 100%;">
          <div class="card-header" style="{{ $cardHeader }}"><h5 class="card-title">User Details</h5></div>
          <div class="card-body" style="{{ $cardBody }}">
            <table class="table-striped" width="100%">
                <tr><th>Location Name </th><td><b>{{ $urll }}</b></td></tr>
                <tr><th>UserName</th><td>{{ $response1['data']['username'] }}</td></tr>
                <tr><th>Name</th><td>{{ $response1['data']['name'] }}</td></tr>
                <tr><th>{{ $radiuslable }}</th><td>{!! html_entity_decode($radiuslink) !!}</td></tr>
                <tr><th>Address</th><td>{{ $response1['data']['address1'] }}</td></tr>
                <tr><th>Mobile</th><td><a href="whatsapp://send?phone={{ $mbl }}">{{ $mbl }}</a> <i class="bi bi-whatsapp"></i></td></tr>
                @if ($response1['data']['status']=="expired")
                    <tr><th>Status</th><td class="text-danger">Expired</td></tr>
                @endif
                @if($online=="Yes")
                <tr class="text-success"><th>Online</th><td class="text-success">{{ $online }}</td></tr>
                @else
                <tr class="text-danger"><th>Online</th><td class="text-danger">{{ $online }}</td></tr>
                @endif
                <tr><th>Subscriber Since</th><td>{{ $response1['data']['subscriber_since'] }}</td></tr>
            </table>
          </div>
        </div>
      </div>
      <div class="col-sm-6 d-flex justify-content-center">
        <div class="card shadow p-3 mb-5 bg-body rounded" style="width: 100%;">
          <div class="card-header" style="background-color: #d4d4d4;"><h5 class="card-title">Package Details</h5></div>
          <div class="card-body" style="background-color: #eeeeee;">
            <table class="table-striped" width="100%">
                <tr><th>Last Login at</th><td>{{ $lstlogindt . " (" . $diff_mins .")" }}</td></tr>
                <tr><th>Location Package Name</th><td>{{ $response1['data']['location_package_name'] }}</td></tr>
                <tr><th>Renewed at</th><td>{{ $renewdt .  " (" . $useddays .")" }}</td></tr>
                <tr><th>Expires at</th><td>{{ $expirydt . " (" . $remainingdays . ")"}}</td></tr>
                @if ($response1['data']['override_package_bandwidth'])
                <tr><th>Override Package Bandwidth</th><td>Yes</td></tr>
                <tr><th>Overridden Bandwidth Upload</th><td>{{ $response1['data']['overridden_bandwidth_upload'].$response1['data']['overridden_bandwidth_upload_unit'] }}</td></tr>
                <tr><th>Overridden bandwidth Download</th><td>{{ $response1['data']['overridden_bandwidth_download'].$response1['data']['overridden_bandwidth_download_unit'] }}</td></tr>
                @else
                <tr><th>Override Package Bandwidth</th><td>No</td></tr>
                @endif
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6 d-flex justify-content-center">
        <div class="card shadow p-3 mb-5 bg-body rounded" style="width: 100%;">
           <div class="card-header" style="{{ $opcardHeader }}"><h5 class="card-title">ONU / ONT Details (live)</h5></div>
          <div class="card-body" style="{{ $opcardBody }}">
            <table class="table-striped" width="100%">
                <tr><th>Model</th><td>{{ $ontmodel }}</td></tr>
                <tr>
                    <th>Optical Power</th>
                        @php
                            if($opticalpower <= env('MIN_ONT_POWER',null)){
                                echo '<td class="text-danger"><b>'. $opticalpower . '</b></td>';
                            }elseif($opticalpower=="Ont Not assign"){
                                echo '<td>'. $opticalpower . '</td>';
                            }elseif($opticalpower=="Snmp Not Available"){
                                echo '<td>'. $opticalpower . '</td>';
                            }elseif($opticalpower=="Not Available"){
                                echo '<td>'. $opticalpower . '</td>';
                            }elseif($opticalpower==0){
                                echo '<td class="text-danger">Offline</td>';
                            }else{
                                echo '<td class="text-success"><b>'. $opticalpower . ' dBm</b></td>';
                            }
                        @endphp
                        {{-- {{ env('MIN_ONT_POWER',null) }} --}}
                </tr>
                <tr>
                    <th>TX Power</th>
                        @php
                    //    echo $ontuptime;
                            if($opticalpower=="Ont Not assign"){
                                echo '<td>'. $opticaltxpower . '</td>';
                            }elseif($opticalpower=="Not Available"){
                                echo '<td>'. $opticaltxpower . '</td>';
                            }elseif($opticalpower=="Snmp Not Available"){
                                echo '<td>'. $opticaltxpower . '</td>';
                            }else{
                                echo '<td>'. $opticaltxpower . ' dBm</td>';
                            }
                        @endphp
                </tr>
                <tr>
                    <th>Uptime</th>
                    <td>
                        @php
                    //    echo $ontuptime;
                            if($ontuptime=="Ont Not assign"){
                                echo $ontuptime;
                            }elseif($ontuptime=="Not Available"){
                                echo $ontuptime;
                            }elseif($ontuptime=="Snmp Not Available"){
                                echo $ontuptime;
                            }else{
                                echo secondsToTime($ontuptime);
                            }
                        @endphp
                    </td>
                </tr>
                <tr><th>Serial</th><td>{{ $ontserial }}</td></tr>
                <tr><th>Temp</th>
                        @php
                        
                            if($onttemp=="Ont Not assign"){
                                echo '<td>'.$onttemp.'</td>';
                            }elseif ($onttemp=="Not Available") {
                                echo '<td>'.$onttemp.'</td>';
                            }elseif ($onttemp=="Snmp Not Available") {
                                echo '<td>'.$onttemp.'</td>';
                            }elseif($onttemp>=50){
                                echo '<td class="text-danger"><b>'.$onttemp.'&deg;C</b></td>';
                            }else{
                                echo '<td>'.$onttemp.'&deg;C</td>';
                            }
                        @endphp
                </tr>
                <tr><th>EthernetPorts</th><td>{{ $onteth }}</td></tr>
                <tr><th>Distance</th>
                    <td>
                        @php
                        if($ontdist=="Ont Not assign"){
                            echo $ontdist;
                        }elseif ($ontdist=="Not Available") {
                            echo $ontdist;
                        }elseif ($ontdist=="Snmp Not Available") {
                            echo $ontdist;
                        }else{
                            echo $ontdist. ' Meter';
                        }
                        @endphp
                        {{-- {{ $ontdist }} Meter --}}
                    </td>
                </tr>
                <tr><th>Last Down Status</th><td>{{ $ontstatus }}</td></tr>
            </table>
          </div>
        </div>
      </div>
      <div class="col-sm-6 d-flex justify-content-center">
        <div class="card shadow p-3 mb-5 bg-body rounded" style="width: 100%;">
          <div class="card-header" style="background-color: #d4d4d4;"><h5 class="card-title">User Data Details</h5></div>
          <div class="card-body" style="background-color: #eeeeee;">
            <table class="table-striped" width="100%">
                <tr><th>Total Upload Today </th><td>{{ $downloadtoday }}</td></tr>
                <tr><th>Total Download Today</th><td>{{ $uploadtoday  }}</td></tr>
                <tr><th>Total Download/Upoad Today</th><td>{{ $totaltoday }}</td></tr>
                <tr><th>Total Upload</th><td>{{ $response1['data']['bytes_uploaded_total_human'] }}</td></tr>
                <tr><th>Total Download</th><td>{{ $response1['data']['bytes_downloaded_total_human'] }}</td></tr>
                <tr><th>Total</th><td>{{ $response1['data']['data_used_total_human'] }}</td></tr>
                <tr><th>Advance Renewal</th><td>{{ $adv }}</td></tr>
                {{-- <tr><th>advance_renewal_package_idddd</th><td>{{ $response1['data']['advance_renewal_package_id'] }}</td></tr> --}}
                @foreach ($locationid as $loc)
                    @if ($loc->radiusid==$response1['data']['advance_renewal_package_id'])
                    <tr><th>Advance Renewal Package</th><td>{{ $loc->name }}</td></tr>
                    {{-- @else --}}
                    {{-- <tr><th>advance_renewal_package_id</th><td>{{ $loc->radiusid }}</td></tr> --}}
                    @endif
                @endforeach
            </table>
          </div>
        </div>
      </div>
    </div>
    <a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
  </div>
@endsection
{{-- {{ env('APP_ENV') }} --}}

@push('page-js')
<script>
$(document).ready(function() {
$('body').on('click','#deregist',function(){
        //e.preventDefault();
        var $button = $(this);
        // console.log($button); // Check if $button is defined
        $button.prop('disabled', false); // Enable button
        $button.html('Processing...');
        var route = "{{route('de.register')}}";
        var variable = $(this).data('oid');
        $.ajax({
            type: 'GET',
            url: route,
            data: {
                oid: variable,
            },
            success: function(response) {
                // $('#opticalpowers').DataTable().ajax.reload(null, false);
                $button.html('De-Register'); // Revert button text
                $button.prop('disabled', false);
            }
        });
    });

$('body').on('click','#regist',function(){
        //e.preventDefault();
        var $button = $(this);
        // console.log($button); // Check if $button is defined
        $button.prop('disabled', false); // Enable button
        $button.html('Processing...');
        var route = "{{route('ont.register')}}";
        var variable = $(this).data('oid');
        $.ajax({
            type: 'GET',
            url: route,
            data: {
                oid: variable,
            },
            success: function(response) {
                // $('#opticalpowers').DataTable().ajax.reload(null, false);
                $button.html('Register'); // Revert button text
                $button.prop('disabled', false);
            }
        });
    });

    $('body').on('click','#rebootont',function(){
        //e.preventDefault();
        var $button = $(this);
        // console.log($button); // Check if $button is defined
        $button.prop('disabled', false); // Enable button
        $button.html('Processing...');
        var route = "{{route('reboot.ont')}}";
        var variable = $(this).data('oid');
        $.ajax({
            type: 'GET',
            url: route,
            data: {
                oid: variable,
            },
            success: function(response) {
                // $('#opticalpowers').DataTable().ajax.reload(null, false);
                $button.html('Reboot ONT'); // Revert button text
                $button.prop('disabled', false);
            }
        });
    });

    $('body').on('click','#resetmac',function(){
            //e.preventDefault();
            var $button = $(this);
            // console.log($button); // Check if $button is defined
            $button.prop('disabled', false); // Enable button
            $button.html('Processing...');
            var route = "{{route('mac.reset')}}";
            var name = $(this).data('name');
            var id = $(this).data('id');
            var location = $(this).data('location');
            $.ajax({
                type: 'GET',
                url: route,
                data: {
                    name: name,
                    id: id,
                    location: location
                },
                success: function(response) {
                    if(response.success){
                        $('#opticalpowers').DataTable().ajax.reload(null, false);
                        $('#message-container').addClass('alert alert-success alert-dismissible');
                        $('#message-container').html('MAC reseted successfully. <button type="button" class="close" data-dismiss="alert">&times;</button>');
                        $('#message-container').show();
                    } else {
                        $('#message-container').addClass('alert alert-danger alert-dismissible');
                        $('#message-container').html('MAC reset failed. <button type="button" class="close" data-dismiss="alert">&times;</button>');
                        $('#message-container').show();
                    }
                    $button.prop('disabled', false);
                    $button.html('Reset MAC');
                }
            });
        });
});
</script>
@endpush