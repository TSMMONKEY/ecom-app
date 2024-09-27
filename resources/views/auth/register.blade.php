@extends('layouts.main')

@section('title', 'sign-up')
@section('content')
    <section class="logins" style="margin-top: 4rem">
        <div class="form-structor">
            <div class="signup">
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
            <div class="login slide-up">
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
        </div>
    </section>
@endsection
