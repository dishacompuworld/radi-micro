@php

    if(isset($sdtime)){$sdtime;}else{$sdtime="";}
    if(isset($edtime)){$edtime;}else{$edtime="";}

@endphp
@extends('layouts.admin')


@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">History Graph</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">PRTG</li>
                        <li class="breadcrumb-item active">History Graph</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    
                    <!-- Success Message -->
                    <div class="mb-3">
                        @if (session('msg'))
                            <label class="badge badge-success">{{ session('msg') }}</label>
                        @endif
                    </div>

                    <!-- Form: All in One Row -->
                    <form action="{{ route('history.graph') }}" method="GET" class="d-flex align-items-center flex-nowrap gap-2">
                        <label for="sdtime" class="text-nowrap">Start Datetime :</label>
                        <input type="datetime-local" id="sdtime" name="sdtime" value="{{$sdtime}}" class="form-control form-control-sm">
                        
                        <label for="edtime" class="text-nowrap">End Datetime :</label>
                        <input type="datetime-local" id="edtime" name="edtime" value="{{$edtime}}" class="form-control form-control-sm">
                        
                        <input type="submit" class="btn btn-primary btn-sm text-nowrap" value="Show">
                    </form>

                    <!-- Graph Image: w-100 makes it fill the card width -->
                    <div class="mt-4">
                        <img src="hgraph.svg" class="w-100" style="height: auto;" alt="History Graph">
                    </div>

                </div> <!-- End of card-body -->
            </div> <!-- End of card -->
            
            <a href="javascript: history.back()" class="btn btn-primary btn-sm mt-3">Back</a>
        </div>
    </div>
</div>

@endsection
