@extends('layouts.admin')


@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Roles</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Access Control</li>
                        <li class="breadcrumb-item active">Roles</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('roles.create') }}" class="btn btn-primary">Add Role</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="role-table" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('page-js')
<script>
		$(document).ready(function() {
            var table = $('#role-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{route('roles.index')}}",
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'permissions', name: 'permissions'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });

			$('#role-table').on('click','.deletebtn',function (){
				if (!confirm('Are you sure you want to delete this role?')) {
					return;
				}

				$.ajax({
					url: $(this).data('route'),
					type: 'DELETE',
					data: {
						_token: $('meta[name="csrf-token"]').attr('content')
					},
					success: function () {
						table.ajax.reload(null, false);
					}
				});
			});

			//
		});
	</script>
@endpush
