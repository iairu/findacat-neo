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
.panel-body {
    display: flex;
    flex-flow: row;
    width: auto;
    gap: 40px;
}
.panel-body .one,
.panel-body .two {
    display: flex;
    flex-flow: column;
    gap: 40px;
}
.panel-body .left {
    min-width: 500px;
}
.in-row ul,
.in-row .form-group {
    display: flex;
    flex-flow: row;
}
</style>
@endsection

@section('content')
@if (request('action') == 'delete' && $cat)
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                @include('cats.partials.delete_confirmation')
            </div>
        </div>
@else
    <div class="pull-right">
        {{ link_to_route('cats.show', __('app.show_profile').' '.$cat->full_name, [$cat->id], ['class' => 'btn btn-default']) }}
    </div>
    <h2 class="page-header">
        {{ __('cat.edit') }} {{ $cat->profileLink() }}
    </h2>
    <div class="row">
        <div class="col-md-2">@include('cats.partials.edit_nav_tabs')</div>
        <div class="col-md-10">
            <div class="row">
                {{ Form::model($cat, ['route' => ['cats.update', $cat->id], 'method' =>'post', 'autocomplete' => 'off', 'files' => true]) }}
                <div class="">
                    @includeWhen(request('tab') == null || !in_array(request('tab'), $validTabs), 'cats.partials.edit_profile')
                    @includeWhen(request('tab') == 'death', 'cats.partials.edit_death')
                    @includeWhen(request('tab') == 'details', 'cats.partials.edit_details')
                    <div class="text-right">
                        {{ Form::submit(__('app.update'), ['class' => 'btn btn-primary']) }}
                        {{ link_to_route('cats.show', __('app.cancel'), [$cat->id], ['class' => 'btn btn-default']) }}
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endif
@endsection

@section('ext_css')
<link href="{{ asset('css/plugins/jquery.datetimepicker.css') }}" rel="stylesheet">
@endsection

@section('script')
<script src="{{ asset('js/plugins/jquery.datetimepicker.js') }}"></script>
@if (request('tab') == 'death')
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
      integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
      crossorigin=""></script>
@endif

<script>
    (function() {
        $('#dob').datetimepicker({
            timepicker:false,
            format:'Y-m-d',
            closeOnDateSelect: true,
            scrollInput: false
        });
        $('#doo').datetimepicker({
            timepicker:false,
            format:'Y-m-d',
            closeOnDateSelect: true,
            scrollInput: false
        });
        $('#dod').datetimepicker({
            timepicker:false,
            format:'Y-m-d',
            closeOnDateSelect: true,
            scrollInput: false
        });
    })();
</script>
@endsection
