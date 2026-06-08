@php
    $location = $location ?? "";
    $urll = url()->current() . "?location=". $location;
@endphp
@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Access Requests Logs</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Radius</li>
                        <li class="breadcrumb-item active">Access Requests Logs</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form class="forms-sample" action="{{ route('show.accesslogs') }}" method="GET">
                        <div class="input-group input-group-sm" style="max-width: 400px;">
                            <span class="input-group-text" id="inputGroup-sizing-sm">Select Location</span>
                            
                            <select class="form-select" id="inputGroupSelect03" name="location" onchange="this.form.submit()">
                                <option value="" {{ !$location ? 'selected' : '' }}>Select Location</option>
                                
                                @foreach ($slocations as $loc)
                                    <option value="{{ $loc->name }}" {{ $loc->name == $location ? 'selected' : '' }}>
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

    @if($location)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="access-logs" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                            <thead>
                                <tr>
                                    <th>User Id</th>
                                    <th>Date</th>
                                    <th>Username</th>
                                    <th>MAC</th>
                                    <th>Reply Type</th>
                                    <th>Reply Message</th>
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
@if($location)
<script>
    $(document).ready(function() {
        var table = $('#access-logs').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ $urll }}",
            columns: [
                {data: '0', name: '0', orderable: false, searchable: false},
                {data: '1', name: '1', orderable: false, searchable: false},
                {data: 'usern', name: 'usern', orderable: false},
                {data: 'newmac', name: 'newmac', orderable: false},
                {data: 'msgg', name: 'msgg', orderable: false, searchable: true},
                {data: 'msggg', name: 'msggg', orderable: false, searchable: true},
            ],
            aaSorting: [],
            pageLength: 50,
        });
    });
</script>
@endif
@endpush
