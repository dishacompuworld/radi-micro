@extends('admin.layouts.header')

<x-assets.datatables />

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Servers</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Server Details</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				
		<!-- Server Details -->
		<table class="table-striped">
            {{-- @foreach ($servers as $server) --}}
                <tr><th>Name</th><td><a href="{{ route('stats.index',['server'=>$servers->id])}}" class="">{{ $servers->name }}</a> </td></tr>
                <tr><th>Short Name</th><td>{{ $servers->shortname }}</td></tr>
                <tr><th>Main IP</th><td>{{ $servers->mip }}</td></tr>
                <tr><th>Secondary IP</th><td>{{ $servers->ip2 }}</td></tr>
                <tr><th>Enabled</th><td>@if ($servers->enable) Yes  @else No @endif </td></tr>
            </tr>
            {{-- @endforeach --}}
        </table>
		<!-- /Server Details -->
	</div>
</div>
	</div>
</div>
@endsection	