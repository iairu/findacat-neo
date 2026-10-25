@props(['items' => []])

@if(count($items) > 0)
<nav class="breadcrumb" aria-label="Breadcrumb">
    @foreach($items as $index => $item)
        <div class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
            @if(!$loop->last)
                <a href="{{ $item['url'] ?? '#' }}">{{ $item['label'] }}</a>
            @else
                <span>{{ $item['label'] }}</span>
            @endif
        </div>

        @if(!$loop->last)
            <span class="breadcrumb-separator">›</span>
        @endif
    @endforeach
</nav>
@endif
