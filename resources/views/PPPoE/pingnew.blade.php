@php
    if(isset($iid)){$iid;}else{$iid="";}
    if(isset($iiip)){$iiip;}else{$iiip="";}
    if(isset($sname)){$sname;}else{$sname="";}
    if(isset($subscriber)){$subscriber;}else{$subscriber="";}
    if(isset($time)){$time;}else{$time=5;}

    $time1=1;

@endphp


@extends('admin.layouts.header')

<x-assets.datatables />

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Ping Result</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Ping Result</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">

		<!-- pingResult -->
		<div>
            @if (session('msg'))
            <label class="badge badge-success"> {{ session('msg') }}</lable>
            @endif

            <div>Server Name : <b>{{ $sname }}</b></div>
            <div>IP : <b>{{ $iiip }}</b></div>
            <div>UserName : <b>{{ $subscriber }}</b></div>
            <div>Select Time
                <form name="ping" action="{{ route('pppoe.ping') }}" class="form-sample">
                    <input type="hidden" name="ip" value="{{ $iiip }}">
                    <input type="hidden" name="server" value="{{ $iid }}">
                    <input type="hidden" name="username" value="{{ $subscriber }}">
                    <Select onchange="this.form.submit()" class="form-control" name="time">

                        @php
                            for($i = 5; $i<=25; $i+=5) {

                                if($time==$i){
                                    echo "<option value=" . $i ." selected>". $i . "</option>";
                                }else{
                                    echo "<option value=" . $i .">". $i . "</option>";
                                }

                            }
                        @endphp
                    </Select>
                </form>
            </div>
        </div>
          {{-- <table class="table-striped">
            <tr><th>Sr. No. </th><th>Result</th></tr>
            @foreach ($PING as $resdd )
            <tr>
                <td>{{ $loop->iteration }}</td>

                @if ($resdd['packet-loss']==0)
                    <td>{{ $resdd['time'] }}</td>
                @else
                    <td>Packet Loss</td>
                @endif


            </tr>
            @endforeach
          </table> --}}
		<!-- /pingResult -->
	</div>
</div>
<div class="container mt-2"> 
    <div class="table-responsive">
        <table id="ping-results" class="table table-striped table-bordered"> 
            <thead class="thead-dark"> 
                <tr> 
                    <th scope="col">SR NO</th> 
                    <th scope="col">Result</th> 
                </tr> 
            </thead> 
            <tbody> 
            </tbody>
        </table>
    </div>
</div>
<a href='javascript: history.back()' class="btn btn-primary btn-sm">Back</a>
    </div>
</div>
{{-- <script src="https://js.pusher.com/7.0/pusher.min.js"></script> 
<script src="{{ asset('js/app.js') }}"></script> 
<script src="http://192.168.1.66/microtik-radius/public{{ mix('js/app.js') }}" defer></script> --}}
<script> 
    console.log("Initializing EventSource");

    if (!!window.EventSource) { 
        const source = new EventSource("/microtik-radius/public/pppoe/real-time-ping?server={{ $iid }}&time={{ $time }}&ip={{ $iiip }}&username={{ $subscriber }}"); 
        let serialNumber = 1;

        source.onopen = function() { 
            console.log("EventSource connection established"); 
        };

        source.onmessage = function(event) { 
            console.log("Data received:", event.data);
            const data = JSON.parse(event.data); 
            // document.getElementById('ping-results').innerHTML = JSON.stringify(data, null, 2); 

            // Append new data to the ping-results table 
            const pingResultsTable = document.getElementById('ping-results').getElementsByTagName('tbody')[0]; 
            const newRow = pingResultsTable.insertRow(); 
            newRow.insertCell(0).textContent = serialNumber++;
            // newRow.insertCell(1).textContent = data[0].time; 
        
            const packetLossCell = newRow.insertCell(1); 
            if (data[0]['packet-loss'] === '0') { 
                
                packetLossCell.textContent = data[0].time;
            } else { 
                packetLossCell.innerHTML = '<strong class="text-danger">Packet Loss</strong>';
                // packetLossCell.textContent = 'Packet Loss'; 
                // packetLossCell.classList.add('text-danger'); 
            }
        }; 
        source.onerror = function(event) { 
            console.error("EventSource failed:", event);
            source.close();
        }; 
    } else { 
        console.error("Your browser doesn't support SSE"); 
    }
</script>
<script src="{{ mix('js/app.js') }}">
@endsection



