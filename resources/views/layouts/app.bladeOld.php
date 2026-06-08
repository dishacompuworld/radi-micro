<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
        @vite(['resources/css/app.css', 'resources/sass/app.scss'])
        <script src="//unpkg.com/alpinejs" defer></script>
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
		<!-- Fontawesome CSS -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

            {{-- <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset --}}

            <!-- Header -->
        @include('includes.header')
        <!-- /Header -->

        <!-- Sidebar -->
        @auth
            @include('includes.sidebar')
        @endauth
        <!-- /Sidebar -->


        <!-- Page Wrapper -->
        <div class="@auth page-wrapper @endauth">

            <div class="content container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="row">
                        @stack('page-header')
                    </div>
                </div>
                <!-- /Page Header -->
                {{-- @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <x-alerts.danger :error="$error" />
                    @endforeach
                @endif --}}

                @yield('content')
            </div>
        </div>
        <!-- /Page Wrapper -->


            <!-- Page Content -->
            {{ $slot }}
        </div>
        
		<!-- jQuery -->
        <script src="{{asset('assets/js/jquery-3.6.0.min.js')}}"></script>
		
		<!-- Slimscroll JS -->
        <script src="{{asset('assets/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>
		<script src="{{asset('assets/js/script.js')}}"></script>
    </body>
</html>
