
@extends('layouts.admin')


@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">All Sensors</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">PRTG</li>
                        <li class="breadcrumb-item active">All Sensors</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>


    <div class="row">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table id="all-sensors" class="datatable table table-striped table-bordered table-hover table-center mb-0">
                <thead>
                  <tr>
                    <th>Datetime</th>
                    <th>MainSensor</th>
                    <th>Type</th>
                    <th>Status</th>
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
{{-- @if($location) --}}
<script>
    $(document).ready(function() {
        var table = $('#all-sensors').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('ptrg.messsages') }}",
            columns: [
                {data: 'datetime', name: 'datetime', orderable: false,},
                {data: 'parent', name: 'parent', orderable: false, searchable: true},
				{data: 'type', name: 'type', orderable: false, searchable: true},
                {data: 'status', name: 'status', orderable: false, searchable: true},
                {data: 'message_raw', name: 'message_raw', orderable: false, searchable: true},
            ],
            aaSorting: [],
            pageLength: 100,
            createdRow: function(row, data, dataIndex) {
                var statusCell = $(row).find('td:eq(3)'); // Status is 4th column (0-based)
                if (data.status.toLowerCase().includes('delet')) {
                    statusCell.addClass('text-danger');
                } else if (data.status === 'Up') {
                    statusCell.addClass('text-success'); 
                } else if (data.status === 'Warning') {
                    statusCell.addClass('text-warning');
                } else if (data.status === 'Down') {
                    statusCell.addClass('text-danger');
                }
            },
        });
        //
    });
</script>
{{-- @endif --}}
@endpush