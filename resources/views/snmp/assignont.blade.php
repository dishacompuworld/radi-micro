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
@extends('layouts.admin')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Assign ONT</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active">Assign ONT</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('assign.ont') }}" class="forms-sample" method="get">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">User Name</label>
                                <div class="form-control-plaintext">{{ $username }}</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold" for="ont-select">Select ONT</label>
                                <select class="form-select form-control" id="ont-select" name="oid" style="width: 100%;">
                                    @foreach ($optdata as $data)
                                        @if($data->oid === $oid)
                                            <option value="{{ $data->oid }}" selected>{{ $data->name . "(" . $data->powers .")" }}</option>
                                        @else
                                            <option value="{{ $data->oid }}">{{ $data->name . "(" . $data->powers .")" }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <input type="hidden" name="name" value="{{ $username }}">
                            <input type="hidden" name="sub" value="yes">

                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-warning btn-sm">SAVE</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('page-js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function () {
        $('#ont-select').select2({
            placeholder: 'Search ONT',
            allowClear: true,
            dropdownParent: $('body'),
            width: '50%'
        });
    });
</script>
@endpush
@endsection
