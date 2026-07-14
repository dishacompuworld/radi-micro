@php
    if(isset($mac)){$mac;}else{$mac="";}
    if(isset($response)){$response;}else{$response="";}
    // return $response;

    if(isset($response->data)){
        $data = $response->data;
        $error="";
    }elseif(isset($response->error)){
        $data="";
        $error = $response->error;
    }else{
        $data="";
        $error="";
    }

@endphp
@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Find Mac Details</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">MAC Details</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-6 col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('find.mac.vendor') }}" method="get">
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text" id="basic-addon1">Search MAC</span>
                            <input type="search" class="form-control" placeholder="XX-XX-XX-XX-XX-XX" aria-label="MAC address" aria-describedby="basic-addon1" name="mac" value="{{ $mac }}">
                            <button class="btn btn-outline-secondary" type="submit">GO</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($data))
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <table class="table table-striped table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th scope="row">Organization Name</th>
                                <td>{{ $data->organization_name }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Organization Address</th>
                                <td>{{ $data->organization_address }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <a href="javascript:history.back()" class="btn btn-primary btn-sm">Back</a>
        </div>
    </div>
    @endif

    @if (!empty($error))
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="alert alert-warning mb-0" role="alert">
                        MAC details not available.
                    </div>
                </div>
            </div>
            <a href="javascript:history.back()" class="btn btn-primary btn-sm">Back</a>
        </div>
    </div>
    @endif
</div>
@endsection

