@extends('admin.layouts.header')

@php
    // if(isset($iid)){$iid;}else{$iid="";}
    $urll = url()->current();
@endphp
<x-assets.datatables />

@push('page-css')

@endpush

@push('page-header')
<div class="col-xs-7 col-auto">
	<h3 class="page-title">Acive PPPoE Users</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">All Acive Users</li>
	</ul>
</div>

@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
      <div class="card bg-primary text-white">
        <div class="card-body">
            <div class="form-row">
          <div class="col-auto">
              <label class="danger">Select Server</label>
          </div>
          {{-- <div class="form-group col-auto">
              <form action="{{ route('pppoe.newactive')}}" class="form-sample" method="get">
                  {{-- @csrf --}}
                  {{-- <select class="form-control-sm" name="server" onchange="this.form.submit()"> --}}
                      {{-- @if (!$iid) --}}
                        {{-- <option value="" selected></option> --}}
                        {{-- @else --}}
                        {{-- <option value=""></option> --}}
                      {{-- @endif --}}
                      {{-- @foreach ($servers as $server) --}}
                        {{-- @if ($server->id==$iid) --}}
                          {{-- <option value="{{ $server->id}}" selected>{{ $server->name}}</option> --}}
                        {{-- @else --}}
                          {{-- <option value="{{ $server->id}}">{{ $server->name}}</option> --}}
                        {{-- @endif --}}
                      {{-- @endforeach --}}
                  {{-- </select> --}}
              {{-- </form> --}}
          {{-- </div> --}}
          <div><input type="checkbox" name="" id="">All Servers</div>
            </div>
          <div>
            @if (session('msg'))
              <P class="alert alert-danger"> {{ session('msg') }}</P>
            @endif
          </div>
      </div>
      </div>
    </div>
</div>

{{-- @if ($iid) --}}
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="all-active" class="datatable table table-striped table-bordered table-hover table-center mb-0">
						<thead>
							<tr style="boder:1px solid black;">
								<th>Server</th>
                                <th>Name</th>
                                <th>Ping</th>
								                <th>MAC</th>
                                <th>IP</th>
                                <th>Uptime</th>
                                <th>Disconnect</th>
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
{{-- @endif --}}

{{-- {{ $iid }} --}}
@endsection

{{-- @if ($iid) --}}
@push('page-js')
<script>
    $(document).ready(function() {
        var table = $('#all-active').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('pppoe.allactive') }}",
            columns: [
                {data: 'server', name: 'server'},
                {data: 'namel', name: 'namel'},
                {data: 'ping', name: 'ping', searchable: false, orderable: false,},
                {data: 'caller-id',name: 'caller-id', orderable: false,},
                {data: 'addressnew',name: 'addressnew'},
                {data: 'time', name: 'time', searchable: false},
                {data: 'remove', name: 'remove', searchable: false, orderable: false,},
                // {data: 'remove', name: 'remove', orderable: false, searchable: false},
            ],
            aaSorting: [[5, 'desc']],
        });
        //
    });
</script>
@endpush
{{-- @endif --}}
{{-- render : function(data, type, row, meta) {return'<a class="d-inline-block fw-normal w-100 h-100 pe-auto" href="pppoe/ping?ip=' + row.address + '&username=' + row.address + '&server=' + $iid +'">' + row.address + '</a>';}, --}}
