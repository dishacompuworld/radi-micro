@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">System Health</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">Microtik</li>
                    <li class="breadcrumb-item active">System Health</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6 col-lg-4">
                            <label for="server-select" class="form-label mb-1">Select Server</label>
                            <select id="server-select" name="sserver" class="form-select" onchange="fetchSystemHealth()">
                                <option value="">-- Select Server --</option>
                                @foreach ($servers as $server)
                                    <option value="{{ $server->id }}" @selected($seletedserver == $server->id)>
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

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="system-health-container">
                        <div class="text-muted">Please select a server to view system health.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function fetchSystemHealth() {
        const serverId = document.getElementById('server-select').value;
        const container = document.getElementById('system-health-container');

        if (!serverId) {
            container.innerHTML = '<div class="text-muted">Please select a server to view system health.</div>';
            return;
        }

        container.innerHTML = '<div class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>';

        fetch(`system-health?sserver=${serverId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    container.innerHTML = `<div class="alert alert-danger mb-0">${data.error}</div>`;
                    return;
                }

                if (!Array.isArray(data) || !data.length) {
                    container.innerHTML = '<div class="alert alert-warning mb-0">No system health data returned.</div>';
                    return;
                }

                const fields = ['.id', 'name', 'value', 'type'];
                const headers = ['ID', 'Name', 'Value', 'Unit'];

                const rows = data.map(item => {
                    const id = item['.id'] ?? item.id ?? '';
                    const name = item.name ?? item['name'] ?? '';
                    const value = item.value ?? item['value'] ?? '';
                    const type = item.type ?? item['type'] ?? '';

                    return `
                        <tr>
                            <td>${id}</td>
                            <td>${name}</td>
                            <td>${value}</td>
                            <td>${type}</td>
                        </tr>
                    `;
                }).join('');

                container.innerHTML = `
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    ${headers.map(header => `<th>${header}</th>`).join('')}
                                </tr>
                            </thead>
                            <tbody>
                                ${rows}
                            </tbody>
                        </table>
                    </div>
                `;
            })
            .catch(() => {
                container.innerHTML = '<div class="alert alert-danger mb-0">Unable to load system health data.</div>';
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('server-select').value) {
            fetchSystemHealth();
        }
    });
</script>
@endsection