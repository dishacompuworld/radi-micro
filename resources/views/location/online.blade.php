@php
    $findyes = 0;
    if(isset($locationshort)){$locationshort;}else{$locationshort="";}

    $urll = url()->current() . "?location=". $locationshort;

@endphp
@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Online Users</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Radius</li>
                        <li class="breadcrumb-item active">Online Users</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form class="forms-sample" action="{{ route('show.onlineusers') }}" method="GET">
                        <div class="input-group input-group-sm mb-3" style="max-width: 400px;">
                            <span class="input-group-text" id="inputGroup-sizing-sm">Location</span>
                            <select class="form-select" id="inputGroupSelect03" name="location" onchange="this.form.submit()">
                                <option value="">Select Location</option>
                                @foreach ($slocations as $loc)
                                    <option value="{{ $loc->name }}" {{ $loc->name == $locationshort ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($locationshort)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="online" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                            <thead>
                                <tr>
                                    <th>User Id</th>
                                    <th>User Name</th>
                                    <th>Nas IP</th>
                                    <th>Logon Time</th>
                                    <th>Upload</th>
                                    <th>Download</th>
                                    <th>MAC</th>
                                    <th>IP</th>
                                    <th>Service Name</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection

@push('page-js')
@if($locationshort)
<script>
    $(document).ready(function() {
        var table = $('#online').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ $urll }}",
            columns: [
                {data: '0', name: '0', orderable: false, searchable: false},
                {data: 'usern', name: 'usern', orderable: false},
                {data: '2', name: '2', orderable: false, searchable: false},
                {data: '3', name: '3', orderable: false, searchable: false},
                {data: '4', name: '4', orderable: false, searchable: false},
                {data: '5', name: '5', orderable: false, searchable: false},
                {data: 'newmac', name: 'newmac', orderable: false},
                {data: 'newip', name: 'newip', orderable: false, searchable: false},
                {data: '9', name: '9', orderable: false, searchable: false},
            ],
            aaSorting: [],
            pageLength: 50,
        });
        //
    });
</script>
@endif
@endpush