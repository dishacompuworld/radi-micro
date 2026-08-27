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
    function formatHistoryTime(value) {
        if (!value) {
            return 'Not available';
        }

        const parsedDate = new Date(value);
        if (!Number.isNaN(parsedDate.getTime())) {
            return parsedDate.toLocaleString('en-US', {
                weekday: 'short',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
                day: 'numeric'
            });
        }

        const routerOsDate = String(value).match(/^(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\/(\d{1,2})\/(\d{2,4})\s+(\d{1,2}):(\d{2})(?::(\d{2}))?/i);
        if (routerOsDate) {
            const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
            const month = months.indexOf(routerOsDate[1].toLowerCase());
            const year = Number(routerOsDate[3]) < 100 ? 2000 + Number(routerOsDate[3]) : Number(routerOsDate[3]);
            const date = new Date(
                year,
                month,
                Number(routerOsDate[2]),
                Number(routerOsDate[4]),
                Number(routerOsDate[5]),
                Number(routerOsDate[6] || 0)
            );

            return date.toLocaleString('en-US', {
                weekday: 'short',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
                day: 'numeric'
            });
        }

        return String(value);
    }

    function getHistoryValue(item, keys) {
        for (const key of keys) {
            if (item[key] !== undefined && item[key] !== null && item[key] !== '') {
                return item[key];
            }
        }

        const normalizedKeys = keys.map(key => key.replace(/^=/, '').toLowerCase());
        for (const [key, value] of Object.entries(item)) {
            if (normalizedKeys.includes(key.replace(/^=/, '').toLowerCase()) && value !== null && value !== '') {
                return value;
            }
        }

        if (keys.includes('time')) {
            for (const [key, value] of Object.entries(item)) {
                if (/(time|date|when|created)/i.test(key) && value !== null && value !== '') {
                    return value;
                }
            }

            for (const value of Object.values(item)) {
                if (typeof value === 'string' && /^(?:\d{4}-\d{2}-\d{2}|(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\/\d{1,2}\/\d{2,4})/i.test(value)) {
                    return value;
                }
            }
        }

        return '';
    }

    function fetchHistory() {
        const serverId = document.getElementById('server-select').value;
        if (!serverId) {
            document.getElementById('history-container').innerHTML = '<p>Please select a server</p>';
            return;
        }

        fetch(`systemhistory?sserver=${serverId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Unable to load history (${response.status})`);
                }
                return response.json();
            })
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
                        const action = getHistoryValue(item, ['action', '=action', 'command', 'cmd', 'message', 'description', 'name', 'type', 'redo', 'undo']) || 'Not available';
                        const time = getHistoryValue(item, ['time', '=time', 'date', 'timestamp', 'when']);
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${action}</td>
                            <td>${formatHistoryTime(time)}</td>`;
                        tbody.appendChild(row);
                    });

                    table.appendChild(thead);
                    table.appendChild(tbody);
                    container.appendChild(table);
                }
            })
            .catch(error => {
                document.getElementById('history-container').innerHTML = `<p>${error.message}</p>`;
                console.error('Error fetching system history:', error);
            });
    }

    fetchHistory();
</script>

</div>
@endsection