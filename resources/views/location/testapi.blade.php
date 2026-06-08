@php
    $findyes = 0;
@endphp
@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Test API</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Test API</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
        <div class="card">
            <div class="card-body">
		<!-- Locations -->
		<div>
            @if (session('msg'))
            <label class="badge badge-success"> {{ session('msg') }}</lable>
            @endif
        </div>
          <table class="table table-striped">
                  <tr><th>ID</th><th>Name</th><th></tr>
                    @foreach ($response['data'] as $resdd )
                    <tr>
                        <td>{{ $resdd[0]}}</td>
                        <td>{{ $resdd[1] }} </td>
                        <td>{{ $resdd[2] }} </td>
                        <td>{{ $resdd[3] }} </td>
                        <td>{{ $resdd[4] }} </td>
                        <td>{{ $resdd[5] }} </td>
                        <td>{{ $resdd[6] }} </td>
                    </tr>
                  @endforeach
          </table>
		<!-- /Locations -->
            </div>
        </div>
	</div>
</div>
@endsection
