@php
    $findyes = 0;
    if(isset($locationshort)){$locationshort;}else{$locationshort="";}
    if(isset($search)){$search;}else{$search="";}

    $urll = url()->current() . "?location=". $locationshort;

@endphp
@extends('layouts.admin')


@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Access Requests Logs</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Radius</li>
                        <li class="breadcrumb-item active">Access Requests Logs</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>


    <div><input type="hidden" id="searchBox" placeholder="Search..." class="form-control mb-3" value="{{ $search }}"></div>
    <div class="row">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table id="admin-acccess-logs" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                <thead>
                  <tr style="boder:1px solid black;">
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>NAS IP</th>
                                    <th>User Name</th>
                                    <th>MAC</th>
                                    <th>Message</th>
                                    <th>Reply Message</th>
                  </tr>
                </thead>
                <tbody>

                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>


</div>
@endsection

@push('page-js')
{{-- @if($locationshort) --}}
<script>
    $(document).ready(function() {
        var table = $('#admin-acccess-logs').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.accesslogs') }}",
            columns: [
                {data: '0', name: '0', orderable: false, searchable: false},
                {data: '1', name: '1', orderable: false, searchable: false},
                {data: '2', name: '2', orderable: false, searchable: false},
                // {data: '3', name: '3', orderable: false},
                {data: 'usern', name: 'usern', orderable: false},
                {data: 'newmac', name: 'newmac', orderable: false},
                {data: '6', name: '6', orderable: false},
                {data: '7', name: '7', orderable: false},
            ],
            aaSorting: [],
            pageLength: 50,
        });
        

        // Get the search parameter from the URL and set the search box value 
        // let searchParam = getParameterByName('search');
        let searchParam = getParameterByName('search') || '';
        // let searchParam = {{ $search }};

        $('#searchBox').val(searchParam); 
        table.search(searchParam).draw(); 
        
        // Trigger search on DataTable when typing in the search box 
        $('#searchBox').on('keyup', function () { 
            let searchValue = this.value; 
            table.search(searchValue).draw(); 
            updateURLParameter('search', searchValue); 
        }); 
        
        // Function to get URL parameter 
        function getParameterByName(name) { name = name.replace(/[ \[\] ]/g, '\\$&'); 
        let regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'), 
        results = regex.exec(window.location.href); 
        
        if (!results) return null; 
        if (!results[2]) return ''; 
        return decodeURIComponent(results[2].replace(/\+/g, ' ')); 
        } 
        
        // Function to update the URL parameter and reload the page 
        function updateURLParameter(param, value) { 
            let url = new URL(window.location.href); 
            url.searchParams.set(param, value); 
            window.history.pushState({}, '', url); 
            location.reload(); }
    });
</script>
{{-- @endif --}}
@endpush
