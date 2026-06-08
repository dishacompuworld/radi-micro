
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
                        <li class="breadcrumb-item active">Location Details</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('locationdetails.show')}}" method="get">
                        <div class="row g-3 align-items-end">
                            <div class="col-sm-6 col-md-4 col-lg-3 d-flex align-items-center">
                                <!-- col-4 gives the label 33.3% of the width, me-2 adds the gap -->
                                <label class="form-label col-4 me-2 mb-0" for="location">Select Location</label>
                                
                                <!-- col-8 gives the select 66.6% of the width -->
                                <select class="form-select col-8" id="location" name="location" onchange="this.form.submit()">
                                    <option value="" @selected(!$locationshort)>Select Location</option>
                                    @foreach ($slocations as $loc)
                                        <option value="{{ $loc->name }}" @selected($loc->name == $locationshort)>
                                            {{ $loc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

  @if ($response)
  <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
              <div class="table-responsive">
                  <table class="table table-striped table-hover align-middle mb-0">
                      <tbody>
                          <tr><th scope="row">Total Subscribers</th><td>{{ $response['all_subscribers_count'] }}</td></tr>
                          <tr><th scope="row">Active Subscribers</th><td>{{ $response['active_subscribers_count'] }}</td></tr>
                          <tr><th scope="row">Online Subscribers</th><td>{{ $response['online_subscribers_count'] }}</td></tr>
                          <tr><th scope="row">Renewed in Advance</th><td>{{ $response['renewed_in_advance'] }}</td></tr>
                          <tr><th scope="row">Packages Sold Today</th><td>{{ $response['packages_sold_today'] }}</td></tr>
                          <tr><th scope="row">Packages Sold Yesterday</th><td>{{ $response['packages_sold_yesterday'] }}</td></tr>
                          <tr><th scope="row">Packages Sold This Month</th><td>{{ $response['packages_sold_this_month'] }}</td></tr>
                          <tr><th scope="row">Packages Sold Last Month</th><td>{{ $response['packages_sold_last_month'] }}</td></tr>
                          <tr><th scope="row">Registered Today</th><td>{{ $response['registered_today'] }}</td></tr>
                          <tr><th scope="row">Registered This Month</th><td>{{ $response['registered_this_month'] }}</td></tr>
                          <tr><th scope="row">Registered Last Month</th><td>{{ $response['registered_last_month'] }}</td></tr>
                      </tbody>
                  </table>
              </div>
          </div>
        </div>
      </div>
    </div>
  @endif

</div>
@endsection
