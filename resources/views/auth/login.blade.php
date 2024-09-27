@extends('layouts.main')

@section('title', 'Login')
@section('content')
    <section class="logins" style="margin-top: 4rem">
        <div class="form-structor">
            <div class="login">
                <div class="center">
                    <h2 class="form-title" id="login"><span>or</span>Log in</h2>
                    <form method="POST" action="{{ route('login') }}">@csrf
                        <div class="form-holder">
                            <!-- Email Address -->
                            <input id="email" type="email" class="input" placeholder="Email" name="email"
                                :value="old('email')" required autofocus autocomplete="on" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger" />

                            <!-- Password -->
                            <input type="password" class="input" id="password" placeholder="Password" name="password"
                                required autocomplete="on" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />
                        </div>
                        <button class="submit-btn">Log in</button>
                    </form>
                </div>
            </div>

            <div class="signup slide-up">
                <h2 class="form-title" id="signup"><span>or</span>Sign up</h2>
                <form method="POST" action="{{ route('register') }}">@csrf
                    <div class="form-holder">
                        <!-- Name -->
                        <input type="text" class="input" id="name" placeholder="Name" name="name" :value="old('name')" required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2 text-danger" />

                        <!-- Email Address -->
                        <input type="email" class="input" id="email" placeholder="Email" name="email" :value="old('email')" required autocomplete="on"/>
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger" />

                         <!-- Password -->
                        <input type="password" class="input" id="password" placeholder="Password"
                            name="password"
                            required autocomplete="new-password"  />
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />

                        <!-- Confirm Password -->
                        <input type="password" class="input" id="password_confirmation" placeholder="Confirm Password" 
                        name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-danger" />
                    </div>
                    <button class="submit-btn">Sign up</button>
                </form>
            </div>
        </div>
    </section>
@endsection












{{-- <x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                required autofocus autocomplete="on" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 danger" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="on" />

            <x-input-error :messages="$errors->get('password')" class="mt-2 danger" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                    name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout> --}}
