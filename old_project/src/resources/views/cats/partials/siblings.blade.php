<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">{{ trans('cat.siblings') }}</h3></div>
    <table class="table">
        <tbody>
            @foreach($cat->siblings() as $index => $sibling)
            @if($index < 25)
            <tr>
                <td>
                @if ($cat->gender_id == 1)
                    @if (null !== $sibling->d())
                    <div class="parent">{{ $sibling->d()->profileLink() }}</div>
                    @else
                    <div class="parent">{{ __('cat.unknown_parent') }}</div>
                    @endif
                    ({{ $sibling->gender }}) {{ $sibling->titles_before_name }} {{ $sibling->profileLink() }} {{ $sibling->titles_after_name }} {{ $sibling->breed }} {{ $sibling->ems_color }} {{ $sibling->dob }}
                @else
                    @if (null !== $sibling->s())
                    <div class="parent">{{ $sibling->s()->profileLink() }}</div>
                    @else
                    <div class="parent">{{ __('cat.unknown_parent') }}</div>
                    @endif
                    ({{ $sibling->gender }}) {{ $sibling->titles_before_name }} {{ $sibling->profileLink() }} {{ $sibling->titles_after_name }} {{ $sibling->breed }} {{ $sibling->ems_color }} {{ $sibling->dob }}
                @endif
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
</div>