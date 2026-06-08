@extends('layouts.admin')


@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Locations</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Radius</li>
                        <li class="breadcrumb-item active">Locations</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
      <div class="col-12">
            <div class="card">
                <div class="card-body">
        <!-- Locations -->
                    @if (session('msg'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('msg') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Domain Name</th>
                                    @role('super-admin')
                                        <th>Insert / Delete</th>
                                    @endrole
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($response1['data'] as $resdd)
                                    @php
                                        $locationExists = $locations->contains('name', $resdd['subdomain']);
                                    @endphp
                                    <tr>
                                        <td>
                                            @if ($locationExists)
                                                <a href="{{ route('locationdetails.show',['location'=> $resdd['subdomain']])}}" class="fw-semibold">
                                                    {{ $resdd['subdomain'] }}
                                                </a>
                                            @else
                                                {{ $resdd['subdomain'] }}
                                            @endif
                                        </td>
                                        <td>{{ $resdd['domain'] }}</td>
                                        @role('super-admin')
                                            <td>
                                                @if ($locationExists)
                                                    <a href="{{ route('location.delete', ['name'=>$resdd['subdomain']])}}" class="btn btn-sm btn-danger">
                                                        <i class="bx bx-trash me-1"></i>Delete
                                                    </a>
                                                @else
                                                    <a href="{{ route('location.insert', ['name'=>$resdd['subdomain'], 'url'=>$resdd['domain']]) }}" class="btn btn-sm btn-primary">
                                                        <i class="bx bx-plus me-1"></i>Insert
                                                    </a>
                                                @endif
                                            </td>
                                        @endrole
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
        <!-- /Locations -->
                </div>
            </div>
      </div>
    </div>

</div>
@endsection
