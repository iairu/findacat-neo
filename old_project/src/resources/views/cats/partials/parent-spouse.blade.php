<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">{{ __('cat.family') }}</h3></div>

    <table class="table">
        <tbody>
            <tr>
                <th class="col-sm-4">{{ __('cat.sire') }}</th>
                <td class="col-sm-8">
                        @if (request('action') == 'set_sire')
                        {{ Form::open(['route' => ['family-actions.set-sire', $cat->id]]) }}
                        {!! FormField::select('set_sire_id', $malePersonList, ['label' => false, 'value' => $cat->sire_id, 'placeholder' => __('app.select_from_existing_males')]) !!}
                        <div class="input-group">
                            {{ Form::text('set_sire', null, ['class' => 'form-control input-sm', 'placeholder' => __('app.enter_new_name')]) }}
                            <span class="input-group-btn">
                                {{ Form::submit(__('app.update'), ['class' => 'btn btn-info btn-sm', 'id' => 'set_sire_button']) }}
                                {{ link_to_route('cats.show', __('app.cancel'), [$cat->id], ['class' => 'btn btn-default btn-sm']) }}
                            </span>
                        </div>
                        {{ Form::close() }}
                        @else
                            @if ($cat->s())
                            {{ $cat->sireLink() }} {{ $cat->s()->breed }} {{ $cat->s()->ems_color }} {{ $cat->s()->dob() }}
                            @endif
                            @if (Auth::user() && Auth::user()->is_admin) 
                            <div class="pull-right">
                                {{ link_to_route('cats.show', __('cat.set_sire'), [$cat->id, 'action' => 'set_sire'], ['class' => 'btn btn-link btn-xs']) }}
                            </div>
                            @endif
                        @endif
                </td>
            </tr>
            <tr>
                <th>{{ __('cat.dam') }}</th>
                <td>
                        @if (request('action') == 'set_dam')
                        {{ Form::open(['route' => ['family-actions.set-dam', $cat->id]]) }}
                        {!! FormField::select('set_dam_id', $femalePersonList, ['label' => false, 'value' => $cat->dam_id, 'placeholder' => __('app.select_from_existing_females')]) !!}
                        <div class="input-group">
                            {{ Form::text('set_dam', null, ['class' => 'form-control input-sm', 'placeholder' => __('app.enter_new_name')]) }}
                            <span class="input-group-btn">
                                {{ Form::submit(__('app.update'), ['class' => 'btn btn-info btn-sm', 'id' => 'set_dam_button']) }}
                                {{ link_to_route('cats.show', __('app.cancel'), [$cat->id], ['class' => 'btn btn-default btn-sm']) }}
                            </span>
                        </div>
                        {{ Form::close() }}
                        @else
                            @if ($cat->d())
                            {{ $cat->damLink() }} {{ $cat->d()->breed }} {{ $cat->d()->ems_color }} {{ $cat->d()->dob() }}
                            @endif
                            @if (Auth::user() && Auth::user()->is_admin) 
                            <div class="pull-right">
                                {{ link_to_route('cats.show', __('cat.set_dam'), [$cat->id, 'action' => 'set_dam'], ['class' => 'btn btn-link btn-xs']) }}
                            </div>
                            @endif
                        @endif
                </td>
            </tr>
            @if ($cat->gender_id == 1)
            <tr>
                <th>{{ __('cat.test_mating') }}</th>
                <td>
                    <div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-7">
                                    {{ link_to_route('cats.test', __('cat.test_mating'), [$cat->getBreed(), 1, $cat->id, 1], ['class' => 'btn btn-xs btn-warning']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @else
            <tr>
                <th>{{ __('cat.test_mating') }}</th>
                <td>
                    <div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-7">
                                    {{ link_to_route('cats.test', __('cat.test_mating'), [1, $cat->id], ['class' => 'btn btn-xs btn-warning']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
