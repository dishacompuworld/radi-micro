<!-- filepath: /c:/inetpub/wwwroot/microtik-radius/resources/views/admin/microtik/logs.blade.php -->
@extends('admin.layouts.header')

@push('page-css')
<!-- Add any additional CSS here -->
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Microtik-Logs</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Microtik-Logs</li>
    </ul>
</div>
@endpush

@section('content')
<style>
    .table-sm th, .table-sm td {
        padding: 0.3rem; /* Adjust padding as needed */
        font-size: 0.875rem; /* Adjust font size as needed */
    }
</style>

<div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-body">
          <div class="form-group col-sm-3">
              {{-- <form action="{{ route('shedule.show')}}" class="form-sample" method="get" name="mtk"> --}}
                <div class="input-group input-group-sm mb-3">
                    <div class="input-group-prepend">
                      <label class="input-group-text" for="inputGroupSelect01">Select Server</label>
                    </div>
                    <select name="sserver" onchange="fetchLogs()" class="custom-select" id="server-select">
                          <option value=""></option>
                          @foreach ($servers as $server)
                          <option value="{{ $server->id }}" {{ $seletedserver == $server->id ? 'selected' : '' }}>
                              {{ $server->name }}
                          </option>
                      @endforeach
                    </select>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span for="auto-refresh">Auto-refresh&nbsp;&nbsp;</span>
                            <input type="checkbox" id="auto-refresh" onchange="toggleAutoRefresh()">
                        </div>                            
                    </div>
                  </div>
              {{-- </form> --}}
          </div>
        </div>
      </div>
    </div>
</div>

{{-- <div class="container"> --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div id="logs-container">
                        <div class="form-group">
                            <label for="record-count">Number of Records:</label>
                            <span id="record-count">0</span>
                        </div>
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Topics</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody id="logs-table-body">
                                <!-- Logs will be dynamically inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
{{-- </div> --}}

<script>
    let autoRefreshInterval;

    function fetchLogs() {
        const serverId = document.getElementById('server-select').value;
        if (!serverId) {
            document.getElementById('logs-table-body').innerHTML = '<tr><td colspan="3">Please select a server</td></tr>';
            document.getElementById('record-count').innerText = '0';
            return;
        }

        fetch(`logs?sserver=${serverId}`)
            .then(response => response.json())
            .then(data => {
                const logsTableBody = document.getElementById('logs-table-body');
                logsTableBody.innerHTML = ''; // Clear existing logs

                if (data.error) {
                    logsTableBody.innerHTML = `<tr><td colspan="3">${data.error}</td></tr>`;
                    document.getElementById('record-count').innerText = '0';
                } else {
                    let recordCount = 0;
                    data.forEach(log => {
                        const topics = log.topics.toLowerCase();
                        if (topics.includes('system') && topics.includes('info') && topics.includes('account')) {
                            return; // Ignore logs with all these topics
                        }

                        const isError = topics.includes('error');
                        const rowClass = isError ? 'text-danger' : '';

                        const row = document.createElement('tr');
                        row.className = rowClass;

                        row.innerHTML = `
                            <td>${log.time}</td>
                            <td>${log.topics}</td>
                            <td>${log.message}</td>
                        `;
                        logsTableBody.appendChild(row);
                        recordCount++;
                    });
                    document.getElementById('record-count').innerText = recordCount;
                }
            })
            .catch(error => {
                console.error('Error fetching logs:', error);
                document.getElementById('record-count').innerText = '0';
            });
    }

    function toggleAutoRefresh() {
        const autoRefresh = document.getElementById('auto-refresh').checked;
        if (autoRefresh) {
            autoRefreshInterval = setInterval(fetchLogs, 5000); // Fetch logs every 5 seconds
        } else {
            clearInterval(autoRefreshInterval);
        }
    }

// Initial fetch
fetchLogs();
</script>
@endsection