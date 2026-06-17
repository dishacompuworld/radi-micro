@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Edit Server</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Microtik</li>
                        <li class="breadcrumb-item active">Edit Server</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">

				<!-- Update Server -->
				<form class="forms-sample" method="post" action="{{ route('server.update', $servers->id) }}"name="update">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Server Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ $servers->name }}">
                        <span class="text-danger"> @error('name') {{ $message }} @enderror </span>
                      </div>
                      <div class="form-group">
                        <label>Short Name</label>
                        <input type="text" class="form-control @error('sname') is-invalid @enderror" id="sname" name="sname" value="{{ $servers->shortname }}">
                        <span class="text-danger"> @error('sname') {{ $message }} @enderror </span>
                      </div>
                      <div class="form-group">
                        <label>IP Address</label>
                        <input type="text" class="form-control @error('ip') is-invalid @enderror" id="ip" name="ip"  value="{{ $servers->mip }}">
                        <span class="text-danger"> @error('ip') {{ $message }} @enderror </span>
                      </div>
                      <div class="form-group">
                        <label>Secondary IP Address</label>
                        <input type="text" class="form-control @error('ip2') is-invalid @enderror" id="ip2" name="ip2"  value="{{ $servers->ip2 }}">
                        <span class="text-danger"> @error('ip2') {{ $message }} @enderror </span>
                      </div>
                      <div class="form-group">
                        <label>Server Username</label>
                        <input type="text" class="form-control @error('susername') is-invalid @enderror" id="susername" name="susername"  value="{{ $servers->username }}">
                        <span class="text-danger"> @error('susername') {{ $message }} @enderror </span>
                      </div>
                      <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control @error('pass') is-invalid @enderror" id="pass" name="pass"  value="{{ $servers->password }}">
                        <span class="text-danger"> @error('pass') {{ $message }} @enderror </span>
                      </div>
                  <div class="form-check form-check-flat form-check-primary">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input" {{ $servers->enable=="1"? 'checked':'' }} name="enable"> Enable
                    </label>
                  </div>
                  <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                </form>
                <div>
                    <form action="{{ route('server.destroy', $servers->id )}}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button><a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
                    </form>
                </div>
				<!-- /Update Server -->

        </div>
      </div>
    </div>
  </div>


</div>
@endsection