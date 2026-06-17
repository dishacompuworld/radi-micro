@php
    if(isset($iid)){$iid;}else{$iid="";}
    $urll = url()->current() . "?server=". $iid;
    if(isset($search)){$search;}else{$search="";}
    // if(isset($checked)){$checked;}else{$checked="0";}
    if(isset($_GET['checked'])){$checked = $_GET['checked'];}else{$checked = "0";}
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

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="pppoe-active" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                            @if($iid)
                                <thead>
                                    <tr style="border:1px solid black;">
                                        <th>Name</th>
                                        <th>Ping</th>
                                        <th>MAC</th>
                                        <th>IP</th>
                                        <th>Uptime</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            @elseif ($checked == "1")
                                <thead>
                                    <tr style="border:1px solid black;">
                                        <th>Server</th>
                                        <th>Name</th>
                                        <th>Ping</th>
                                        <th>MAC</th>
                                        <th>IP</th>
                                        <th>Uptime</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            @endif
                            <tbody>
                                <!-- Your rows will be here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('page-js')
@if ($iid)
<script>

    $(document).ready(function() {
        var table = $('#pppoe-active').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ $urll }}",
            columns: [
                {data: 'namel', name: 'name'},
                {   data: 'ping',
                    name: 'ping',
                    render : function(data, type, row, meta) {return'<a class="d-inline-block fw-normal w-100 h-100 pe-auto" href="ping?ip=' + row.address + '&username=' + row.name + '&server={{ $iid }}">Ping</a>';},
                    orderable: false, searchable: false,
                },
                {data: 'newmac',name: 'newmac', orderable: false,},
                {data: 'addressnew',name: 'addressnew'},
                {data: 'uptime', name: 'uptime', searchable: false},
                {data: 'remove', name: 'remove', orderable: false, searchable: false},
            ],
            aaSorting: [],
            // "initComplete": function () {
            //     this.api().search(getParam()).draw();
            // });
        });
        //
    });
</script>
@elseif ($checked=="1")
<script>
    $(document).ready(function() {
        var table = $('#pppoe-active').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('pppoe.allactivenew') }}",
            columns: [
                {data: 'server', name: 'server'},
                {data: 'namel', name: 'namel'},
                {data: 'ping', name: 'ping', searchable: false, orderable: false,},
                {data: 'newmac',name: 'newmac', orderable: false,},
                {data: 'addressnew',name: 'addressnew'},
                {data: 'time', name: 'time', searchable: false, visible:false},
                {data: 'newuptime', name: 'newuptime', searchable: false, orderable: false},
                {data: 'remove', name: 'remove', searchable: false, orderable: false,},
                // {data: 'remove', name: 'remove', orderable: false, searchable: false},
            ],
            order: [],
        });

        //Add tooltip to specific column
        // $(document).ready(function() {
            // var table = $('#pppoe-active').DataTable(); // Initialize your DataTable and assign it to the `table` variable

        // $('#pppoe-active tbody').on('mouseenter', 'td', function () {
        //     var cellData = table.cell(this).data();
        //     var cellIndex = table.cell(this).index();
        //     var $this = $(this); // Save the reference to this cell

        //     if (cellIndex.column === 3) { // Assuming column index 3 is the one you want to add tooltip
        //         // Dispose of any existing tooltips
        //         $('[data-toggle="tooltip"]').tooltip('dispose');

        //         $.ajax({
        //             url: "{{ route('find.mac') }}",
        //             type: 'GET',
        //             data: { mac: cellData },
        //             success: function(response) {
        //                 $this.attr('title', response.tooltip_data).tooltip({
        //                     placement: 'top'
        //                 }).tooltip('show');
        //             }
        //         });
        //     }
        // });

        // // Hide the tooltip on mouseleave
        // $('#pppoe-active tbody').on('mouseleave', 'td', function () {
        //     $(this).tooltip('dispose');
        // });

        // // Remove all tooltips when leaving the DataTable
        // $('#pppoe-active').on('mouseleave', function() {
        //     $('[data-toggle="tooltip"]').tooltip('dispose');
        // });
    // });

    var table = $('#pppoe-active').DataTable(); // Initialize your DataTable and assign it to the `table` variable

    var tooltipTimeout = null;

    $('#pppoe-active tbody').on('mouseenter', 'td', function () {
        var cellData = table.cell(this).data();
        var cellIndex = table.cell(this).index();
        var $this = $(this); // Save the reference to this cell

        if (cellIndex.column === 3) { // Assuming column index 2 is the one you want to add tooltip
            // Clear any existing tooltip timeouts
            clearTimeout(tooltipTimeout);

            // Set a new timeout to show the tooltip after a short delay
            tooltipTimeout = setTimeout(function () {
                // Remove any existing tooltips
                $('[data-toggle="tooltip"]').tooltip('dispose');

                $.ajax({
                    url: "{{ route('find.mac') }}",
                    type: 'GET',
                    data: { mac: cellData },
                    success: function(response) {
                        $this.attr('title', response.tooltip_data).tooltip({
                            placement: 'top'
                        }).tooltip('show');
                    }
                });
            }, 200); // Adjust the delay time as needed
        }
    });

    // Hide the tooltip on mouseleave
    $('#pppoe-active tbody').on('mouseleave', 'td', function () {
        $(this).tooltip('dispose');
        clearTimeout(tooltipTimeout);
    });

    // Remove all tooltips when leaving the DataTable
    $('#pppoe-active').on('mouseleave', function() {
        $('[data-toggle="tooltip"]').tooltip('dispose');
        clearTimeout(tooltipTimeout);
    });

        
        // Get the search parameter from the URL and set the search box value 
        // let searchParam = getParameterByName('search');
        let searchParam = getParameterByName('search') || '';
        // let searchParam = {{ $search }};

        $('#searchBox').val(searchParam); 
        table.search(searchParam).draw(); 
        
        // Trigger search on DataTable when typing in the search box 
        $('#searchBox').on('keyup', function () { 
            let searchValue = this.value; 
            table.search(searchValue).draw(); 
            updateURLParameter('search', searchValue); 
        }); 
        
        // Function to get URL parameter 
        function getParameterByName(name) { name = name.replace(/[ \[\] ]/g, '\\$&'); 
        let regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'), 
        results = regex.exec(window.location.href); 
        
        if (!results) return null; 
        if (!results[2]) return ''; 
        return decodeURIComponent(results[2].replace(/\+/g, ' ')); 
        } 
        
        // Function to update the URL parameter and reload the page 
        function updateURLParameter(param, value) { 
            let url = new URL(window.location.href); 
            url.searchParams.set(param, value); 
            window.history.pushState({}, '', url); 
            location.reload(); }

    });


    $('body').on('click','#removebtn',function(){
    //e.preventDefault();
    var $button = $(this);
    // console.log($button); // Check if $button is defined
    $button.prop('disabled', false); // Enable button
    $button.html('Processing...');
    var route = $(this).data('route');
    var cname = $(this).data('cname');
    var server = $(this).data('server');
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
            if(response.success){
                $('#pppoe-active').DataTable().ajax.reload(null, false);
                $('#message-container').addClass('alert alert-success alert-dismissible');
                $('#message-container').html(response.cname + ' user removed from ' + response.server + '. <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                $('#message-container').show();
            } else {
                $('#pppoe-active').DataTable().ajax.reload(null, false);
                $('#message-container').addClass('alert alert-danger alert-dismissible');
                $('#message-container').html('Removing ' + response.cname + ' user failed from ' + response.server + '. Please try again later. <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                $('#message-container').show();
            }
            $button.prop('disabled', false);
            $button.html('Remove');
        }
    });
});
</script>
@endif

@endpush