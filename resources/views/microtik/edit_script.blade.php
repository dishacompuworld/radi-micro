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
    <h1>{{ $title }}</h1>
    @if (!empty($script))
        <form method="POST" action="{{ route('microtik.script.update', ['id' => $script['.id'], 'sserver' => $seletedserver]) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="script_name">Script Name:</label>
                <input type="text" name="script_name" id="script_name" class="form-control" value="{{ $script['name'] }}" required>
            </div>
            <div class="form-group">
                <label for="script_source">Script Source:</label>
                <textarea name="script_source" id="script_source" class="form-control" rows="10" required>{{ $script['source'] }}</textarea>
            </div>
            <div class="form-group">
                <label for="sserver">Select Server:</label>
                <select name="sserver" id="sserver" class="form-control">
                    @foreach($servers as $server)
                        <option value="{{ $server->id }}" {{ $seletedserver == $server->id ? 'selected' : '' }}>{{ $server->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Script</button>
        </form>
    @else
        <div class="alert alert-danger">
            Script not found.
        </div>
    @endif
</div>
@endsection