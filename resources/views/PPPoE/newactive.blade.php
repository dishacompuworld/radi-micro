@php
    $iid = $iid ?? '';
    $search = $search ?? '';
    $checked = $checked ?? (request()->query('checked', 0));
    $urll = $urll ?? (url()->current() . '?server=' . $iid);

    $removeuserfail = (new App\Http\Controllers\AlertMessageController())->get('remove.user.error');
    $removeusersuccess = (new App\Http\Controllers\AlertMessageController())->get('remove.user.success');

    $removeuserfail = (array) $removeuserfail;
    $removeuserfailmsg = $removeuserfail['message'] ?? '';
    $removeusersuccess = (array) $removeusersuccess;
    $removeusersuccessmsg = $removeusersuccess['message'] ?? '';
@endphp
@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Active Users</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">Microtik</li>
                    <li class="breadcrumb-item active">Active Users</li>
                </ol>
            </nav>
        </div>
    </div>

    <div id="message-container" style="display: none;" class="col-sm-12"></div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <form action="{{ route('pppoe.newactive') }}" method="get" name="act" class="d-flex align-items-center mb-0">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Select Server</span>
                                    <select class="form-select" name="server" onchange="this.form.submit()">
                                        <option value="" @selected(!$iid)></option>
                                        @foreach ($servers as $server)
                                            <option value="{{ $server->id }}" @selected($server->id == $iid)>
                                                {{ $server->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>

                            {{-- <input type="search" class="form-control" placeholder="XX-XX-XX-XX-XX-XX" aria-label="XX-XX-XX-XX-XX-XX" aria-describedby="inputGroup-sizing-sm" name="mac" value="{{ $mac }}"> --}}
                            <form action="{{ route('pppoe.allactivenew') }}" method="GET" name="allservers" class="d-flex align-items-center mb-0">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">All Servers</span>
                                    <span class="input-group-text">
                                        <input class="form-check-input mt-0" type="checkbox" name="checked" value="1" onclick="this.form.submit()" {{ $checked == 1 ? 'checked' : '' }}>
                                    </span>
                                </div>
                            </form>
                        </div>
                        <div class="mt-3">
                            @if (session('msg'))
                                <p class="alert alert-danger mb-0">{{ session('msg') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($iid)
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="pppoe-active" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                            <thead>
                                <tr style="border:1px solid black;">
                                    @if($iid)
                                        <th>Name</th>
                                        <th>Ping</th>
                                        <th>MAC</th>
                                        <th>IP</th>
                                        <th>Uptime</th>
                                        <th>Action</th>
                                    @else
                                        <th>Server</th>
                                        <th>Name</th>
                                        <th>Ping</th>
                                        <th>MAC</th>
                                        <th>IP</th>
                                        <th>Uptime</th>
                                        <th>Action</th>
                                    @endif
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
@if ($iid)
<script>
    $(document).ready(function() {
        var table = $('#pppoe-active').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ $urll }}',
                type: 'GET'
            },
            columns: [
                { data: 'namel', name: 'name' },
                { data: 'ping', name: 'ping', orderable: false, searchable: false },
                { data: 'newmac', name: 'newmac', orderable: false, searchable: false },
                { data: 'addressnew', name: 'addressnew' },
                { data: 'uptime', name: 'uptime', searchable: false },
                { data: 'remove', name: 'remove', orderable: false, searchable: false }
            ],
            aaSorting: []
        });

        var tooltipTimeout = null;

        $('#pppoe-active tbody').on('mouseenter', 'td', function () {
            var cellData = table.cell(this).data();
            var cellIndex = table.cell(this).index();
            var $this = $(this);

            if (cellIndex.column === 2) {
                clearTimeout(tooltipTimeout);
                tooltipTimeout = setTimeout(function () {
                    $('[data-toggle="tooltip"]').tooltip('dispose');

                    $.ajax({
                        url: '{{ route('find.mac') }}',
                        type: 'GET',
                        data: { mac: cellData },
                        success: function(response) {
                            $this.attr('title', response.tooltip_data).tooltip({
                                placement: 'top'
                            }).tooltip('show');
                        }
                    });
                }, 200);
            }
        });

        $('#pppoe-active tbody').on('mouseleave', 'td', function () {
            $(this).tooltip('dispose');
            clearTimeout(tooltipTimeout);
        });

        $('#pppoe-active').on('mouseleave', function() {
            $('[data-toggle="tooltip"]').tooltip('dispose');
            clearTimeout(tooltipTimeout);
        });
    });

    $('body').on('click', '#removebtn', function() {
        var $button = $(this);
        $button.prop('disabled', true);
        $button.html('Processing...');

        var route = $(this).data('route');
        var cname = $(this).data('cname');
        var server = $(this).data('server');
        var successmessage = '{{ $removeusersuccessmsg }}';
        var failmessage = '{{ $removeuserfailmsg }}';
        var id = $(this).data('id');

        $.ajax({
            type: 'GET',
            url: route,
            data: {
                cname: cname,
                server: server,
                id: id,
            },
            success: function(response) {
                if (response.success) {
                    successmessage = successmessage.replace(':user', response.cname);
                    $('#pppoe-active').DataTable().ajax.reload(null, false);
                    $('#message-container')
                        .removeClass('alert-danger')
                        .addClass('alert alert-success alert-dismissible')
                        .html(successmessage + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>')
                        .show();
                } else {
                    failmessage = failmessage.replace(':user', response.cname);
                    $('#pppoe-active').DataTable().ajax.reload(null, false);
                    $('#message-container')
                        .removeClass('alert-success')
                        .addClass('alert alert-danger alert-dismissible')
                        .html(failmessage + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>')
                        .show();
                }
                $button.prop('disabled', false);
                $button.html('Remove');
            }
        });
    });
</script>

@endif
@endpush