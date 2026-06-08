@php
   if(isset($location)){$location;}else{$location="";}
   if(isset($name)){$name;}else{$name="";}
   if(isset($_GET['id'])){$id=$_GET['id'];}else{$id="";}
   if(isset($_GET['location'])){$location=$_GET['location'];}else{$location="";}
   if(isset($_GET['name'])){$name=$_GET['name'];}else{$name="";}
//    if(isset($id)){$id;}else{$id="";}


    $urll = $location . ".xceednet.com";

    $nurl = url()->current();

    $newurl = url()->current() . '?name=' . $name . '&location=' . $location;

@endphp
@extends('admin.layouts.datatable')

<x-assets.datatables />

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Subscriber Access Request Logs</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
        <li class="breadcrumb-item active"><a href="{{route('location.show')}}">Locations</a></li>
		<li class="breadcrumb-item active">Subscriber Access Request Logs</li>
	</ul>
</div>

<div class="col-xs-5 col">
    @can('delete-user-access')
    <div><a href="javascript:void(0)" class="btn btn-danger float-right mt-2" id="dlt" data-id="{{ $id }}" data-location="{{ $location }}" data-name="{{ $name }}"><i class="bi bi-trash"></i> Delete</a></div>
    @endcan
	{{-- <label class="badge badge-success"> {{ $lastchkmsg }}</lable> --}}
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="subscriber-access-logs" class="datatable table table-striped table-bordered table-hover table-center mb-0">
						<thead>
							<tr style="boder:1px solid black;">
								{{-- <th>User Id</th> --}}
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
        <a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
	</div>

</div>

@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        var table = $('#subscriber-access-logs').DataTable({
            processing: true,
            serverSide: true,
        //    ajax: "{{ $newurl }}",
            ajax: {
            url: '{{ $nurl }}',
            type: "get",
            data: function (d) {
                d.name = '{{$_GET['name']}}';
                d.location ='{{$_GET['location']}}';
                },
            },
            columns: [
                // {data: '0', name: '0', orderable: false, searchable: false},
                {data: '1', name: '1', orderable: false, searchable: false},
                {data: '3', name: '3', orderable: false, searchable: false},
                {data: '4', name: '4', orderable: false,},
                {data: '6', name: '6', orderable: false,},
                {data: '7', name: '7', orderable: false,},
            ],
            aaSorting: [],
        });
        //

        $('body').on('click','#dlt',function(){
        //e.preventDefault();
        var $button = $(this);
        // console.log($button); // Check if $button is defined
        $button.prop('disabled', false); // Enable button
        $button.html('Processing...');
        var route = "{{route('subscriber.accessrequest.delete')}}";
        var loc = $(this).data('location');
        var id = $(this).data('id');
        var name = $(this).data('name');
        $.ajax({
            type: 'GET',
            url: route,
            data: {
                id: id,
                location: loc,
                name: name,
            },
            success: function(response) {
                $('#subscriber-access-logs').DataTable().ajax.reload(null, false);
                $button.html('<i class="bi bi-trash"></i> Delete'); // Revert button text
                $button.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush