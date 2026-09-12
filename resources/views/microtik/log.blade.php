@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">MikroTik Logs</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Microtik</li>
                    <li class="breadcrumb-item active">Logs</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <button id="start-listener" class="btn btn-success btn-sm">Start Service</button>
                    <button id="stop-listener" class="btn btn-danger btn-sm d-none">Stop Service</button>
                    <button id="delete-logs" class="btn btn-outline-danger btn-sm">Delete Logs</button>
                </div>
                <span id="listener-status" class="badge bg-secondary">Stopped</span>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent MikroTik UDP Logs</h5>
            <span class="text-muted small" id="last-updated">Waiting...</span>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-5">
                    <input id="mikrotik-log-search" type="text" class="form-control form-control-sm" placeholder="Search source or message...">
                </div>
                <div class="col-md-3">
                    <select id="mikrotik-log-server-filter" class="form-select form-select-sm">
                        <option value="all">All Servers</option>
                        <option value="main">Main Server</option>
                        <option value="local">Local Server</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="mikrotik-log-filter" class="form-select form-select-sm">
                        <option value="all">All</option>
                        <option value="error">Errors</option>
                        <option value="warning">Warnings</option>
                        <option value="success">Success</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div id="mikrotik-log-summary-top" class="text-muted small"></div>
                <nav aria-label="MikroTik log pagination top">
                    <ul class="pagination pagination-sm mb-0" id="mikrotik-log-pagination-top"></ul>
                </nav>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Server</th>
                            <th>Source</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody id="mikrotik-log-body">
                        <tr>
                            <td colspan="3" class="text-muted text-center">No logs received yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div id="mikrotik-log-summary-bottom" class="text-muted small"></div>
                <nav aria-label="MikroTik log pagination bottom">
                    <ul class="pagination pagination-sm mb-0" id="mikrotik-log-pagination-bottom"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    .mikrotik-success {
        color: #0b5d35 !important;
    }
</style>

@push('page-js')
<script>
$(document).ready(function () {
    const csrfToken = '{{ csrf_token() }}';
    const pageSize = 50;
    const mainPrefix = {!! json_encode(App\Models\Setting::where('key', 'microtik_main_prefix')->value('value') ?: 'Main') !!};
    const localPrefix = {!! json_encode(App\Models\Setting::where('key', 'microtik_local_prefix')->value('value') ?: 'SuperClick') !!};
    let allLogs = [];
    let currentPage = 1;

    function refreshStatus() {
        $.get('{{ route('microtik.logs.status') }}', function (res) {
            const isRunning = !!res.running;
            $('#listener-status')
                .removeClass('bg-success bg-secondary')
                .addClass(isRunning ? 'bg-success' : 'bg-secondary')
                .text(isRunning ? 'Receiving' : 'Stopped');

            $('#start-listener').toggleClass('d-none', isRunning);
            $('#stop-listener').toggleClass('d-none', !isRunning);
        });
    }

    function getServerLabel(message) {
        const text = (message || '').toString();
        const prefixes = [mainPrefix, localPrefix].filter(Boolean);
        const pattern = new RegExp('(^|\\s)(' + prefixes.map(function (value) {
            return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }).join('|') + ')\\s*:', 'i');
        const match = text.match(pattern);

        if (!match) {
            return 'Unknown';
        }

        const label = (match[2] || '').toLowerCase();
        return label === (mainPrefix || '').toLowerCase() ? 'Main Server' : 'Local Server';
    }

    function cleanLogMessage(message) {
        let text = (message || '').toString();

        text = text.replace(/^\s+|\s+$/g, '');
        const prefixes = [mainPrefix, localPrefix].filter(Boolean);
        if (prefixes.length) {
            const prefixPattern = new RegExp('^(?:.*?)(?:' + prefixes.map(function (value) {
                return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }).join('|') + ')\\s*:\\s*', 'i');
            text = text.replace(prefixPattern, '');
        }
        text = text.replace(/^pppoe(?:,[a-z0-9_\-]+)*\s+/i, '');
        text = text.replace(/\s+\([^)]*\)\s*:/, ':');

        const wrappedMatch = text.match(/^<pppoe-([^>]+)>\s*:\s*(authenticated|connected|disconnected|terminating\.\.\.)(?:\s*-\s*(.*))?$/i);
        if (wrappedMatch) {
            const user = wrappedMatch[1].replace(/_+$/, '');
            const status = wrappedMatch[2].toLowerCase();
            const details = (wrappedMatch[3] || '').trim();

            if (details && /peer is not responding|not responding/i.test(details)) {
                return user + ' terminating — peer is not responding';
            }

            return user + ' ' + status;
        }

        const authFailedMatch = text.match(/^user\s+([A-Za-z0-9_.-]+)\s+authentication failed(?:\s*-\s*(.*))?$/i)
            || text.match(/^.*?:\s*user\s+([A-Za-z0-9_.-]+)\s+authentication failed(?:\s*-\s*(.*))?$/i);
        if (authFailedMatch) {
            const user = authFailedMatch[1] || authFailedMatch[0];
            const detail = (authFailedMatch[2] || '').trim();
            return detail ? user + ' authentication failed — ' + detail : user + ' authentication failed';
        }

        const accountMatch = text.match(/^([A-Za-z0-9_.-]+)\s+(logged in|logged out|authenticated|connected|disconnected|terminating\.\.\.)(.*)$/i);
        if (accountMatch) {
            const account = accountMatch[1];
            const status = accountMatch[2].toLowerCase();
            const remainder = (accountMatch[3] || '').trim();
            const ip = remainder.match(/\b(\d{1,3}(?:\.\d{1,3}){3})\b/i)?.[1];
            const mac = remainder.match(/from\s+([A-Fa-f0-9:]{11,17})/i)?.[1] || remainder.match(/\b([A-Fa-f0-9]{2}(?::[A-Fa-f0-9]{2}){5})\b/i)?.[1];

            if (status === 'logged out' && mac) {
                return account + ' logged out — MAC: ' + mac + (ip ? ' IP: ' + ip : '');
            }

            if (status === 'logged in') {
                const details = [];
                if (mac) details.push('MAC: ' + mac);
                if (ip) details.push('IP: ' + ip);
                if (details.length) {
                    return account + ' logged in — ' + details.join(', ');
                }
                return account + ' logged in';
            }

            if (/peer is not responding/i.test(remainder)) {
                return account + ' terminating — peer is not responding';
            }

            return account + ' ' + status;
        }

        if (/terminating\.\.\.\s*-\s*peer is not responding/i.test(text)) {
            const userMatch = text.match(/<pppoe-([^>]+)>/i);
            if (userMatch) {
                return userMatch[1].replace(/_+$/, '') + ' terminating — peer is not responding';
            }
        }

        const pppoeEstablished = text.match(/^PPPoE connection established from\s+([A-Fa-f0-9:]+)\s*$/i);
        if (pppoeEstablished) {
            return 'connection established';
        }

        text = text.replace(/\s+/g, ' ');
        return text || '-';
    }

    function formatMessageCell(message) {
        const text = (message || '').toString();
        const cleanText = cleanLogMessage(text);
        const macMatch = text.match(/from\s+([A-Fa-f0-9:]{11,17})/i) || text.match(/\b([A-Fa-f0-9]{2}(?::[A-Fa-f0-9]{2}){5})\b/);
        const mac = macMatch ? macMatch[1] : null;
        const ipMatch = text.match(/\b(\d{1,3}(?:\.\d{1,3}){3})\b/i);
        const ip = ipMatch ? ipMatch[1] : null;

        if (!mac && !ip) {
            return cleanText || '-';
        }

        const primary = cleanText
            .replace(/\s+—\s+MAC:\s+[A-Fa-f0-9:]{11,17}$/i, '')
            .replace(/\s+—\s+IP:\s*\d{1,3}(?:\.\d{1,3}){3}$/i, '')
            .replace(/\s+—\s+MAC:\s+[A-Fa-f0-9:]{11,17},\s*IP:\s*\d{1,3}(?:\.\d{1,3}){3}$/i, '')
            .trim();

        const details = [];
        if (mac) details.push('MAC: ' + mac);
        if (ip) details.push('IP: ' + ip);

        return '<div>' + (primary || 'Connection event') + '</div><div class="small text-muted mt-1">' + details.join(' • ') + '</div>';
    }

    function logMessageClass(message) {
        const text = (message || '').toLowerCase();

        if (/authentication failed|disconnected|terminating\.\.\.|failed|error/.test(text)) {
            return 'text-danger fw-semibold';
        }

        if (/logged out|warning|timeout/.test(text)) {
            return 'text-warning fw-semibold';
        }

        if (/logged in|connected|authenticated|connection established/.test(text)) {
            return 'mikrotik-success fw-semibold';
        }

        return 'text-body';
    }

    function getFilteredLogs(logs) {
        const searchTerm = ($('#mikrotik-log-search').val() || '').trim().toLowerCase();
        const filterType = $('#mikrotik-log-filter').val() || 'all';
        const serverFilter = $('#mikrotik-log-server-filter').val() || 'all';

        return logs.filter(function (row) {
            const source = (row.source || '').toString().toLowerCase();
            const server = getServerLabel(row.message || row.raw || '').toLowerCase();
            const message = cleanLogMessage(row.message || row.raw || '');
            const text = (source + ' ' + server + ' ' + message).toLowerCase();
            const matchesSearch = !searchTerm || text.includes(searchTerm);
            const matchesServer = serverFilter === 'all' || server === (serverFilter === 'main' ? 'main server' : 'local server');
            const category = logMessageClass(row.message || row.raw || '').includes('text-danger')
                ? 'error'
                : logMessageClass(row.message || row.raw || '').includes('text-warning')
                    ? 'warning'
                    : logMessageClass(row.message || row.raw || '').includes('text-success')
                        ? 'success'
                        : 'info';

            const matchesFilter = filterType === 'all' || category === filterType;
            return matchesSearch && matchesServer && matchesFilter;
        });
    }

    function paginateRows(items) {
        const totalPages = Math.max(1, Math.ceil(items.length / pageSize));
        currentPage = Math.min(currentPage, totalPages);

        const startIndex = (currentPage - 1) * pageSize;
        const rows = items.slice(startIndex, startIndex + pageSize);

        return {
            totalPages,
            totalItems: items.length,
            rows
        };
    }

    function formatRelativeTime(timeValue) {
        const rawValue = (timeValue || '').toString();
        const date = new Date(rawValue);

        if (isNaN(date.getTime())) {
            return {
                full: rawValue || '-',
                relative: ''
            };
        }

        const diffMs = Date.now() - date.getTime();
        const diffSeconds = Math.max(0, Math.floor(diffMs / 1000));
        const diffMinutes = Math.floor(diffSeconds / 60);
        const diffHours = Math.floor(diffMinutes / 60);
        const diffDays = Math.floor(diffHours / 24);

        let relative = 'just now';
        if (diffMinutes > 0) {
            relative = diffMinutes + ' minute' + (diffMinutes === 1 ? '' : 's') + ' ago';
        }
        if (diffHours > 0) {
            relative = diffHours + ' hour' + (diffHours === 1 ? '' : 's') + ' ago';
        }
        if (diffDays > 0) {
            relative = diffDays + ' day' + (diffDays === 1 ? '' : 's') + ' ago';
        }

        const full = date.toLocaleString([], {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });

        return {
            full,
            relative
        };
    }

    function renderPagination(totalPages, totalItems) {
        const paginations = ['#mikrotik-log-pagination-top', '#mikrotik-log-pagination-bottom'];
        const summaries = ['#mikrotik-log-summary-top', '#mikrotik-log-summary-bottom'];

        paginations.forEach(function (selector) {
            const pagination = $(selector);
            pagination.empty();

            if (totalItems === 0) {
                return;
            }

            const prevItem = $('<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '"><a class="page-link" href="#" aria-label="Previous">&laquo;</a></li>');
            prevItem.on('click', function (e) {
                e.preventDefault();
                if (currentPage > 1) {
                    currentPage -= 1;
                    refreshLogs();
                }
            });
            pagination.append(prevItem);

            for (let page = 1; page <= totalPages; page++) {
                const pageItem = $('<li class="page-item ' + (page === currentPage ? 'active' : '') + '"><a class="page-link" href="#">' + page + '</a></li>');
                pageItem.on('click', function (e) {
                    e.preventDefault();
                    currentPage = page;
                    refreshLogs();
                });
                pagination.append(pageItem);
            }

            const nextItem = $('<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') + '"><a class="page-link" href="#" aria-label="Next">&raquo;</a></li>');
            nextItem.on('click', function (e) {
                e.preventDefault();
                if (currentPage < totalPages) {
                    currentPage += 1;
                    refreshLogs();
                }
            });
            pagination.append(nextItem);
        });

        const summaryText = totalItems === 0
            ? '0 records'
            : 'Showing ' + Math.min((currentPage - 1) * pageSize + 1, totalItems) + ' to ' + Math.min(currentPage * pageSize, totalItems) + ' of ' + totalItems + ' records';

        summaries.forEach(function (selector) {
            $(selector).text(summaryText);
        });
    }

    function refreshLogs() {
        $.get('{{ route('microtik.logs.recent') }}', function (res) {
            allLogs = res.logs || [];
            const filtered = getFilteredLogs(allLogs);
            const { totalPages, totalItems, rows } = paginateRows(filtered);
            const body = $('#mikrotik-log-body');
            body.empty();

            if (!rows.length) {
                body.append('<tr><td colspan="4" class="text-muted text-center">No matching logs found.</td></tr>');
                $('#last-updated').text(new Date().toLocaleTimeString());
                renderPagination(totalPages, totalItems);
                return;
            }

            $.each(rows, function (_, row) {
                const timeValue = row.time || '-';
                const timeInfo = formatRelativeTime(timeValue);
                const server = getServerLabel(row.message || row.raw || '');
                const source = row.source || '-';
                const messageText = formatMessageCell(row.message || row.raw || '-');
                const messageClass = logMessageClass(row.message || row.raw || '');
                const timeHtml = '<div class="fw-semibold">' + timeInfo.full + '</div><div class="small text-muted">' + timeInfo.relative + '</div>';
                body.append('<tr><td>' + timeHtml + '</td><td>' + server + '</td><td>' + source + '</td><td class="' + messageClass + '">' + messageText + '</td></tr>');
            });

            $('#last-updated').text(new Date().toLocaleTimeString());
            renderPagination(totalPages, totalItems);
        });
    }

    $('#mikrotik-log-search, #mikrotik-log-filter, #mikrotik-log-server-filter').on('input change', function () {
        currentPage = 1;
        refreshLogs();
    });

    $('#start-listener').on('click', function () {
        $.ajax({
            url: '{{ route('microtik.logs.start') }}',
            type: 'POST',
            data: { _token: csrfToken },
            success: function (res) {
                alert(res.message || 'MikroTik UDP listener started.');
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
            url: '{{ route('microtik.logs.stop') }}',
            type: 'POST',
            data: { _token: csrfToken },
            success: function (res) {
                alert(res.message || 'MikroTik UDP listener stopped.');
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
        if (!confirm('Delete the saved UDP logs?')) {
            return;
        }

        $.ajax({
            url: '{{ route('microtik.logs.delete') }}',
            type: 'POST',
            data: { _token: csrfToken },
            success: function (res) {
                alert(res.message || 'MikroTik logs deleted.');
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