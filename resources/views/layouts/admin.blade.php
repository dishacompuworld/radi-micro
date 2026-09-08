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
        flex-wrap: wrap;
    }

    .mseb-container {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 0.3rem; /* Slightly smaller radius */
        padding: 0.4rem 0.75rem; /* Reduced padding for a more compact look */
        border: 1px solid rgba(255, 255, 255, 0.2);
        min-width: 150px; /* Give it a minimum width to prevent squishing */
        min-height: 40px;
        height: auto;
    }

    .navbar-status-menu {
        flex: 0 0 auto;
    }

    .navbar-status-menu .nav-item {
        flex: 0 0 auto;
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
        min-height: 40px;
        height: auto;
    }

    /* Target the text elements specifically inside the MSEB container */
    .temp-container span {
        font-size: 0.8rem !important; /* Smaller font for the "MSEB Status" label */
        font-weight: 600 !important;
    }

    .navbar-search-nav {
        min-width: 0;
        align-self: center;
    }

    .notification-menu {
        width: min(360px, calc(100vw - 2rem));
        max-height: 420px;
        overflow-y: auto;
    }

    .notification-item {
        white-space: normal;
        border-left: 3px solid transparent;
    }

    .notification-unread {
        background-color: rgba(105, 108, 255, 0.08);
        border-left-color: var(--bs-primary);
    }

    .notification-badge {
        position: absolute;
        top: 0.1rem;
        right: 0.05rem;
        min-width: 1.1rem;
        padding: 0.15rem 0.3rem;
        font-size: 0.65rem;
    }

    #navbar-search-form {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        width: 100%;
        min-width: 0;
        height: 2.375rem;
        align-self: center;
    }

    #navbar-search-form select,
    #navbar-search-form input[type="search"] {
        height: 2.375rem;
        margin-top: 0;
        margin-bottom: 0;
        align-self: center;
    }

    #navbar-search-form > .bx-search {
        flex: 0 0 1.25rem;
        text-align: center;
    }

    #navbar-search-form select {
        flex: 0 0 8.5rem;
        width: 8.5rem;
        min-width: 0;
        padding-right: 1.75rem;
    }

    #navbar-search-form input[type="search"] {
        flex: 1 1 auto;
        width: 1%;
        min-width: 0;
    }

    @media (max-width: 1199.98px) {
        #layout-navbar {
            align-items: center;
        }

        #navbar-collapse {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.5rem;
        }

        #navbar-collapse > .navbar-nav:first-of-type {
            grid-column: 1 / -1;
            min-width: 0;
            margin-right: 0 !important;
            align-self: center;
        }

        #navbar-search-form {
            width: 100%;
            min-width: 0;
            flex-wrap: nowrap;
        }

        #navbar-search-form select {
            flex: 0 1 42%;
            width: auto;
            max-width: 42%;
        }

        #navbar-search-form input[type="search"] {
            min-width: 0;
            flex: 1 1 auto;
        }

        #navbar-collapse > .user-menu {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-column: 1;
            gap: 0.5rem;
            min-width: 0;
            margin-right: 0 !important;
        }

        #navbar-collapse > .navbar-status-menu {
            display: flex;
            grid-column: 1 / -1;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-right: 0 !important;
        }

        .user-menu .nav-item {
            min-width: 0;
            width: 100%;
        }

        .temp-container,
        .mseb-container {
            width: 100%;
            min-width: 0;
        }

        .navbar-status-menu .nav-item {
            flex: 1 1 0;
            min-width: 0;
        }

        .temp-container .d-flex,
        .mseb-container .d-flex {
            flex-wrap: wrap;
            gap: 0.25rem 0.5rem;
        }

        #mseb-uptime {
            overflow-wrap: anywhere;
        }
    }

    @media (max-width: 575.98px) {
        #layout-navbar {
            padding: 0.75rem;
        }

        #navbar-search-form select {
            max-width: 45%;
        }

        #navbar-collapse {
            grid-template-columns: 1fr;
        }

        #navbar-collapse > .user-menu {
            grid-column: 1;
        }

        .user-menu .nav-item {
            width: 100%;
        }
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
