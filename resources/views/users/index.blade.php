@extends('layouts.admin')


@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Users</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('users.create') }}" class="btn btn-primary">Add User</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="user-table" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Avatar</th>
                            <th>Created At</th>
                            <th>Updated At</th>
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
    var table = $('#user-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{route('users.index')}}",
        columns: [
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'role', name: 'role'},
            {data: 'avatar', name: 'avatar', orderable: false, searchable: false},
            {data: 'created_at', name: 'created_at'},
            {data: 'updated_at', name: 'updated_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    $('#user-table').on('click', '.deletebtn', function() {
        var button = $(this);
        var route = button.data('route');
        var id = button.data('id');

        if (!confirm('Are you sure you want to delete this user?')) {
            return;
        }

        $.ajax({
            url: route,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: id
            },
            success: function() {
                table.ajax.reload(null, false);
            },
            error: function() {
                alert('Unable to delete user. Please try again.');
            }
        });
    });
});
</script>
@endpush
