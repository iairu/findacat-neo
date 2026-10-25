@extends('layouts.cat-profile')

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

@section('subtitle', __('cat.death'))

@section('cat-content')
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                {{ link_to_route('cats.edit', __('app.edit'), [$cat->id, 'tab' => 'death'], ['class' => 'pull-right']) }}
                <h3 class="panel-title">{{ __('cat.death') }}</h3>
            </div>
            <table class="table">
                <tbody>
                    <tr>
                        <th>{{ __('address.location_name') }}</th>
                        <td>{{ $cat->getMetadata('cemetery_location_name') }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('address.address') }}</th>
                        <td>{{ $cat->getMetadata('cemetery_location_address') }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('cat.age') }}</th>
                        <td>
                            @if ($cat->age)
                            {!! $cat->age_string !!}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">{{ __('cat.cemetery_location') }}</h3></div>
            @if ($mapCenterLatitude && $mapCenterLongitude)
                <div class="panel-body"><div id="mapid"></div></div>
                <div class="panel-footer">
                    @php
                        $locationCoordinate = $mapCenterLatitude.','.$mapCenterLongitude.'/@'.$mapCenterLatitude.','.$mapCenterLongitude.','.$mapZoomLevel.'z';
                    @endphp
                    {{ link_to(
                        'https://www.google.com/maps/place/'.$locationCoordinate,
                        __('app.open_in_google_map'),
                        ['class' => 'btn btn-default btn-block', 'target' => '_blank']
                    ) }}
                </div>
            @else
                <div class="panel-body">{{ __('app.data_not_available') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@if ($mapCenterLatitude && $mapCenterLongitude)
    @section('ext_css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
      integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
      crossorigin=""/>

    <style>
        #mapid { height: 300px; }
    </style>
    @endsection

    @section('script')
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
      integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
      crossorigin=""></script>

    <script>
        var mapCenter = [{{ $mapCenterLatitude }}, {{ $mapCenterLongitude }}];
        var map = L.map('mapid').setView(mapCenter, {{ $mapZoomLevel }});

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var marker = L.marker(mapCenter).addTo(map);
    </script>
    @endsection
@endif