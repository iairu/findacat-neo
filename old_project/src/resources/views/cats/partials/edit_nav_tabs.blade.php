<!-- Nav tabs -->
<ul class="nav nav-pills nav-stacked">
    <li class="{{ request('tab') == null ? 'active' : '' }}">
        {!! link_to_route('cats.edit', __('cat.edit'), [$cat->id]) !!}
    </li>
    <li class="{{ request('tab') == 'details' ? 'active' : '' }}">
        {!! link_to_route('cats.edit', __('cat.details'), [$cat->id, 'tab' => 'details']) !!}
    </li>
    <!-- <li class="{{ request('tab') == 'death' ? 'active' : '' }}">
        {!! link_to_route('cats.edit', __('cat.death'), [$cat->id, 'tab' => 'death']) !!}
    </li> -->
</ul>
<br>
{{ link_to_route('cats.edit', __('cat.delete'), [$cat, 'action' => 'delete'], ['class' => 'btn btn-danger', 'id' => 'del-cat-'.$cat->id]) }}
