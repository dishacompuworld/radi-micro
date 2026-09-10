@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">OLT Logs</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">OLT</li>
                    <li class="breadcrumb-item active">OLT Logs</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div class="d-flex flex-wrap gap-2">
                    <button id="start-listener" class="btn btn-success btn-sm">Start Service</button>
                    <button id="stop-listener" class="btn btn-danger btn-sm d-none">Stop Service</button>
                    <button id="delete-logs" class="btn btn-outline-danger btn-sm">Delete All Logs</button>
                </div>
                <span id="listener-status" class="badge {{ $running ? 'bg-success' : 'bg-secondary' }}">
                    {{ $running ? 'Running' : 'Stopped' }}
                </span>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent OLT Logs</h5>
            <span class="text-muted small" id="last-updated">Waiting...</span>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-8">
                    <input id="olt-log-search" type="text" class="form-control form-control-sm" placeholder="Search serial, OID, name, alarm...">
                </div>
                <div class="col-md-4">
                    <select id="olt-log-filter" class="form-select form-select-sm">
                        <option value="all">All</option>
                        <option value="critical">Critical</option>
                        <option value="success">Success</option>
                        <option value="info">Informational</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Serial</th>
                            <th>OID</th>
                            <th>Name</th>
                            <th>Alarm</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody id="olt-log-body">
                        <tr>
                            <td colspan="6" class="text-muted text-center">No logs received yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function () {
        function parseOltLine(rawLine) {
            const prefixMatch = rawLine.match(/^\[[^\]]+\]\s*[^\s]+:\d+\s*(.*)$/);
            const payload = prefixMatch ? prefixMatch[1] : rawLine;
            const patterns = [
                /^<(?<pri>\d+)>\s*(?<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?<device>\S+)\s+(?<event>\d+)\s+ONU\s+\(SN\s+(?<serial>[A-Z0-9-]+)\)\s+(?<ont>\d+)\s+in\s+PON\s+(?<pon>\d+)\s+(?<alarm>.+?)\s+(?<count>\d+)\s+reported\.?\s*$/i,
                /^<(?<pri>\d+)>\s*(?<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?<device>\S+)\s+(?<event>\d+)\s+(?:\[[^\]]+\]\s*)?PON\s+(?<pon>\d+)\s+ONU\(SN\s+(?<serial>[A-Z0-9-]+)\)\s+(?<ont>\d+)\s+(?<alarm>.+?)\s*$/i,
                /^<(?<pri>\d+)>\s*(?<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?<device>\S+)\s+(?<event>\d+)\s+Signals\s+were\s+lost\s+for\s+ONU\s+\(SN\s+(?<serial>[A-Z0-9-]+)\)\s+(?<ont>\d+)\s+in\s+PON\s+(?<pon>\d+)\.?\s*$/i,
                /^<(?<pri>\d+)>\s*(?<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?<device>\S+)\s+(?<event>\d+)\s+ONU\s+\(SN\s+(?<serial>[A-Z0-9-]+)\)\s+(?<ont>\d+)\s+in\s+PON\s+(?<pon>\d+)\s+last\s+down\s+causes?\s*:\s*(?<alarm>.+?)\.?\s*$/i,
                /^<(?<pri>\d+)>\s*(?<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?<device>\S+)\s+(?<event>\d+)\s+ONU\s+\(SN\s+(?<serial>[A-Z0-9-]+)\)\s+(?<ont>\d+)\s+in\s+PON\s+(?<pon>\d+)\s+was\s+disconnected\.?\s*$/i,
                /^<(?<pri>\d+)>\s*(?<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?<device>\S+)\s+(?<event>\d+)\s+Warn\s+of\s+dying-gasp\s+for\s+ONU\s+\(SN\s+(?<serial>[A-Z0-9-]+)\)\s+(?<ont>\d+)\s+in\s+PON\s+(?<pon>\d+)\.?\s*$/i,
                /^<(?<pri>\d+)>\s*(?<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?<device>\S+)\s+(?<event>\d+)\s+ONU\s+\(SN\s+(?<serial>[A-Z0-9-]+)\)\s+(?<ont>\d+)\s+in\s+PON\s+(?<pon>\d+)\s+was\s+connected\.?\s*$/i,
                /^<(?<pri>\d+)>\s*(?<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?<device>\S+)\s+(?<event>\d+)\s+PON\s+(?<pon>\d+)\s+ONU\(SN\s+(?<serial>[A-Z0-9-]+)\)\s+(?<ont>\d+)\s+ethernet\s+port\s+\d+\s+link\s+was\s+up\.?\s*$/i,
                /^<(?<pri>\d+)>\s*(?<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?<device>\S+)\s+(?<event>\d+)\s+ONU\s+(?<ont>\d+)\s+in\s+PON\s+(?<pon>\d+)\s+was\s+activated\s+successfully\.?\s*$/i
            ];

            for (const pattern of patterns) {
                const match = payload.match(pattern);
                if (match) {
                    let alarm = (match.groups.alarm || '').replace(/[\.\s]+$/, '').trim();

                    if (!alarm) {
                        if (/link was up|was connected/i.test(payload)) {
                            alarm = 'Connected';
                        } else if (/was activated successfully/i.test(payload)) {
                            alarm = 'Activated successfully';
                        } else if (/dying-gasp/i.test(payload)) {
                            alarm = 'Dying-gasp';
                        } else if (/Signals were lost/i.test(payload)) {
                            alarm = 'Signals were lost';
                        } else if (/was disconnected/i.test(payload)) {
                            alarm = 'ONU was disconnected';
                        } else if (/last down causes/i.test(payload)) {
                            alarm = 'Last down causes';
                        }
                    }

                    return {
                        time: match.groups.timestamp || '',
                        device: match.groups.device || '',
                        serial: match.groups.serial || '',
                        ont: match.groups.ont || '',
                        pon: match.groups.pon || '',
                        alarm: alarm,
                        count: match.groups.count || '',
                        raw: payload.trim()
                    };
                }
            }

            return {
                time: '',
                device: '',
                serial: '',
                ont: '',
                pon: '',
                alarm: '',
                count: '',
                raw: payload.trim()
            };
        }

        function getAlarmCategory(alarmText) {
            if (/lost|disconnected|last down causes|link was down|LOS|dying-gasp|dying gasp/i.test(alarmText)) {
                return 'critical';
            }

            if (/activated successfully|connected/i.test(alarmText)) {
                return 'success';
            }

            return 'info';
        }

        function renderLogs(logs) {
            const tbody = $('#olt-log-body');
            const searchTerm = $('#olt-log-search').val().trim().toLowerCase();
            const filterType = $('#olt-log-filter').val() || 'all';
            tbody.empty();

            if (!logs || logs.length === 0) {
                tbody.append('<tr><td colspan="6" class="text-muted text-center">No logs received yet.</td></tr>');
                return;
            }

            const filteredLogs = logs.filter(function (row) {
                const entry = typeof row === 'string' ? { raw: row } : row;
                const parsed = entry.raw ? parseOltLine(entry.raw) : {
                    time: entry.time || '',
                    serial: entry.serial || '',
                    pon: entry.pon || '',
                    ont: entry.ont || '',
                    alarm: entry.alarm || '',
                    count: entry.count || '',
                };

                const alarmText = (parsed.alarm || entry.alarm || '').toLowerCase();
                const oidValue = (parsed.pon && parsed.ont) ? parsed.pon + '.' + parsed.ont : (entry.oid || '');
                const nameText = (entry.name || '').toLowerCase();
                const serialText = (parsed.serial || entry.serial || '').toLowerCase();
                const searchText = [serialText, oidValue, nameText, alarmText].join(' ');

                const matchesSearch = !searchTerm || searchText.includes(searchTerm);
                const category = getAlarmCategory(parsed.alarm || entry.alarm || '');
                const matchesFilter = filterType === 'all' || category === filterType;

                return matchesSearch && matchesFilter;
            });

            if (filteredLogs.length === 0) {
                tbody.append('<tr><td colspan="6" class="text-muted text-center">No matching logs found.</td></tr>');
                return;
            }

            $.each(filteredLogs, function (_, row) {
                const entry = typeof row === 'string' ? { raw: row } : row;
                const timeMatch = (entry.raw || '').match(/^\[(.*?)\]/);
                const lineTime = timeMatch ? timeMatch[1] : '';
                const parsed = entry.raw ? parseOltLine(entry.raw) : {
                    time: entry.time || '',
                    serial: entry.serial || '',
                    pon: entry.pon || '',
                    ont: entry.ont || '',
                    alarm: entry.alarm || '',
                    count: entry.count || '',
                };

                const time = parsed.time || entry.time || lineTime;
                const serial = parsed.serial || entry.serial || '-';
                const oidValue = (parsed.pon && parsed.ont) ? parsed.pon + '.' + parsed.ont : (entry.oid || '');
                const oidLink = oidValue
                    ? '<a href="{{ route('edit.ont') }}?oid=' + encodeURIComponent(oidValue) + '">' + oidValue + '</a>'
                    : '-';

                const alarmText = (parsed.alarm || entry.alarm || '-');
                const isCriticalAlarm = /lost|disconnected|last down causes|link was down|LOS|dying-gasp|dying gasp/i.test(alarmText);
                const isSuccessAlarm = /activated successfully|connected|link was up/i.test(alarmText);
                const alarmCell = isCriticalAlarm
                    ? '<span class="text-danger fw-semibold">' + alarmText + '</span>'
                    : isSuccessAlarm
                        ? '<span class="text-success fw-semibold">' + alarmText + '</span>'
                        : alarmText;

                const nameText = entry.name || '-';
                const powerText = entry.power !== undefined && entry.power !== null && entry.power !== '' ? entry.power : '';
                const displayName = powerText ? nameText + ' (' + powerText + ')' : nameText;

                const tr = $('<tr>');
                tr.append('<td>' + (time || '-') + '</td>');
                tr.append('<td>' + serial + '</td>');
                tr.append('<td>' + oidLink + '</td>');
                tr.append('<td>' + displayName + '</td>');
                tr.append('<td>' + alarmCell + '</td>');
                tr.append('<td>' + (parsed.count || entry.count || '-') + '</td>');
                tbody.append(tr);
            });
        }

        function refreshLogs() {
            $.get('{{ route('olt.logs.recent') }}', function (res) {
                renderLogs(res.logs || []);
                $('#last-updated').text(new Date().toLocaleTimeString());
            });
        }

        function refreshStatus() {
            $.get('{{ route('olt.logs.status') }}', function (res) {
                const isRunning = !!res.running;
                $('#listener-status')
                    .removeClass('bg-success bg-secondary')
                    .addClass(isRunning ? 'bg-success' : 'bg-secondary')
                    .text(isRunning ? 'Running' : 'Stopped');

                $('#start-listener').toggleClass('d-none', isRunning);
                $('#stop-listener').toggleClass('d-none', !isRunning);
            });
        }

        $('#olt-log-search').on('input', function () {
            refreshLogs();
        });

        $('#olt-log-filter').on('change', function () {
            refreshLogs();
        });

        $('#start-listener').on('click', function () {
            $.ajax({
                url: '{{ route('olt.logs.start') }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    alert(res.message);
                    refreshStatus();
                    refreshLogs();
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not start listener.';
                    alert(msg);
                }
            });
        });

        $('#stop-listener').on('click', function () {
            $.ajax({
                url: '{{ route('olt.logs.stop') }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    alert(res.message);
                    refreshStatus();
                    refreshLogs();
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not stop listener.';
                    alert(msg);
                }
            });
        });

        $('#delete-logs').on('click', function () {
            if (!confirm('Delete all OLT logs?')) {
                return;
            }

            $.ajax({
                url: '{{ route('olt.logs.delete') }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    alert(res.message || 'All OLT logs deleted.');
                    refreshLogs();
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not delete logs.';
                    alert(msg);
                }
            });
        });

        refreshStatus();
        refreshLogs();
        setInterval(function () {
            refreshStatus();
            refreshLogs();
        }, 3000);
    });
</script>
@endpush
