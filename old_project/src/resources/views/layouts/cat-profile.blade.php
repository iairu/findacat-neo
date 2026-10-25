@extends('layouts.app')

@section('content')
    @include('cats.partials.action-buttons', ['cat' => $cat])
    <h2 class="page-header">
        {{ $cat->full_name }} <small>@yield('subtitle')</small>
    </h2>
    @yield('cat-content')
@endsection
