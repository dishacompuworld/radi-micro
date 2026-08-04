@php
    // if(isset($seletedserver)){$seletedserver;}else{$seletedserver="";}
    $urll = url()->current() . "?sserver=". $seletedserver;
@endphp
@extends('layouts.admin')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Logs</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Microtik</li>
                        <li class="breadcrumb-item active">Logs</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-body">
          <div class="form-group">
            <form action="{{ route('microtik.log')}}" class="form-sample" method="get" name="mtk">
              <div class="d-flex align-items-center">
                <div class="mr-3">
                  <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="server-select-label">Select Server</span>
                    </div>
                    <select name="sserver" onchange="this.form.submit()" class="custom-select" id="server-select" aria-describedby="server-select-label">
                      <option value=""></option>
                      @foreach ($servers as $server)
                        <option value="{{ $server->id }}" {{ $seletedserver == $server->id ? 'selected' : '' }}>
                          {{ $server->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" id="auto-refresh" onchange="toggleAutoRefresh()">
                  <label class="form-check-label" for="auto-refresh">Auto-refresh</label>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
</div>

{{-- <div class="container"> --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="logs-table" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                            <thead>
                                <tr style="boder:1px solid black;">
                                    <th>Time</th>
                                    <th>Topics</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
    
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('page-js')

@if($seletedserver)
<script>
    $(document).ready(function() {
        var source = null;
        var table = $('#logs-table').DataTable({
            processing: true,
            ajax: {
                url: "{{ route('microtik.log') }}",
                type: 'GET',
                data: function(d) {
                    d.sserver = "{{ $seletedserver }}";
                },
                dataSrc: 'data'
            },
            columns: [
                {
                    data: null,
                    name: 'time1',
                    orderable: true,
                    searchable: false,
                    className: 'dt-left',
                    render: function(data) {
                        return data.time1 || data.time || '';
                    }
                },
                {
                    data: null,
                    name: 'topics1',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return data.topics1 || data.topics || '';
                    }
                },
                {data: 'message', name: 'message', className: 'dt-left', orderable: false},
            ],
            order: [[0, 'desc']],
            rowCallback: function(row, data){
                var topics = data.topics1 || data.topics || '';
                if (topics.includes('error')) {
                    $(row).addClass('text-danger');
                }
            },
            pageLength: 25
        });

        var loginUrl = "{{ route('microtik.log.login') }}";
        var logoutUrl = "{{ route('microtik.log.logout') }}";
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        window.toggleAutoRefresh = function() {
            if ($('#auto-refresh').is(':checked')) {
                loginAndStart();
            } else {
                logoutAndStop();
            }
        };

        var refreshTimer = null;

        function loginAndStart() {
            var selectedServer = $('#server-select').val();
            if (!selectedServer) {
                alert('Please select a server before enabling auto-refresh.');
                $('#auto-refresh').prop('checked', false);
                return;
            }

            fetch(loginUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ sserver: selectedServer })
            }).then(function(response) {
                return response.json().then(function(data) {
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'MikroTik login failed');
                    }
                    startAutoRefresh();
                });
            }).catch(function(err) {
                console.error('MikroTik login failed:', err);
                alert('Auto-refresh login failed: ' + err.message);
                $('#auto-refresh').prop('checked', false);
            });
        }

        function logoutAndStop() {
            stopAutoRefresh();
            fetch(logoutUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            }).then(function(response) {
                return response.json();
            }).then(function(data) {
                if (!data.success) {
                    console.warn('MikroTik logout failed:', data);
                }
            }).catch(function(err) {
                console.error('MikroTik logout failed:', err);
            });
        }

        function startAutoRefresh() {
            if (refreshTimer !== null) {
                return;
            }

            console.info('Starting auto-refresh polling');
            table.ajax.reload(null, false);
            refreshTimer = setInterval(function() {
                table.ajax.reload(null, false);
            }, 5000);
        }

        function stopAutoRefresh() {
            if (refreshTimer !== null) {
                console.info('Stopping auto-refresh polling');
                clearInterval(refreshTimer);
                refreshTimer = null;
            }
        }

        window.addEventListener('beforeunload', stopAutoRefresh);

        $('#auto-refresh').on('change', function() {
            if ($(this).is(':checked')) {
                startAutoRefresh();
            } else {
                stopAutoRefresh();
            }
        });
    });

</script>
@endif
@endpush