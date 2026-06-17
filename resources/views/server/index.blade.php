@extends('layouts.admin')


@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Add Server</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Microtik</li>
                        <li class="breadcrumb-item active">Add Server</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
	
		<!-- Servers -->
		<div class="col-md-12">
            {{-- <div class="card"> --}}

                {{-- <div class="card-body"> --}}
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                {{-- </div> --}}
            {{-- </div> --}}
        </div>
        <table class="table">
            <tr><th>ID</th><th>Name</th><th>Short Name</th><th>IP</th><th>Seconary IP</th><th>View</th>@can('update-server')<th>Edit</th>@endcan</tr>
            @foreach ($servers as $server)
            <tr>
                <td>{{ $server -> id }}</td>
                <td>
                  @if ($server -> enable)
                  <a href="{{ route('stats.index',['server'=>$server->id])}}">{{ $server -> name }}</a>
                  @else
                  {{ $server -> name }}
                  @endif
                </td>
                <td>{{ $server -> shortname }}</td>
                <td>{{ $server -> mip }}</td>
                <td>{{ $server -> ip2 }}</td>
                <td><a href="{{ route('server.show', $server -> id) }}" class="btn btn-outline-primary btn-icon-text btn-sm">Show</a></td>
                @can('update-server')
                    <td><a href="{{ route('server.edit', $server -> id) }}" class="btn btn-outline-primary btn-icon-text btn-sm">Edit</a></td>    
                @endcan
                
            </tr>
            @endforeach
        </table>
		<!-- /Servers -->
		
	    </div>
    </div>
    </div>
  </div>

</div>
@endsection