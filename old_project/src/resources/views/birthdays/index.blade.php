@extends('layouts.app')

@section('ext_css')
<style>
body {
        background-image: url("images/cat-1045782-3.jpg");
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        background-attachment: fixed;
    }
.navbar-default  {
        background: white;
}
</style>
@endsection

@section('title', __('cat.upcoming_birthday'))

@section('content')

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default text-center">
            <div class="panel-heading text-left">
                <h3 class="panel-title">{{ __('birthday.upcoming') }}</h3>
            </div>
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <td>#</td>
                        <td class="text-left">{{ __('cat.full_name') }}</td>
                        <td>{{ __('birthday.birthday') }}</td>
                        <td>{{ __('cat.age') }}</td>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = 1;
                    @endphp
                    @forelse($cats as $key => $cat)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td class="text-left">{{ link_to_route('cats.show', $cat->full_name, $cat->cat_id) }}</td>
                        <td>
                            {{ $cat->birthday->format('j M') }}
                            ({{ __('birthday.remaining', ['count' => $cat->birthday_remaining]) }})
                        </td>
                        <td>{{ __('birthday.age_years', ['age' => $cat->age]) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">{{ __('birthday.no_upcoming', ['days' => 60]) }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
