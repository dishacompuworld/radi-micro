@extends('layouts.admin')


@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                  <div class="col-sm-7">
                  <div class="card-body">
                    <div>
                      <h4>Services</h4>
                    </div>
                    <nav aria-label="breadcrumb">
                      <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                          <a href="{{ route('dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Microtik</li>
                        <li class="breadcrumb-item active">Services</li>
                      </ol>
                    </nav>
                </div>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>
@endsection

