@extends('layouts.app')

@section('content')
</div>
<div class="container-fluid">
    @include('cats.partials.action-buttons', ['cat' => $cat])
    <h2 class="page-header">
        @yield('title') <small>@yield('subtitle')</small>
    </h2>
    @yield('cat-content')
@endsection
