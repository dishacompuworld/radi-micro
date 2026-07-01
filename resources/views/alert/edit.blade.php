@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Edit Alert Message</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">Alert Messages</li>
                    <li class="breadcrumb-item active">Edit Alert Message</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Edit Alert Message</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('alert.update', $alertMessage->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group row">
                                <label for="type" class="col-md-4 col-form-label text-md-right">Type</label>

                                <div class="col-md-6">
                                    <select id="type" type="text" class="form-control @error('type') is-invalid @enderror" name="type" required autocomplete="type" autofocus>
                                        <option value="">Select Type</option>
                                        <option value="success" {{ $alertMessage->type == 'success' ? 'selected' : '' }}>Success</option>
                                        <option value="error" {{ $alertMessage->type == 'error' ? 'selected' : '' }}>Error</option>
                                        <option value="warning" {{ $alertMessage->type == 'warning' ? 'selected' : '' }}>Warning</option>
                                        <option value="info" {{ $alertMessage->type == 'info' ? 'selected' : '' }}>Info</option>
                                    </select>

                                    @error('type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="category" class="col-md-4 col-form-label text-md-right">Category</label>

                                <div class="col-md-6">
                                    <select id="category" type="text" class="form-control @error('category') is-invalid @enderror" name="category" required autocomplete="category" autofocus>
                                        <option value="">Select Category</option>
                                        <option value="system" {{ $alertMessage->category == 'system' ? 'selected' : '' }}>System</option>
                                        <option value="user" {{ $alertMessage->category == 'user' ? 'selected' : '' }}>User</option>
                                        <option value="security" {{ $alertMessage->category == 'security' ? 'selected' : '' }}>Security</option>
                                    </select>

                                    @error('category')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="msgcode" class="col-md-4 col-form-label text-md-right">Msg Code</label>

                                <div class="col-md-6">
                                    <input id="msgcode" type="text" class="form-control @error('msgcode') is-invalid @enderror" name="msgcode" value="{{ $alertMessage->msgcode }}" required autocomplete="msgcode" autofocus>

                                    @error('msgcode')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="message" class="col-md-4 col-form-label text-md-right">Message</label>

                                <div class="col-md-6">
                                    <textarea id="message" type="text" class="form-control @error('message') is-invalid @enderror" name="message" required autocomplete="message" autofocus>{{ $alertMessage->message }}</textarea>

                                    @error('message')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Update Alert Message
                                    </button>
                                    <a href="{{ route('alert.index') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection