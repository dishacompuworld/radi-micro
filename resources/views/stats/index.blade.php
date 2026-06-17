@php
    if ($data) {
        $fmemory = floatval($data['freememory']) / 1024 / 1024;
        $tmemory = floatval($data['totalmemory']) / 1024 / 1024;

        $fhdd = floatval($data['freehdd']) / 1024 / 1024;
        $thdd = floatval($data['totalhdd']) / 1024 / 1024;
    }
@endphp

@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Statistics</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">Microtik</li>
                    <li class="breadcrumb-item active">Statistics</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('stats.index') }}" method="get" class="row g-3 align-items-end">
                        <div class="col-md-6 col-lg-5">
                            <label for="server" class="form-label mb-1">Select Server</label>
                            <select id="server" name="server" class="form-select" onchange="this.form.submit()">
                                <option value="" @selected(!$iid)></option>
                                @foreach ($servers as $server)
                                    <option value="{{ $server->id }}" @selected($server->id == $iid)>
                                        {{ $server->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($data)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th class="text-nowrap" style="width: 240px;">Server Identity</th>
                                        <td>{{ $data['identity'] }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-nowrap">Server Model</th>
                                        <td>{{ $data['model'] }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-nowrap">Server Factory version</th>
                                        <td>{{ $data['factorysoftware'] }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-nowrap">Server Current version</th>
                                        <td>{{ $data['version'] }} ({{ $data['buildtime'] }})</td>
                                    </tr>
                                    <tr>
                                        <th class="text-nowrap">Server uptime</th>
                                        <td>{{ $data['uptime'] }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-nowrap">Server CPU</th>
                                        <td>{{ $data['cpu'] }} %</td>
                                    </tr>
                                    <tr>
                                        <th class="text-nowrap">Server memory free/total</th>
                                        <td>{{ $fmemory }} / {{ $tmemory }} MB</td>
                                    </tr>
                                    <tr>
                                        <th class="text-nowrap">Server hdd free/total</th>
                                        <td>{{ $fhdd }} / {{ $thdd }} MB</td>
                                    </tr>
                                    <tr>
                                        <th class="text-nowrap">Server Active users</th>
                                        <td>
                                            <a href="{{ route('pppoe.newactive', ['server' => $iid]) }}">
                                                {{ $data['active'] }}
                                            </a>
                                        </td>
                                    </tr>
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