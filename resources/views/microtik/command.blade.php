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
                        <select name="sserver" class="custom-select" id="server-select">
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

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Run Command</div>

                <div class="card-body">
                    <form>
                        <textarea id="command" class="form-control" rows="10"></textarea>
                        <button id="run-command" class="btn btn-primary">Run Command</button>
                    </form>

                    <div id="response" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>    

</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function () {
    console.log('Document ready');

    $('#run-command').click(function (e) {
        console.log('Run Command button clicked');
        e.preventDefault();

        var command = $('#command').val();
        console.log('Command:', command);

        var sserver = $('#server-select').val();
        console.log('Selected server:', sserver);

        $.ajax({
            type: 'POST',
            url: 'runCommand',
            data: {
                _token: '{{ csrf_token() }}',
                command: command,
                sserver: sserver
            },
            beforeSend: function(xhr) {
                console.log('Ajax request sent');
            },
            success: function (response, command) {
                console.log('Ajax response received');
                console.log('Response:', response);
                console.log('Command:', command);
                $('#response').html(response);
            }
        });
    });
});
//     $('#view-command').click(function (e) {
//     e.preventDefault();

//     var sserver = $('#server-select').val();

//     $.ajax({
//         type: 'GET',
//         url: '/mikrotik/view-command',
//         data: { sserver: sserver },
//         success: function (response) {
//             $('#response').html(response);
//         }
//     });
// });
</script>
@endpush