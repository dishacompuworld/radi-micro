@php
   if(isset($loca)){$loca;}else{$loca="";}
   if(isset($name)){$name;}else{$name="";}


    $urll = $loca . ".xceednet.com";

    $url = url()->current();

@endphp
@extends('layouts.admin')


@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Search Subscribers</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Radius</li>
                        <li class="breadcrumb-item active">Search Subscribers</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-body">
              <div class="form-group col-sm-6">
                  <form class="forms-sample" action="{{ route('search.subscriber')}}">
                      <div class="input-group input-group-sm mb-3">
                              <div class="input-group-prepend">
                              <label class="input-group-text" for="inputGroupSelect01">Select Location</label>
                              </div>
                              <select class="" name="loca" class="custom-select">
                                  @if (!$loca)
                                      <option value="" selected></option>
                                  @else
                                      <option value=""></option>
                                  @endif
                                  {{-- @if (isset($location->name)) --}}
                                      @foreach ($location as $loc)
                                      @if ($loc->name==$loca)
                                          <option value="{{ $loc->name }}" selected>{{ $loc->name }}</option>
                                      @else
                                          <option value="{{ $loc->name }}">{{ $loc->name }}</option>
                                      @endif
                                      @endforeach
                                  {{-- @endif --}}
                                  </select>
                                  <input type="search" class="form-control" placeholder="Customer Name/Username/Mobile/Address/MAC" aria-label="Customer Name/Username/Mobile/Address/MAC" aria-describedby="basic-addon2"  name="name" value="{{ $name }}">
                                  <div class="input-group-append">
                                      <button class="btn btn-outline-secondary" type="submit">Search</button>
                                  </div>
                          </div>
                      </form>
                  </div>
              </div>
        </div>
      </div>
  </div>
  @if($name)
  <div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table id="search-subscriber" class="datatable table table-striped table-bordered table-hover table-center mb-0">
              <thead>
                <tr style="boder:1px solid black;">
                  <th>User Id</th>
                                  <th>User Name</th>
                                  <th>Name</th>
                                  <th>Mobile</th>
                                  <th>Online</th>
                                  <th>Renewal Date</th>
                                  <th>Expiry Date</th>
                </tr>
              </thead>
              <tbody>

              </tbody>
            </table>
          </div>
        </div>
      </div>
          <a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
    </div>

  </div>
  @endif

</div>
@endsection

@push('page-js')
@if($name)
<script>
    $(document).ready(function() {
        var table = $('#search-subscriber').DataTable({
            processing: true,
            serverSide: true,
            // ajax: "{{ $url }}",
            ajax: {
            url: '{{ $url }}',
            type: "get",
            data: function (d) {
                d.name = '{{$_GET['name']}}';
                d.loca ='{{$_GET['loca']}}';
                },
            },
            columns: [
                {data: '0', name: '0', orderable: false, searchable: false},
                {data: 'usern', name: 'usern', orderable: false},
                {data: '2', name: '2', orderable: false},
                {data: '4', name: '4', orderable: false, searchable: false},
                {data: 'online', name: 'online', orderable: false, searchable: true},
                {data: 'renewaldt', name: 'renewaldt', orderable: false, searchable: false},
                {data: 'expirydt', name: 'expirydt', orderable: false, searchable: false},
            ],
            aaSorting: [],
        });
        //
    });
</script>
@endif
@endpush
