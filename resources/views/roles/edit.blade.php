@extends('layouts.admin')


@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Edit Roles</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Roles</li>
                        <li class="breadcrumb-item active">Edit Role</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12 col-lg-12">
        
            <div class="card">
                <div class="card-body">
                    <div class="p-4">
                        <form method="POST" action="{{route('roles.update',$role)}}">
                            @csrf
                            @method("PUT")
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <input type="text" name="role" id="role" value="{{$role->name}}" class="form-control" placeholder="super-admin">
                            </div>
                            <div class="mb-3">
                                <label for="permissions" class="form-label">Select Permissions</label>
                                <select class="form-select" name="permission[]" id="permissions" multiple> 
                                    @foreach ($permissions as $permission)
                                        <option value="{{$permission->name}}" {{$role->hasPermissionTo($permission->name) ? 'selected': ''}}>{{$permission->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
