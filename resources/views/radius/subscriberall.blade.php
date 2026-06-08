@php
   if(isset($name)){$name;}else{$name="";}


    // $urll = $loca . ".xceednet.com";

    $url = url()->current();

@endphp
@extends('layouts.admin')


@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Search User All Location</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Radius</li>
                        <li class="breadcrumb-item active">Search User All Location</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- Subscribers -->
                            <div class="form-group col-12 col-md-12">
                                <form class="forms-sample" action="{{ route('search.subscriberall') }}">
                                    <div class="input-group input-group-sm mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text fixed-size-span" id="inputGroup-sizing-sm">Customer Name / Username / Mobile / Address / MAC</span>
                                        </div>
                                        <input type="search" class="form-control" placeholder="Customer Name/Username/Mobile/Address/MAC" aria-label="Customer Name/Username/Mobile/Address/MAC" aria-describedby="basic-addon2"  name="name" value="{{ $name }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- /Subscribers -->
                        </div>
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
                <table id="search-subscriberall" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                  <thead>
                    <tr style="boder:1px solid black;">
                      <th>User Id</th>
                      <th>Name</th>
                      <th>Location</th>
                      <th>Online</th>
                      <th>Static IP</th>
                      <th>Renewal</th>
                      <th>Expiry</th>
                      <th>Status</th>
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
        var table = $('#search-subscriberall').DataTable({
            processing: true,
            serverSide: true,
            // ajax: "{{ $url }}",
            ajax: {
            url: '{{ $url }}',
            type: "get",
            data: function (d) {
                d.name = '{{$_GET['name']}}';
                },
            },
            columns: [
                {
                    data: 'namen',
                    name: 'namen',
                    render : function(data, type, row, meta) {
                        return '<a class="d-inline-block fw-normal w-100 h-100 pe-auto" href="subscribermicrotik?name=' + row.namen +'">' + row.namen + '</a>';
                    },
                    orderable: false,
                },
                {
                    data: 'usern',
                    name: 'usern',
                    orderable: false,
                },
                {data: 'isp', name: 'isp', orderable: false, searchable: true},
                {
                    data: 'online',
                    name: 'online',
                    render : function(data, type, row, meta) {
                        if(row.online == "Online"){
                            return '<p class="text-success">' + row.online + '</p>';
                        }else{
                            return '<p class="text-danger">' + row.online + '</p>';
                        }

                    },
                    orderable: false,
                    searchable: true,
                },
                {data: 'static', name: 'static', orderable: false, searchable: false},
                {data: '6', name: '6', orderable: false, searchable: false},
                {data: '7', name: '7', orderable: false, searchable: false},
                {
                    data: 'status',
                    name: 'status',
                    render : function(data, type, row, meta) {
                        if(row.status == "Expired"){
                            return '<p class="text-danger">' + row.status + '</p>';
                        }else{
                            return row.status ;
                        }

                    },
                    orderable: false,
                },
            ],
            aaSorting: [],
        });
        //
    });
</script>
@endif
@endpush