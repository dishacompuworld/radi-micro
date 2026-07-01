@php
    if(isset($seletedserver)){$seletedserver;}else{$seletedserver="";}
    // if(isset($iiip)){$iiip;}else{$iiip="";}
    // if(isset($sname)){$sname;}else{$sname="";}
    // if(isset($subscriber)){$subscriber;}else{$subscriber="";}
    //
    if(isset($scripts)){$scripts;}else{$scripts=[];}
    if(isset($_GET['state'])){ $state=$_GET['state']; }else{$state='off';}

@endphp
@extends('layouts.admin')

@push('page-css')
<!-- Add any additional CSS here -->
<style>
    .card-body {
        overflow-x: auto; /* Enable horizontal scrolling if needed */
    }
    .table {
        width: 100%; /* Ensure the table takes full width */
    }
</style>
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Scripts</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Microtik</li>
                        <li class="breadcrumb-item active">Scripts</li>
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
            <form method="GET" action="{{ route('microtik.scripts') }}">
                <div class="form-group">
                    <label for="sserver">Select Server:</label>
                    <select name="sserver" id="sserver" class="form-control" onchange="this.form.submit()">
                        @if (!$seletedserver)
                        <option value="" selected></option>
                        @else
                        <option value=""></option>
                      @endif
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}" {{ $seletedserver == $server->id ? 'selected' : '' }}>{{ $server->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
          </div>
        
            @if(isset($scripts))
            <div class=" table table-responsive">
                <table class="table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Source</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scripts as $script)
                            <tr>
                                <td>{{ $script['name'] }}</td>
                                <td>{{ $script['source'] }}</td>
                                <td>
                                    <form method="POST" action="{{ route('microtik.script.run', $script['.id']) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Run</button>
                                    </form>
                                    <a href="{{ route('microtik.script.edit', ['id' => $script['.id'], 'sserver' => $seletedserver]) }}" class="btn btn-primary">Edit</a>
                                    <form method="POST" action="{{ route('microtik.script.delete', $script['.id']) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
      </div>
    </div>
</div>

</div>
@endsection