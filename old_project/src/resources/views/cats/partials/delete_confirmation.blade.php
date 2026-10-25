<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">{{ __('cat.delete') }} : {{ $cat->full_name }}</h3></div>
    <div class="panel-body">
        <table class="table table-condensed">
            <tr><td>{{ __('cat.titles_before_name') }}</td><td>{{ $cat->titles_before_name }}</td></tr>
            <tr><td>{{ __('cat.full_name') }}</td><td>{{ $cat->full_name }}</td></tr>
            <tr><td>{{ __('cat.titles_after_name') }}</td><td>{{ $cat->titles_after_name }}</td></tr>
            <tr><td>{{ __('cat.gender_id') }}</td><td>{{ $cat->gender_id }}</td></tr>
            <tr><td>{{ __('cat.sire') }}</td><td>{{ $cat->sire_id ? $cat->sire->full_name : '' }}</td></tr>
            <tr><td>{{ __('cat.dam') }}</td><td>{{ $cat->dam_id ? $cat->dam->full_name : '' }}</td></tr>
        </table>
        {!! FormField::delete(
            ['route' => ['cats.destroy', $cat]],
            __('cat.delete_confirm_button'),
            ['class' => 'btn btn-danger'],
            ['cat_id' => $cat->id]
        ) !!}
        {{ link_to_route('cats.edit', __('app.cancel'), [$cat], ['class' => 'btn btn-default']) }}
    </div>
</div>
