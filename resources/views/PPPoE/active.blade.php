@extends('admin.layouts.header')

<x-assets.datatables />

@push('page-css')

@endpush

@push('page-header')
<div class="col-xs-7 col-auto">
	<h3 class="page-title">Acive Users</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Acive Users</li>
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
          <div class="form-group col-auto">
              <form action="{{ route('pppoe.active')}}" class="form-sample" method="get">
                  {{-- @csrf --}}
                  <select class="form-control-sm" name="server" onchange="this.form.submit()">
                      @if (!$iid)
                        <option value="" selected></option>
                        @else
                        <option value=""></option>
                      @endif
                      @foreach ($servers as $server)
                        @if ($server->id==$iid)
                          <option value="{{ $server->id}}" selected>{{ $server->name}}</option>
                        @else
                          <option value="{{ $server->id}}">{{ $server->name}}</option>
                        @endif
                      @endforeach
                  </select>
              </form>
          </div>
          <div class="col-auto">
            <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search for names.." class="form-control-sm">
          </div>
            </div>
          <div>
            @if (session('msg'))
              <label class="badge badge-success"> {{ session('msg') }}</lable>
            @endif
          </div>
      </div>
      </div>
    </div>
</div>

@if ($activeu)
<div class="row">
  <div class="col-xs-12">
    <div class="card">
      <div class="card-body">
		<!-- Active Users -->
        @php
          $totalusers =count($activeu);
        @endphp
          <div class="form-group">
            <h4 class="card-title">Online Users {{ $totalusers }}</h4>
            <table class="table table-hover" id="myTable">
                  <tr><th>Name</th><th>Ping</th><th>Mac ID</th><th>IP</th><th>Uptime</th><th>Remove</th></tr>
                  @foreach (array_reverse($activeu) as $no => $activeuser)
                  @php
                        $tenip = explode(".",$activeuser['address']) ;
                  @endphp
                  @if ($tenip[0]=="10")
                          <tr style="background-color:#e7e7e7">
                  @else
                          <tr>
                  @endif
                              <td>
                                @if(Auth::user()->can('view-subscriber'))
                                <a href="{{ route('subscriber.microtik', ['name'=> $activeuser['name']]) }}" class="link-warning">{{ $activeuser['name'] }}</a>
                                @else
                                {{ $activeuser['name'] }}
                                @endif
                              </td>
                              <td><a href="{{ route('pppoe.ping',['ip'=>$activeuser['address'], 'server'=>$iid,'username'=>$activeuser['name']])}}" class="bt-danger btn-sm">Ping</a></td>
                              <td>{{ $activeuser['caller-id'] }}</td>
                              <td><a href="{{ "http://".$activeuser['address'].":8080"  }}" target="_blank">{{ $activeuser['address'] }}</a></td>
                              {{-- <td>{{ $activeuser['address'] }}</td> --}}
                              <td>{{ $activeuser['uptime'] }}</td>
                              <td>
                                <form action="{{ route('pppoe.delete', $activeuser['.id'])}}" method="POST" name="{{ $activeuser['.id'] }}">
                                  @csrf
                                  <input type="hidden" name="server" value="{{ $iid }}">
                                  <input type="hidden" name="cname" value={{ $activeuser['name'] }}>
                                  <button type="submit" name="" id="" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                                {{-- <a href="{{ route('pppoe.delete',$activeuser['.id'])}}" class="btn btn-danger btn-sm">Delete</a> --}}
                              </td>
                          </tr>
                      @endforeach
            </table>
        </div>

		<!-- /Active Users -->
	</div>
    </div>
</div>
</div>
@endif
<script>
  function myFunction() {
    // Declare variables
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("myInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("myTable");
    tr = table.getElementsByTagName("tr");

    // Loop through all table rows, and hide those who don't match the search query
    for (i = 0; i < tr.length; i++) {
      td = tr[i].getElementsByTagName("td")[0];
      if (td) {
        txtValue = td.textContent || td.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
          tr[i].style.display = "";
        } else {
          tr[i].style.display = "none";
        }
      }
    }
  }
  </script>
@endsection
