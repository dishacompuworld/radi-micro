@extends('admin.layouts.header')

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Script</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Script</li>
	</ul>
</div>

@endpush

@section('content')
<div class="container">
    <h1>Add New Script</h1>
    <form method="POST" action="{{ route('microtik.script.store') }}">
        @csrf
        <div class="form-group">
            <label for="sserver">Select Server:</label>
            <select name="sserver" id="sserver" class="form-control">
                @foreach($servers as $server)
                    <option value="{{ $server->id }}">{{ $server->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="script_name">Script Name:</label>
            <input type="text" name="script_name" id="script_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="script_source">Script Source:</label>
            <textarea name="script_source" id="script_source" class="form-control" rows="10" required></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Add Script</button>
    </form>
</div>
@endsection