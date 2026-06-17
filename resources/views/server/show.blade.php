@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Show Server</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Microtik</li>
                        <li class="breadcrumb-item active">Show Server</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>


	<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<!-- Server Details -->
					<table class="table-striped">
						{{-- @foreach ($servers as $server) --}}
							<tr><th>Name</th><td><a href="{{ route('stats.index',['server'=>$servers->id])}}" class="">{{ $servers->name }}</a> </td></tr>
							<tr><th>Short Name</th><td>{{ $servers->shortname }}</td></tr>
							<tr><th>Main IP</th><td>{{ $servers->mip }}</td></tr>
							<tr><th>Secondary IP</th><td>{{ $servers->ip2 }}</td></tr>
							<tr><th>Enabled</th><td>@if ($servers->enable) Yes  @else No @endif </td></tr>
						</tr>
						{{-- @endforeach --}}
					</table>
				<!-- /Server Details -->
			</div>
		</div>
		</div>
	</div>
</div>
@endsection