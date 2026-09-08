@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h4 class="mb-1">Notifications</h4>
                <p class="text-muted mb-0">PRTG alerts and system notifications</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($unreadNotificationCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bx bx-check-double me-1"></i>Mark all as read
                        </button>
                    </form>
                @endif
                @if($totalNotificationCount > 0)
                    <form method="POST" action="{{ route('notifications.delete-all') }}" onsubmit="return confirm('Delete all notifications? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bx bx-trash me-1"></i>Delete all notifications
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="text-muted d-block mb-1">Total notifications</span>
                        <h3 class="mb-0">{{ $totalNotificationCount }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="text-muted d-block mb-1">Unread notifications</span>
                        <h3 class="mb-0 text-primary">{{ $unreadNotificationCount }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="list-group list-group-flush">
                @forelse($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $status = $data['status'] ?? 'Alert';
                        $device = $data['device'] ?? 'Unknown device';
                        $sensor = $data['sensor'] ?? 'Unknown sensor';
                        $message = $data['message'] ?? '';
                    @endphp
                    <a href="{{ route('notifications.read', $notification) }}" class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'bg-light' }}">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bx {{ $notification->read_at ? 'bx-envelope-open' : 'bx-error-circle' }} fs-3 text-{{ strtolower($status) === 'up' ? 'success' : 'danger' }}"></i>
                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <h6 class="mb-1">{{ $status }} on {{ $device }}</h6>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="text-muted">Sensor: {{ $sensor }}</div>
                                @if($message)
                                    <div class="text-muted">{{ $message }}</div>
                                @endif
                                <small class="{{ $notification->read_at ? 'text-muted' : 'text-primary fw-semibold' }}">{{ $notification->read_at ? 'Read' : 'Unread' }}</small>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-4 text-center text-muted">No notifications yet.</div>
                @endforelse
            </div>
            @if($notifications->hasPages())
                <div class="card-footer">{{ $notifications->links() }}</div>
            @endif
        </div>
    </div>
@endsection
