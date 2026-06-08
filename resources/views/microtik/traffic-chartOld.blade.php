<!-- filepath: /c:/inetpub/wwwroot/microtik-radius/resources/views/admin/microtik/traffic-chart.blade.php -->
@extends('admin.layouts.header')

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

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Traffic Chart</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Traffic Chart</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <canvas id="traffic-chart"></canvas>
                <button class="btn btn-sm btn-primary mt-3" onclick="window.history.back()">Back</button>
                <button class="btn btn-sm btn-success mt-3" onclick="sendChartToWhatsApp()">Send to WhatsApp</button>
            </div>
        </div>
    </div>
</div>

<!-- Include jQuery and Chart.js -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let chart;
    let chartData = {
        labels: [],
        datasets: [
            {
                label: 'Upload Mbps',
                data: [],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                tension: 0.4 // Add tension for smooth curves
            },
            {
                label: 'Download Mbps',
                data: [],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                tension: 0.4 // Add tension for smooth curves
            }
        ]
    };
    let updateInterval;

    function fetchTrafficData() {
        const serverId = '{{ $serverId }}';
        const username = '{{ $username }}';
        const today = new Date().toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });

        function updateChart() {
            fetch(`ppp-traffic?server=${serverId}&interface=${username}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    const timestamp = new Date().toLocaleTimeString();
                    chartData.labels.push(timestamp);
                    chartData.datasets[0].data.push(data['rx-bits-per-second']);
                    chartData.datasets[1].data.push(data['tx-bits-per-second']);

                    // Keep the last 50 data points
                    if (chartData.labels.length > 50) {
                        chartData.labels.shift();
                        chartData.datasets[0].data.shift();
                        chartData.datasets[1].data.shift();
                    }

                    chart.update();
                })
                .catch(error => console.error('Error fetching live traffic data:', error));
        }

        const ctx = document.getElementById('traffic-chart').getContext('2d');
        if (chart) {
            chart.destroy();
        }
        chart = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: ''
                    }
                }
            },
            plugins: [{
                id: 'customTitle',
                afterDraw: (chart) => {
                    const ctx = chart.ctx;
                    ctx.save();
                    ctx.font = '20px Arial';
                    ctx.fillStyle = '#a0a0a0'; // Color for username
                    ctx.textAlign = 'left';
                    ctx.fillText(`User: ${username}`, 10, 10); // Align left
                    ctx.font = '16px Arial';
                    ctx.fillStyle = '#89f4ea'; // Color for date
                    ctx.textAlign = 'right';
                    ctx.fillText(`Date: ${today}`, chart.width - 10, 10); // Align right
                    ctx.restore();
                }
            }]
        });

        updateChart();
        updateInterval = setInterval(updateChart, 5000); // Update every 5 seconds
    }

    fetchTrafficData();

    function sendChartToWhatsApp() {
        const imageData = generateChartImage();
        uploadChartImage(imageData).then(imageUrl => {
            const recipientNumber = prompt("Enter the recipient's WhatsApp number (with country code):");
            if (recipientNumber) {
                sendImageToWhatsApp(imageUrl, recipientNumber);
            }
        });
    }

    function generateChartImage() {
        const canvas = document.getElementById('traffic-chart');
        return canvas.toDataURL('image/png');
    }

    function uploadChartImage(imageData) {
        return $.ajax({
            url: 'upload-chart-image', // Your server endpoint to handle the image upload
            method: 'POST',
            data: {
                image: imageData
            },
            success: function(response) {
                return response.imageUrl; // The URL of the uploaded image
            },
            error: function(error) {
                console.error('Error uploading image:', error);
            }
        });
    }

    function sendImageToWhatsApp(imageUrl, recipientNumber) {
        const data = {
            number: recipientNumber,
            image_url: imageUrl
        };

        $.ajax({
            url: 'send-image', // Your server endpoint to handle sending the image
            method: 'POST',
            data: data,
            success: function(response) {
                console.log('Image sent successfully:', response);
            },
            error: function(error) {
                console.error('Error sending image:', error);
            }
        });
    }
</script>
@endsection