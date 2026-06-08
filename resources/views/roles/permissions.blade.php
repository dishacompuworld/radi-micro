@extends('layouts.admin')


@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Permissions</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Access Control</li>
						<li class="breadcrumb-item active">Permissions</li>
                    </ol>
                </nav>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_permission">
                    Add Permission
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="permission-table" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Created Date</th>
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


<!-- Add Modal -->
<div class="modal fade" id="add_permission" tabindex="-1" aria-labelledby="addPermissionLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addPermissionLabel">Add Permission</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form method="POST" action="{{route('permissions.store')}}">
					@csrf
					<div class="row g-3">
						<div class="col-12">
							<div class="mb-3">
								<label for="permission" class="form-label">Permission</label>
								<input type="text" name="permission" id="permission" class="form-control">
							</div>
						</div>
					</div>
					<button type="submit" class="btn btn-primary w-100">Save Changes</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- /ADD Modal -->

<!-- Edit Details Modal -->
<div class="modal fade" id="edit_permission" tabindex="-1" aria-labelledby="editPermissionLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="editPermissionLabel">Edit Permission</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form method="post" action="" id="edit_permission_form">
					@csrf
					@method("PUT")
					<div class="row g-3">
						<div class="col-12">
							<input type="hidden" name="id" id="edit_id">
							<div class="mb-3">
								<label for="edit_permission_name" class="form-label">Permission</label>
								<input type="text" class="form-control perm_name" name="permission" id="edit_permission_name">
							</div>
						</div>
						
					</div>
					<button type="submit" class="btn btn-primary w-100">Save Changes</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- /Edit Details Modal -->
@endsection

@push('page-js')
<script>
		$(document).ready(function() {
            var table = $('#permission-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{route('permissions.index')}}",
                columns: [
                    {data: 'name', name: 'name'},
                    // {data: 'role', name: 'role'},
                    {data: 'created_at',name: 'created_at'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });
			$('#permission-table').on('click','.editbtn',function (){
				var id = $(this).data('id');
				var permission = $(this).data('name');
				var route = $(this).data('route');

				$('#edit_id').val(id);
				$('.perm_name').val(permission);
				$('#edit_permission_form').attr('action', route);
				bootstrap.Modal.getOrCreateInstance(document.getElementById('edit_permission')).show();
			});

			$('#permission-table').on('click','.deletebtn',function (){
				if (!confirm('Are you sure you want to delete this permission?')) {
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
