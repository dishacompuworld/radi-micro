@php
   if(isset($msg)){$msg;}else{$msg="";}
//    if(isset($opticalpower)){$opticalpower;}else{$opticalpower="";}
//    if(isset($ontname)){$ontname;}else{$ontname="";}
//    if(isset($ontuptime)){$ontuptime;}else{$ontuptime="";}
//    if(isset($ontserial)){$ontserial;}else{$ontserial="";}
//    if(isset($onttemp)){$onttemp;}else{$onttemp="";}
//    if(isset($onteth)){$onteth;}else{$onteth="";}

@endphp
@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
    <div class="col-md-8 d-flex flex-column justify-content-center">
        <h4 class="mb-3">Add ONT</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">OLT</li>
                <li class="breadcrumb-item active">Add ONT</li>
            </ol>
        </nav>
    </div>
    </div>
    
    <div class="col-md-12">
          @if (session('success'))
              <div class="alert alert-success">
                  {{ session('success') }}
              </div>
          @endif

          @if (session('error'))
              <div class="alert alert-danger">
                  {{ session('error') }}
              </div>
          @endif
</div>
<div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-body col-sm-3">
            {{-- <div>
                @if ($msg)
                  <label class="badge badge-success"> {{ $msg }}</lable>
                @endif
              </div> --}}
		<!-- Subscribers -->
        <form action="{{ route('add.ont')}}">
        <div class="input-group input-group-sm mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text" id="basic-addon1">ONT Sr. No.</span>
            </div>
            <input type="text" class="form-control" placeholder="ONT/ONU Sr. No." aria-label="ONT/ONU Sr. No." aria-describedby="basic-addon1" name="oid">
        </div>
        <input type="submit" value="Save" class="btn btn-primary btn-sm">
        </form>
    </div>
    </div>
<div class="w-auto mb-3">
    <a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
</div>
</div></div>
</div>
@endsection
