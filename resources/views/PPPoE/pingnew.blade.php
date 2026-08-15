@php
    if(isset($iid)){$iid;}else{$iid="";}
    if(isset($iiip)){$iiip;}else{$iiip="";}
    if(isset($sname)){$sname;}else{$sname="";}
    if(isset($subscriber)){$subscriber;}else{$subscriber="";}
    if(isset($time)){$time;}else{$time=5;}

    $time1=1;

@endphp

@extends('layouts.admin')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Ping Result</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active">Ping Result</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

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
          <table class="table table-striped table-bordered mt-3">
            <thead class="thead-dark">
                <tr><th>Sr. No.</th><th>Result</th></tr>
            </thead>
            <tbody>
            @if (isset($PING) && is_array($PING) && count($PING) > 0)
                @foreach ($PING as $resdd)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    @php
                        $packetLoss = $resdd['packet-loss'] ?? $resdd['packet_loss'] ?? $resdd['packetloss'] ?? null;
                        $pingTime = $resdd['time'] ?? null;
                        $resultText = $pingTime ?: 'No response';

                        if (is_string($resultText) && preg_match('/^\d+(?:\.\d+)?\s*ms$/i', trim($resultText))) {
                            $resultText = trim($resultText);
                        } elseif (is_numeric((string) $resultText)) {
                            $resultText = (string) $resultText . ' ms';
                        }
                    @endphp
                    @if ($pingTime !== null && $pingTime !== '')
                        <td>{{ $resultText }} ms</td>
                    @elseif ($packetLoss === null || $packetLoss === '' || (is_numeric((string) $packetLoss) && (float) $packetLoss === 0))
                        <td>{{ $resultText }}</td>
                    @else
                        <td><strong class="text-danger">Packet Loss</strong></td>
                    @endif
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="2" class="text-center text-muted">No ping result returned.</td>
                </tr>
            @endif
            </tbody>
          </table>
		<!-- /pingResult -->
	</div>
</div>
<div class="mt-3 mb-3">
    <a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
</div>
    </div>
</div>
{{-- <script src="https://js.pusher.com/7.0/pusher.min.js"></script> 
<script src="{{ asset('js/app.js') }}"></script> 
<script src="http://192.168.1.66/microtik-radius/public{{ mix('js/app.js') }}" defer></script> --}}
<script>
    console.log("Initializing EventSource");

    if (!!window.EventSource) {
        const sourceUrl = "{{ url('pppoe/real-time-ping') }}?server={{ urlencode((string) $iid) }}&time={{ urlencode((string) $time) }}&ip={{ urlencode((string) $iiip) }}&username={{ urlencode((string) $subscriber) }}";
        const source = new EventSource(sourceUrl);
        let serialNumber = 1;

        source.onopen = function() {
            console.log("EventSource connection established");
        };

        source.onmessage = function(event) {
            console.log("Data received:", event.data);

            let parsed = null;
            try {
                parsed = JSON.parse(event.data);
            } catch (error) {
                console.warn('Non-JSON ping payload:', event.data);
                parsed = { raw: event.data };
            }

            const data = Array.isArray(parsed) ? parsed[0] : parsed;
            const row = data && typeof data === 'object' ? data : { raw: parsed };
            const packetLoss = row['packet-loss'] ?? row.packet_loss ?? row.packetloss ?? null;
            const pingTime = row.time ?? row['time'] ?? null;

            const pingResultsTable = document.getElementById('ping-results').getElementsByTagName('tbody')[0];
            const newRow = pingResultsTable.insertRow();
            newRow.insertCell(0).textContent = serialNumber++;

            const packetLossCell = newRow.insertCell(1);
            const packetValue = String(packetLoss ?? '').replace('%', '').trim();

            if (pingTime !== null && pingTime !== undefined && pingTime !== '') {
                const pingText = typeof pingTime === 'string' && pingTime.toLowerCase().includes('ms')
                    ? pingTime
                    : (String(pingTime).trim() + ' ms');
                packetLossCell.textContent = pingText;
            } else if (packetLoss === null || packetLoss === undefined || packetLoss === '') {
                packetLossCell.textContent = 'No response';
            } else if (packetValue === '0' || Number(packetValue) === 0) {
                packetLossCell.textContent = '0 ms';
            } else if (typeof packetLoss === 'string' && packetLoss.toLowerCase().includes('loss')) {
                packetLossCell.innerHTML = '<strong class="text-danger">Packet Loss</strong>';
            } else {
                packetLossCell.textContent = String(packetLoss) + ' %';
            }
        };

        source.onerror = function(event) {
            console.error("EventSource failed:", event);
            console.log("Keeping the stream open so browser can retry automatically.");
        };
    } else {
        console.error("Your browser doesn't support SSE");
    }
</script>
{{-- <script src="{{ mix('js/app.js') }}"> --}}


</div>
@endsection