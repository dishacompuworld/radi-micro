@php
    // if($data){
    //     $fmemory = floatval($data['freememory'])/1024/1024;
    //     $tmemory = floatval($data['totalmemory'])/1024/1024;

    //     $fhdd = floatval($data['freehdd'])/1024/1024;
    //     $thdd = floatval($data['totalhdd'])/1024/1024;
    // }

    if(isset($username)){$username;}else{$username="";}
    if(isset($_GET['oid'])){$oid=$_GET['oid'];}else{$oid="";}
    // if(isset($doid)){$doid;}else{$doid="";}
@endphp

@extends('admin.layouts.header')

<x-assets.datatables />

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Assign ONT</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Assign ONT</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
                <div class="form-group col-md-12">
                    <div class=" table table-responsive">
                        <table class="table-hover">
                            <form action="{{ route('assign.ont')}}" class="forms-sample" method="get">
                                <tr><th>User Name :</th><td>{{ $username }}</td></tr>
                                    <tr><th>Select ONT :</th>
                                    <td>
                                        <select class="select2 form-select form-control" name="oid">
                                            @foreach ($optdata as $data)
                                                @if($data->oid === $oid)
                                                    <option value="{{ $data->oid}}" selected>{{ $data->name . "(" . $data->powers .")" }}</option>
                                                @else
                                                    <option value="{{ $data->oid}}">{{ $data->name . "(" . $data->powers .")" }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <input type="hidden" name="name" value="{{ $username }}">
                                <input type="hidden" name="sub" value="yes">
                                <tr><td colspan="2" align="center"><input type="submit" class="btn btn-warning btn-sm" value="SAVE"></input></td></tr>
                            </form>
                        </table>
                    </div>
                </div>
	        </div>
        </div>
    </div>
</div>
@endsection
