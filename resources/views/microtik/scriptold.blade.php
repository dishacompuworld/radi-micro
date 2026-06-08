@php
    if(isset($seletedserver)){$seletedserver;}else{$seletedserver="";}
    // if(isset($iiip)){$iiip;}else{$iiip="";}
    // if(isset($sname)){$sname;}else{$sname="";}
    // if(isset($subscriber)){$subscriber;}else{$subscriber="";}
    //
    if(isset($scripts)){$scripts;}else{$scripts=[];}
    if(isset($_GET['state'])){ $state=$_GET['state']; }else{$state='off';}

@endphp


@extends('admin.layouts.header')

<x-assets.datatables />

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Script</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Script</li>
	</ul>
</div>

@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-body">
          <div class="form-group col-sm-3">
            <form action="{{ route('script.show')}}" class="form-sample" method="get" name="scriptssss">
              <div class="input-group input-group-sm mb-3">
                  <div class="input-group-prepend">
                    <label class="input-group-text" for="inputGroupSelect01">Select Server</label>
                  </div>
                  <select name="sserver" onchange="this.form.submit()" class="custom-select" id="inputGroupSelect01">
                      @if (!$seletedserver)
                        <option value="" selected></option>
                        @else
                        <option value=""></option>
                      @endif
                      @foreach ($servers as $server)
                        @if ($server->id==$seletedserver)
                          <option value="{{ $server->id}}" selected>{{ $server->name}}</option>
                        @else
                          <option value="{{ $server->id}}">{{ $server->name}}</option>
                        @endif
                      @endforeach
                  </select>
                </div>
            </form>
        </div>
          <div>
            @if (session('msg'))
              <label class="badge badge-success"> {{ session('msg') }}</lable>
            @endif
          </div>
          @if($scripts)
          <div class="table table-responsive">

                <table class="table-hover">
                    <tr><th>Name</th><th>Script</th><th>Run Count</th><th> Run </th></tr>

                    @foreach ($scripts as $script)
                    <form action="{{ route('script.show') }}" name="{{ $script['name'] }}" method="get" class="form-sample">
                        <tr>
                            <td>{{ $script['name'] }}</td>
                            <td>{{ $script['source'] }}</td>
                            <td>{{ $script['run-count'] }}</td>
                            <td><input class="form-control-sm" type="Button" value="Execute" onclick="this.form.submit()" class="danger"></td>
                        </tr>
                        <input type="hidden" name="sserver" value="{{ $seletedserver }}">
                        <input type="hidden" name="checkbox" value="pressed">
                        <input type="hidden" name="id" value="{{ $script['.id'] }}">
                    </form>
                    @endforeach
                </table>
          </div>
          @endif
      </div>
      </div>
    </div>
</div>
@endsection
