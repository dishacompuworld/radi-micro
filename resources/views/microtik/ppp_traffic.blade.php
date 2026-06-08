<!-- filepath: /c:/inetpub/wwwroot/microtik-radius/resources/views/admin/microtik/ppp_traffic.blade.php -->
@extends('admin.layouts.header')

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

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">PPP Interface Traffic</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">PPP Interface Traffic</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="form-group col-sm-3">
                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="inputGroupSelect01">Select Server</label>
                        </div>
                        <select name="sserver" onchange="fetchPppTraffic()" class="custom-select" id="server-select">
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
                <div id="ppp-traffic-container">
                    <!-- PPP interface traffic information will be dynamically inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include jQuery and Chart.js -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let chart;
    let chartData = {
        labels: [],
        datasets: [
            {
                label: 'RX Mbps',
                data: [],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                tension: 0.4 // Add tension for smooth curves
            },
            {
                label: 'TX Mbps',
                data: [],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                tension: 0.4 // Add tension for smooth curves
            }
        ]
    };
    let updateInterval;

    function fetchPppTraffic() {
        const serverId = document.getElementById('server-select').value;
        if (!serverId) {
            document.getElementById('ppp-traffic-container').innerHTML = '<p>Please select a server</p>';
            return;
        }

        fetch(`ppp-traffic?sserver=${serverId}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('ppp-traffic-container');
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
                    const keys = Object.keys(data[0]);
                    keys.forEach(key => {
                        const th = document.createElement('th');
                        th.textContent = key;
                        headerRow.appendChild(th);
                    });
                    thead.appendChild(headerRow);

                    // Filter and create table rows
                    data.filter(item => item.name.startsWith('<pppoe-')).forEach(item => {
                        const row = document.createElement('tr');
                        keys.forEach(key => {
                            const cell = document.createElement('td');
                            if (key === 'name') {
                                const link = document.createElement('a');
                                link.href = '#';
                                const interfaceName = item[key].replace('<pppoe-', '').replace('>', '');
                                link.textContent = interfaceName;
                                link.onclick = (event) => {
                                    event.preventDefault();
                                    showLiveTraffic(interfaceName);
                                };
                                cell.appendChild(link);
                            } else {
                                cell.textContent = item[key] || ''; // Handle missing keys
                            }
                            row.appendChild(cell);
                        });
                        tbody.appendChild(row);
                    });

                    table.appendChild(thead);
                    table.appendChild(tbody);
                    container.appendChild(table);
                }
            })
            .catch(error => console.error('Error fetching PPP interface traffic:', error));
    }

    function showLiveTraffic(interfaceName) {
        const serverId = document.getElementById('server-select').value;
        if (!serverId) {
            alert('Please select a server');
            return;
        }

        const container = document.getElementById('ppp-traffic-container');
        container.innerHTML = `
            <h4>Username: ${interfaceName}</h4>
            <canvas id="traffic-chart"></canvas>
            <button class="btn btn-sm btn-primary mt-3" onclick="stopLiveTraffic()">Back</button>
        `;

        const ctx = document.getElementById('traffic-chart').getContext('2d');
        if (chart) {
            chart.destroy();
        }
        chart = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function updateChart() {
            fetch(`ppp-traffic?sserver=${serverId}&interface=<pppoe-${interfaceName}>`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    const timestamp = new Date().toLocaleTimeString();
                    chartData.labels.push(timestamp);
                    chartData.datasets[0].data.push(data['rx-mbps']);
                    chartData.datasets[1].data.push(data['tx-mbps']);

                    if (chartData.labels.length > 10) {
                        chartData.labels.shift();
                        chartData.datasets[0].data.shift();
                        chartData.datasets[1].data.shift();
                    }

                    chart.update();
                })
                .catch(error => console.error('Error fetching live traffic data:', error));
        }

        updateChart();
        updateInterval = setInterval(updateChart, 5000); // Update every 5 seconds
    }

    function stopLiveTraffic() {
        clearInterval(updateInterval);
        fetchPppTraffic();
    }

    // Initial fetch
    fetchPppTraffic();
</script>
@endsection