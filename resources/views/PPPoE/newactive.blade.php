@extends('admin.layouts.datatable')

@php
    if(isset($iid)){$iid;}else{$iid="";}
    if(isset($search)){$search;}else{$search="";}
    $urll = url()->current() . "?server=". $iid;

    $removeuserfail = (new App\Http\Controllers\AlertMessageController())->get('remove.user.error');
     $removeusersuccess = (new App\Http\Controllers\AlertMessageController())->get('remove.user.success');

     $removeuserfail = (array) $removeuserfail;
     $removeuserfailmsg = $removeuserfail['message'];
     $removeusersuccess = (array) $removeusersuccess;
     $removeusersuccessmsg = $removeusersuccess['message'];
@endphp
<x-assets.datatables />

@push('page-css')

@endpush

@push('page-header')
<div class="col-xs-7 col-auto">
	<h3 class="page-title">Acive PPPoE Users</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Acive Users</li>
	</ul>
</div>

@endpush

@section('content')
<div id="message-container" style="display: none;" class="col-sm-12"></div>
<div class="row">
    <div class="col-md-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Select Server</span>
                    </div>
                    <form action="{{ route('pppoe.newactive')}}" method="get" name="act">
                    <select class="custom-select" name="server" onchange="this.form.submit()">
                        @if (!$iid)
                        <option value="" selected></option>
                        @else
                        <option value=""></option>
                        @endif
                        @foreach ($servers as $server)
                        @if ($server->id==$iid)
                            <option value="{{ $server->id}}" selected>{{ $server->name}}</option>
                        @else
                            <option value="{{ $server->id}}">{{ $server->name}}</option>
                        @endif
                        @endforeach
                    </select>
                    </form>
                {{-- <input type="search" class="form-control" placeholder="XX-XX-XX-XX-XX-XX" aria-label="XX-XX-XX-XX-XX-XX" aria-describedby="inputGroup-sizing-sm" name="mac" value="{{ $mac }}"> --}}
                    <form action="{{ route('pppoe.allactivenew') }}" method="GET" name="allservers">
                        <div class="input-group-append">
                            <div class="input-group-text">
                            <span>All Servers&nbsp;&nbsp;</span>
                            <input type="checkbox" name="checked" value="1" onclick="this.form.submit()" onchange="document.getElementById('serverselect').disabled = !this.checked;">
                            </div>
                        </div>
                    </form>
                </div>
                <div>
                    @if (session('msg'))
                    <P class="alert alert-danger"> {{ session('msg') }}</P>
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
            @if($iid)
            <thead>
                <tr style="boder:1px solid black;">
                    <th>Name</th>
                    <th>Ping</th>
                    <th>MAC</th>
                    <th>IP</th>
                    <th>Uptime</th>
                    <th>Action</th>
				</tr>
			</thead>
            @else
            <thead>
				<tr style="boder:1px solid black;">
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

						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@endif

{{-- {{ $iid }} --}}
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

        });
        //
        // $('#searchBox').keyup(function () { table.search(this.value).draw(); });

    // $(document).ready(function() {
    // var table = $('#pppoe-active').DataTable(); // Initialize your DataTable and assign it to the `table` variable

    // $('#pppoe-active tbody').on('mouseenter', 'td', function () {
    //     var cellData = table.cell(this).data();
    //     var cellIndex = table.cell(this).index();
    //     var $this = $(this); // Save the reference to this cell

    //     if (cellIndex.column === 2) { // Assuming column index 2 is the one you want to add tooltip
    //         // Remove any existing tooltip
    //         $this.tooltip('dispose');
            
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

    var table = $('#pppoe-active').DataTable(); // Initialize your DataTable and assign it to the `table` variable

    var tooltipTimeout = null;

    $('#pppoe-active tbody').on('mouseenter', 'td', function () {
        var cellData = table.cell(this).data();
        var cellIndex = table.cell(this).index();
        var $this = $(this); // Save the reference to this cell

        if (cellIndex.column === 2) { // Assuming column index 2 is the one you want to add tooltip
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
            if(response.success){
                successmessage = successmessage.replace(':user', response.cname);
                $('#pppoe-active').DataTable().ajax.reload(null, false);
                $('#message-container').addClass('alert alert-success alert-dismissible');
                $('#message-container').html(successmessage + ' <button type="button" class="close" data-dismiss="alert">&times;</button>');
                $('#message-container').show();
            } else {
                failmessage = failmessage.replace(':user', response.cname);
                $('#pppoe-active').DataTable().ajax.reload(null, false);
                $('#message-container').addClass('alert alert-danger alert-dismissible');
                $('#message-container').html(failmessage + ' <button type="button" class="close" data-dismiss="alert">&times;</button>');
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

// {{-- render : function(data, type, row, meta) {return'<a class="d-inline-block fw-normal w-100 h-100 pe-auto" href="pppoe/ping?ip=' + row.address + '&username=' + row.address + '&server=' + $iid +'">' + row.address + '</a>';}, --}}