@php
    // if(isset($seletedserver)){$seletedserver;}else{$seletedserver="";}
    $urll = url()->current() . "?sserver=". $seletedserver;
@endphp
@extends('layouts.admin')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Logs</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Microtik</li>
                        <li class="breadcrumb-item active">Logs</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-body">
          <div class="form-group col-sm-3">
              {{-- <form action="{{ route('shedule.show')}}" class="form-sample" method="get" name="mtk"> --}}
                <div class="input-group input-group-sm mb-3">
                    <div class="input-group-prepend">
                      <label class="input-group-text" for="inputGroupSelect01">Select Server</label>
                    </div>
                    <form action="{{ route('microtik.log')}}" class="form-sample" method="get" name="mtk">
                    <select name="sserver" onchange="this.form.submit()" class="custom-select" id="server-select">
                          <option value=""></option>
                          @foreach ($servers as $server)
                          <option value="{{ $server->id }}" {{ $seletedserver == $server->id ? 'selected' : '' }}>
                              {{ $server->name }}
                          </option>
                            @endforeach
                    </select>
                </form>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span for="auto-refresh">Auto-refresh&nbsp;&nbsp;</span>
                            <input type="checkbox" id="auto-refresh" onchange="toggleAutoRefresh()">
                        </div>                            
                    </div>
                  </div>
              {{-- </form> --}}
          </div>
        </div>
      </div>
    </div>
</div>

{{-- <div class="container"> --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="logs-table" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                            <thead>
                                <tr style="boder:1px solid black;">
                                    <th>Time</th>
                                    <th>Topics</th>
                                    <th>Message</th>
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

@if($seletedserver){
<script>
    $(document).ready(function() {
        var autoRefresh = false;
        var table = $('#logs-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ $urll }}",
            type: "GET",
            columns: [
                {data: 'time1',name: 'time1', orderable: false, searchable: false},
                {data: 'topics1',name: 'topics1',orderable: false, searchable: false},
                {data: 'message', name: 'message', className: 'dt-center', orderable: false},
            ],
            rowCallback: function(row, data){
                if (data.topics1.includes('error')) {
                    $(row).addClass('text-danger');
                }
            },
            aaSorting: [],
            pageLength: 25
        });
    });

    function toggleAutoRefresh() {
        var autoRefresh = !autoRefresh;
        if (autoRefresh) {
            $('#logs-table').DataTable().ajax.reload();
            setInterval(toggleAutoRefresh, 5000);
        } else {
            clearInterval(setInterval);
        }
    }

    $('#auto-refresh').click(function() {
        toggleAutoRefresh();
    });

</script>
}
@endif
@endpush