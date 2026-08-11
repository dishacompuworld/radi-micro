<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ appSettings('app_name', config('app.name', 'App')) }} - {{ ucfirst($title ?? '') }}</title>

         <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

        <link rel="stylesheet" href="{{ asset('assets/vendor/core.css') }}" class="template-customizer-core-css" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/theme-default.css') }}" class="template-customizer-theme-css" />
        <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

        <!-- Vendors CSS -->
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />

        <!-- Page CSS -->

        <!-- Helpers -->
        <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>

        <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
        <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
        <script src="{{ asset('assets/js/config.js') }}"></script>

        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css" />

        <style>
    /* Ensure all items in the user menu are vertically centered */
    .user-menu {
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    .mseb-container {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 0.3rem; /* Slightly smaller radius */
        padding: 0.4rem 0.75rem; /* Reduced padding for a more compact look */
        border: 1px solid rgba(255, 255, 255, 0.2);
        min-width: 150px; /* Give it a minimum width to prevent squishing */
        height: 40px;
    }

    /* Target the text elements specifically inside the MSEB container */
    .mseb-container span {
        font-size: 0.8rem !important; /* Smaller font for the "MSEB Status" label */
    }
    .mseb-container h6 {
        font-size: 0.8rem !important; /* Smaller font for the status text 'Up'/'Down' */
        /* font-weight: 600 !important; */
        /* line-height: 1.2; */
    }

	.temp-container {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 0.3rem; /* Slightly smaller radius */
        padding: 0.4rem 0.75rem; /* Reduced padding for a more compact look */
        border: 1px solid rgba(255, 255, 255, 0.2);
        min-width: 150px; /* Give it a minimum width to prevent squishing */
        height: 40px;
    }

    /* Target the text elements specifically inside the MSEB container */
    .temp-container span {
        font-size: 0.8rem !important; /* Smaller font for the "MSEB Status" label */
        font-weight: 600 !important;
    }

</style>
        
    </head>
    <body>
        <!-- Layout wrapper -->
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                
                @include('layouts.partials.sidebar')

                <!-- Layout container -->
            <div class="layout-page">
            <!-- Navbar -->
                @include('layouts.partials.navbar')

                
                <div class="content-wrapper d-flex flex-column">
                    <div class="flex-grow-1">
                        @yield('content')
                    </div>
                    @include('layouts.partials.footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
            
        </div>


        <!-- Core JS -->
        <!-- build:js assets/vendor/js/core.js -->
        <script src="{{ asset('assets/vendor/libs/jquery/jquery.js')}}"></script>
        <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
        <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

        <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
        <!-- endbuild -->

        <!-- Vendors JS -->
        <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

        <!-- Main JS -->
        <script src="{{ asset('assets/js/main.js') }}"></script>

        <!-- Page JS -->
        <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>

        <!-- Place this tag in your head or just before your close body tag. -->
        <script async defer src="https://buttons.github.io/buttons.js"></script>

        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/2.3.8/js/dataTables.min.js"></script>

        <!-- Page Scripts Stack -->
        @stack('page-js')
    </body>
</html>
