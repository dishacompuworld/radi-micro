@php
    if(isset($iid)){$iid;}else{$iid="";}
    if(isset($iiip)){$iiip;}else{$iiip="";}
    if(isset($sname)){$sname;}else{$sname="";}
    if(isset($subscriber)){$subscriber;}else{$subscriber="";}
    if(isset($username)){$username;}else{$username="";}
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
          <table class="table-striped">
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
          </table>
		<!-- /pingResult -->
	</div>
</div>
<div id="ping-results"></div>
<a href='javascript: history.back()' class="btn btn-primary btn-sm">Back</a>
    </div>
</div>
{{-- <script src="https://js.pusher.com/7.0/pusher.min.js"></script> 
<script src="{{ asset('js/app.js') }}"></script> 
<script src="http://192.168.1.66/microtik-radius/public{{ mix('js/app.js') }}" defer></script> --}}
<script>
    console.log("Initializing EventSource");

    if (!!window.EventSource) {
        const sourceUrl = "{{ url('pppoe/real-time-ping') }}?server={{ urlencode((string) $iid) }}&time={{ urlencode((string) $time1) }}&ip={{ urlencode((string) $iiip) }}&username={{ urlencode((string) $subscriber) }}";
        const source = new EventSource(sourceUrl);

        source.onopen = function() {
            console.log("EventSource connection established");
        };

        source.onmessage = function(event) {
            console.log("Data received:", event.data);

            try {
                const data = JSON.parse(event.data);
                const row = Array.isArray(data) ? data[0] : data;
                const packetLoss = row && (row['packet-loss'] ?? row.packet_loss ?? row.packetloss ?? null);
                const pingTime = row && (row.time ?? row['time'] ?? 'No data');

                const pingResultsDiv = document.getElementById('ping-results');
                const newData = document.createElement('div');
                newData.textContent = Number(packetLoss) === 0 || String(packetLoss).trim() === '0'
                    ? 'Ping OK: ' + pingTime
                    : 'Packet Loss';
                pingResultsDiv.appendChild(newData);
            } catch (error) {
                console.error('Failed to parse ping payload:', error);
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
{{-- <script src="http://192.168.1.66/microtik-radius/public{{ mix('js/app.js') }}"> --}}
@endsection



