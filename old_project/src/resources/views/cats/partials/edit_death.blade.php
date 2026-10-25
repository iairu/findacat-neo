<fieldset>
    <legend>{{ __('cat.cemetery_location') }}</legend>
    {!! FormField::text('cemetery_location_name', ['label' => __('address.location_name'), 'value' => old('cemetery_location_name', $cat->getMetadata('cemetery_location_name'))]) !!}
    {!! FormField::textarea('cemetery_location_address', ['label' => __('address.address'), 'value' => old('cemetery_location_address', $cat->getMetadata('cemetery_location_address'))]) !!}
    <div class="row">
        <div class="col-md-6">{!! FormField::text('cemetery_location_latitude', ['label' => __('address.latitude'), 'value' => old('cemetery_location_latitude', $cat->getMetadata('cemetery_location_latitude'))]) !!}</div>
        <div class="col-md-6">{!! FormField::text('cemetery_location_longitude', ['label' => __('address.longitude'), 'value' => old('cemetery_location_longitude', $cat->getMetadata('cemetery_location_longitude'))]) !!}</div>
    </div>
</fieldset>
