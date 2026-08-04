@php
$info = exec('systeminfo | find /i "Boot Time"');
$timeee = trim(str_replace("System Boot Time:", "", $info));

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
$systemdate = new DateTime($timeee);
$diff = $now->diff($systemdate);
$uptime = format_interval($diff);

// if ($msebstatus['success'] && $msebstatus['statustext'] == 'Up') {
//     $mseb = 'Up';
//     $updowntime = $msebstatus['uptime'];
// } else {
//     $mseb = 'Down';
//     $updowntime = $msebstatus['downtime'];
// }

// echo $temp[];

// $bgClass = $mseb == 'Up' ? 'bg-success' : ($mseb == 'Down' ? 'bg-danger' : 'bg-secondary');
// $bgOpacity = 'bg-opacity-25';

@endphp

@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-7 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex flex-column flex-sm-row align-items-center gap-3">
                        <div>
                            <h5 class="card-title text-primary mb-2">Welcome back {{ auth()->user()->name }} 🎉</h5>
                            <p class="mb-0 text-muted">Have a nice day! Here's what's happening with your network today.</p>
                        </div>
                        <div class="ms-auto text-center">
                            <img src="{{ asset('assets/img/avatar.png') }}" class="img-fluid rounded-circle" width="65" height="65" alt="User avatar" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="bx bx-history fs-2 text-primary"></i>
                        <div>
                            <h6 class="card-title text-primary mb-1">Server Uptime</h6>
                            <p class="mb-0 text-muted">{{ $systemdate->format('d M Y, h:i:s A') }}</p>
                            <span class="fw-semibold">{{ $uptime }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{$total_servers}}</h3>
                            <p class="mb-0 text-muted">Live Servers</p>
                        </div>
                        <i class="bx bx-server fs-2 text-success"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{$total_locations}}</h3>
                            <p class="mb-0 text-muted">Active Locations</p>
                        </div>
                        <i class="bx bx-map fs-2 text-primary"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{$upsensorcount}} / {{$totalsensorcount}}</h3>
                            <p class="mb-0 text-muted">Up Sensors</p>
                        </div>
                        <i class="bx bx-check-circle fs-2 text-warning"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{$totalactiveuserc}}</h3>
                            <p class="mb-0 text-muted">Active Radius Users</p>
                        </div>
                        <i class="bx bx-group fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{$userss}}</h3>
                            <p class="mb-0 text-muted">Microtik Users</p>
                        </div>
                        <i class="bx bx-user fs-2 text-secondary"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{\DB::table('opticalpowers')->count()}}</h3>
                            <p class="mb-0 text-muted">Total ONTs</p>
                        </div>
                        <i class="bx bx-wifi fs-2 text-success"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-danger mb-1">{{$disabledsubscribers}}</h3>
                            <p class="mb-0 text-muted">Disabled Users</p>
                        </div>
                        <i class="bx bx-block fs-2 text-danger"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 mb-4 order-0">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title text-success mb-1">{{$total_servers}}</h3>
                            <p class="mb-0 text-muted">Live Servers</p>
                        </div>
                        <i class="bx bx-server fs-2 text-success"></i>
                    </div>
                </div>
            </div>
            
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title mb-0">All Subscriber Count</h5>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-1">
                            <form id="subscriberFilterForm" class="d-flex flex-wrap align-items-center gap-1" style="flex-wrap: nowrap;">
                                <input type="hidden" name="view" value="{{ request('view', 'datewise') }}">
                                <select name="preset" class="form-select form-select-sm" style="min-width: 92px; max-width: 92px;">
                                    <option value="current-year" {{ $selectedPreset === 'current-year' ? 'selected' : '' }}>This Year</option>
                                    <option value="previous-year" {{ $selectedPreset === 'previous-year' ? 'selected' : '' }}>Prev Year</option>
                                    <option value="last-month" {{ $selectedPreset === 'last-month' ? 'selected' : '' }}>Last Month</option>
                                    <option value="current-month" {{ $selectedPreset === 'current-month' ? 'selected' : '' }}>This Month</option>
                                    <option value="custom" {{ $selectedPreset === 'custom' ? 'selected' : '' }}>Custom</option>
                                    <option value="all" {{ $selectedPreset === 'all' ? 'selected' : '' }}>All</option>
                                </select>
                                <select id="subscriberYearSelect" name="year" class="form-select form-select-sm" style="min-width: 70px; max-width: 70px;">
                                    @foreach($availableYears as $year)
                                        <option value="{{ $year }}" {{ (int) $selectedYear === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                                <input id="subscriberFromDate" type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate ?? '' }}" style="width: 110px; min-width: 110px;">
                                <input id="subscriberToDate" type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate ?? '' }}" style="width: 110px; min-width: 110px;">
                                <button type="submit" class="btn btn-sm btn-primary" style="padding: 0.25rem 0.5rem;">Go</button>
                            </form>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Subscriber view toggle">
                                <button type="button" class="btn btn-outline-primary {{ request('view', 'datewise') === 'datewise' ? 'active' : '' }}" data-view="datewise">Datewise</button>
                                <button type="button" class="btn btn-outline-primary {{ request('view', 'monthwise') ? 'active' : '' }}" data-view="monthwise">Monthwise</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="subscriberChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartContainer = document.getElementById('subscriberChart');
            const buttons = document.querySelectorAll('[data-view]');
            const filterForm = document.getElementById('subscriberFilterForm');
            const presetSelect = filterForm ? filterForm.querySelector('select[name="preset"]') : null;
            const yearSelect = document.getElementById('subscriberYearSelect');
            const fromDateInput = document.getElementById('subscriberFromDate');
            const toDateInput = document.getElementById('subscriberToDate');

            if (!chartContainer) {
                return;
            }

            const initialView = '{{ request('view', 'datewise') }}';
            let chart = null;
            let chartData = @json($subscriberChartData);

            function toggleFilterControls() {
                if (!presetSelect || !yearSelect || !fromDateInput || !toDateInput) {
                    return;
                }

                const preset = presetSelect.value;
                const showYear = preset === 'current-year' || preset === 'previous-year';
                const showCustomDates = preset === 'custom';

                yearSelect.style.display = showYear ? '' : 'none';
                yearSelect.disabled = !showYear;
                fromDateInput.style.display = showCustomDates ? '' : 'none';
                toDateInput.style.display = showCustomDates ? '' : 'none';
            }

            function sortChartData(data) {
                const labels = Array.isArray(data.labels) ? data.labels : [];
                const series = Array.isArray(data.series) ? data.series : [];
                const pairs = labels.map(function (label, index) {
                    return {
                        label: label,
                        value: series[index] !== undefined ? series[index] : 0,
                        sortValue: Date.parse(label)
                    };
                }).filter(function (item) {
                    return !isNaN(item.sortValue);
                });

                if (pairs.length === 0) {
                    return {
                        labels: labels,
                        series: series
                    };
                }

                pairs.sort(function (a, b) {
                    return a.sortValue - b.sortValue;
                });

                return {
                    labels: pairs.map(function (item) { return item.label; }),
                    series: pairs.map(function (item) { return item.value; })
                };
            }

            function renderChart(view) {
                const viewKey = view === 'monthwise' ? 'monthwise' : 'datewise';
                const currentData = chartData && chartData[viewKey] ? sortChartData(chartData[viewKey]) : { labels: [], series: [] };

                if (chart) {
                    chart.destroy();
                }

                chart = new ApexCharts(chartContainer, {
                    chart: {
                        type: 'line',
                        height: 340,
                        toolbar: { show: false },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 1000,
                            animateGradually: { enabled: true, delay: 150 },
                            dynamicAnimation: { enabled: true, speed: 1000 }
                        }
                    },
                    series: [{ name: 'Subscribers', data: currentData.series }],
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 4 },
                    colors: ['#696cff'],
                    xaxis: {
                        categories: currentData.labels,
                        labels: { rotate: view === 'monthwise' ? 0 : -35 }
                    },
                    yaxis: {
                        labels: {
                            formatter: function (value) {
                                return Math.round(value).toString();
                            }
                        }
                    },
                    tooltip: { shared: true, intersect: false },
                    grid: { borderColor: '#e7e7e7' }
                });

                chart.render();
            }

            toggleFilterControls();
            renderChart(initialView);

            if (presetSelect) {
                presetSelect.addEventListener('change', toggleFilterControls);
            }

            if (filterForm) {
                filterForm.addEventListener('submit', function (event) {
                    event.preventDefault();

                    const formData = new FormData(filterForm);
                    const params = new URLSearchParams(formData);
                    const viewInput = filterForm.querySelector('input[name="view"]');
                    if (viewInput) {
                        params.set('view', viewInput.value);
                    }

                    const url = `{{ route('dashboard.subscriber.chart') }}?${params.toString()}`;

                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        chartData = data;
                        const currentView = (viewInput ? viewInput.value : initialView);
                        renderChart(currentView);
                    })
                    .catch(function (error) {
                        console.error('Subscriber chart update failed.', error);
                    });
                });
            }

            buttons.forEach(function (button) {
                button.addEventListener('click', function () {
                    buttons.forEach(function (item) {
                        item.classList.remove('active');
                    });

                    button.classList.add('active');
                    const view = button.getAttribute('data-view');
                    const data = chartData[view];
                    const form = document.getElementById('subscriberFilterForm');
                    if (form) {
                        const viewInput = form.querySelector('input[name="view"]');
                        if (viewInput) {
                            viewInput.value = view;
                        }
                    }

                    renderChart(view);
                });
            });
        });
    </script>

@endsection