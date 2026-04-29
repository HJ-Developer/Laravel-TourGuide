@php
    $loginTourSteps = [
        [
            'element' => '#container',
            'key' => 'intro',
            'disableActiveInteraction' => true,
            'popover' => [
                'title' => 'Bem-vindo',
                'description' => 'Vamos preparar tudo para ti',
            ],
        ],
        [
            'element' => '#emailInput',
            'key' => 'email',
            'requiresValidation' => true,
            'popover' => [
                'title' => 'Email',
                'description' => 'Enter a valid email',
            ],
        ],
        [
            'element' => '#passwordInputField',
            'key' => 'password',
            'requiresValidation' => true,
            'popover' => [
                'title' => 'Password',
                'description' => 'We recommend a strong one',
            ],
        ],
        [
            'element' => '#rememberCheckBox',
            'key' => 'remember',
            'popover' => [
                'title' => 'Automatically login next time?',
                'description' => 'Just check here',
            ],
        ],
        [
            'element' => '#passwordRecoverLink',
            'key' => 'passwordRecover',
            // 'disableActiveInteraction' => true,
            'popover' => [
                'title' => "Can't remember your password?",
                'description' => 'We can help with that',
            ],
        ],
        [
            'element' => '#signUpAlt',
            'key' => 'signup',
            // 'disableActiveInteraction' => true,
            'popover' => [
                'title' => "Don't even have an account?",
                'description' => "Let's take care of that right now, shall we?",
            ],
        ],
        [
            'element' => '#loginBtn',
            'key' => 'login',
            'popover' => [
                'title' => 'Everything set up',
                'description' => 'Hit this button when ready',
            ],
        ],
    ];

    $loginTourValidators = [
        'email' => [
            'type' => 'email',
            'selector' => '#emailInput',
        ],
        'password' => [
            'type' => 'minLength',
            'selector' => '#passwordInput',
            'min' => 6,
        ],
        'remember' => [
            'type' => 'checked',
            'selector' => '#rememberCheckBox input',
        ],
    ];
@endphp

<x-layouts::auth :title="__('Log in')">
    <div id="container" class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input id="emailInput" name="email" :label="__('Email address')" :value="old('email')" type="email"
                required autofocus autocomplete="email" placeholder="email@example.com" />

            <!-- Password -->
            <div id="passwordInputField" class="relative">
                <flux:input id="passwordInput" name="password" :label="__('Password')" type="password" required
                    autocomplete="current-password" :placeholder="__('Password')" place viewable />
            </div>

            <div class="flex items-center justify-between">
                <div id="rememberCheckBox">
                    <!-- Remember Me -->
                    <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />
                </div>

                @if (Route::has('password.request'))
                    <flux:link id="passwordRecoverLink" class="text-sm inset-e-0" :href="route('password.request')"
                        wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <div class="flex items-center justify-end">
                <flux:button id="loginBtn" variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>

            </div>
        </form>


        @if (Route::has('register'))
            <div id="signUpAlt"
                class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Don\'t have an account?') }}</span>
                <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
            </div>
        @endif

        <livewire:tour-register :name="'login'" :steps="$loginTourSteps" :validators="$loginTourValidators" />



        <button id="startTourBtn" variant="primary" type="button"
            class="w-fit px-8 py-3 cursor-pointer absolute top-5 right-6  border border-accent-foreground bg-accent text-accent-foreground text-sm font-medium rounded-2xl"
            x-on:click="window.startTour('login')" data-test="login-button">
            Start Tour
        </button>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const {
                    registerValidator,
                    start
                } = window.TourEngine;

                // 1. register validators FIRST
                registerValidator("email", ({
                    selector
                }) => {
                    const val = document.querySelector(selector)?.value || "";
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
                }, "page");

                registerValidator("minLength", ({
                    selector,
                    min
                }) => {
                    const val = document.querySelector(selector)?.value || "";
                    return val.length >= min;
                }, "page");

                registerValidator("checked", ({
                    selector
                }) => {
                    return document.querySelector(selector)?.checked === true;
                }, "page");

                const t = setTimeout(() => {
                    start("login");
                }, 100);

                // 2. optional manual start only (no timeout)
                document.getElementById("startTourBtn")?.addEventListener("click", () => {
                    start("login");
                });

                return () => clearTimeout(t);
            });
        </script>
    </div>
</x-layouts::auth>
