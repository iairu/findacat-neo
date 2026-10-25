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
            {{ __('app.confirm-secure-area') }}
        </div>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <!-- Password -->
            <div>
                <x-label for="password" :value="__('app.password')" />

                <x-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
            </div>

            <div class="flex justify-end mt-4">
                <x-button>
                    {{ __('app.confirm') }}
                </x-button>
            </div>
        </form>
    </x-auth-card>
</div>
@endsection