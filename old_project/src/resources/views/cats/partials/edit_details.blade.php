<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">{{ __('cat.details') }}</h3></div>
    <div class="panel-body">
        <div class="left">
            <div class="one">
                {!! FormField::text('alternative_name', ['label' => __('cat.alternative_name')]) !!}
                {!! FormField::text('print_name_r1', ['label' => __('cat.print_name_r1')]) !!}
                {!! FormField::text('print_name_r2', ['label' => __('cat.print_name_r2')]) !!}
                {!! FormField::text('chip_number', ['label' => __('cat.chip_number')]) !!}
                {!! FormField::text('breeding_station', ['label' => __('cat.breeding_station')]) !!}
                {!! FormField::text('notes', ['label' => __('cat.notes')]) !!}
                {!! FormField::text('breeder', ['label' => __('cat.breeder')]) !!}
                {!! FormField::text('current_owner', ['label' => __('cat.current_owner')]) !!}
                {!! FormField::text('ownership_notes', ['label' => __('cat.ownership_notes')]) !!}
            </div>
            <div class="two">
                {!! FormField::text('personal_info', ['label' => __('cat.personal_info')]) !!}
                {!! FormField::file('photo', ['label' => __('cat.photo'), 'placeholder' => 'Photo upload']) !!}
                {!! FormField::textarea('genetic_tests', ['label' => __('cat.genetic_tests')]) !!}
                {!! FormField::file('genetic_tests_file', ['label' => __('cat.genetic_tests'), 'placeholder' => 'Genetic Tests upload']) !!}
                {!! FormField::file('vet_confirmation', ['label' => __('cat.vet_confirmation'), 'placeholder' => 'Vet Confirmation upload']) !!}
            </div>
        </div>
        <div class="right">
            <div class="one">
                {!! FormField::text('dob', ['label' => __('cat.dob'), 'placeholder' => __('app.example').' 1959-07-20']) !!}
                {!! FormField::text('dod', ['label' => __('cat.dod'), 'placeholder' => __('app.example').' 1959-07-20']) !!}
                {!! FormField::text('doo', ['label' => __('cat.doo'), 'placeholder' => __('app.example').' 1959-07-20']) !!}
            </div>
            <div class="two">
                {!! FormField::text('original_reg_num', ['label' => __('cat.original_reg_num')]) !!}
                {!! FormField::text('last_reg_num', ['label' => __('cat.last_reg_num')]) !!}
                {!! FormField::text('reg_num_2', ['label' => __('cat.reg_num_2')]) !!}
                {!! FormField::text('reg_num_3', ['label' => __('cat.reg_num_3')]) !!}
                {!! FormField::text('country_code', ['label' => __('cat.country_code')]) !!}
                {!! FormField::text('country_of_origin', ['label' => __('cat.country_of_origin')]) !!}
                {!! FormField::text('country', ['label' => __('cat.country')]) !!}
            </div>
        </div>
    </div>
</div>
