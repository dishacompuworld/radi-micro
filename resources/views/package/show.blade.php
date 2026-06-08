@php
    if(isset($locationname)){$locationname;}else{$locationname="";}

    $findyes = 0;

@endphp
@extends('layouts.admin')


@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Locations</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Radius</li>
                        <li class="breadcrumb-item active">Locations</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>


    <div class="row">
      <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('packages.show') }}" method="get">
                        <div class="row g-3 align-items-end">
                            <!-- Both label and select are now inside this single column div -->
                            <div class="col-sm-6 col-md-4 col-lg-3 d-flex align-items-center">
                                <label class="form-label col-4 me-2 mb-0" for="location">Select Location</label>
                                <select class="form-select col-8" id="location" name="location" onchange="this.form.submit()">
                                    <option value="" @selected(!$locationname)>Select Location</option>
                                    @foreach ($locations as $loc)
                                        <option value="{{ $loc->id }}" @selected($loc->name == $locationname)>
                                            {{ $loc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                </div>

                <div>
                    @if (session('msg'))
                    <label class="badge badge-success"> {{ session('msg') }}</lable>
                    @endif
                </div>
                    @if($locationname)
                    <table class="table table-striped">
                        <tr><th>ID</th><th>Name</th><th>Validity</th><th>Upload</th><th>Download</th><th>Description</th><th>Insert/Delete</th></tr>
                            @foreach ($response['data'] as $resdd )
                            {{-- {{ $packages }} --}}
                            <form action="{{route ('package.insertdelete')}}" name="{{ $resdd['id']}}" method="POST">
                                @csrf
                            <tr>
                                @foreach ($packages as $package)
                                @if ($package->radiusid == $resdd['id'] )

                                <td>{{ $resdd['id'] }}<input type="hidden" name="rid" value="{{ $resdd['id']}}"></td>
                                <td>{{ $resdd['name'] }} <input type="hidden" name="name" value="{{ $resdd['name']}}"></td>
                                <td>{{ $resdd['valid_for'] . " " . $resdd['validity_unit'] }} </td>
                                <td>{{ $resdd['bandwidth_up'] . " " . $resdd['bandwidth_up_unit'] }} </td>
                                <td>{{ $resdd['bandwidth_down'] . " " . $resdd['bandwidth_down_unit'] }} </td>
                                <td>{{ $resdd['description'] }} <input type="hidden" name="description" value="{{ $resdd['description']}}"></td>
                                <input type="hidden" name="locationname" value="{{ $locationname }}">
                                <td><input type="Submit" name="btn" value="Delete" class="btn btn-danger btn-sm"></td>

                                @php
                                    $findyes=1;
                                @endphp
                                @break
                                @endif
                                @php
                                    $findyes=0;
                                @endphp
                                @endforeach

                                @if($findyes == 0)
                                <td>{{ $resdd['id']}}<input type="hidden" name="rid" value="{{ $resdd['id']}}"></td>
                                <td>{{ $resdd['name'] }} <input type="hidden" name="name" value="{{ $resdd['name']}}"></td>
                                <td>{{ $resdd['valid_for'] . " " . $resdd['validity_unit'] }} </td>
                                <td>{{ $resdd['bandwidth_up'] . " " . $resdd['bandwidth_up_unit'] }} </td>
                                <td>{{ $resdd['bandwidth_down'] . " " . $resdd['bandwidth_down_unit'] }} </td>
                                <td>{{ $resdd['description'] }} <input type="hidden" name="description" value="{{ $resdd['description']}}"></td>
                                <input type="hidden" name="locationname" value="{{ $locationname }}">
                                <td><input type="Submit" name="btn" value="Insert" class="btn btn-primary btn-sm"></td>
                                @endif

                            </tr>
                        </form>
                        @endforeach

                    </table>
                @endif
        <!-- /Locations -->
                </div>
            </div>
      </div>
</div>
@endsection
