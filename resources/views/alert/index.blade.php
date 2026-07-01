@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
    <div class="col-md-8 d-flex flex-column justify-content-center">
        <h4 class="mb-3">Manage Alert Messages</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">Microtik</li>
                <li class="breadcrumb-item active">Alert Messages</li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 d-flex justify-content-end align-items-center">
        <a href="{{ route('alert.create') }}" class="btn btn-primary mt-2">New Alert</a>
    </div>
</div>


    <div id="message-container" style="display: none;" class="col-sm-12"></div>
{{-- <div class="container"> --}}
    {{-- <div class="row justify-content-center"> --}}
        <div class="col-md-12">
            {{-- <div class="card"> --}}

                {{-- <div class="card-body"> --}}
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                {{-- </div> --}}
            {{-- </div> --}}
        </div>
    {{-- </div> --}}


{{-- </div> --}}

<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="alertmsg" class="datatable table table-striped table-bordered table-hover table-center mb-0">
						<thead>
							<tr style="boder:1px solid black;">
								<th>ID</th>
                                <th>Type</th>
                                <th>Catagory</th>
                                <th>Msg Code</th>
                                <th>Message</th>
                                <th>Updated At</th>
                                <th>Actions</th>
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
<script>
    $(document).ready(function() {
        var table = $('#alertmsg').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('alert.index') }}",
            columns: [
                {data: 'id', name: 'id', searchable: false,},
                {data: 'type', name: 'type', orderable: false,},
                {data: 'category', name: 'category', orderable: false,},
                {data: 'msgcode', name: 'msgcode', orderable: false,},
                {data: 'message', name: 'message', orderable: false,}, 
                {data: 'updated', name: 'updated', orderable: false,searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false,},
            ],
            aaSorting: [],
            pageLength: 100,
        });

        $('body').on('click','#delete-btn',function(){
            // e.preventDefault();
            var id = $(this).data('id'); // assuming you have a data-id attribute on the button
            var $button = $(this);
            // console.log($button); // Check if $button is defined
            $button.prop('disabled', false); // Enable button
            $button.html('Deleting...');

            $.ajax({
                type: 'DELETE',
                url: '{{ url("alert-messages") }}/' + id,
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    // console.log(data);
                    if(response.success){
                        // $('#alertmsg').DataTable().ajax.reload(null, false);
                        $('#message-container').addClass('alert alert-success alert-dismissible');
                        $('#message-container').html('Deleted Successfully!') + '. <button type="button" class="close" data-dismiss="alert">&times;</button>';
                        $('#message-container').show();
                    }else{
                        // $('#alertmsg').DataTable().ajax.reload(null, false);
                        $('#message-container').addClass('alert alert-danger alert-dismissible');
                        $('#message-container').html('Error!') + '. <button type="button" class="close" data-dismiss="alert">&times;</button>';
                        $('#message-container').show();
                    }
                    $('#alertmsg').DataTable().ajax.reload(null, false);
                    // $button.html('Delete'); // Revert button text
                    // $button.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush