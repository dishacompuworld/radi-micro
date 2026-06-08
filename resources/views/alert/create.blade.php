// resources/views/alert-messages/create.blade.php

@extends('admin.layouts.header')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Create Alert Message</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('alert.store') }}">
                            @csrf

                            <div class="form-group row">
                                <label for="type" class="col-md-4 col-form-label text-md-right">Type <span class="text-danger">*</span></label>

                                <div class="col-md-6">
                                    <select id="type" type="text" class="form-control @error('type') is-invalid @enderror" name="type" required autocomplete="type" autofocus>
                                        <option value="">Select Type</option>
                                        <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Success</option>
                                        <option value="error" {{ old('type') == 'error' ? 'selected' : '' }}>Error</option>
                                        <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                                        <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Info</option>
                                    </select>

                                    @error('type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="category" class="col-md-4 col-form-label text-md-right">Category <span class="text-danger">*</span></label>

                                <div class="col-md-6">
                                    <select id="category" type="text" class="form-control @error('category') is-invalid @enderror" name="category" required autocomplete="category" autofocus>
                                        <option value="">Select Category</option>
                                        <option value="system" {{ old('category') == 'system' ? 'selected' : '' }}>System</option>
                                        <option value="user" {{ old('category') == 'user' ? 'selected' : '' }}>User</option>
                                        <option value="security" {{ old('category') == 'security' ? 'selected' : '' }}>Security</option>
                                    </select>

                                    @error('category')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="msgcode" class="col-md-4 col-form-label text-md-right">Msg Code <span class="text-danger">*</span></label>

                                <div class="col-md-6">
                                    <input id="msgcode" type="text" class="form-control @error('msgcode') is-invalid @enderror" name="msgcode" value="{{ old('msgcode') }}" required autocomplete="msgcode" autofocus>

                                    @error('msgcode')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="message" class="col-md-4 col-form-label text-md-right">Message <span class="text-danger">*</span></label>

                                <div class="col-md-6">
                                    <textarea id="message" type="text" class="form-control @error('message') is-invalid @enderror" name="message" required autocomplete="message" autofocus required>{{ old('message') }}</textarea>

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
                                        Create Alert Message
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
@endsection