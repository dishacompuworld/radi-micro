@php
//    if(isset($username)){$username;}else{$username="";}
   if(isset($updval)){$updval;}else{$updval="";}
   if(isset($search)){$search;}else{$search="";}

   function format_interval(DateInterval $interval) {
        $result = "";
        if ($interval->y) { $result .= $interval->format("%y years "); }
        if ($interval->m) { $result .= $interval->format("%m months "); }
        if ($interval->d) { $result .= $interval->format("%d days "); }
        if ($interval->h) { $result .= $interval->format("%h hours "); }
        if ($interval->i) { $result .= $interval->format("%i minutes "); }
        if ($interval->s) { $result .= $interval->format("%s seconds "); }

        return $result;
    }

	$now = now();
	$fdate = new DateTime($updval);
    $datte = $fdate->format('d M Y, h:i:s A');
	$diff = $now->diff($datte);
    $diff_mins = format_interval($diff);
    // $urll = $loca . ".xceednet.com";

    // $url = url()->current();
    // if($username && $updval){
    //     $msg = 'Last fetched information not available';
    // }else {
        $msg = 'Last Fetched at ' . $updval . ' ('. $diff_mins.')';
    // }


    // $oprefreshfail = (new App\Http\Controllers\AlertMessageController())->get('op.refresh.fail');
    // $oprefreshsuccess = (new App\Http\Controllers\AlertMessageController())->get('op.refresh.success');
    // $oprefreshfail = (array) $oprefreshfail;
    // $oprefreshfailmsg = $oprefreshfail['message'];
    // $oprefreshsuccess = (array) $oprefreshsuccess;
    // $oprefreshsuccessmsg = $oprefreshsuccess['message'];

    $oprefreshfail = (new App\Http\Controllers\AlertMessageController())->get('op.refresh.fail');
    $oprefreshsuccess = (new App\Http\Controllers\AlertMessageController())->get('op.refresh.success');

    if (isset($oprefreshfail['message'])) {
        $oprefreshfailmsg = $oprefreshfail['message'];
    }

    if (isset($oprefreshsuccess['message'])) {
        $oprefreshsuccessmsg = $oprefreshsuccess['message'];
    }

     $opdeletefail = (new App\Http\Controllers\AlertMessageController())->get('ont.delete.error');
     $opdeletesuccess = (new App\Http\Controllers\AlertMessageController())->get('ont.delete.success');
     //$opdeletefail = (array) $opdeletefail;
     if (isset($opdeletefail['message'])) {
        $opdeletefailmsg = $opdeletefail['message'];
     }

     //$opdeletesuccess = (array) $opdeletesuccess;

     if (isset($opdeletesuccess['message'])) {
         $opdeletesuccessmsg = $opdeletesuccess['message'];
     }
     
     $oprebootfail = (new App\Http\Controllers\AlertMessageController())->get('ont.reboot.error');
     $oprebootsuccess = (new App\Http\Controllers\AlertMessageController())->get('ont.reboot.success');
     //$oprebootfail = (array) $oprebootfail;
     if (isset($oprebootfail['message'])) {
        $oprebootfailmsg = $oprebootfail['message'];
     }
     //$oprebootsuccess = (array) $oprebootsuccess;
     if (isset($oprebootsuccess['message'])) {
        $oprebootsuccessmsg = $oprebootsuccess['message'];
     }
@endphp
@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Optical Power</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">OLT</li>
                    <li class="breadcrumb-item active">Optical Power</li>
                </ol>
            </nav>
        </div>
    </div>

    <div id="message-container" style="display: none;" class="col-sm-12"></div>
<div>
	{{-- @if (isset('lastchkmsg')) --}}
	<label class="badge badge-success"> {{ $msg; }}</lable>
	{{-- @endif --}}
</div>

<div><input type="hidden" id="searchBox" placeholder="Search..." class="form-control mb-3" value="{{ $search }}"></div>
{{-- @if($name) --}}
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="opticalpowers" class="datatable table table-striped table-bordered table-hover table-center mb-0">
						<thead>
							<tr style="boder:1px solid black;">
								<th>SR No</th>
                                <th>ONT ID</th>
                                <th>Name of ONT</th>
                                <th>Username</th>
                                <th>Optical Power(dBm)</th>
                                <th>Updated ON</th>
                                <th>Action</th>
							</tr>
						</thead>
						<tbody>

						</tbody>
					</table>
				</div>
			</div>
		</div>
        <a href="javascript: history.back()" class="btn btn-primary btn-sm">Back</a>
	</div>

</div>

</div>
@endsection

@push('page-js')
{{-- @if($name) --}}
<script>
    $(document).ready(function() {
        var table = $('#opticalpowers').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('show.opticalpowers') }}",
            columns: [
                {data: 'srno', name: 'srno', searchable: false,},
                {data: 'oid', name: 'oid', orderable: false,},
                {data: 'names', name: 'names', orderable: false,},
                {data: 'user', name: 'user', orderable: false,},
                {data: 'opower', name: 'opower', orderable: true, searchable: false,},
                {data: 'updated', name: 'updated', orderable: true, searchable: false,},
                {data: 'action', name: 'action', orderable: false, searchable: false,},
            ],
            aaSorting: [],
            pageLength: 50,
        });

        $('body').on('click','#refreshbtn',function(){
            //e.preventDefault();
            var $button = $(this);
            // console.log($button); // Check if $button is defined
            $button.prop('disabled', false); // Enable button
            $button.html('Processing...');
            var route = $(this).data('route');
            var variable = $(this).data('variable');
            var ont = $(this).data('ont');
            var user = $(this).data('user');
            var successmessage = '{{ $oprefreshsuccessmsg }}';
            $.ajax({
                type: 'GET',
                url: route,
                data: {
                    variable: variable,
                    ont: ont,
                    user: user
                },
                success: function(response) {
                    if(response.success){
                        successmessage = successmessage.replace(':power', response.op);
                        successmessage = successmessage.replace(':ontname', response.ont);
                        successmessage = successmessage.replace(':user', response.user);
                        $('#opticalpowers').DataTable().ajax.reload(null, false);
                        $('#message-container').addClass('alert alert-success alert-dismissible');
                        $('#message-container').html(successmessage + ' <button type="button" class="close" data-dismiss="alert">&times;</button>');
                        $('#message-container').show();
                    } else {
                        $('#message-container').addClass('alert alert-danger alert-dismissible');
                        $('#message-container').html('{{ $oprefreshfailmsg }} <button type="button" class="close" data-dismiss="alert">&times;</button>');
                        $('#message-container').show();
                    }
                    $button.prop('disabled', false);
                    $button.html('<i class="bi bi-arrow-repeat"></i>');
                }
            });
        });

    $('body').on('click','#deletebtn',function(){
        //e.preventDefault();
        var $button = $(this);
        // console.log($button); // Check if $button is defined
        $button.prop('disabled', false); // Enable button
        $button.html('Deleting...');
        var route = $(this).data('routee');
        // var route = "{{route('delete.ont')}}";
        var variable = $(this).data('oid');
        var ont = $(this).data('ont');
        var user = $(this).data('user');
        var deletemessage = '{{ $opdeletesuccessmsg }}';
        var deletemessagefail = '{{ $opdeletefailmsg }}';
        $.ajax({
            type: 'GET',
            url: route,
            data: {
                variable: variable,
                ont: ont,
                user: user
            },
            success: function(response) {
                if(response.success){
                    deletemessage = deletemessage.replace(':oid', response.oid);
                    $('#opticalpowers').DataTable().ajax.reload(null, false);
                    $('#message-container').addClass('alert alert-success alert-dismissible');
                    $('#message-container').html(deletemessage + ' <button type="button" class="close" data-dismiss="alert">&times;</button>');
                    $('#message-container').show();
                } else {
                    deletemessagefail = deletemessagefail.replace(':oid', response.oid);
                    $('#message-container').addClass('alert alert-danger alert-dismissible');
                    $('#message-container').html(deletemessagefail + ' <button type="button" class="close" data-dismiss="alert">&times;</button>');
                    $('#message-container').show();
                }
                $button.prop('disabled', false);
                $button.html('<i class="fas fa-trash"></i>');
            }
        });
    });

    $('body').on('click','#rebootbtn',function(){
        //e.preventDefault();
        var $button = $(this);
        // console.log($button); // Check if $button is defined
        $button.prop('disabled', false); // Enable button
        $button.html('Rebooting...');
        // var route = $(this).data('routee');
        var route = "{{route('reboot.ont')}}";
        var variable = $(this).data('oid');
        var ont = $(this).data('ont');
        var user = $(this).data('user');
        var rebootmessage = '{{ $oprebootsuccessmsg }}';
        var rebootmessagefail = '{{ $oprebootfailmsg }}';
        $.ajax({
            type: 'GET',
            url: route,
            data: {
                oid: variable,
                ont: ont,
                user: user
            },
            success: function(response) {
                if(response.success){
                    rebootmessage = rebootmessage.replace(':oid', response.oid);
                    $('#opticalpowers').DataTable().ajax.reload(null, false);
                    $('#message-container').addClass('alert alert-success alert-dismissible');
                    $('#message-container').html(rebootmessage + ' <button type="button" class="close" data-dismiss="alert">&times;</button>');
                    $('#message-container').show();
                }else {
                    rebootmessagefail = rebootmessagefail.replace(':oid', response.oid);
                    $('#opticalpowers').DataTable().ajax.reload(null, false);
                    $('#message-container').addClass('alert alert-danger alert-dismissible');
                    $('#message-container').html(rebootmessagefail + ' <button type="button" class="close" data-dismiss="alert">&times;</button>');
                    $('#message-container').show();
                }
                $button.html('<i class="bi bi-power"></i>'); // Revert button text
                $button.prop('disabled', false);
            }
        });
    });

    $('body').on('click','#mailbtn',function(){
        //e.preventDefault();
        var $button = $(this);
        // console.log($button); // Check if $button is defined
        $button.prop('disabled', false); // Enable button
        $button.html('Processing...');
        var route = "{{route('send.op.mail')}}";
        // var variable = $(this).data('variable');
        $.ajax({
            type: 'GET',
            url: route,
            data: {},
            success: function(response) {
                if(response.success){
                // $('#opticalpowers').DataTable().ajax.reload(null, false);
                    $('#message-container').addClass('alert alert-success alert-dismissible');
                    $('#message-container').html('Mail Sent Successfully. Try after some time. <button type="button" class="close" data-dismiss="alert">&times;</button>');
                }else {
                    $('#message-container').addClass('alert alert-danger alert-dismissible');
                    $('#message-container').html('Mail Sent Failed. Try after some time. <button type="button" class="close" data-dismiss="alert">&times;</button>');
                }
                $button.html('<i class="bi bi-envelope"></i> Mail'); // Revert button text
                $button.prop('disabled', false);
            }
        });
    });
        

    // Get the search parameter from the URL and set the search box value 
        // let searchParam = getParameterByName('search');
        let searchParam = getParameterByName('name') || '';
        // let searchParam = {{ $search }};

        $('#searchBox').val(searchParam); 
        table.search(searchParam).draw(); 
        
        // Trigger search on DataTable when typing in the search box 
        $('#searchBox').on('keyup', function () { 
            let searchValue = this.value; 
            table.search(searchValue).draw(); 
            updateURLParameter('name', searchValue); 
        }); 
        
        // Function to get URL parameter 
        function getParameterByName(name) { name = name.replace(/[ \[\] ]/g, '\\$&'); 
        let regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'), 
        results = regex.exec(window.location.href); 
        
        if (!results) return null; 
        if (!results[2]) return ''; 
        return decodeURIComponent(results[2].replace(/\+/g, ' ')); 
        } 
        
        // Function to update the URL parameter and reload the page 
        function updateURLParameter(param, value) { 
            let url = new URL(window.location.href); 
            url.searchParams.set(param, value); 
            window.history.pushState({}, '', url); 
            location.reload(); }
    });
    

    function checkTaskStatus() { 
        $.ajax({ 
            url: '{{ route("task.status") }}', 
            method: 'GET', 
            success: function(response) { 
                const status = response.status; 
                // $('#taskStatus').text('Task Status: ' + status); 
                // $('#taskOutput').text(response.output); // Update button based on status 
                if (status === 'Enabled') { 
                    $('#manageTask').text('Disable Task').data('action', 'disable').show(); 
                } else if (status === 'Disabled') { 
                    $('#manageTask').text('Enable Task').data('action', 'enable').show(); 
                } 
            }, 
            error: function(xhr, status, error) { 
                console.error('Error checking task status:', error); 
                alert('Error checking task status: ' + xhr.responseJSON.error); 
            } 
        }); 
    }

    // Initial check on page load 
    $(document).ready(function() { 
        //checkTaskStatus();

        // Handle button click to enable/disable task 
        $('#manageTask').click(function() { 
            const action = $(this).data('action'); 
            $.ajax({ 
                url: '{{ route("task.manage") }}',
                method: 'POST', 
                data: { 
                    _token: '{{ csrf_token() }}', 
                    action: action 
                }, 
                success: function(response) { 
                    alert(response.message); // Re-check the status after managing the task 
                    checkTaskStatus(); 
                }, 
                error: function(xhr, status, error) { 
                    console.error('Error managing task:', error); 
                    alert('Error managing task: ' + xhr.responseJSON.error); 
                } 
            }); 
        }); 
    });

    </script>
{{-- @endif --}}
@endpush
