@extends('layouts.app')

@section('ext_css')
<style>
    html,body,#app {
        height: 100%;
        margin-bottom: 0 !important;
    }
    #app>.container {
        display: flex;
        min-height: 85%;
        flex-flow: column;
        justify-content: center;
    }
    .page-header {
        border-color: rgba(0,0,0,0.15) !important;
    }
    body {
        background-image: url("images/cat-1045782-2.jpg");
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        background-attachment: fixed;
    }
    .navbar > .container .navbar-brand.non-collapsed-only {
        visibility: hidden;
    }
    .navbar-default {
        border-color: rgba(0, 0, 0, 0.07) !important;
    }
    .input-group input[type="text"] {
        min-width: 500px;
    }
</style>
@endsection

@section('content')
<h2 class="page-header" style="text-align: center">


    {{ trans('app.find_a_cat') }}
    @if (request('q'))
    <small class="pull-right">{!! trans('app.cat_found', ['total' => $cats->total(), 'keyword' => request('q')]) !!}</small>
    @endif
</h2>


{{ Form::open(['method' => 'get','class' => '']) }}
<div class="input-group" style="width:100%">
    {{ Form::text('full_name', request('full_name'), ['class' => 'form-control', 'placeholder' => trans('cat.full_name')]) }}
    <details style="margin: 35px 0 0;">
        <summary style="cursor:pointer; width:100%;text-align:center; padding: 20px 0;">Advanced Search</summary>
        {{ Form::text('ems_color', request('ems_color'), ['class' => 'form-control', 'placeholder' => trans('cat.ems_color')]) }}
        {{ Form::text('dob', request('dob'), ['class' => 'form-control', 'placeholder' => trans('cat.dob')]) }}
        {{ Form::text('breed', request('breed'), ['class' => 'form-control', 'placeholder' => trans('cat.breed')]) }}
        {{ Form::text('reg_num', request('reg_num'), ['class' => 'form-control', 'placeholder' => trans('cat.reg_num')]) }}
        <label for="kind">Search kind: </label>
        <select name="kind" id="kind" required>
            <option value="substring">substring</option>
            <option value="approximate">approximate</option>
            <option value="exact">exact</option>
        </select>
        <br>
        <label for="generations">Number of generations: </label>
        <select name="generations" id="generations" required>
            <option value="5">5</option>
            <option value="4">4</option>
            <option value="3">3</option>
            <option value="2">2</option>
            <option value="1">1</option>
        </select>
    </details>
    <span class="input-group-btn" style="display:flex;justify-content:center;width:100%;">
        {{ Form::submit(trans('app.search'), ['class' => 'btn btn-default']) }}
    </span>
</div>
{{ Form::close() }}

@if (request('full_name') || request('ems_color') || request('dob') || request('breed') || request('reg_num'))
<br>
{{ $cats->appends(Request::except('page'))->render() }}
@foreach ($cats->chunk(4) as $chunkedUser)
<div class="row">
    @foreach ($chunkedUser as $cat)
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-body">
                @if ($cat->gender == "F")
                @if ($cat->titles_before_name)
                <div style="color:red;">({{ $cat->gender }}) {{ $cat->titles_before_name }}</div>
                @else
                <div style="color:red;">({{ $cat->gender }})</div>
                @endif
                <h3 class="panel-title" style="color:red;">{{ $cat->profileLink() }}</h3>
                @if ($cat->titles_after_name)
                <div style="color:red;">{{ $cat->titles_after_name }}</div>
                @endif
                @else
                @if ($cat->titles_before_name)
                <div style="color:blue;">({{ $cat->gender }}) {{ $cat->titles_before_name }}</div>
                @else
                <div style="color:blue;">({{ $cat->gender }})</div>
                @endif
                <h3 class="panel-title" style="color:blue;">{{ $cat->profileLink() }}</h3>
                @if ($cat->titles_after_name)
                <div style="color:blue;">{{ $cat->titles_after_name }}</div>
                @endif
                @endif
                <hr style="margin: 5px 0;">
                <div>{{ $cat->breed }} {{ $cat->ems_color }} <span style="float: right;">{{ $cat->dob() }}</span></div>
                <hr style="margin: 5px 0;">
                <div>{{ trans('cat.sire') }} : {{ $cat->sire_id ? $cat->sire->full_name : '' }}</div>
                <div>{{ trans('cat.dam') }} : {{ $cat->dam_id ? $cat->dam->full_name : '' }}</div>
            </div>
            <div class="panel-footer">
                {{ link_to_route('cats.show', trans('app.show_profile'), [$cat->id], ['class' => 'btn btn-default btn-xs']) }}
                {{ link_to_route('cats.tree', trans('app.show_family_tree'), [$cat->id, request('generations')], ['class' => 'btn btn-default btn-xs']) }}
            </div>
        </div>
    </div>
    @endforeach
</div>
@endforeach

{{ $cats->appends(Request::except('page'))->render() }}
@endif
@endsection
