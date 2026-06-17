
@extends('layouts.admin')


@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-3">Live Graph</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">PRTG</li>
                        <li class="breadcrumb-item active">Live Graph</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <!-- Controls Section -->
                <div class="card-body pb-0">
                    <div class="mb-3">
                        @if (session('msg'))
                            <label class="badge badge-success">{{ session('msg') }}</label>
                        @endif

                        @if (isset($error))
                            <p class="text-danger mb-0">{{ $error }}</p>
                        @endif
                    </div>
                    
                    <!-- Refresh controls with checkbox toggle -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="form-check me-3">
                            <input type="checkbox" class="form-check-input" id="refreshToggle" checked>
                            <label class="form-check-label" for="refreshToggle">Enable Auto-Refresh (10s)</label>
                        </div>
                        <small class="text-muted ms-2">Last updated: <span id="last-update-time">Now</span></small>
                        <!-- Manual refresh button -->
                        <button id="manualRefresh" class="btn btn-sm btn-outline-primary ms-auto">
                            <i class="fa fa-refresh"></i> Refresh Now
                        </button>
                    </div>
                </div>

                <!-- Graph Section -->
                <div class="card-body">
                    <div id="svg-container" class="d-flex justify-content-center align-items-center bg-light rounded" style="min-height: 500px;">
                        <img id="live-graph-img" src="{{ route('live.graph.image') }}?t={{ time() }}" class="w-100" style="height: 100%; max-height: 500px; object-fit: contain;" alt="Live Graph">
                    </div>
                </div>
            </div>
            
            <a href="javascript: history.back()" class="btn btn-primary btn-sm mt-3">Back</a>
        </div>
    </div>
</div>

@endsection

@push('page-ps')

<script>
        let autoRefreshEnabled = true;
    let refreshInterval = null;

    function refreshSvg() {
        const imgElement = document.getElementById('live-graph-img');
        const timestamp = new Date().getTime();
        
        // Simply update the image source with a new timestamp to bypass cache
        imgElement.src = '{{ route("live.graph.image") }}?t=' + timestamp;
        updateLastRefreshTime();
    }

    function updateLastRefreshTime() {
        const timeElement = document.getElementById('last-update-time');
        const now = new Date();
        timeElement.textContent = now.toLocaleTimeString();
    }

    function startAutoRefresh() {
        stopAutoRefresh(); // Clear any existing interval
        refreshInterval = setInterval(() => {
            if (autoRefreshEnabled) {
                refreshSvg();
            }
        }, 10000);
    }

    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', () => {
        const refreshToggle = document.getElementById('refreshToggle');
        const manualRefresh = document.getElementById('manualRefresh');

        refreshToggle.addEventListener('change', function() {
            autoRefreshEnabled = this.checked;
            if (autoRefreshEnabled) {
                startAutoRefresh();
            } else {
                stopAutoRefresh();
            }
        });

        manualRefresh.addEventListener('click', refreshSvg);

        // Start auto-refresh on page load
        refreshToggle.checked = true;
        startAutoRefresh();
    });
</script>

@endpush  
