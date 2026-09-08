<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|space-grotesk:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        {{-- @vite(['resources/sass/app.scss', 'resources/js/app.js']) --}}
        @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <main class="auth-page">
            <section class="auth-visual" aria-label="Disha CompuWorld network management">
                <div class="auth-grid"></div>
                <div class="auth-scanline"></div>
                <div class="auth-visual-content">
                    <a href="/" class="auth-brand" aria-label="Disha CompuWorld home">
                        <img src="{{ asset('assets/img/dishcompuworldlogo.png') }}" alt="Disha CompuWorld" class="auth-logo">
                    </a>
                    <div class="auth-visual-copy">
                        <p class="auth-kicker"><span class="auth-live-dot"></span> Router operations / online</p>
                        <h1>Everything connected.<br><em>Nothing overlooked.</em></h1>
                        <p class="auth-description">A clearer command center for the networks that keep your business moving.</p>
                    </div>
                    <div class="auth-status-row" aria-hidden="true">
                        <span><strong>24</strong> active nodes</span>
                        <span><strong>99.9%</strong> uptime</span>
                        <span class="auth-signal"><i></i><i></i><i></i><i></i><i></i></span>
                    </div>
                </div>
            </section>

            <section class="auth-panel">
                <div class="auth-form-wrap">
                    {{ $slot }}
                </div>
                <p class="auth-footer">Protected access <span></span> Disha CompuWorld</p>
            </section>
        </main>

        <style>
            :root { --navy: #122c47; --paper: #f7f8f5; }
            .auth-page { min-height: 100vh; display: grid; grid-template-columns: minmax(420px, 1.05fr) minmax(420px, .95fr); background: var(--paper); font-family: 'Figtree', sans-serif; }
            .auth-visual { position: relative; overflow: hidden; background: var(--navy); color: #f4f6f3; isolation: isolate; }
            .auth-grid { position: absolute; inset: 0; opacity: .18; background-image: linear-gradient(rgba(171, 220, 241, .3) 1px, transparent 1px), linear-gradient(90deg, rgba(171, 220, 241, .3) 1px, transparent 1px); background-size: 48px 48px; mask-image: linear-gradient(to bottom right, black, transparent 80%); animation: drift 18s linear infinite; }
            .auth-scanline { position: absolute; z-index: -1; width: 70vw; aspect-ratio: 1; right: -42%; top: -15%; border: 1px solid rgba(117, 194, 224, .35); border-radius: 50%; box-shadow: 0 0 0 42px rgba(117, 194, 224, .06), 0 0 0 84px rgba(117, 194, 224, .045), 0 0 0 126px rgba(117, 194, 224, .03); animation: breathe 7s ease-in-out infinite; }
            .auth-visual-content { position: relative; z-index: 1; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; padding: clamp(32px, 6vw, 84px); }
            .auth-brand { display: inline-flex; width: fit-content; padding: 10px 14px; background: #fff; box-shadow: 9px 9px 0 rgba(8, 20, 34, .25); transform: rotate(-2deg); transition: transform .25s ease; }
            .auth-brand:hover { transform: rotate(0deg) translateY(-2px); }
            .auth-logo { display: block; width: clamp(150px, 14vw, 205px); height: auto; }
            .auth-visual-copy { max-width: 570px; margin: auto 0; animation: rise .8s ease both; }
            .auth-kicker { color: #9ed8e4; font: 600 11px/1.2 'Space Grotesk', sans-serif; letter-spacing: .16em; text-transform: uppercase; }
            .auth-live-dot { display: inline-block; width: 7px; height: 7px; margin-right: 8px; border-radius: 50%; background: #73d1af; box-shadow: 0 0 0 5px rgba(115, 209, 175, .12); animation: blink 2s ease-in-out infinite; }
            .auth-visual h1 { margin: 23px 0 22px; color: #fff; font: 600 clamp(38px, 4.8vw, 70px)/.99 'Space Grotesk', sans-serif; letter-spacing: 0; }
            .auth-visual h1 em { color: #89cddd; font-style: normal; }
            .auth-description { max-width: 400px; color: #adc0ca; font-size: 16px; line-height: 1.7; }
            .auth-status-row { display: flex; align-items: center; gap: clamp(18px, 3vw, 42px); color: #8ea8b5; font: 500 11px/1.2 'Space Grotesk', sans-serif; letter-spacing: .05em; text-transform: uppercase; }
            .auth-status-row strong { display: block; margin-bottom: 5px; color: #f1f6f3; font-size: 19px; letter-spacing: 0; }
            .auth-signal { display: flex; align-items: end; gap: 3px; height: 19px; margin-left: auto; }
            .auth-signal i { display: block; width: 4px; background: #7bd0d8; animation: signal 1.3s ease-in-out infinite alternate; }
            .auth-signal i:nth-child(1) { height: 6px; } .auth-signal i:nth-child(2) { height: 10px; animation-delay: .15s; } .auth-signal i:nth-child(3) { height: 15px; animation-delay: .3s; } .auth-signal i:nth-child(4) { height: 12px; animation-delay: .45s; } .auth-signal i:nth-child(5) { height: 19px; animation-delay: .6s; }
            .auth-panel { display: flex; flex-direction: column; justify-content: center; padding: clamp(32px, 7vw, 112px); background: var(--paper); }
            .auth-form-wrap { width: 100%; max-width: 410px; margin: 0 auto; }
            .auth-footer { max-width: 410px; width: 100%; margin: clamp(45px, 8vh, 90px) auto 0; color: #93a0a7; font: 500 10px/1.2 'Space Grotesk', sans-serif; letter-spacing: .12em; text-transform: uppercase; }
            .auth-footer span { display: inline-block; width: 3px; height: 3px; margin: 0 9px 2px; border-radius: 50%; background: #9dc8ca; }
            @keyframes drift { to { background-position: 48px 48px; } } @keyframes breathe { 50% { transform: scale(1.06); opacity: .7; } } @keyframes rise { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } } @keyframes blink { 50% { opacity: .35; } } @keyframes signal { to { opacity: .45; transform: scaleY(.7); } }
            @media (max-width: 800px) { .auth-page { display: block; } .auth-visual-content { min-height: 54vh; padding: 30px 26px 28px; } .auth-visual-copy { margin: 70px 0 50px; } .auth-visual h1 { font-size: clamp(38px, 11vw, 56px); } .auth-status-row { gap: 18px; } .auth-panel { min-height: 46vh; padding: 46px 26px 32px; } .auth-footer { margin-top: 45px; } }
            @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; } }
        </style>
        </div>
    </body>
</html>
