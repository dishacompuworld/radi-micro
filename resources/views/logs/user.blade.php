@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">User Logs</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">Logs</li>
                    <li class="breadcrumb-item active">User Logs</li>
                </ol>
            </nav>
        </div>
    </div>


<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="user-logs" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                        <thead>
                            <tr style="boder:1px solid black;">
                                <th>Log Type</th>
                                <th>Description</th>
                                <th>Event</th>
                                <th>Subject</th>
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
        var table = $('#user-logs').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('show.log') }}",
            columns: [
                {data: 'log_name', name: 'log_name', orderable: false,},
                {data: 'description', name: 'description', searchable: false, orderable: false,},
                {data: 'event', name: 'event', searchable: false, orderable: false,},
                {data: 'subject_id', name: 'subject_id', searchable: false, orderable: false,},
                {data: 'updated_at', name: 'updated_at'},
            ],
            aaSorting: [],
        });
        //
    });
</script>
@endpush