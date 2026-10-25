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
            {{ __('app.signup-message') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ __('app.verify-message') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div>
                    <x-button>
                        {{ __('app.resend-verification-email') }}
                    </x-button>
                </div>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
                    {{ __('app.logout') }}
                </button>
            </form>
        </div>
    </x-auth-card>
</div>
@endsection