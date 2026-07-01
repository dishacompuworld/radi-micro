@php
    if(isset($seletedserver)){$seletedserver;}else{$seletedserver="";}
    if(isset($pptpStatus)){$pptpStatus;}else{$pptpStatus="disabled";}
    if(isset($l2tpStatus)){$l2tpStatus;}else{$l2tpStatus="disabled";}
    if(isset($telnetStatus)){$telnetStatus;}else{$telnetStatus="disabled";}
    if(isset($wwwsslStatus)){$wwwsslStatus;}else{$wwwsslStatus="disabled";}
    if(isset($wwwStatus)){$wwwStatus;}else{$wwwStatus="disabled";}
    if(isset($sshStatus)){$sshStatus;}else{$sshStatus="disabled";}
    if(isset($winboxStatus)){$winboxStatus;}else{$winboxStatus="disabled";}
    
    // $urll = url()->current() . "?sserver=". $seletedserver;
@endphp
@extends('layouts.admin')

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

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Services</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Microtik</li>
                        <li class="breadcrumb-item active">Services</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>


    <div class="row">
    <div class="col-sm-12 col-md-5">
      <div class="card">
        <div class="card-body">
          <div class="form-group">
              {{-- <form action="{{ route('shedule.show')}}" class="form-sample" method="get" name="mtk"> --}}
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                      <label class="input-group-text" for="inputGroupSelect01">Select Server</label>
                    </div>
                    {{-- <form action="{{ route('microtik.log')}}" class="form-sample" method="get" name="mtk"> --}}
                    <select name="sserver" onchange="showStatus(this.value)" class="custom-select" id="server-select">
                          <option value=""></option>
                          @foreach ($servers as $server)
                          <option value="{{ $server->id }}" {{ $seletedserver == $server->id ? 'selected' : '' }}>
                              {{ $server->name }}
                          </option>
                            @endforeach
                    </select>
                {{-- </form> --}}
                  </div>
              {{-- </form> --}}
          </div>
        </div>
      </div>
    </div>
</div>
    <div id="message-container" style="display: none;" class="col-sm-12"></div>

<div class="row">
    <div class="col-sm-12 col-md-5" id="services-enabled-container" style="display: none">
        <div class="card">
          <div class="card-body">
            <div class="row">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="pptp-enabled" name="pptp_enabled" {{ $pptpStatus === 'enabled' ? 'checked' : '' }} onchange="updatePptpStatus(this.checked)">
                    <label class="form-check-label" for="pptp-enabled"> PPTP Server</label>
                </div>
            </div>
            <div class="row">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="l2tp-enabled" name="l2tp_enabled" {{ $l2tpStatus === 'enabled' ? 'checked' : '' }} onchange="updateL2tpStatus(this.checked)">
                    <label class="form-check-label" for="l2tp-enabled"> L2TP Server</label>
                </div>
            </div>
            <div class="row">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="telnet-enabled" name="telnet_enabled" {{ $telnetStatus === 'enabled' ? 'checked' : '' }} onchange="telnetStatus(this.checked)">
                    <label class="form-check-label" for="telnet-enabled">Telnet</label>
                </div>
            </div>
            <div class="row">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="wwwssl-enabled" name="wwwssl_enabled" {{ $wwwsslStatus === 'enabled' ? 'checked' : '' }} onchange="wwwsslStatus(this.checked)">
                    <label class="form-check-label" for="wwwssl-enabled">Www-Ssl</label>
                </div>
            </div>
            <div class="row">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="www-enabled" name="www_enabled" {{ $wwwStatus === 'enabled' ? 'checked' : '' }} onchange="wwwStatus(this.checked)">
                    <label class="form-check-label" for="www-enabled">Www</label>
                </div>
            </div>
            <div class="row">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="ssh-enabled" name="ssh_enabled" {{ $sshStatus === 'enabled' ? 'checked' : '' }} onchange="sshStatus(this.checked)">
                    <label class="form-check-label" for="ssh-enabled">Ssh</label>
                </div>
            </div class="form-check">
            <div class="row">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="winbox-enabled" name="winbox_enabled" {{ $winboxStatus === 'enabled' ? 'checked' : '' }} onchange="winboxStatus(this.checked)">
                    <label class="form-check-label" for="winbox-enabled">Winbox</label>
                </div>
            </div>
          </div>
        </div>
    </div>
</div>
</div>

</div>
@endsection

@push('page-js')    
    <script>
        function updatePptpStatus(enabled) {
            $.ajax({
                type: 'POST',
                url: '{{ route('pptp.update') }}',
                data: {pptp_enabled: enabled, sserver: $('#server-select').val()},
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.type === 'success') {
                        $('#message-container').addClass('alert alert-success alert-dismissible');
                    } else if (response.type === 'error') {
                        $('#message-container').addClass('alert alert-danger alert-dismissible');
                    }
                    $('#message-container').html(response.message + '<button type="button" class="close" data-dismiss="alert">&times;</button>');
                    $('#message-container').show();
                    setTimeout(function() {
                        $('#message-container').fadeOut('slow');
                    }, 5000); // 3000 milliseconds = 3 seconds
                }
            });
        }

        function updateL2tpStatus(enabled) {
            $.ajax({
                type: 'POST',
                url: '{{ route('l2tp.update') }}',
                data: {l2tp_enabled: enabled, sserver: $('#server-select').val()},
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.type === 'success') {
                        $('#message-container').addClass('alert alert-success alert-dismissible');
                    } else if (response.type === 'error') {
                        $('#message-container').addClass('alert alert-danger alert-dismissible');
                    }
                    $('#message-container').html(response.message + '<button type="button" class="close" data-dismiss="alert">&times;</button>');
                    $('#message-container').show();
                    setTimeout(function() {
                        $('#message-container').fadeOut('slow');
                    }, 5000); // 3000 milliseconds = 3 seconds
                }
            });
        }

        function telnetStatus(enabled) {
            $.ajax({
                type: 'POST',
                url: '{{ route('telnet.update') }}',
                data: {telnet_enabled: enabled, sserver: $('#server-select').val()},
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.type === 'success') {
                        $('#message-container').addClass('alert alert-success alert-dismissible');
                    } else if (response.type === 'error') {
                        $('#message-container').addClass('alert alert-danger alert-dismissible');
                    }
                    $('#message-container').html(response.message + '<button type="button" class="close" data-dismiss="alert">&times;</button>');
                    $('#message-container').show();
                    setTimeout(function() {
                        $('#message-container').fadeOut('slow');
                    }, 5000); // 3000 milliseconds = 3 seconds
                }
            });
        }

        function wwwsslStatus(enabled) {
            $.ajax({
                type: 'POST',
                url: '{{ route('wwwssl.update') }}',
                data: {wwwssl_enabled: enabled, sserver: $('#server-select').val()},
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.type === 'success') {
                        $('#message-container').addClass('alert alert-success alert-dismissible');
                    } else if (response.type === 'error') {
                        $('#message-container').addClass('alert alert-danger alert-dismissible');
                    }
                    $('#message-container').html(response.message + '<button type="button" class="close" data-dismiss="alert">&times;</button>');
                    $('#message-container').show();
                    setTimeout(function() {
                        $('#message-container').fadeOut('slow');
                    }, 5000); // 3000 milliseconds = 3 seconds
                }
            });
        }

        function wwwStatus(enabled) {
            $.ajax({
                type: 'POST',
                url: '{{ route('www.update') }}',
                data: {www_enabled: enabled, sserver: $('#server-select').val()},
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.type === 'success') {
                        $('#message-container').addClass('alert alert-success alert-dismissible');
                    } else if (response.type === 'error') {
                        $('#message-container').addClass('alert alert-danger alert-dismissible');
                    }
                    $('#message-container').html(response.message + '<button type="button" class="close" data-dismiss="alert">&times;</button>');
                    $('#message-container').show();
                    setTimeout(function() {
                        $('#message-container').fadeOut('slow');
                    }, 5000); // 3000 milliseconds = 3 seconds
                }
            });
        }

        function sshStatus(enabled) {
            $.ajax({
                type: 'POST',
                url: '{{ route('ssh.update') }}',
                data: {ssh_enabled: enabled, sserver: $('#server-select').val()},
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.type === 'success') {
                        $('#message-container').addClass('alert alert-success alert-dismissible');
                    } else if (response.type === 'error') {
                        $('#message-container').addClass('alert alert-danger alert-dismissible');
                    }
                    $('#message-container').html(response.message + '<button type="button" class="close" data-dismiss="alert">&times;</button>');
                    $('#message-container').show();
                    setTimeout(function() {
                        $('#message-container').fadeOut('slow');
                    }, 5000); // 3000 milliseconds = 3 seconds
                }
            });
        }

        function winboxStatus(enabled) {
            $.ajax({
                type: 'POST',
                url: '{{ route('winbox.update') }}',
                data: {winbox_enabled: enabled, sserver: $('#server-select').val()},
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.type === 'success') {
                        $('#message-container').addClass('alert alert-success alert-dismissible');
                    } else if (response.type === 'error') {
                        $('#message-container').addClass('alert alert-danger alert-dismissible');
                    }
                    $('#message-container').html(response.message + '<button type="button" class="close" data-dismiss="alert">&times;</button>');
                    $('#message-container').show();
                    setTimeout(function() {
                        $('#message-container').fadeOut('slow');
                    }, 5000); // 3000 milliseconds = 3 seconds
                }
            });
        }
        
        $(document).ready(function () {
            $('#server-select').on('change', function () {
                if (this.value) {
                    $('#services-enabled-container').show();
                } else {
                    $('#services-enabled-container').hide();
                }
            });
        });

        function showStatus(serverId) {
        $.ajax({
            type: 'GET',
            url: '{{ route('service.status') }}',
            data: {sserver: serverId},
            success: function (response) {
                var pptpStatus = response.pptpStatus;
                $('#pptp-enabled').prop('checked', pptpStatus === 'enabled');

                var l2tpStatus = response.l2tpStatus;
                $('#l2tp-enabled').prop('checked', l2tpStatus === 'enabled');

                var telnetStatus = response.telnetStatus;
                $('#telnet-enabled').prop('checked', telnetStatus === 'enabled');

                var wwwsslStatus = response.wwwsslStatus;
                $('#wwwssl-enabled').prop('checked', wwwsslStatus === 'enabled');

                var wwwStatus = response.wwwStatus;
                $('#www-enabled').prop('checked', wwwStatus === 'enabled');

                var sshStatus = response.sshStatus;
                $('#ssh-enabled').prop('checked', sshStatus === 'enabled');

                var winboxStatus = response.winboxStatus;
                $('#winbox-enabled').prop('checked', winboxStatus === 'enabled');
            }
        });
    }
    </script>
@endpush