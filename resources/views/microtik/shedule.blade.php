@php
    if(isset($seletedserver)){$seletedserver;}else{$seletedserver="";}
    // if(isset($iiip)){$iiip;}else{$iiip="";}
    // if(isset($sname)){$sname;}else{$sname="";}
    // if(isset($subscriber)){$subscriber;}else{$subscriber="";}
    //
    if(isset($shedules)){$shedules;}else{$shedules=[];}
    if(isset($_GET['state'])){ $state=$_GET['state']; }else{$state='off';}

@endphp
@extends('layouts.admin')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Scheduler</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Microtik</li>
                        <li class="breadcrumb-item active">Scheduler</li>
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
              <form action="{{ route('shedule.show')}}" class="form-sample" method="get" name="mtk">
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
          @if($shedules)
          <div>
            <table>
                <tr><th>Name</th><th>Enable</th></tr>
                @foreach ($shedules as $shedule)
                <form action="{{ route('shedule.show') }}" name="{{ $shedule['name'] }}" method="get">
                    <tr><td>{{ $shedule['name'] }}</td>
                        @if($shedule['disabled']=='false')
                            <td><input type='checkbox' checked name='state' onChange="this.form.submit();"></td>
                        @else
                        <td><input type='checkbox' name='state' onChange="this.form.submit();"></td>
                        @endif
                    </tr>
                    <input type="hidden" name="sserver" value="{{ $seletedserver }}">
                    <input type="hidden" name="checkbox" value="pressed">
                    <input type="hidden" name="id" value="{{ $shedule['.id'] }}">
                </form>
                @endforeach
            </table>
          </div>
          @endif
      </div>
      </div>
    </div>
</div>

</div>
@endsection