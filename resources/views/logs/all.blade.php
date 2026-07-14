@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-7">
            <h4 class="mb-3">All User Logs</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">Logs</li>
                    <li class="breadcrumb-item active">All User Logs</li>
                </ol>
            </nav>
        </div>

        @can('delete-all-logs')
        <div class="col-5 d-flex justify-content-end align-items-center">
          <a href="{{route('delete.log')}}" class="btn btn-danger mt-2">Clear All Logs </a>
        </div>
        @endcan


    </div>

    <div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="all-logs" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                        <thead>
                            <tr style="boder:1px solid black;">
                                <th>id</th>
                                <th>Log Type</th>
                                <th>Description</th>
                                <th>Event</th>
                                <th>Subject</th>
                                <th>User</th>
                                <th>Updated at</th>
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
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        var table = $('#all-logs').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('show.alllogs') }}",
            columns: [
                {data: 'id', name: 'id', searchable: false,},
                {data: 'log_name', name: 'log_name', orderable: false,},
                {data: 'description', name: 'description', orderable: false,},
                {data: 'event', name: 'event',  orderable: false,},
                {data: 'subject_id', name: 'subject_id', orderable: false,},
                {data: 'user', name: 'user', orderable: false,},
                {data: 'updated_at', name: 'updated_at'},
            ],
            aaSorting: [],
        });
        //
    });
</script>
@endpush