@extends('layouts.admin')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">History</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Microtik</li>
                        <li class="breadcrumb-item active">History</li>
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
                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="inputGroupSelect01">Select Server</label>
                        </div>
                        <select name="sserver" onchange="fetchHistory()" class="custom-select" id="server-select">
                            <option value=""></option>
                            @foreach ($servers as $server)
                                <option value="{{ $server->id }}" {{ $seletedserver == $server->id ? 'selected' : '' }}>
                                    {{ $server->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div id="history-container">
                    <!-- IP neighbors information will be dynamically inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function fetchHistory() {
        const serverId = document.getElementById('server-select').value;
        if (!serverId) {
            document.getElementById('history-container').innerHTML = '<p>Please select a server</p>';
            return;
        }

        fetch(`systemhistory?sserver=${serverId}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('history-container');
                container.innerHTML = ''; // Clear existing data

                if (data.error) {
                    container.innerHTML = `<p>${data.error}</p>`;
                } else {
                    const table = document.createElement('table');
                    table.className = 'table table-bordered table-sm';
                    const thead = document.createElement('thead');
                    const tbody = document.createElement('tbody');

                    // Create table headers
                    const headerRow = document.createElement('tr');
                    const headerKeys = ['Sr No', 'Action', 'Time'];
                    headerKeys.forEach(key => {
                        const th = document.createElement('th');
                        th.textContent = key;
                        headerRow.appendChild(th);
                    });
                    thead.appendChild(headerRow);

                    // Create table rows
                    data.forEach((item, index) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${item.action}</td>
                            <td>${new Date(item.time).toLocaleString('en-US', { weekday: 'short', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true, day: 'numeric' })}</td>`;
                        tbody.appendChild(row);
                    });

                    table.appendChild(thead);
                    table.appendChild(tbody);
                    container.appendChild(table);
                }
            })
            .catch(error => console.error('Error fetching IP neighbors:', error));
    }

    // Initial fetch
    fetchIpNeighbors();
</script>

</div>
@endsection