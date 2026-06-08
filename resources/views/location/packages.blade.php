@php
    if(isset($slocation)){$slocation;}else{$slocation="";}
@endphp
@extends('admin.layouts.header')

<x-assets.datatables />

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Packages</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Location Packages</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="form-group col-sm-3">
                    <form class="forms-sample" action="{{ route('packages.show')}}" method="get">
                        <div class="input-group input-group-sm mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="inputGroup-sizing-sm">Select Location</span>
                            </div>
                            <select class="custom-select" id="inputGroupSelect03" name="location" onchange="this.form.submit()">
                                @if (!$locationshort)
                                <option value="" selected></option>
                            @else
                                <option value=""></option>
                            @endif
                            {{-- @if (isset($location->name)) --}}
                            @foreach ($slocations as $loc)
                            @if ($loc->name==$locationshort)
                                <option value="{{ $loc->name }}" selected>{{ $loc->name }}</option>
                            @else
                                <option value="{{ $loc->name }}">{{ $loc->name }}</option>
                            @endif
                            @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div>
                @if (session('msg'))
                    <label class="badge badge-success"> {{ session('msg') }}</lable>
                @endif
                </div>
            {{-- <div>{{$response}}</div> --}}
            @if($slocation)
                <table class="table table-striped">
                    <tr><th>ID</th><th>Name</th><th>Validity</th><th>Upload</th><th>Download</th><th>Description</th></tr>
            @foreach ($response['data'] as $resdd )
                    <tr>
                        <td>{{ $resdd['id']}}</td>
                        <td>{{ $resdd['name'] }} </td>
                        <td>{{ $resdd['valid_for'] . " " . $resdd['validity_unit'] }} </td>
                        <td>{{ $resdd['bandwidth_up'] . " " . $resdd['bandwidth_up_unit'] }} </td>
                        <td>{{ $resdd['bandwidth_down'] . " " . $resdd['bandwidth_down_unit'] }} </td>
                        <td>{{ $resdd['description'] }} </td>
                    </tr>
            @endforeach
                </table>
            @endif
            <!-- /Locations -->
            </div>
        </div>
    </div>
</div>
@endsection
