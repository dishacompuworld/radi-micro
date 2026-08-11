@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Settings</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Application Settings</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="app_name" class="form-label">App Name</label>
                            <input type="text" class="form-control @error('app_name') is-invalid @enderror" id="app_name" name="app_name" value="{{ old('app_name', $appName) }}" required>
                            @error('app_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="olt_ip" class="form-label">OLT Telnet IP / Host</label>
                            <input type="text" class="form-control @error('olt_ip') is-invalid @enderror" id="olt_ip" name="olt_ip" value="{{ old('olt_ip', $oltIp) }}" placeholder="e.g. 192.168.1.100">
                            @error('olt_ip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="olt_telnet_username" class="form-label">OLT Telnet Username</label>
                            <input type="text" class="form-control @error('olt_telnet_username') is-invalid @enderror" id="olt_telnet_username" name="olt_telnet_username" value="{{ old('olt_telnet_username', $oltTelnetUsername) }}" placeholder="rajesh">
                            @error('olt_telnet_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="olt_telnet_password" class="form-label">OLT Telnet Password</label>
                            <input type="password" class="form-control @error('olt_telnet_password') is-invalid @enderror" id="olt_telnet_password" name="olt_telnet_password" placeholder="Enter password to save or leave blank to keep existing">
                            @error('olt_telnet_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave blank to keep the existing password.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">OLT SNMP Control</h5>
                </div>
                <div class="card-body">
                    <p>Add your OLT IP and credentials, then use the toggle below to enable or disable SNMP on the device.</p>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="snmp-enabled" {{ $snmpEnabled === '1' ? 'checked' : '' }} onchange="toggleSnmp(this.checked)">
                        <label class="form-check-label" for="snmp-enabled">SNMP Service Enabled</label>
                    </div>
                    <div id="snmp-feedback"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    function toggleSnmp(enabled) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const feedback = document.getElementById('snmp-feedback');
        feedback.innerHTML = '<div class="alert alert-info">Updating SNMP status...</div>';

        fetch('{{ route('settings.snmp.toggle') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ olt_snmp_enabled: enabled ? 1 : 0 })
        })
        .then(response => response.json())
        .then(data => {
            const type = data.type === 'success' ? 'success' : 'danger';
            feedback.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' + data.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            if (type === 'danger') {
                document.getElementById('snmp-enabled').checked = !enabled;
            }
        })
        .catch(error => {
            document.getElementById('snmp-enabled').checked = !enabled;
            feedback.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Unable to update SNMP status. Please try again.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            console.error(error);
        });
    }
</script>
@endpush
