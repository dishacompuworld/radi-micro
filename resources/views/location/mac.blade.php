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
    <div class="col-sm-12">
      <div class="card">
        <div class="card-body col-sm-3">
            <div class="form-group">
		        <form class="forms-sample" action="{{ route('find.mac.vendor')}}" method="get">
                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">Search MAC</span>
                          </div>
						<input type="search" class="form-control" placeholder="XX-XX-XX-XX-XX-XX" aria-label="XX-XX-XX-XX-XX-XX" aria-describedby="inputGroup-sizing-sm" name="mac" value="{{ $mac }}">
						<div class="input-group-append">
							<button class="btn btn-outline-secondary" type="submit">GO</button>
						</div>
					  </div>
                </form>
            </div>
        </div>
        </div>
    </div>
</div>
@if ($data)
<div class="row">
    <div class="col-sm-12">
      <div class="card shadow p-3 mb-5 bg-body rounded">
        <div class="card-body">
            <table  class="table-striped">
                <tr><th>Organization Name</th><td>{{ $data->organization_name }}</td></tr>
                <tr><th>Organization Address</th><td>{{ $data->organization_address }}</td></tr>
            </table>
        </div>
      </div>
      <a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
    </div>
</div>
  @endif
  @if ($error)
  <div class="row">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-body">
              <table  class="table-striped">
                  <tr><th>Not Available</th></tr>
              </table>
          </div>
        </div>
        <a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
      </div>
  </div>
  @endif
</div>
@endsection

