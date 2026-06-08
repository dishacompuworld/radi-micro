@php
   if(isset($loca)){$loca;}else{$loca="";}
   if(isset($urll)){ $subdomain = strtok($urll, '.');}

   if(isset($response1['data']['online'])){
        if($response1['data']['online']=='true'){
            $online="Yes";
        }else{
            $online="No";
    }
    $urll = $loca . ".xceednet.com";

    if($loca=='disha' & Auth::user()->hasRole('super-admin')){

        $radiuslable = "Xceednet Link / UniCRM Link";
        $radiuslink = "<a href='https://". $urll . "/subscribers/" . $response1['data']['id'] . "' target='_new'>Xceednet</a> / <a href='https://superclick.dishacompuworld.com/net/index.php?page=view_customer&q=" . $response1['data']['username']. "' target='_new'>UniCRM</a>";

        }else {
            $radiuslable = "Xceednet Link";
            $radiuslink = "<a href='https://". $urll . "/subscribers/" . $response1['data']['id'] . "' target='_new'>Xceednet</a>";
        }
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
        <li class="breadcrumb-item active"><a href="{{route('location.show')}}">Locations</a></li>
		<li class="breadcrumb-item active">Subscriber Details</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-body">
		<!-- Subscribers -->
		<form class="forms-sample" action="{{ route('subscriber.show')}}">
            <div class="form-group">
                <label>Select Location</label>

                    <select class="" name="loca" class="form-control-sm">
                        @if (!$loca)
                            <option value="" selected></option>
                        @else
                            <option value=""></option>
                        @endif
                        {{-- @if (isset($location->name)) --}}
                            @foreach ($location as $loc)
                            @if ($loc->name==$loca)
                                <option value="{{ $loc->name }}" selected>{{ $loc->name }}</option>
                            @else
                                <option value="{{ $loc->name }}">{{ $loc->name }}</option>
                            @endif
                            @endforeach
                        {{-- @endif --}}
                    </select>
                    <input type="text" name="username" id="" class="form-control-sm" value="{{ isset($response1['data']['username']) ? $response1['data']['username']: '' }}">
                    <button type="submit" class="btn btn-sm btn-primary">Show</button>
                </form>
                </div>
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
                $renewdt = $renewdate->format('d.m.Y, h:i:s A');

                $expirydate = new DateTime($response1['data']['expires_at']);
                $expirydt = $expirydate->format('d.m.Y, h:i:s A');

                $lstlogindate = new DateTime($response1['data']['last_login_at']);
                $lstlogindt = $lstlogindate->format('d.m.Y,h:i:s A');

                $downloadtoday = $response1['data']['bytes_uploaded_in_24_hours'] / (1024 * 1024);
                $uploadtoday = $response1['data']['bytes_downloaded_in_24_hours'] / (1024 * 1024);
                $totaltoday = $downloadtoday + $uploadtoday;

                if($response1['data']['advance_renewal']){
                    $adv = 'Yes';
                }else {
                    $adv ='No';
                }
            @endphp
                {{-- <tr><th>ID</th><td>{{ $response1['data']['id'] }} </td></tr> --}}
                <tr><th>UserName</th><td>{{ $response1['data']['username'] }}</td></tr>
                <tr><th>Name</th><td>{{ $response1['data']['name'] }}</td></tr>
                <tr><th>{{ $radiuslable }}</th><td>{!! html_entity_decode($radiuslink) !!}</td></tr>
                <tr><th>Address</th><td>{{ $response1['data']['address1'] }}</td></tr>
                <tr><th>Mobile</th><td>{{ $response1['data']['mobile1'] }}</td></tr>
                @if ($response1['data']['status']=="expired")
                    <tr><th>Status</th><td class="text-danger">Expired</td></tr>
                @endif
                @if($online=="Yes")
                <tr class="text-success"><th>Online</th><td class="text-success">{{ $online }}</td></tr>
                @else
                <tr class="text-danger"><th>Online</th><td class="text-danger">{{ $online }}</td></tr>
                @endif
                <tr><th>location_package_name</th><td>{{ $response1['data']['location_package_name'] }}</td></tr>
                <tr><th>renewed_at</th><td>{{ $renewdt }}</td></tr>
                <tr><th>expires_at</th><td>{{ $expirydt }}</td></tr>
                @if ($response1['data']['override_package_bandwidth'])
                <tr><th>Override_package_bandwidth</th><td>Yes</td></tr>
                @else
                <tr><th>Override_package_bandwidth</th><td>No</td></tr>
                @endif
                <tr><th>overridden_bandwidth_upload</th><td>{{ $response1['data']['overridden_bandwidth_upload'].$response1['data']['overridden_bandwidth_upload_unit'] }}</td></tr>
                <tr><th>overridden_bandwidth_download</th><td>{{ $response1['data']['overridden_bandwidth_download'].$response1['data']['overridden_bandwidth_download_unit'] }}</td></tr>
                <tr><th>Total Upload Today </th><td>{{ $downloadtoday . " MB" }}</td></tr>
                <tr><th>Total Download Today</th><td>{{ $uploadtoday . " MB" }}</td></tr>
                <tr><th>Total Download/Upoad Today</th><td>{{ $totaltoday . " MB" }}</td></tr>
                <tr><th>Total Upload</th><td>{{ $response1['data']['bytes_uploaded_total_human'] }}</td></tr>
                <tr><th>Total Download</th><td>{{ $response1['data']['bytes_downloaded_total_human'] }}</td></tr>
                <tr><th>Total</th><td>{{ $response1['data']['data_used_total_human'] }}</td></tr>
                <tr><th>Advance Renewal</th><td>{{ $adv }}</td></tr>
                {{-- <tr><th>advance_renewal_package_idddd</th><td>{{ $response1['data']['advance_renewal_package_id'] }}</td></tr> --}}
                @foreach ($locationid as $loc)
                    @if ($loc->radiusid==$response1['data']['advance_renewal_package_id'])
                    <tr><th>Advance Renewal Package </th><td>{{ $loc->name }}</td></tr>
                    {{-- @else --}}
                    {{-- <tr><th>advance_renewal_package_id</th><td>{{ $loc->radiusid }}</td></tr> --}}
                    @endif
                @endforeach

                <tr><th>last_login_at</th><td>{{ $lstlogindt }}</td></tr>
                <tr>
                    <td colspan="2">
                        @can('reset-mac')
                            <a href="{{ route('reset.mac',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$loca]) }}" class="btn btn-danger btn-sm">Reset MAC</a>
                        @endcan

                        @can('enable-disable')
                            @if ($response1['data']['status']=="disabled")
                                <a href="{{ route('enable.subscriber',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$loca]) }}" class="btn btn-danger btn-sm">Enable</a>
                            @else
                                <a href="{{ route('disable.subscriber',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$loca]) }}" class="btn btn-danger btn-sm">Disable</a>
                            @endif
                        @endcan

                        @can('overright-bandwidth')
                            <a href="{{ route('speed.change',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$loca]) }}" class="btn btn-danger btn-sm">Overright Speed</a>
                        @endcan
                        <a href="{{ route('subscriber.accessrequest',['name'=>$response1['data']['username'], 'id'=>$response1['data']['id'], 'location'=>$loca]) }}" class="btn btn-primary btn-sm">Access Request Logs</a>
                    </td>
                </tr>
            @endif
                {{-- @endforeach --}}
        @endif
    </table>

		<!-- /Subscribers -->
	</div>
</div>
<a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
</div>

@endsection
