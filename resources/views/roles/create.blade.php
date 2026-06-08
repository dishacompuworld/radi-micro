@extends('layouts.admin')


@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Create User</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Users</li>
                        <li class="breadcrumb-item active">Create User</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>


    <div class="row">
    <div class="col-md-12 col-lg-12">
    
        <div class="card card-table">
            <div class="card-header">
                <h4 class="card-title ">Add Role</h4>
            </div>
            <div class="card-body">
                <div class="p-5">
                    <form method="POST" action="{{route('roles.store')}}">
                        @csrf
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" name="role" class="form-control" placeholder="super-admin">
                        </div>
                        <div class="form-group">
                            <lable>Select Permissions</lable>
                            <select class="select2 form-select form-control" name="permission[]" multiple="multiple"> 
                                @foreach ($permissions as $permission)
                                    <option value="{{$permission->name}}">{{$permission->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
    
</div>
@endsection
