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

    <div class="card settings-tabs-card shadow-sm border-0">
        <div class="card-header settings-tabs-header p-0 border-0">
            <ul class="nav nav-tabs settings-tabs flex-wrap w-100" role="tablist">
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#settings-main" aria-selected="true">Main</button>
                </li>
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#settings-olt" aria-selected="false">OLT</button>
                </li>
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#settings-radius" aria-selected="false">Radius</button>
                </li>
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#settings-prtg" aria-selected="false">PRTG</button>
                </li>
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#settings-whatsapp" aria-selected="false">WhatsApp</button>
                </li>
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#settings-mac" aria-selected="false">Find MAC</button>
                </li>
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#settings-mikrotik" aria-selected="false">MikroTik</button>
                </li>
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#settings-mail" aria-selected="false">Mail</button>
                </li>
            </ul>
        </div>

        <div class="card-body pt-4">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="settings-main" role="tabpanel">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="app_name" class="form-label">App Name</label>
                                <input type="text" class="form-control @error('app_name') is-invalid @enderror" id="app_name" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" required>
                                @error('app_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Main</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="settings-olt" role="tabpanel">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="card border-0 shadow-none mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center px-0 pb-2">
                                <h5 class="card-title mb-0">OLT Settings</h5>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="olt_snmp_enabled" name="olt_snmp_enabled" value="1" {{ old('olt_snmp_enabled', $settings['olt_snmp_enabled']) === '1' ? 'checked' : '' }} onchange="toggleSnmp(this.checked)">
                                    <label class="form-check-label" for="olt_snmp_enabled">SNMP on/off</label>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4"><label for="olt_ip" class="form-label">IP</label><input type="text" class="form-control" id="olt_ip" name="olt_ip" value="{{ old('olt_ip', $settings['olt_ip']) }}"></div>
                            <div class="col-md-4"><label for="olt_telnet_username" class="form-label">User</label><input type="text" class="form-control" id="olt_telnet_username" name="olt_telnet_username" value="{{ old('olt_telnet_username', $settings['olt_telnet_username']) }}"></div>
                            <div class="col-md-4"><label for="olt_telnet_password" class="form-label">Password</label><input type="password" class="form-control" id="olt_telnet_password" name="olt_telnet_password" value="" placeholder="Leave blank to keep current password"></div>
                            <div class="col-md-4"><label for="snmp_oid_names" class="form-label">SNMP_OID_NAMES</label><input type="text" class="form-control" id="snmp_oid_names" name="snmp_oid_names" value="{{ old('snmp_oid_names', $settings['snmp_oid_names']) }}"></div>
                            <div class="col-md-4"><label for="snmp_oid_powers" class="form-label">SNMP_OID_POWERS</label><input type="text" class="form-control" id="snmp_oid_powers" name="snmp_oid_powers" value="{{ old('snmp_oid_powers', $settings['snmp_oid_powers']) }}"></div>
                            <div class="col-md-4"><label for="snmp_oid_powers_tr" class="form-label">SNMP_OID_POWERS_TR</label><input type="text" class="form-control" id="snmp_oid_powers_tr" name="snmp_oid_powers_tr" value="{{ old('snmp_oid_powers_tr', $settings['snmp_oid_powers_tr']) }}"></div>
                            <div class="col-md-4"><label for="min_ont_power" class="form-label">MIN_ONT_POWER</label><input type="text" class="form-control" id="min_ont_power" name="min_ont_power" value="{{ old('min_ont_power', $settings['min_ont_power']) }}"></div>
                            <div class="col-md-4"><label for="snmp_oid_uptime" class="form-label">SNMP_OID_UPTIME</label><input type="text" class="form-control" id="snmp_oid_uptime" name="snmp_oid_uptime" value="{{ old('snmp_oid_uptime', $settings['snmp_oid_uptime']) }}"></div>
                            <div class="col-md-4"><label for="snmp_oid_brand" class="form-label">SNMP_OID_BRAND</label><input type="text" class="form-control" id="snmp_oid_brand" name="snmp_oid_brand" value="{{ old('snmp_oid_brand', $settings['snmp_oid_brand']) }}"></div>
                            <div class="col-md-4"><label for="snmp_oid_temp" class="form-label">SNMP_OID_TEMP</label><input type="text" class="form-control" id="snmp_oid_temp" name="snmp_oid_temp" value="{{ old('snmp_oid_temp', $settings['snmp_oid_temp']) }}"></div>
                            <div class="col-md-4"><label for="snmp_oid_eth" class="form-label">SNMP_OID_ETH</label><input type="text" class="form-control" id="snmp_oid_eth" name="snmp_oid_eth" value="{{ old('snmp_oid_eth', $settings['snmp_oid_eth']) }}"></div>
                            <div class="col-md-4"><label for="snmp_oid_model" class="form-label">SNMP_OID_MODEL</label><input type="text" class="form-control" id="snmp_oid_model" name="snmp_oid_model" value="{{ old('snmp_oid_model', $settings['snmp_oid_model']) }}"></div>
                            <div class="col-md-4"><label for="snmp_oid_dist" class="form-label">SNMP_OID_DIST</label><input type="text" class="form-control" id="snmp_oid_dist" name="snmp_oid_dist" value="{{ old('snmp_oid_dist', $settings['snmp_oid_dist']) }}"></div>
                            <div class="col-md-4"><label for="snmp_oid_regist" class="form-label">SNMP_OID_REGIST</label><input type="text" class="form-control" id="snmp_oid_regist" name="snmp_oid_regist" value="{{ old('snmp_oid_regist', $settings['snmp_oid_regist']) }}"></div>
                            <div class="col-md-4"><label for="snmp_oid_status" class="form-label">SNMP_OID_STATUS</label><input type="text" class="form-control" id="snmp_oid_status" name="snmp_oid_status" value="{{ old('snmp_oid_status', $settings['snmp_oid_status']) }}"></div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save OLT</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="settings-radius" role="tabpanel">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6"><label for="radius_login" class="form-label">Xceednet Login</label><input type="text" class="form-control" id="radius_login" name="radius_login" value="{{ old('radius_login', $settings['radius_login']) }}"></div>
                            <div class="col-md-6"><label for="radius_password" class="form-label">Xceednet Password</label><input type="password" class="form-control" id="radius_password" name="radius_password" value="" placeholder="Leave blank to keep current password"></div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Radius</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="settings-prtg" role="tabpanel">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4"><label for="prtg_url" class="form-label">PRTG_URL</label><input type="text" class="form-control" id="prtg_url" name="prtg_url" value="{{ old('prtg_url', $settings['prtg_url']) }}"></div>
                            <div class="col-md-4"><label for="prtg_api_key" class="form-label">PRTG_API_KEY</label><input type="text" class="form-control" id="prtg_api_key" name="prtg_api_key" value="{{ old('prtg_api_key', $settings['prtg_api_key']) }}"></div>
                            <div class="col-md-4"><label for="prtg_all_traffic_graph_id" class="form-label">PRTG_ALL_TRAFFIC_GRAPH_ID</label><input type="text" class="form-control" id="prtg_all_traffic_graph_id" name="prtg_all_traffic_graph_id" value="{{ old('prtg_all_traffic_graph_id', $settings['prtg_all_traffic_graph_id']) }}"></div>
                            <div class="col-md-4"><label for="prtg_main_prob_id" class="form-label">PRTG_MAIN_PROB_ID</label><input type="text" class="form-control" id="prtg_main_prob_id" name="prtg_main_prob_id" value="{{ old('prtg_main_prob_id', $settings['prtg_main_prob_id']) }}"></div>
                            <div class="col-md-4"><label for="prtg_mseb" class="form-label">PRTG_MSEB</label><input type="text" class="form-control" id="prtg_mseb" name="prtg_mseb" value="{{ old('prtg_mseb', $settings['prtg_mseb']) }}"></div>
                            <div class="col-md-4"><label for="prtg_temp" class="form-label">PRTG_TEMP</label><input type="text" class="form-control" id="prtg_temp" name="prtg_temp" value="{{ old('prtg_temp', $settings['prtg_temp']) }}"></div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save PRTG</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="settings-whatsapp" role="tabpanel">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3"><label for="whats_app_url" class="form-label">WHATS_APP_URL</label><input type="text" class="form-control" id="whats_app_url" name="whats_app_url" value="{{ old('whats_app_url', $settings['whats_app_url']) }}"></div>
                            <div class="col-md-3"><label for="whats_app_token" class="form-label">WHATS_APPTOKEN</label><input type="password" class="form-control" id="whats_app_token" name="whats_app_token" value="" placeholder="Leave blank to keep current token"></div>
                            <div class="col-md-3"><label for="whatsapp_instance" class="form-label">WHATSAPP_INSTANCE</label><input type="text" class="form-control" id="whatsapp_instance" name="whatsapp_instance" value="{{ old('whatsapp_instance', $settings['whatsapp_instance']) }}"></div>
                            <div class="col-md-3"><label for="whatsapp_number" class="form-label">WHATSAPP_NUMBER</label><input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number']) }}"></div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save WhatsApp</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="settings-mac" role="tabpanel">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6"><label for="macurl" class="form-label">MACURL</label><input type="text" class="form-control" id="macurl" name="macurl" value="{{ old('macurl', $settings['macurl']) }}"></div>
                            <div class="col-md-6"><label for="mactoken" class="form-label">MACTOKEN</label><input type="password" class="form-control" id="mactoken" name="mactoken" value="" placeholder="Leave blank to keep current token"></div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Find MAC</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="settings-mikrotik" role="tabpanel">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6"><label for="microtik_interface1" class="form-label">MICROTIK_INTERFACE1</label><input type="text" class="form-control" id="microtik_interface1" name="microtik_interface1" value="{{ old('microtik_interface1', $settings['microtik_interface1']) }}"></div>
                            <div class="col-md-6"><label for="microtik_interface2" class="form-label">MICROTIK_INTERFACE2</label><input type="text" class="form-control" id="microtik_interface2" name="microtik_interface2" value="{{ old('microtik_interface2', $settings['microtik_interface2']) }}"></div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save MikroTik</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="settings-mail" role="tabpanel">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4"><label for="mail_mailer" class="form-label">MAIL_MAILER</label><input type="text" class="form-control" id="mail_mailer" name="mail_mailer" value="{{ old('mail_mailer', $settings['mail_mailer']) }}"></div>
                            <div class="col-md-4"><label for="mail_host" class="form-label">MAIL_HOST</label><input type="text" class="form-control" id="mail_host" name="mail_host" value="{{ old('mail_host', $settings['mail_host']) }}"></div>
                            <div class="col-md-4"><label for="mail_port" class="form-label">MAIL_PORT</label><input type="number" class="form-control" id="mail_port" name="mail_port" value="{{ old('mail_port', $settings['mail_port']) }}"></div>
                            <div class="col-md-4"><label for="mail_username" class="form-label">MAIL_USERNAME</label><input type="text" class="form-control" id="mail_username" name="mail_username" value="{{ old('mail_username', $settings['mail_username']) }}"></div>
                            <div class="col-md-4"><label for="mail_password" class="form-label">MAIL_PASSWORD</label><input type="password" class="form-control" id="mail_password" name="mail_password" value="" placeholder="Leave blank to keep current password"></div>
                            <div class="col-md-4"><label for="mail_encryption" class="form-label">MAIL_ENCRYPTION</label><input type="text" class="form-control" id="mail_encryption" name="mail_encryption" value="{{ old('mail_encryption', $settings['mail_encryption']) }}"></div>
                            <div class="col-md-6"><label for="mail_from_address" class="form-label">MAIL_FROM_ADDRESS</label><input type="text" class="form-control" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}"></div>
                            <div class="col-md-6"><label for="mail_from_name" class="form-label">MAIL_FROM_NAME</label><input type="text" class="form-control" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}"></div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Mail</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-css')
<style>
    .settings-tabs-card {
        overflow: hidden;
        border-radius: 16px;
        background: #fff;
    }

    .settings-tabs {
        background: #f8f9fa;
        border-bottom: 1px solid #e5e7eb;
        margin: 0;
    }

    .settings-tabs .nav-item {
        min-width: 120px;
    }

    .settings-tabs .nav-link {
        border: 0;
        border-radius: 0;
        color: #5f6368;
        font-weight: 600;
        padding: 0.9rem 1rem;
        transition: all 0.2s ease;
        background: transparent;
    }

    .settings-tabs .nav-link:hover {
        color: #0d6efd;
        background: rgba(13, 110, 253, 0.04);
    }

    .settings-tabs .nav-link.active {
        color: #0d6efd;
        background: #fff;
        border-bottom: 3px solid #0d6efd;
    }

    .settings-tabs .nav-link:focus {
        box-shadow: none;
    }
</style>
@endpush

@push('page-js')
<script>
    function toggleSnmp(enabled) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const feedback = document.createElement('div');
        feedback.className = 'mt-3';
        feedback.innerHTML = '<div class="alert alert-info">Updating SNMP status...</div>';

        const target = document.getElementById('olt_snmp_enabled');
        target.parentElement.parentElement.parentElement.appendChild(feedback);

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
                document.getElementById('olt_snmp_enabled').checked = !enabled;
            }
        })
        .catch(error => {
            document.getElementById('olt_snmp_enabled').checked = !enabled;
            feedback.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Unable to update SNMP status. Please try again.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            console.error(error);
        });
    }
</script>
@endpush
