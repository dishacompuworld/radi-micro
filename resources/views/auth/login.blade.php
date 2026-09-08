<x-guest-layout>
    <div class="auth-heading">
        <p class="auth-eyebrow">Welcome back</p>
        <h2>Sign in to your workspace</h2>
        <p class="auth-subheading">Enter your details to continue to the network console.</p>
    </div>

    <x-auth-session-status class="auth-session-status" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="auth-field">
            <x-input-label for="email" :value="__('Email address')" class="auth-label" />
            <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>

        <!-- Password -->
        <div class="auth-field auth-field-password">
            <x-input-label for="password" :value="__('Password')" class="auth-label" />

            <x-text-input id="password" class="auth-input"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="Enter your password" />

            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

        <!-- Remember Me -->
        <div class="auth-options">
            <label for="remember_me" class="auth-remember">
                <input id="remember_me" type="checkbox" name="remember">
                <span>{{ __('Remember me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="auth-forgot" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="auth-submit">
            <span>{{ __('Log in') }}</span>
            <span class="auth-arrow" aria-hidden="true">&#8594;</span>
        </x-primary-button>
    </form>

    <style>
        .auth-heading { margin-bottom: 43px; }
        .auth-eyebrow { margin: 0 0 13px; color: #2b829c; font: 700 11px/1.2 'Space Grotesk', sans-serif; letter-spacing: .17em; text-transform: uppercase; }
        .auth-heading h2 { margin: 0; color: #102033; font: 600 clamp(28px, 3vw, 38px)/1.08 'Space Grotesk', sans-serif; letter-spacing: 0; }
        .auth-subheading { margin: 15px 0 0; color: #81909a; font-size: 14px; line-height: 1.6; }
        .auth-session-status { margin-bottom: 20px; color: #28775d; font-size: 13px; }
        .auth-field { margin-bottom: 23px; }
        .auth-field-password { margin-top: 3px; }
        .auth-label { display: block; margin-bottom: 9px; color: #405362; font: 600 11px/1.2 'Space Grotesk', sans-serif; letter-spacing: .1em; text-transform: uppercase; }
        .auth-input { width: 100%; height: 54px; border: 1px solid #d7e0df; border-radius: 0; background: #fff; color: #102033; padding: 0 16px; font-size: 14px; box-shadow: 4px 4px 0 transparent; transition: border-color .2s ease, box-shadow .2s ease; }
        .auth-input::placeholder { color: #b3bec0; }
        .auth-input:focus { border-color: #318ba3; outline: none; box-shadow: 4px 4px 0 #c4e3e2; }
        .auth-error { display: block; margin-top: 7px; color: #bd5d55; font-size: 12px; }
        .auth-options { display: flex; align-items: center; justify-content: space-between; margin: 30px 0 31px; }
        .auth-remember { display: inline-flex; align-items: center; gap: 9px; color: #78868d; font-size: 12px; cursor: pointer; }
        .auth-remember input { width: 16px; height: 16px; border: 1px solid #cbd7d6; border-radius: 0; accent-color: #2b829c; }
        .auth-forgot { color: #26738b; font-size: 12px; text-decoration: none; transition: color .2s ease; }
        .auth-forgot:hover { color: #102033; }
        .auth-submit { display: flex; align-items: center; justify-content: space-between; width: 100%; height: 56px; border: 0; border-radius: 0; padding: 0 20px 0 23px; background: #102f4b; color: #fff; font: 600 13px/1 'Space Grotesk', sans-serif; letter-spacing: .08em; text-transform: uppercase; box-shadow: 6px 6px 0 #b8d8d5; transition: transform .2s ease, box-shadow .2s ease, background .2s ease; }
        .auth-submit:hover { background: #1f6079; transform: translate(2px, 2px); box-shadow: 4px 4px 0 #b8d8d5; }
        .auth-submit:focus { outline: 2px solid #2b829c; outline-offset: 4px; }
        .auth-arrow { font-size: 21px; font-weight: 400; line-height: 0; }
        @media (max-width: 800px) { .auth-heading { margin-bottom: 34px; } }
    </style>
</x-guest-layout>
