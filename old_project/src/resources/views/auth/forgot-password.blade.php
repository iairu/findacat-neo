@extends('layouts.app')

@section('content')
<div>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="" style="width:50px;height:50px;" />
            </a>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('app.forgot-message') }}
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-label for="email" :value="__('Email')" />

                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('app.email-password-reset-link') }}
                </x-button>
            </div>
        </form>
    </x-auth-card>
</div>
@endsection