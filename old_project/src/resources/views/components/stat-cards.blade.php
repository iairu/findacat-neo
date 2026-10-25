@props(['cat'])

<div class="row" style="margin-bottom: 30px;">
    <!-- Children Count -->
    @php
        $childrenCount = $cat->childs ? $cat->childs->count() : 0;
    @endphp
    <div class="col-md-3 col-sm-6">
        <div class="stat-card animate-scaleIn">
            <div class="stat-card-icon">👶</div>
            <div class="stat-card-value">{{ $childrenCount }}</div>
            <div class="stat-card-label">{{ __('cat.children', 'Children') }}</div>
        </div>
    </div>

    <!-- Age -->
    @if($cat->dob)
    <div class="col-md-3 col-sm-6">
        <div class="stat-card animate-scaleIn" style="animation-delay: 0.1s;">
            <div class="stat-card-icon">🎂</div>
            <div class="stat-card-value">
                @if($cat->dod)
                    {{ \Carbon\Carbon::parse($cat->dob)->diffInYears(\Carbon\Carbon::parse($cat->dod)) }}
                @else
                    {{ \Carbon\Carbon::parse($cat->dob)->age }}
                @endif
            </div>
            <div class="stat-card-label">{{ __('cat.years_old', 'Years Old') }}</div>
        </div>
    </div>
    @endif

    <!-- Gender -->
    <div class="col-md-3 col-sm-6">
        <div class="stat-card animate-scaleIn" style="animation-delay: 0.2s;">
            <div class="stat-card-icon">{{ $cat->gender_id == 1 ? '♂️' : '♀️' }}</div>
            <div class="stat-card-value">{{ $cat->gender_id == 1 ? __('cat.male', 'Male') : __('cat.female', 'Female') }}</div>
            <div class="stat-card-label">{{ __('cat.gender', 'Gender') }}</div>
        </div>
    </div>

    <!-- Status -->
    <div class="col-md-3 col-sm-6">
        <div class="stat-card animate-scaleIn" style="animation-delay: 0.3s;">
            <div class="stat-card-icon">{{ $cat->dod ? '😢' : '❤️' }}</div>
            <div class="stat-card-value">{{ $cat->dod ? __('cat.deceased', 'Deceased') : __('cat.living', 'Living') }}</div>
            <div class="stat-card-label">{{ __('cat.status', 'Status') }}</div>
        </div>
    </div>
</div>
