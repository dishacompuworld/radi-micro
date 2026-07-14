@extends('layouts.admin')


@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Edit User</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Users</li>
                        <li class="breadcrumb-item active">Edit User</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Edit User</h5>
        </div>
        <div class="card-body">
            @if(config('app.debug'))
                <div class="mb-3">
                    <strong>DEBUG:</strong>
                    <pre style="background:#f8f9fa;padding:8px;border:1px solid #ddd;">User: {{ json_encode($user) }}
Slocations: {{ json_encode($slocations ?? []) }}
Srolename: {{ json_encode($srolename ?? []) }}</pre>
                </div>
            @endif
            {{-- @if (isset($user) && isset($user->id)) --}}
                <form method="POST" enctype="multipart/form-data" action="{{ url('users/'.$user->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="name">Full Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name ?? '') }}"
                            placeholder="Full Name"
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email ?? '') }}"
                            placeholder="example@gmail.com"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="role">Role</label>
                        @php
                            $selectedRole = old('role');
                            if (empty($selectedRole) && isset($user)) {
                                if (!empty($srolename) && isset($srolename[0]->name)) {
                                    $selectedRole = $srolename[0]->name;
                                } elseif (method_exists($user, 'getRoleNames')) {
                                    $selectedRole = $user->getRoleNames()->first() ?: null;
                                }
                            }
                        @endphp
                        <select id="role" class="select2 form-select @error('role') is-invalid @enderror" name="role">
                            <option value="">Select Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" @selected((string)($selectedRole ?? '') === (string)$role->name)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="location">Select Locations <span class="text-muted">(Optional)</span></label>
                        @php
                            $selectedLocationIds = old('location');
                            if (empty($selectedLocationIds)) {
                                $selectedLocationIds = collect($slocations ?? [])->pluck('locationid')->map(function($v){ return (int) $v; })->toArray();
                            }
                        @endphp
                        <select
                            id="location"
                            class="select2 form-select @error('location') is-invalid @enderror"
                            name="location[]"
                            multiple
                        >
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}" @selected(in_array($loc->id, $selectedLocationIds))>
                                    {{ $loc->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('location')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="avatar">Picture <span class="text-muted">(Optional)</span></label>
                        <input
                            type="file"
                            id="avatar"
                            name="avatar"
                            class="form-control @error('avatar') is-invalid @enderror"
                        >
                        @error('avatar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                        >
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
            {{-- @else
                <div class="alert alert-danger mb-0">User data is missing. Please return to the users list and try again.</div>
            @endif --}}
        </div>
    </div>
</div>
@endsection
