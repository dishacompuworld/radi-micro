@extends('layouts.admin')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Live Server Traffic</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Microtik</li>
                        <li class="breadcrumb-item active">Live Server Traffic</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-end gap-3 mb-3">
                        <div style="min-width: 240px;">
                            <label class="form-label">MikroTik Server</label>
                            <input type="text" class="form-control" value="{{ $server ? ($server->name ?? $server->mip) : 'No server configured' }}" readonly>
                        </div>
                        <div style="min-width: 260px;">
                            <label class="form-label">Monitored Interfaces</label>
                            <input type="text" class="form-control" id="interface-name" value="{{ env('MICROTIK_INTERFACE1') }}{{ env('MICROTIK_INTERFACE2') ? ', ' . env('MICROTIK_INTERFACE2') : '' }}" readonly>
                        </div>
                        <div style="min-width: 220px;">
                            <label class="form-label">Traffic Status</label>
                            <div id="traffic-status" class="form-control text-muted">Waiting for login...</div>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-secondary" id="refresh-button" type="button">Reconnect</button>
                        </div>
                    </div>
                    <div class="form-text">Traffic is fetched from the interfaces defined by <code>MICROTIK_INTERFACE1</code> and <code>MICROTIK_INTERFACE2</code>.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="chart-container" style="position: relative; min-height: 420px; width: 100%; height: 420px;">
                        <canvas id="traffic-chart" style="display: block; width: 100%; height: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const loginUrl = '{{ route('microtik.log.login') }}';
    const trafficDataUrl = '{{ route('microtik.live.traffic.data') }}';

    const interfaceName = document.getElementById('interface-name').value;
    const trafficStatus = document.getElementById('traffic-status');
    const refreshButton = document.getElementById('refresh-button');

    let trafficChart;
    let pollInterval;
    let isAuthenticated = false;

    const interfaceLabels = {
        '{{ env('MICROTIK_INTERFACE1') }}': 'JPR',
        '{{ env('MICROTIK_INTERFACE2') }}': 'SoftCall'
    };

    const interfaceKeys = interfaceName
        .split(',')
        .map((name) => name.trim())
        .filter(Boolean);

    const chartColors = [
        { borderColor: 'rgba(0, 128, 0, 1)', backgroundColor: 'rgba(0, 128, 0, 0)' },
        { borderColor: 'rgba(255, 0, 0, 1)', backgroundColor: 'rgba(255, 0, 0, 0)' },
        { borderColor: 'rgba(128, 128, 128, 1)', backgroundColor: 'rgba(128, 128, 128, 0.2)' }
    ];

    const chartConfig = {
        type: 'line',
        data: {
            labels: [],
            datasets: []
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Mbps'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Time'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Live Traffic from Main Server'
                }
            }
        }
    };

    function buildChart() {
        const ctx = document.getElementById('traffic-chart').getContext('2d');
        if (trafficChart) {
            trafficChart.destroy();
        }
        trafficChart = new Chart(ctx, chartConfig);
    }

    function updateStatus(message, isError = false) {
        trafficStatus.textContent = message;
        trafficStatus.className = isError ? 'text-danger' : 'text-muted';
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    function initializeDatasets() {
        chartConfig.data.datasets = interfaceKeys.map((interfaceName, index) => {
            const color = chartColors[index % chartColors.length];
            return {
                label: interfaceLabels[interfaceName] || interfaceName,
                data: [],
                borderColor: color.borderColor,
                backgroundColor: color.backgroundColor,
                tension: 0.35,
                fill: false,
                borderWidth: index === 0 ? 1 : 1,
            };
        });

        chartConfig.data.datasets.push({
            label: 'Total',
            data: [],
            borderColor: chartColors[2].borderColor,
            backgroundColor: chartColors[2].backgroundColor,
            tension: 0.35,
            fill: true,
            borderWidth: 2,
        });
    }

    async function loginToMikroTik() {
        if (isAuthenticated) {
            return true;
        }

        updateStatus('Logging into MikroTik...', false);

        const response = await fetch(loginUrl, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({})
        });

        if (!response.ok) {
            const text = await response.text();
            updateStatus(`Login failed: ${response.status} ${response.statusText}`, true);
            console.error('Login request failed', response.status, response.statusText, text);
            return false;
        }

        const data = await response.json();
        if (data.success) {
            updateStatus('Logged in. Fetching live traffic...', false);
            isAuthenticated = true;
            return true;
        }

        updateStatus(data.message || 'Login failed.', true);
        return false;
    }

    function addTrafficPoint(data) {
        const timestamp = new Date().toLocaleTimeString();
        if (chartConfig.data.labels.length >= 20) {
            chartConfig.data.labels.shift();
            chartConfig.data.datasets.forEach((dataset) => dataset.data.shift());
        }

        chartConfig.data.labels.push(timestamp);

        interfaceKeys.forEach((interfaceName, index) => {
            const metric = data.metrics && data.metrics[interfaceName];
            chartConfig.data.datasets[index].data.push(Number(metric?.['total-bps'] || 0) / 1000 / 1000);
        });

        const totalDataset = chartConfig.data.datasets[chartConfig.data.datasets.length - 1];
        totalDataset.data.push(Number(data['total-mbps']) || 0);
        trafficChart.update();
    }

    async function fetchTraffic() {
        try {
            console.debug('Fetching traffic data');
            const response = await fetch(trafficDataUrl, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-cache'
            });

            if (!response.ok) {
                const text = await response.text();
                updateStatus(`Traffic request failed: ${response.status} ${response.statusText}`, true);
                console.error('Traffic request failed', response.status, response.statusText, text);
                stopPolling();
                return;
            }

            const data = await response.json();
            if (data.error) {
                updateStatus(data.error, true);
                stopPolling();
                return;
            }

            addTrafficPoint(data);
        } catch (error) {
            updateStatus('Traffic fetch failed', true);
            console.error('Traffic fetch exception', error);
            stopPolling();
        }
    }

    async function startTrafficMonitoring() {
        stopPolling();

        if (!await loginToMikroTik()) {
            return;
        }

        console.debug('Traffic monitoring started');
        buildChart();
        await fetchTraffic();
        pollInterval = setInterval(fetchTraffic, 5000);
    }

    refreshButton.addEventListener('click', async () => {
        stopPolling();
        await startTrafficMonitoring();
    });

    window.addEventListener('beforeunload', () => {
        stopPolling();
    });

    window.addEventListener('pagehide', () => {
        stopPolling();
    });

    document.addEventListener('DOMContentLoaded', async () => {
        initializeDatasets();
        buildChart();
        await startTrafficMonitoring();
    });
</script>

@endsection