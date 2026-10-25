<div class="panel panel-default">
    <div class="panel-heading">
        <div class="pull-right" style="margin: -3px -6px">
        </div>
        <h3 class="panel-title">{{ __('cat.childs') }} ({{ $cat->childs->count() }})</h3>
    </div>

    <ul class="list-group" id="children">
        @forelse($cat->childs as $index => $child)
            @if($index < 100)
            <li class="list-group-item">
                @if ($cat->gender_id == 1)
                    @if (null !== $child->d())
                    <div class="parent">{{ __('cat.with') }} ({{ $child->d()->gender }}) {{ $child->d()->titles_before_name() }} {{ $child->d()->profileLink() }} {{ $child->d()->titles_after_name() }} {{ $child->d()->breed }} {{ $child->d()->ems_color }} {{ $child->d()->dob() }}</div>
                    @else
                    <div class="parent">{{ __('cat.with') }} {{ __('cat.unknown_parent') }}</div>
                    @endif
                    <div class="child" style="margin-left: 17px;">({{ $child->gender }}) {{ $child->titles_before_name() }} {{ $child->profileLink() }} {{ $child->titles_after_name() }} {{ $child->breed }} {{ $child->ems_color }} {{ $child->dob() }}</div>
                @else
                    @if (null !== $child->s())
                    <div class="parent">{{ __('cat.with') }} ({{ $child->s()->gender }}) {{ $child->s()->titles_before_name() }} {{ $child->s()->profileLink() }} {{ $child->s()->titles_after_name() }} {{ $child->s()->breed }} {{ $child->s()->ems_color }} {{ $child->s()->dob() }}</div>
                    @else
                    <div class="parent">{{ __('cat.with') }} {{ __('cat.unknown_parent') }}</div>
                    @endif
                    <div class="child" style="margin-left: 17px;">({{ $child->gender }}) {{ $child->titles_before_name() }} {{ $child->profileLink() }} {{ $child->titles_after_name() }} {{ $child->breed }} {{ $child->ems_color }} {{ $child->dob() }}</div>
                @endif
            </li>
            @endif
        @empty
            <li class="list-group-item">{{ __('app.childs_were_not_recorded') }}</li>
        @endforelse
        @if (request('action') == 'add_child')
        <li class="list-group-item">
            {{ Form::open(['route' => ['family-actions.add-child', $cat->id]]) }}
            <div class="row">
                <div class="col-md-8">
                    {!! FormField::text('add_child_name', ['label' => __('cat.child_name')]) !!}
                </div>
                <div class="col-md-4">
                    {!! FormField::radios('add_child_gender_id', [1 => __('app.male'), 2 => __('app.female')], ['label' => __('cat.child_gender')]) !!}
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    {!! FormField::select('add_child_parent_id', $catsMariageList, ['label' => __('cat.add_child_from_existing_couples', ['full_name' => $cat->full_name]), 'placeholder' => __('app.unknown')]) !!}
                </div>
            </div>

            {{ Form::submit(__('cat.add_child'), ['class' => 'btn btn-success btn-sm']) }}
            {{ link_to_route('cats.show', __('app.cancel'), [$cat->id], ['class' => 'btn btn-default btn-sm']) }}
            {{ Form::close() }}
        </li>
        @endif
    </ul>
</div>
