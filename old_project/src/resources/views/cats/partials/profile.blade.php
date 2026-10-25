<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">{{ trans('cat.profile') }}</h3></div>
    <table class="table">
        <tbody>
            @if ($cat->titles_before_name)
            <tr>
                <th>{{ trans('cat.titles_before_name') }}</th>
                <td>{{ $cat->titles_before_name }}</td>
            </tr>
            @endif
            <tr>
                <th class="col-sm-4">{{ trans('cat.full_name') }}</th>
                <td class="col-sm-8">{{ $cat->profileLink() }}</td>
            </tr>
            @if ($cat->titles_after_name)
            <tr>
                <th>{{ trans('cat.titles_after_name') }}</th>
                <td>{{ $cat->titles_after_name }}</td>
            </tr>
            @endif
            @if ($cat->photo)
            <tr>
                <th>{{ trans('cat.photo') }}</th>
                <td><img src="{{ $cat->photo }}" alt="Cat Photo" /></td>
            </tr>
            @endif
            @if ($cat->gender)
            <tr>
                <th>{{ trans('cat.gender') }}</th>
                <td>{{ $cat->gender }}</td>
            </tr>
            @endif
            @if ($cat->dob)
            <tr>
                <th>{{ trans('cat.dob') }}</th>
                @if ($cat->dob == '1111-11-11')
                <td>{{ $cat->dob }} = Not set</td>
                @else
                <td>{{ $cat->dob }}</td>
                @endif
            </tr>
            @endif
            @if ($cat->breed)
            <tr>
                <th>{{ trans('cat.breed') }}</th>
                <td>{{ $cat->breed }} = {{ $cat->findBreedName() }}</td>
            </tr>
            @endif
            @if ($cat->ems_color)
            <tr>
                <th>{{ trans('cat.ems_color') }}</th>
                <td>{{ $cat->ems_color }} = {{ $cat->findEMS() }}</td>
            </tr>
            @endif
            @if ($cat->breeding_station)
            <tr>
                <th>{{ trans('cat.breeding_station') }}</th>
                <td>{{ $cat->breeding_station }}</td>
            </tr>
            @endif
            @if ($cat->country_code)
            <tr>
                <th>{{ trans('cat.country_code') }}</th>
                <td>{{ $cat->country_code }}</td>
            </tr>
            @endif
            @if ($cat->alternative_name)
            <tr>
                <th>{{ trans('cat.alternative_name') }}</th>
                <td>{{ $cat->alternative_name }}</td>
            </tr>
            @endif
            @if ($cat->print_name_r1)
            <tr>
                <th>{{ trans('cat.print_name_r1') }}</th>
                <td>{{ $cat->print_name_r1 }}</td>
            </tr>
            @endif
            @if ($cat->print_name_r2)
            <tr>
                <th>{{ trans('cat.print_name_r2') }}</th>
                <td>{{ $cat->print_name_r2 }}</td>
            </tr>
            @endif
            @if ($cat->dod)
            <tr>
                <th>{{ trans('cat.dod') }}</th>
                <td>{{ $cat->dod }}</td>
            </tr>
            @endif
            @if ($cat->original_reg_num)
            <tr>
                <th>{{ trans('cat.original_reg_num') }}</th>
                <td>{{ $cat->original_reg_num }}</td>
            </tr>
            @endif
            @if ($cat->last_reg_num)
            <tr>
                <th>{{ trans('cat.last_reg_num') }}</th>
                <td>{{ $cat->last_reg_num }}</td>
            </tr>
            @endif
            @if ($cat->reg_num_2)
            <tr>
                <th>{{ trans('cat.reg_num_2') }}</th>
                <td>{{ $cat->reg_num_2 }}</td>
            </tr>
            @endif
            @if ($cat->reg_num_3)
            <tr>
                <th>{{ trans('cat.reg_num_3') }}</th>
                <td>{{ $cat->reg_num_3 }}</td>
            </tr>
            @endif
            @if ($cat->notes)
            <tr>
                <th>{{ trans('cat.notes') }}</th>
                <td>{{ $cat->notes }}</td>
            </tr>
            @endif
            @if ($cat->breeder)
            <tr>
                <th>{{ trans('cat.breeder') }}</th>
                <td>{{ $cat->breeder }}</td>
            </tr>
            @endif
            @if ($cat->current_owner)
            <tr>
                <th>{{ trans('cat.current_owner') }}</th>
                <td>{{ $cat->current_owner }}</td>
            </tr>
            @endif
            @if ($cat->country_of_origin)
            <tr>
                <th>{{ trans('cat.country_of_origin') }}</th>
                <td>{{ $cat->country_of_origin }}</td>
            </tr>
            @endif
            @if ($cat->country)
            <tr>
                <th>{{ trans('cat.country') }}</th>
                <td>{{ $cat->country }}</td>
            </tr>
            @endif
            @if ($cat->ownership_notes)
            <tr>
                <th>{{ trans('cat.ownership_notes') }}</th>
                <td>{{ $cat->ownership_notes }}</td>
            </tr>
            @endif
            @if ($cat->personal_info)
            <tr>
                <th>{{ trans('cat.personal_info') }}</th>
                <td>{{ $cat->personal_info }}</td>
            </tr>
            @endif
            @if ($cat->genetic_tests)
            <tr>
                <th>{{ trans('cat.genetic_tests') }}</th>
                <td>{!! nl2br($cat->genetic_tests) !!}</td>
            </tr>
            @endif
            @if ($cat->genetic_tests_file)
            <tr>
                <th>{{ trans('cat.genetic_tests_file') }}</th>
                <td><a href="{{ $cat->genetic_tests_file }}">{{ trans('app.download') }}</a></td>
            </tr>
            @endif
            @if ($cat->vet_confirmation)
            <tr>
                <th>{{ trans('cat.vet_confirmation') }}</th>
                <td><a href="{{ $cat->vet_confirmation }}">{{ trans('app.download') }}</a></td>
            </tr>
            @endif
            @if ($cat->doo)
            <tr>
                <th>{{ trans('cat.doo') }}</th>
                <td>{{ $cat->doo }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
