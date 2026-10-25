@extends('layouts.app')

@section('content')
    <!-- Breadcrumb -->
    @component('components.breadcrumb', ['items' => [
        ['label' => 'Home', 'url' => url('/')],
        ['label' => 'Search', 'url' => route('cats.search')],
        ['label' => $cat->full_name]
    ]])
    @endcomponent

    <!-- Cat Header with Favorite Button -->
    <div class="cat-header-container" style="display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 30px;">
        <div style="flex: 1;">
            <h2 class="page-header" style="margin-bottom: 0;">
                {{ $cat->full_name }} <small>@yield('subtitle')</small>
            </h2>
            @if($cat->breed || $cat->ems_color)
            <div class="cat-meta" style="margin-top: 10px;">
                @if($cat->breed)
                <span class="badge badge-info">{{ $cat->breed }}</span>
                @endif
                @if($cat->ems_color)
                <span class="badge badge-success">{{ $cat->ems_color }}</span>
                @endif
            </div>
            @endif
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button class="btn-favorite"
                    data-favorite-id="{{ $cat->id }}"
                    data-favorite-name="{{ $cat->full_name }}"
                    data-tooltip="Add to favorites"
                    data-tooltip-position="left"
                    onclick="favorites.toggle('{{ $cat->id }}', '{{ $cat->full_name }}')">
                ☆
            </button>
        </div>
    </div>

    @include('cats.partials.action-buttons', ['cat' => $cat])

    <!-- Pedigree Tools -->
    @component('components.pedigree-tools', ['cat' => $cat])
    @endcomponent

    @yield('cat-content')

    <script>
        // Track recent view
        if (window.recentViews) {
            recentViews.add('{{ $cat->id }}', '{{ $cat->full_name }}', '{{ $cat->breed ?? "Unknown" }}');
        }
    </script>
@endsection
