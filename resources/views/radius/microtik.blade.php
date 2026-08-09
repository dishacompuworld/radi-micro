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
                        <li class="breadcrumb-item active">Subscriber Details</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div id="message-container" class="row" style="display: none;">
        <div class="col-12">
            <div class="alert alert-success mb-4" role="alert"></div>
        </div>
    </div>
    <div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                @if (session('msg'))
                    <div class="alert alert-success mb-3">{{ session('msg') }}</div>
                @endif

                <div class="d-flex flex-wrap gap-2 align-items-center py-2">
                        @php
                            $err = 'No Error';
                        @endphp

                        @if ($err == 'not_found')
                            <div class="text-muted">User Not Found</div>
                        @else
                            @if (isset($response1['data']['id']))
                                @php
                                    $renewdate = new DateTime($response1['data']['renewed_at']);
                                    $renewdt = $renewdate->format('d M Y, h:i:s A');

                                    $expirydate = new DateTime($response1['data']['expires_at']);
                                    $expirydt = $expirydate->format('d M Y, h:i:s A');

                                    $now = now();

                                    $diff = date_diff($now, $expirydate);
                                    $remainingdays = $diff->format("%R%a days remaining");

                                    $diff2 = date_diff($now, $renewdate);
                                    $useddays = $diff2->format("%R%a days used");

                                    $lstlogindate = new DateTime($response1['data']['last_login_at']);
                                    $lstlogindt = $lstlogindate->format('d M Y,h:i:s A');

                                    $diff = $now->diff($lstlogindate);
                                    $diff_mins = format_interval($diff);

                                    $downloadtoday = convertData($response1['data']['bytes_uploaded_in_24_hours']);
                                    $uploadtoday = convertData($response1['data']['bytes_downloaded_in_24_hours']);
                                    $totaltoday = convertData($response1['data']['bytes_uploaded_in_24_hours'] + $response1['data']['bytes_downloaded_in_24_hours']);

                                    $adv = $response1['data']['advance_renewal'] ? 'Yes' : 'No';
                                    $mbl = $response1['data']['mobile1'];
                                @endphp

                                @foreach ($userlocations as $uLoc)
                                    @if ($uLoc->name == $subdomain)
                                        @can('reset-mac')
                                            <a href="{{ route('reset.mac', ['name' => $response1['data']['username'], 'id' => $response1['data']['id'], 'location' => $subdomain]) }}"
                                               class="btn btn-warning px-3 py-2"
                                               id="resetmac"
                                               onclick="return confirm('Reset MAC for this subscriber?')">
                                                Reset MAC
                                            </a>
                                        @endcan

                                        @can('enable-disable')
                                            @if ($response1['data']['status'] == 'disabled')
                                                <a href="{{ route('enable.subscriber',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$subdomain]) }}" class="btn btn-success px-3 py-2">Enable</a>
                                            @else
                                                <a href="{{ route('disable.subscriber',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$subdomain]) }}" class="btn btn-danger px-3 py-2">Disable</a>
                                            @endif
                                        @endcan

                                        @can('overright-bandwidth')
                                            <a href="{{ route('speed.change',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$subdomain]) }}" class="btn btn-warning px-3 py-2">Override Speed</a>
                                        @endcan

                                        <a href="{{ route('subscriber.accessrequest',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$subdomain]) }}" class="btn btn-primary px-3 py-2">Access Request Logs</a>

                                        @can('assign-optical-power')
                                            @if($subdomain == 'disha')
                                                <a href="{{ route('assign.ont',['name'=>$response1['data']['username'], 'oid'=>$doid]) }}" class="btn btn-success px-3 py-2">Assign/Update ONT</a>
                                            @endif
                                        @endcan

                                        @if($opticalpower != 'Ont Not assign')
                                            <a href="javascript:void(0)" class="btn btn-danger px-3 py-2" id="rebootont" data-oid="{{ $doid }}">Reboot ONT</a>
                                            @can('register-ont')
                                                <a href="javascript:void(0)" class="btn btn-danger px-3 py-2" id="deregist" data-oid="{{ $doid }}">De-Register</a>
                                                <a href="javascript:void(0)" class="btn btn-danger px-3 py-2" id="regist" data-oid="{{ $doid }}">Register</a>
                                            @endcan
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="row row-cols-1 row-cols-md-2 g-4">
      <div class="col">
        <div class="card shadow-sm h-100">
          <div class="card-header" style="{{ $cardHeader }}"><h5 class="card-title mb-0">User Details</h5></div>
          <div class="card-body" style="{{ $cardBody }}">
            <div class="table-responsive">
              <table class="table table-borderless table-sm mb-0">
                  <tr><th>Location Name</th><td><b>{{ $urll }}</b></td></tr>
                  <tr><th>UserName</th><td>{{ $response1['data']['username'] }}</td></tr>
                  <tr><th>Name</th><td>{{ $response1['data']['name'] }}</td></tr>
                  <tr><th>{{ $radiuslable }}</th><td>{!! html_entity_decode($radiuslink) !!}</td></tr>
                  <tr><th>Address</th><td>{{ $response1['data']['address1'] }}</td></tr>
                  <tr><th>Mobile</th><td><a href="whatsapp://send?phone={{ $mbl }}">{{ $mbl }}</a> <i class="bi bi-whatsapp"></i></td></tr>
                  @if ($response1['data']['status'] == "expired")
                      <tr><th>Status</th><td class="text-danger">Expired</td></tr>
                  @endif
                  <tr><th>Online</th><td class="{{ $online == 'Yes' ? 'text-success' : 'text-danger' }}">{{ $online }}</td></tr>
                  <tr><th>Subscriber Since</th><td>{{ $response1['data']['subscriber_since'] }}</td></tr>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-secondary"><h5 class="card-title mb-0">Package Details</h5></div>
          <div class="card-body bg-light">
            <div class="table-responsive">
              <table class="table table-borderless table-sm mb-0">
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

    <div class="row row-cols-1 row-cols-md-2 g-4 mt-4">
      <div class="col">
        <div class="card shadow-sm h-100">
          <div class="card-header" style="{{ $opcardHeader }}"><h5 class="card-title mb-0">ONU / ONT Details (live)</h5></div>
          <div class="card-body" style="{{ $opcardBody }}">
            <div class="table-responsive">
              <table class="table table-borderless table-sm mb-0">
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
                  </tr>
                  <tr>
                      <th>TX Power</th>
                          @php
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
                      </td>
                  </tr>
                  <tr><th>Last Down Status</th><td>{{ $ontstatus }}</td></tr>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-secondary"><h5 class="card-title mb-0">User Data Details</h5></div>
          <div class="card-body bg-light">
            <div class="table-responsive">
              <table class="table table-borderless table-sm mb-0">
                  <tr><th>Total Upload Today</th><td>{{ $downloadtoday }}</td></tr>
                  <tr><th>Total Download Today</th><td>{{ $uploadtoday }}</td></tr>
                  <tr><th>Total Download/Upload Today</th><td>{{ $totaltoday }}</td></tr>
                  <tr><th>Total Upload</th><td>{{ $response1['data']['bytes_uploaded_total_human'] }}</td></tr>
                  <tr><th>Total Download</th><td>{{ $response1['data']['bytes_downloaded_total_human'] }}</td></tr>
                  <tr><th>Total</th><td>{{ $response1['data']['data_used_total_human'] }}</td></tr>
                  <tr><th>Advance Renewal</th><td>{{ $adv }}</td></tr>
                  @foreach ($locationid as $loc)
                      @if ($loc->radiusid == $response1['data']['advance_renewal_package_id'])
                          <tr><th>Advance Renewal Package</th><td>{{ $loc->name }}</td></tr>
                      @endif
                  @endforeach
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-start mt-3">
        <a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
    </div>
</div>
@endsection