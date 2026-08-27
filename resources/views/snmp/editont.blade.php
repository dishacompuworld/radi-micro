@php
   if(isset($oid)){$oid;}else{$oid="";}
   if(isset($opticalpower)){$opticalpower;}else{$opticalpower="";}
   if(isset($onttxpower)){$onttxpower;}else{$onttxpower="";}
   if(isset($ontname)){$ontname;}else{$ontname="";}
   if(isset($ontuptime)){$ontuptime;}else{$ontuptime="";}
   if(isset($ontserial)){$ontserial;}else{$ontserial="";}
   if(isset($onttemp)){$onttemp;}else{$onttemp="";}
   if(isset($onteth)){$onteth;}else{$onteth="";}
   if(isset($ontstatus)){$ontstatus;}else{$ontstatus="";}
@endphp
@extends('layouts.admin')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
      <div class="col-md-8 d-flex flex-column justify-content-center">
          <h4 class="mb-3">Edit ONT</h4>
          <nav aria-label="breadcrumb">
              <ol class="breadcrumb breadcrumb-style1 mb-0">
                  <li class="breadcrumb-item">
                      <a href="{{ route('dashboard') }}">Dashboard</a>
                  </li>
                  <li class="breadcrumb-item">OLT</li>
                  <li class="breadcrumb-item active">Edit ONT</li>
              </ol>
          </nav>
      </div>
    </div>

<div class="col-md-12">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
</div>
<div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-body">
            <div class="form-group col-md-3">
                <form class="forms-sample" action="{{ route('edit.ont')}}" method="get">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroup-sizing-sm"><b>Select OID</b></span>
                        </div>
                        <select class="select2 form-select form-control" id="inputGroupSelect03" name="oid" style="width: 100%;" onchange="this.form.submit()">
                        {{-- @if (isset($location->name)) --}}
                        @foreach ($optdata as $data)
                            @if($data->oid === $oid)
                                <option value="{{ $data->oid}}" selected>{{ $data->name . "(" . $data->oid .")" }}</option>
                            @else
                                <option value="{{ $data->oid}}">{{ $data->name . "(" . $data->oid .")" }}</option>
                            @endif
                        @endforeach
                        {{-- @endif --}}
                        </select>
                    </div>
                </form>
            </div>
                @if($oid)
                <div class="mt-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0">ONT Details</h5>
                        <span class="badge bg-label-primary">OID: {{ $oid }}</span>
                    </div>
                    <form action="{{ route('edit.ont')}}" class="form-group mb-0">
                        <div class="table-responsive border rounded">
                            <table class="table table-hover align-middle mb-0">
                                <tr><th class="text-muted fw-semibold">Name</th>
                                    <td>
                                        @can('rename-ont')
                                            @php
                                            if($opticalpower=="Ont Not assign" or $opticalpower=="Snmp Not Available"){
                                                echo $ontname;
                                            }else{
                                                echo "<input type='text' value='". $ontname ."' name='ontnewname' class='form-control form-control-sm'>";
                                            }
                                            @endphp
                                        @else
                                        {{ $ontname }}
                                        @endcan
                                    </td>
                                </tr>
                                <tr><th class="text-muted fw-semibold">Optical Power</th>
                                    @php
                                        if($opticalpower <= env('MIN_ONT_POWER',null)){
                                            echo '<td class="text-danger"><b>'. $opticalpower . ' dBm</b></td>';
                                        }elseif($opticalpower=="Ont Not assign"){
                                            echo '<td>'. $opticalpower . '</td>';
                                        }elseif($opticalpower=="Snmp Not Available"){
                                            echo '<td>'. $opticalpower . '</td>';
                                        }else{
                                            echo '<td class="text-success"><b>'. $opticalpower . ' dBm</b></td>';
                                        }
                                    @endphp
                                </tr>
                                <tr><th class="text-muted fw-semibold">Optical TX Power</th><td class="fw-semibold">{{ $onttxpower }}</td></tr>
                                <tr><th class="text-muted fw-semibold">Model</th><td class="fw-semibold">{{ $ontmodel }}</td></tr>
                                <tr><th class="text-muted fw-semibold">Serial No.</th><td class="fw-semibold">{{ $ontserial }}</td></tr>
                                <tr><th class="text-muted fw-semibold">ONT Uptime</th>
                                    <td>
                                        @php
                                        if($ontuptime=="Not Available"){
                                            echo $ontuptime;
                                        }elseif($ontuptime=="Snmp Not Available"){
                                            echo $ontuptime;
                                        }else{
                                            echo gmdate('H:i:s', $ontuptime);
                                        }
                                        @endphp
                                    </td>
                                </tr>
                                <tr><th class="text-muted fw-semibold">Temp.</th>
                                    @php
                                        if($onttemp>="Not Available"){
                                            echo '<td>'.$onttemp.'</td>';
                                        }elseif($onttemp>=50){
                                            echo '<td class="text-danger"><b>'.$onttemp.'&deg;C</b></td>';
                                        }else{
                                            echo '<td>'.$onttemp.'&deg;C</td>';
                                        }
                                    @endphp
                                </tr>
                                <tr><th class="text-muted fw-semibold">Ethernet Ports</th><td class="fw-semibold">{{ $onteth }}</td></tr>
                                <tr><th class="text-muted fw-semibold">Distance</th><td class="fw-semibold">{{ $ontdist }}</td></tr>
                                <tr><th class="text-muted fw-semibold">Status</th><td class="fw-semibold">{{ $ontstatus }}</td></tr>
                                
                                <input type="hidden" name="oid" value="{{ $oid }}">
                                <tr><td colspan='2' class="text-center py-3">
                                    @can('rename-ont')
                                    @php
                                        if($opticalpower=="Ont Not assign" or $opticalpower=="Snmp Not Available"){
                                            echo "";
                                        }else{
                                            echo "<input type='submit' value='Save' class='btn btn-success btn-sm'>";
                                        }
                                    @endphp
                                        {{-- <input type='Button' value='Reboot' data-oid='{{ $oid }}' id='rebootont' class='btn btn-danger btn-sm'> --}}
                                        <a href="javascript:void(0)" class='btn btn-danger btn-sm' id="rebootont" data-oid="{{ $oid }}">Reboot ONT</a>
                                        {{-- <input type='Button' value='De-Register' data-oid='{{ $oid }}' id='deregist' class='btn btn-danger btn-sm'> --}}
                                        {{-- <a href="javascript:void(0)" class='btn btn-danger btn-sm' id="deregist" data-oid="{{ $oid }}">De-Register</a> --}}
                                            {{-- <a href='javascript:void(0)' class='btn btn-danger float-right mt-2' id='deregist'>Deregister</a> --}}
                                        {{-- <input type='Button' value='Register' data-oid='{{ $oid }}' id='regist' class='btn btn-danger btn-sm'> --}}
                                        {{-- <a href="javascript:void(0)" class='btn btn-danger btn-sm' id="regist" data-oid="{{ $oid }}">Register</a> --}}
                                    @endcan
                                            </td>
                                </tr>
                            </table>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-start mt-3">
        <a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
    </div>
</div>
</div>
@endsection

@push('page-js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#inputGroupSelect03').select2({
        placeholder: 'Search OID',
        allowClear: true,
        dropdownParent: $('body'),
        width: '100%'
    });

// $('body').on('click','#deregist',function(){
//         //e.preventDefault();
//         var $button = $(this);
//         // console.log($button); // Check if $button is defined
//         $button.prop('disabled', false); // Enable button
//         $button.html('Processing...');
//         var route = "{{route('de.register')}}";
//         var variable = $(this).data('oid');
//         $.ajax({
//             type: 'GET',
//             url: route,
//             data: {
//                 oid: variable,
//             },
//             success: function(response) {
//                 // $('#opticalpowers').DataTable().ajax.reload(null, false);
//                 $button.html('De-Register'); // Revert button text
//                 $button.prop('disabled', false);
//             }
//         });
//     });

// $('body').on('click','#regist',function(){
//         //e.preventDefault();
//         var $button = $(this);
//         // console.log($button); // Check if $button is defined
//         $button.prop('disabled', false); // Enable button
//         $button.html('Processing...');
//         var route = "{{route('de.register')}}";
//         var variable = $(this).data('oid');
//         $.ajax({
//             type: 'GET',
//             url: route,
//             data: {
//                 oid: variable,
//             },
//             success: function(response) {
//                 // $('#opticalpowers').DataTable().ajax.reload(null, false);
//                 $button.html('Register'); // Revert button text
//                 $button.prop('disabled', false);
//             }
//         });
//     });

    $('body').on('click','#rebootont',function(){
        //e.preventDefault();
        var $button = $(this);
        // console.log($button); // Check if $button is defined
        $button.prop('disabled', false); // Enable button
        $button.html('Processing...');
        var route = "{{route('reboot.ont')}}";
        var variable = $(this).data('oid');
        $.ajax({
            type: 'GET',
            url: route,
            data: {
                oid: variable,
            },
            success: function(response) {
                // $('#opticalpowers').DataTable().ajax.reload(null, false);
                $button.html('Reboot ONT'); // Revert button text
                $button.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush