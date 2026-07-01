@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Show Alert Message</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">Alert Messages</li>
                    <li class="breadcrumb-item active">Show Alert Message</li>
                </ol>
            </nav>
        </div>
    </div>


    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Alert Message Details</div>

                    <div class="card-body">
                        <div class="form-group row">
                            <label for="id" class="col-md-4 col-form-label text-md-right">ID</label>

                            <div class="col-md-6">
                                <input id="id" type="text" class="form-control" value="{{ $alertMessage->id }}" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="type" class="col-md-4 col-form-label text-md-right">Type</label>

                            <div class="col-md-6">
                                <input id="type" type="text" class="form-control" value="{{ $alertMessage->type }}" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="message" class="col-md-4 col-form-label text-md-right">Message</label>

                            <div class="col-md-6">
                                <textarea id="message" type="text" class="form-control" readonly>{{ $alertMessage->message }}</textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="created_at" class="col-md-4 col-form-label text-md-right">Created At</label>

                            <div class="col-md-6">
                                <input id="created_at" type="text" class="form-control" value="{{ $alertMessage->created_at }}" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="updated_at" class="col-md-4 col-form-label text-md-right">Updated At</label>

                            <div class="col-md-6">
                                <input id="updated_at" type="text" class="form-control" value="{{ $alertMessage->updated_at }}" readonly>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <a href="{{ route('alert.index') }}" class="btn btn-secondary">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection