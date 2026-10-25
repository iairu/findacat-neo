@extends('layouts.cat-profile-wide')

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
    #set {
        display: flex;
        flex-flow: row;
        gap: 10px;
        border: 1px solid rgba(0, 0, 0, 0.15);
        justify-content: center;
        padding: 25px;
    }

    #pedigree input {
        /*border: 1px solid rgba(0,0,0,0.25) !important;*/
        background: none !important;
        padding: 0 10px;
        border-radius: 5px 0 0 0;
        margin: 5px 0 0;
        display: none;
    }
    #pedigree td br {
        display: none;
    }
    #pedigree a {
        /*border: 1px solid rgba(0,0,0,0.25);*/
        /* border-top: none; */
        display: block;
        position: relative;
        /*background: white;*/
        padding: 0 10px;
        border-radius: 5px 0 0 5px;
        margin: 0 0 5px;
        width: 15vw;
    }
    label {
        padding: 0 5px;
    }
    #controls {
        padding: 15px 0;
    }
    #pedigree tr {
    }
    #pedigree td {
        max-width: 200px !important;
        width: 200px;
        border-left: 1px solid rgba(0,0,0,0.25);
        border-top: 1px solid rgba(0,0,0,0.25);
        border-bottom: 1px solid rgba(0,0,0,0.25);
        background: white;
    }
    @media print {
        #description {
            display: none;
        }
    }
    .reg_num {
        font-size: 12px;
    }
</style>
<link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
@endsection


@section ('ext_js')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/select2.full.min.js') }}"></script>
@if ($breed->id > 1 && $breed2->id > 1 && $cat->id > 1 && $cat2->id > 1)
<script src="{{ asset('js/inbreeding.js') }}"></script>
@endif
@endsection

@section ('script')
<script>
    function breedButton(e) {
        e.preventDefault();
        var maleBreedSelector = document.querySelector('select[name="set_breed"]');
        var femaleBreedSelector = document.querySelector('select[name="set_breed2"]');
        var breed1 = maleBreedSelector.options[maleBreedSelector.selectedIndex].value;
        var breed2 = femaleBreedSelector.options[femaleBreedSelector.selectedIndex].value;
        var uriComponents = window.location.pathname.matchAll(/\/test\/([^\/]*)\/([^\/]*)\/([^\/]*)\/([^\/]*)\/?/g);
        for (const match of uriComponents) {
            var breed1_ = match[1];
            var breed2_ = match[2];
            var cat1 = match[3];
            var cat2 = match[4];
            var newURI = window.location.origin + "/test/" + breed1 + "/" + breed2 + "/" + cat1 + "/" + cat2;
            window.location.replace(newURI);
    }}
    function genderButton(e) {
        e.preventDefault();
        var maleSelector = document.querySelector('select[name="set_sire_id"]');
        var femaleSelector = document.querySelector('select[name="set_dam_id"]');
        var cat1 = maleSelector.options[maleSelector.selectedIndex].value;
        var cat2 = femaleSelector.options[femaleSelector.selectedIndex].value;
        var uriComponents = window.location.pathname.matchAll(/\/test\/([^\/]*)\/([^\/]*)\/([^\/]*)\/([^\/]*)\/?/g);
        for (const match of uriComponents) {
            var breed1 = match[1];
            var breed2 = match[2];
            var cat1_ = match[3];
            var cat2_ = match[4];
            var newURI = window.location.origin + "/test/" + breed1 + "/" + breed2 + "/" + cat1 + "/" + cat2;
            window.location.replace(newURI);
    }}
    function buttonBehavior() {
        if (document.querySelector('#set_gender_button')) {
        document.querySelector('#set_gender_button').addEventListener("click", genderButton);
        }
        if (document.querySelector('#set_breed_button')) {
        document.querySelector('#set_breed_button').addEventListener("click", breedButton);
        }
        document.querySelector(".pull-right").style = "display:none;";
    }
    function regnumCheckboxBehavior() {
        var checkbox = document.querySelector('input[name="reg_num"]');
        if (checkbox) {
        var storedValue = localStorage.getItem('reg_num_checkbox');
        if (JSON.parse(storedValue)) {
            checkbox.checked = true;
        }
        if (checkbox.checked) {
            Array.prototype.slice.call(document.querySelectorAll('.reg_num')).forEach(div => div.setAttribute("style", "display:block;"))
        } else {
            Array.prototype.slice.call(document.querySelectorAll('.reg_num')).forEach(div => div.setAttribute("style", "display:none;"))
        }
        checkbox.addEventListener("click", function(){
            var checkbox = document.querySelector('input[name="reg_num"]');
            localStorage.setItem('reg_num_checkbox', JSON.stringify(checkbox.checked));
            if (checkbox.checked) {
                Array.prototype.slice.call(document.querySelectorAll('.reg_num')).forEach(div => div.setAttribute("style", "display:block;"))
            } else {
                Array.prototype.slice.call(document.querySelectorAll('.reg_num')).forEach(div => div.setAttribute("style", "display:none;"))
            }
        })
        }
    }
    function loadScripts() {
        if (document.querySelector('#set_gender_button')) {
            regnumCheckboxBehavior()
        }
        // Interactive searchable select (default select options no longer work from now on until reload)
        $('[name="set_breed"]').select2();
        $('[name="set_breed2"]').select2();
        if (document.querySelector('#set_gender_button')) {
            $('[name="set_sire_id"]').select2();
            $('[name="set_dam_id"]').select2();
        }
        setInterval(function(){
            if (document.querySelector('#set_breed_button')) {
                if (document.querySelector('#set_breed_button').value !== "Update") {
                    document.querySelector('#set_breed_button').value = "Update";
                }
            }
            if (document.querySelector('#set_gender_button')) {
                if (document.querySelector('#set_gender_button').value !== "Update") {
                    document.querySelector('#set_gender_button').value = "Update";
                }
            }
        }, 250)
    }
    buttonBehavior();
    loadScripts();
</script>
@endsection


@section('title', trans('cat.test_mating'))
@section('subtitle', trans('cat.test_mating_description'))

@section('cat-content')
<div id="description">
    {{ __('cat.test_mating_extended_description') }}
</div>
<div id="set">
    <div class="breed">
        {{ Form::open(['route' => ['cats.test', $breed->id, $breed2->id, $cat->id, $cat2->id]]) }}
        {!! FormField::select('set_breed', $breedList, ['label' => false, 'value' => $breed->id, 'placeholder' => __('app.select_from_existing_male_breeds')]) !!}
        {!! FormField::select('set_breed2', $breedList, ['label' => false, 'value' => $breed2->id, 'placeholder' => __('app.select_from_existing_female_breeds')]) !!}
        <div class="input-group">
            <span class="input-group-btn">
                {{ Form::submit(__('app.update'), ['class' => 'btn btn-info btn-sm', 'id' => 'set_breed_button']) }}
            </span>
        </div>
        {{ Form::close() }}
    </div>
</div>
@if ($breed->id > 1 && $breed2->id > 1)
<div id="set">
    <div class="gender">
        {{ Form::open(['route' => ['cats.test', $breed->id, $breed2->id, $cat->id, $cat2->id]]) }}
        {!! FormField::select('set_sire_id', $malePersonList, ['label' => false, 'value' => $cat->id, 'placeholder' => __('app.select_from_existing_males')]) !!}
        {!! FormField::select('set_dam_id', $femalePersonList, ['label' => false, 'value' => $cat2->id, 'placeholder' => __('app.select_from_existing_females')]) !!}
        <div class="input-group">
            <span class="input-group-btn">
                {{ Form::submit(__('app.update'), ['class' => 'btn btn-info btn-sm', 'id' => 'set_gender_button']) }}
            </span>
        </div>
        {{ Form::close() }}
    </div>
</div>
@if ($cat->id > 1 && $cat2->id > 1)
<div id="controls">
    <strong>Inbreeding:</strong>
        <span id="result"><i>F</i> = 0.0%</span><br>
        <input type="checkbox" name="reg_num"><label for="reg_num">{{ __('cat.display_reg_num') }}</label>
</div>
<div id="wrapper" class="family-tree">
    <div id="pedigree">
        @php $json = "" @endphp
        <table data-level="0">

            <tbody>
                @php $json .= "{" @endphp
                <tr class="offspring">

                    <td style="display: none;"><br><input class="ind" id="offspring" type="text" data="0" disabled><br><br>
                        @php $json .= "\"name\": \"" . "0" . ($generations >= 1 ? "\"," : "\"") @endphp

                    </td>

                    <td class="anc">

                        <table data-level="1">

                            <tbody>

                                @if ($cat && $generations >= 1)
                                @php $json .= "\"s\": {" @endphp
                                <tr class="s">
                                    <td> Sire:<br><input class="ind" id="s" type="text" data="{{$cat->id()}}" disabled><br>{{$cat->l($generations)}}<br>{{ $cat->breed }} {{ $cat->ems_color }} {{ $cat->dob() }}
                                        <div class="reg_num">
    @if ($cat->id != "1")
    {{ $cat->original_reg_num }}<br> 
        {{ $cat->last_reg_num }}<br> {{ $cat->reg_num_2 }}<br> {{ $cat->reg_num_3 }}
    @endif
    </div><br>
            @if ($cat->genetic_tests_file)
    <a href="{{ $cat->genetic_tests_file }}">{{ trans('cat.download_genetic_tests_file') }}</a><br>
    @endif
            @if ($cat->vet_confirmation)
    <a href="{{ $cat->vet_confirmation }}">{{ trans('cat.download_vet_confirmation') }}</a>
    @endif
                                        @php $json .= "\"name\": \"" . $cat->id() . ($generations >= 2 ? "\"," : "\"") @endphp
                                        
                                    </td>

                                    <td class="anc">

                                        <table data-level="2">

                                            <tbody>
                                                @if ($cat->s() && $generations >= 2)
                                                @php $json .= "\"s\": {" @endphp
                                                <tr class="s">

                                                    <td> <input class="ind" id="ss" type="text" data="{{$cat->s()->id()}}" disabled><br>{{$cat->s()->l($generations)}}<br>{{ $cat->s()->breed }} {{ $cat->s()->ems_color }} {{ $cat->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->id != "1")
    {{ $cat->s()->original_reg_num }}<br> 
        {{ $cat->s()->last_reg_num }}<br> {{ $cat->s()->reg_num_2 }}<br> {{ $cat->s()->reg_num_3 }}
    @endif
    </div><br>
            @if ($cat->s()->genetic_tests_file)
    <a href="{{ $cat->s()->genetic_tests_file }}">{{ trans('cat.download_genetic_tests_file') }}</a><br>
    @endif
            @if ($cat->s()->vet_confirmation)
    <a href="{{ $cat->s()->vet_confirmation }}">{{ trans('cat.download_vet_confirmation') }}</a>
    @endif
                                                        @php $json .= "\"name\": \"" . $cat->s()->id() . ($generations >= 3 ? "\"," : "\"") @endphp

                                                    </td>

                                                    <td class="anc">

                                                        <table data-level="3">

                                                            <tbody>
                                                                @if ($cat->s()->s() && $generations >= 3)
                                                                @php $json .= "\"s\": {" @endphp
                                                                <tr class="s">

                                                                    <td> <input class="ind" id="sss" type="text" data="{{$cat->s()->s()->id()}}" disabled><br>{{$cat->s()->s()->l($generations)}}<br>{{ $cat->s()->s()->breed }} {{ $cat->s()->s()->ems_color }} {{ $cat->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->id != "1")
    {{ $cat->s()->s()->original_reg_num }}<br> 
        {{ $cat->s()->s()->last_reg_num }}<br> {{ $cat->s()->s()->reg_num_2 }}<br> {{ $cat->s()->s()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->id() . ($generations >= 4 ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>
                                                                                @if ($cat->s()->s()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="ssss" type="text" data="{{$cat->s()->s()->s()->id()}}" disabled><br>{{$cat->s()->s()->s()->l($generations)}}<br>{{ $cat->s()->s()->s()->breed }} {{ $cat->s()->s()->s()->ems_color }} {{ $cat->s()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->s()->id != "1")
    {{ $cat->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat->s()->s()->s()->last_reg_num }}<br> {{ $cat->s()->s()->s()->reg_num_2 }}<br> {{ $cat->s()->s()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->s()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->s()->s()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sssss" data="{{$cat->s()->s()->s()->s()->id()}}" disabled><br>{{$cat->s()->s()->s()->s()->l($generations)}}<br>{{ $cat->s()->s()->s()->s()->breed }} {{ $cat->s()->s()->s()->s()->ems_color }} {{ $cat->s()->s()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->s()->s()->id != "1")
    {{ $cat->s()->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat->s()->s()->s()->s()->last_reg_num }}<br> {{ $cat->s()->s()->s()->s()->reg_num_2 }}<br> {{ $cat->s()->s()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->s()->s()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ssssd" data="{{$cat->s()->s()->s()->d()->id()}}" disabled><br>{{$cat->s()->s()->s()->d()->l($generations)}}<br>{{ $cat->s()->s()->s()->d()->breed }} {{ $cat->s()->s()->s()->d()->ems_color }} {{ $cat->s()->s()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->s()->d()->id != "1")
    {{ $cat->s()->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat->s()->s()->s()->d()->last_reg_num }}<br> {{ $cat->s()->s()->s()->d()->reg_num_2 }}<br> {{ $cat->s()->s()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->s()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}," @endphp
                                                                                @endif
                                                                                @if ($cat->s()->s()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="sssd" type="text" data="{{$cat->s()->s()->d()->id()}}" disabled><br>{{$cat->s()->s()->d()->l($generations)}}<br>{{ $cat->s()->s()->d()->breed }} {{ $cat->s()->s()->d()->ems_color }} {{ $cat->s()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->d()->id != "1")
    {{ $cat->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat->s()->s()->d()->last_reg_num }}<br> {{ $cat->s()->s()->d()->reg_num_2 }}<br> {{ $cat->s()->s()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->d()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->s()->s()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sssds" data="{{$cat->s()->s()->d()->s()->id()}}" disabled><br>{{$cat->s()->s()->d()->s()->l($generations)}}<br>{{ $cat->s()->s()->d()->s()->breed }} {{ $cat->s()->s()->d()->s()->ems_color }} {{ $cat->s()->s()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->d()->s()->id != "1")
    {{ $cat->s()->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat->s()->s()->d()->s()->last_reg_num }}<br> {{ $cat->s()->s()->d()->s()->reg_num_2 }}<br> {{ $cat->s()->s()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->s()->s()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sssdd" data="{{$cat->s()->s()->d()->d()->id()}}" disabled><br>{{$cat->s()->s()->d()->d()->l($generations)}}<br>{{ $cat->s()->s()->d()->d()->breed }} {{ $cat->s()->s()->d()->d()->ems_color }} {{ $cat->s()->s()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->d()->d()->id != "1")
    {{ $cat->s()->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat->s()->s()->d()->d()->last_reg_num }}<br> {{ $cat->s()->s()->d()->d()->reg_num_2 }}<br> {{ $cat->s()->s()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->d()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}" @endphp
                                                                                @endif
                                                                            </tbody>

                                                                        </table>



                                                                    </td>

                                                                </tr>
                                                                @php $json .= "}," @endphp
                                                                @endif
                                                                @if ($cat->s()->d() && $generations >= 3)
                                                                @php $json .= "\"d\": {" @endphp
                                                                <tr class="d">

                                                                    <td> <input class="ind" id="ssd" type="text" data="{{$cat->s()->d()->id()}}" disabled><br>{{$cat->s()->d()->l($generations)}}<br>{{ $cat->s()->d()->breed }} {{ $cat->s()->d()->ems_color }} {{ $cat->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->id != "1")
    {{ $cat->s()->d()->original_reg_num }}<br> 
        {{ $cat->s()->d()->last_reg_num }}<br> {{ $cat->s()->d()->reg_num_2 }}<br> {{ $cat->s()->d()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->id() . ($generations >= 4 ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>

                                                                                @if ($cat->s()->d()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="ssds" type="text" data="{{$cat->s()->d()->s()->id()}}" disabled><br>{{$cat->s()->d()->s()->l($generations)}}<br>{{ $cat->s()->d()->s()->breed }} {{ $cat->s()->d()->s()->ems_color }} {{ $cat->s()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->s()->id != "1")
    {{ $cat->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat->s()->d()->s()->last_reg_num }}<br> {{ $cat->s()->d()->s()->reg_num_2 }}<br> {{ $cat->s()->d()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->s()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->s()->d()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ssdss" data="{{$cat->s()->d()->s()->s()->id()}}" disabled><br>{{$cat->s()->d()->s()->s()->l($generations)}}<br>{{ $cat->s()->d()->s()->s()->breed }} {{ $cat->s()->d()->s()->s()->ems_color }} {{ $cat->s()->d()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->s()->s()->id != "1")
    {{ $cat->s()->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat->s()->d()->s()->s()->last_reg_num }}<br> {{ $cat->s()->d()->s()->s()->reg_num_2 }}<br> {{ $cat->s()->d()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->s()->d()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ssdsd" data="{{$cat->s()->d()->s()->d()->id()}}" disabled><br>{{$cat->s()->d()->s()->d()->l($generations)}}<br>{{ $cat->s()->d()->s()->d()->breed }} {{ $cat->s()->d()->s()->d()->ems_color }} {{ $cat->s()->d()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->s()->d()->id != "1")
    {{ $cat->s()->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat->s()->d()->s()->d()->last_reg_num }}<br> {{ $cat->s()->d()->s()->d()->reg_num_2 }}<br> {{ $cat->s()->d()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->s()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}," @endphp
                                                                                @endif
                                                                                @if ($cat->s()->d()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="ssdd" type="text" data="{{$cat->s()->d()->d()->id()}}" disabled><br>{{$cat->s()->d()->d()->l($generations)}}<br>{{ $cat->s()->d()->d()->breed }} {{ $cat->s()->d()->d()->ems_color }} {{ $cat->s()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->d()->id != "1")
    {{ $cat->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat->s()->d()->d()->last_reg_num }}<br> {{ $cat->s()->d()->d()->reg_num_2 }}<br> {{ $cat->s()->d()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->d()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->s()->d()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ssdds" data="{{$cat->s()->d()->d()->s()->id()}}" disabled><br>{{$cat->s()->d()->d()->s()->l($generations)}}<br>{{ $cat->s()->d()->d()->s()->breed }} {{ $cat->s()->d()->d()->s()->ems_color }} {{ $cat->s()->d()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->d()->s()->id != "1")
    {{ $cat->s()->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat->s()->d()->d()->s()->last_reg_num }}<br> {{ $cat->s()->d()->d()->s()->reg_num_2 }}<br> {{ $cat->s()->d()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->s()->d()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ssddd" data="{{$cat->s()->d()->d()->d()->id()}}" disabled><br>{{$cat->s()->d()->d()->d()->l($generations)}}<br>{{ $cat->s()->d()->d()->d()->breed }} {{ $cat->s()->d()->d()->d()->ems_color }} {{ $cat->s()->d()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->d()->d()->id != "1")
    {{ $cat->s()->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat->s()->d()->d()->d()->last_reg_num }}<br> {{ $cat->s()->d()->d()->d()->reg_num_2 }}<br> {{ $cat->s()->d()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->d()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}" @endphp
                                                                                @endif
                                                                            </tbody>

                                                                        </table>



                                                                    </td>

                                                                </tr>
                                                                @php $json .= "}" @endphp
                                                                @endif
                                                            </tbody>

                                                        </table>



                                                    </td>

                                                </tr>
                                                @php $json .= "}," @endphp
                                                @endif
                                                @if ($cat->d() && $generations >= 2)
                                                @php $json .= "\"d\": {" @endphp
                                                <tr class="d">

                                                    <td> <input class="ind" id="sd" type="text" data="{{$cat->d()->id()}}" disabled><br>{{$cat->d()->l($generations)}}<br>{{ $cat->d()->breed }} {{ $cat->d()->ems_color }} {{ $cat->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->id != "1")
    {{ $cat->d()->original_reg_num }}<br> 
        {{ $cat->d()->last_reg_num }}<br> {{ $cat->d()->reg_num_2 }}<br> {{ $cat->d()->reg_num_3 }}
    @endif
    </div>
            @if ($cat->d()->genetic_tests_file)
    <a href="{{ $cat->d()->genetic_tests_file }}">{{ trans('cat.download_genetic_tests_file') }}</a><br>
    @endif
            @if ($cat->d()->vet_confirmation)
    <a href="{{ $cat->d()->vet_confirmation }}">{{ trans('cat.download_vet_confirmation') }}</a>
    @endif
                                                        @php $json .= "\"name\": \"" . $cat->d()->id() . ($generations >= 3 ? "\"," : "\"") @endphp

                                                    </td>

                                                    <td class="anc">

                                                        <table data-level="3">

                                                            <tbody>

                                                                @if ($cat->d()->s() && $generations >= 3)
                                                                @php $json .= "\"s\": {" @endphp
                                                                <tr class="s">

                                                                    <td> <input class="ind" id="sds" type="text" data="{{$cat->d()->s()->id()}}" disabled><br>{{$cat->d()->s()->l($generations)}}<br>{{ $cat->d()->s()->breed }} {{ $cat->d()->s()->ems_color }} {{ $cat->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->id != "1")
    {{ $cat->d()->s()->original_reg_num }}<br> 
        {{ $cat->d()->s()->last_reg_num }}<br> {{ $cat->d()->s()->reg_num_2 }}<br> {{ $cat->d()->s()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->id() . ($generations >= 4 ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>
                                                                                @if ($cat->d()->s()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="sdss" type="text" data="{{$cat->d()->s()->s()->id()}}" disabled><br>{{$cat->d()->s()->s()->l($generations)}}<br>{{ $cat->d()->s()->s()->breed }} {{ $cat->d()->s()->s()->ems_color }} {{ $cat->d()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->s()->id != "1")
    {{ $cat->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat->d()->s()->s()->last_reg_num }}<br> {{ $cat->d()->s()->s()->reg_num_2 }}<br> {{ $cat->d()->s()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->s()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->d()->s()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sdsss" data="{{$cat->d()->s()->s()->s()->id()}}" disabled><br>{{$cat->d()->s()->s()->s()->l($generations)}}<br>{{ $cat->d()->s()->s()->s()->breed }} {{ $cat->d()->s()->s()->s()->ems_color }} {{ $cat->d()->s()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->s()->s()->id != "1")
    {{ $cat->d()->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat->d()->s()->s()->s()->last_reg_num }}<br> {{ $cat->d()->s()->s()->s()->reg_num_2 }}<br> {{ $cat->d()->s()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->d()->s()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sdssd" data="{{$cat->d()->s()->s()->d()->id()}}" disabled><br>{{$cat->d()->s()->s()->d()->l($generations)}}<br>{{ $cat->d()->s()->s()->d()->breed }} {{ $cat->d()->s()->s()->d()->ems_color }} {{ $cat->d()->s()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->s()->d()->id != "1")
    {{ $cat->d()->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat->d()->s()->s()->d()->last_reg_num }}<br> {{ $cat->d()->s()->s()->d()->reg_num_2 }}<br> {{ $cat->d()->s()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->s()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}," @endphp
                                                                                @endif

                                                                                @if ($cat->d()->s()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="sdsd" type="text" data="{{$cat->d()->s()->d()->id()}}" disabled><br>{{$cat->d()->s()->d()->l($generations)}}<br>{{ $cat->d()->s()->d()->breed }} {{ $cat->d()->s()->d()->ems_color }} {{ $cat->d()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->d()->id != "1")
    {{ $cat->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat->d()->s()->d()->last_reg_num }}<br> {{ $cat->d()->s()->d()->reg_num_2 }}<br> {{ $cat->d()->s()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->d()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->d()->s()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sdsds" data="{{$cat->d()->s()->d()->s()->id()}}" disabled><br>{{$cat->d()->s()->d()->s()->l($generations)}}<br>{{ $cat->d()->s()->d()->s()->breed }} {{ $cat->d()->s()->d()->s()->ems_color }} {{ $cat->d()->s()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->d()->s()->id != "1")
    {{ $cat->d()->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat->d()->s()->d()->s()->last_reg_num }}<br> {{ $cat->d()->s()->d()->s()->reg_num_2 }}<br> {{ $cat->d()->s()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->d()->s()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sdsdd" data="{{$cat->d()->s()->d()->d()->id()}}" disabled><br>{{$cat->d()->s()->d()->d()->l($generations)}}<br>{{ $cat->d()->s()->d()->d()->breed }} {{ $cat->d()->s()->d()->d()->ems_color }} {{ $cat->d()->s()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->d()->d()->id != "1")
    {{ $cat->d()->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat->d()->s()->d()->d()->last_reg_num }}<br> {{ $cat->d()->s()->d()->d()->reg_num_2 }}<br> {{ $cat->d()->s()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->d()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}" @endphp
                                                                                @endif
                                                                            </tbody>

                                                                        </table>



                                                                    </td>

                                                                </tr>
                                                                @php $json .= "}," @endphp
                                                                @endif
                                                                @if ($cat->d()->d() && $generations >= 3)
                                                                @php $json .= "\"d\": {" @endphp
                                                                <tr class="d">

                                                                    <td> <input class="ind" id="sdd" type="text" data="{{$cat->d()->d()->id()}}" disabled><br>{{$cat->d()->d()->l($generations)}}<br>{{ $cat->d()->d()->breed }} {{ $cat->d()->d()->ems_color }} {{ $cat->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->id != "1")
    {{ $cat->d()->d()->original_reg_num }}<br> 
        {{ $cat->d()->d()->last_reg_num }}<br> {{ $cat->d()->d()->reg_num_2 }}<br> {{ $cat->d()->d()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->id() . ($generations >= 4 ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>

                                                                                @if ($cat->d()->d()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="sdds" type="text" data="{{$cat->d()->d()->s()->id()}}" disabled><br>{{$cat->d()->d()->s()->l($generations)}}<br>{{ $cat->d()->d()->s()->breed }} {{ $cat->d()->d()->s()->ems_color }} {{ $cat->d()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->s()->id != "1")
    {{ $cat->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat->d()->d()->s()->last_reg_num }}<br> {{ $cat->d()->d()->s()->reg_num_2 }}<br> {{ $cat->d()->d()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->s()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->d()->d()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sddss" data="{{$cat->d()->d()->s()->s()->id()}}" disabled><br>{{$cat->d()->d()->s()->s()->l($generations)}}<br>{{ $cat->d()->d()->s()->s()->breed }} {{ $cat->d()->d()->s()->s()->ems_color }} {{ $cat->d()->d()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->s()->s()->id != "1")
    {{ $cat->d()->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat->d()->d()->s()->s()->last_reg_num }}<br> {{ $cat->d()->d()->s()->s()->reg_num_2 }}<br> {{ $cat->d()->d()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->d()->d()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sddsd" data="{{$cat->d()->d()->s()->d()->id()}}" disabled><br>{{$cat->d()->d()->s()->d()->l($generations)}}<br>{{ $cat->d()->d()->s()->d()->breed }} {{ $cat->d()->d()->s()->d()->ems_color }} {{ $cat->d()->d()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->s()->d()->id != "1")
    {{ $cat->d()->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat->d()->d()->s()->d()->last_reg_num }}<br> {{ $cat->d()->d()->s()->d()->reg_num_2 }}<br> {{ $cat->d()->d()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->s()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}," @endphp
                                                                                @endif
                                                                                @if ($cat->d()->d()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="sddd" type="text" data="{{$cat->d()->d()->d()->id()}}" disabled><br>{{$cat->d()->d()->d()->l($generations)}}<br>{{ $cat->d()->d()->d()->breed }} {{ $cat->d()->d()->d()->ems_color }} {{ $cat->d()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->d()->id != "1")
    {{ $cat->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat->d()->d()->d()->last_reg_num }}<br> {{ $cat->d()->d()->d()->reg_num_2 }}<br> {{ $cat->d()->d()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->d()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->d()->d()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sddds" data="{{$cat->d()->d()->d()->s()->id()}}" disabled><br>{{$cat->d()->d()->d()->s()->l($generations)}}<br>{{ $cat->d()->d()->d()->s()->breed }} {{ $cat->d()->d()->d()->s()->ems_color }} {{ $cat->d()->d()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->d()->s()->id != "1")
    {{ $cat->d()->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat->d()->d()->d()->s()->last_reg_num }}<br> {{ $cat->d()->d()->d()->s()->reg_num_2 }}<br> {{ $cat->d()->d()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->d()->d()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sdddd" data="{{$cat->d()->d()->d()->d()->id()}}" disabled><br>{{$cat->d()->d()->d()->d()->l($generations)}}<br>{{ $cat->d()->d()->d()->d()->breed }} {{ $cat->d()->d()->d()->d()->ems_color }} {{ $cat->d()->d()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->d()->d()->id != "1")
    {{ $cat->d()->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat->d()->d()->d()->d()->last_reg_num }}<br> {{ $cat->d()->d()->d()->d()->reg_num_2 }}<br> {{ $cat->d()->d()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->d()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}" @endphp
                                                                                @endif
                                                                            </tbody>

                                                                        </table>



                                                                    </td>

                                                                </tr>
                                                                @php $json .= "}" @endphp
                                                                @endif
                                                            </tbody>

                                                        </table>



                                                    </td>

                                                </tr>
                                                @php $json .= "}" @endphp
                                                @endif
                                            </tbody>

                                        </table>



                                    </td>

                                </tr>
                                @php $json .= "}," @endphp
                                @endif

                                @if ($cat2 && $generations >= 1)
                                @php $json .= "\"d\": {" @endphp
                                <tr class="d">

                                    <td> Dam:<br>

                                        <input class="ind" id="d" type="text" data="{{$cat2->id()}}" disabled><br>{{$cat2->l($generations)}}<br>{{ $cat2->breed }} {{ $cat2->ems_color }} {{ $cat2->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->id != "1")
    {{ $cat2->original_reg_num }}<br> 
        {{ $cat2->last_reg_num }}<br> {{ $cat2->reg_num_2 }}<br> {{ $cat2->reg_num_3 }}
    @endif
    </div>
            @if ($cat2->genetic_tests_file)
    <a href="{{ $cat2->genetic_tests_file }}">{{ trans('cat.download_genetic_tests_file') }}</a><br>
    @endif
            @if ($cat2->vet_confirmation)
    <a href="{{ $cat2->vet_confirmation }}">{{ trans('cat.download_vet_confirmation') }}</a>
    @endif
                                        @php $json .= "\"name\": \"" . $cat2->id() . ($generations >= 2 ? "\"," : "\"") @endphp



                                    </td>

                                    <td class="anc">

                                        <table data-level="2">

                                            <tbody>
                                                @if ($cat2->s() && $generations >= 2)
                                                @php $json .= "\"s\": {" @endphp
                                                <tr class="s">

                                                    <td> <input class="ind" id="ds" type="text" data="{{$cat2->s()->id()}}" disabled><br>{{$cat2->s()->l($generations)}}<br>{{ $cat2->s()->breed }} {{ $cat2->s()->ems_color }} {{ $cat2->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->id != "1")
    {{ $cat2->s()->original_reg_num }}<br> 
        {{ $cat2->s()->last_reg_num }}<br> {{ $cat2->s()->reg_num_2 }}<br> {{ $cat2->s()->reg_num_3 }}
    @endif
    </div>
            @if ($cat2->s()->genetic_tests_file)
    <a href="{{ $cat2->s()->genetic_tests_file }}">{{ trans('cat.download_genetic_tests_file') }}</a><br>
    @endif
            @if ($cat2->s()->vet_confirmation)
    <a href="{{ $cat2->s()->vet_confirmation }}">{{ trans('cat.download_vet_confirmation') }}</a>
    @endif
                                                        @php $json .= "\"name\": \"" . $cat2->s()->id() . ($generations >= 3 ? "\"," : "\"") @endphp

                                                    </td>

                                                    <td class="anc">

                                                        <table data-level="3">

                                                            <tbody>
                                                                @if ($cat2->s()->s() && $generations >= 3)
                                                                @php $json .= "\"s\": {" @endphp
                                                                <tr class="s">

                                                                    <td> <input class="ind" id="dss" type="text" data="{{$cat2->s()->s()->id()}}" disabled><br>{{$cat2->s()->s()->l($generations)}}<br>{{ $cat2->s()->s()->breed }} {{ $cat2->s()->s()->ems_color }} {{ $cat2->s()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->s()->id != "1")
    {{ $cat2->s()->s()->original_reg_num }}<br> 
        {{ $cat2->s()->s()->last_reg_num }}<br> {{ $cat2->s()->s()->reg_num_2 }}<br> {{ $cat2->s()->s()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat2->s()->s()->id() . ($generations >= 4 ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>
                                                                                @if ($cat2->s()->s()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="dsss" type="text" data="{{$cat2->s()->s()->s()->id()}}" disabled><br>{{$cat2->s()->s()->s()->l($generations)}}<br>{{ $cat2->s()->s()->s()->breed }} {{ $cat2->s()->s()->s()->ems_color }} {{ $cat2->s()->s()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->s()->s()->id != "1")
    {{ $cat2->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat2->s()->s()->s()->last_reg_num }}<br> {{ $cat2->s()->s()->s()->reg_num_2 }}<br> {{ $cat2->s()->s()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat2->s()->s()->s()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat2->s()->s()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dssss" data="{{$cat2->s()->s()->s()->s()->id()}}" disabled><br>{{$cat2->s()->s()->s()->s()->l($generations)}}<br>{{ $cat2->s()->s()->s()->s()->breed }} {{ $cat2->s()->s()->s()->s()->ems_color }} {{ $cat2->s()->s()->s()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->s()->s()->s()->id != "1")
    {{ $cat2->s()->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat2->s()->s()->s()->s()->last_reg_num }}<br> {{ $cat2->s()->s()->s()->s()->reg_num_2 }}<br> {{ $cat2->s()->s()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->s()->s()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat2->s()->s()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dsssd" data="{{$cat2->s()->s()->s()->d()->id()}}" disabled><br>{{$cat2->s()->s()->s()->d()->l($generations)}}<br>{{ $cat2->s()->s()->s()->d()->breed }} {{ $cat2->s()->s()->s()->d()->ems_color }} {{ $cat2->s()->s()->s()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->s()->s()->d()->id != "1")
    {{ $cat2->s()->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat2->s()->s()->s()->d()->last_reg_num }}<br> {{ $cat2->s()->s()->s()->d()->reg_num_2 }}<br> {{ $cat2->s()->s()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->s()->s()->s()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}," @endphp
                                                                                @endif
                                                                                @if ($cat2->s()->s()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="dssd" type="text" data="{{$cat2->s()->s()->d()->id()}}" disabled><br>{{$cat2->s()->s()->d()->l($generations)}}<br>{{ $cat2->s()->s()->d()->breed }} {{ $cat2->s()->s()->d()->ems_color }} {{ $cat2->s()->s()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->s()->d()->id != "1")
    {{ $cat2->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat2->s()->s()->d()->last_reg_num }}<br> {{ $cat2->s()->s()->d()->reg_num_2 }}<br> {{ $cat2->s()->s()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat2->s()->s()->d()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat2->s()->s()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dssds" data="{{$cat2->s()->s()->d()->s()->id()}}" disabled><br>{{$cat2->s()->s()->d()->s()->l($generations)}}<br>{{ $cat2->s()->s()->d()->s()->breed }} {{ $cat2->s()->s()->d()->s()->ems_color }} {{ $cat2->s()->s()->d()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->s()->d()->s()->id != "1")
    {{ $cat2->s()->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat2->s()->s()->d()->s()->last_reg_num }}<br> {{ $cat2->s()->s()->d()->s()->reg_num_2 }}<br> {{ $cat2->s()->s()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->s()->s()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat2->s()->s()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dssdd" data="{{$cat2->s()->s()->d()->d()->id()}}" disabled><br>{{$cat2->s()->s()->d()->d()->l($generations)}}<br>{{ $cat2->s()->s()->d()->d()->breed }} {{ $cat2->s()->s()->d()->d()->ems_color }} {{ $cat2->s()->s()->d()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->s()->d()->d()->id != "1")
    {{ $cat2->s()->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat2->s()->s()->d()->d()->last_reg_num }}<br> {{ $cat2->s()->s()->d()->d()->reg_num_2 }}<br> {{ $cat2->s()->s()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->s()->s()->d()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}" @endphp
                                                                                @endif
                                                                            </tbody>

                                                                        </table>



                                                                    </td>

                                                                </tr>
                                                                @php $json .= "}," @endphp
                                                                @endif
                                                                @if ($cat2->s()->d() && $generations >= 3)
                                                                @php $json .= "\"d\": {" @endphp
                                                                <tr class="d">

                                                                    <td> <input class="ind" id="dsd" type="text" data="{{$cat2->s()->d()->id()}}" disabled><br>{{$cat2->s()->d()->l($generations)}}<br>{{ $cat2->s()->d()->breed }} {{ $cat2->s()->d()->ems_color }} {{ $cat2->s()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->d()->id != "1")
    {{ $cat2->s()->d()->original_reg_num }}<br> 
        {{ $cat2->s()->d()->last_reg_num }}<br> {{ $cat2->s()->d()->reg_num_2 }}<br> {{ $cat2->s()->d()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat2->s()->d()->id() . ($generations >= 4 ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>

                                                                                @if ($cat2->s()->d()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="dsds" type="text" data="{{$cat2->s()->d()->s()->id()}}" disabled><br>{{$cat2->s()->d()->s()->l($generations)}}<br>{{ $cat2->s()->d()->s()->breed }} {{ $cat2->s()->d()->s()->ems_color }} {{ $cat2->s()->d()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->d()->s()->id != "1")
    {{ $cat2->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat2->s()->d()->s()->last_reg_num }}<br> {{ $cat2->s()->d()->s()->reg_num_2 }}<br> {{ $cat2->s()->d()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat2->s()->d()->s()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat2->s()->d()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dsdss" data="{{$cat2->s()->d()->s()->s()->id()}}" disabled><br>{{$cat2->s()->d()->s()->s()->l($generations)}}<br>{{ $cat2->s()->d()->s()->s()->breed }} {{ $cat2->s()->d()->s()->s()->ems_color }} {{ $cat2->s()->d()->s()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->d()->s()->s()->id != "1")
    {{ $cat2->s()->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat2->s()->d()->s()->s()->last_reg_num }}<br> {{ $cat2->s()->d()->s()->s()->reg_num_2 }}<br> {{ $cat2->s()->d()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->s()->d()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat2->s()->d()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dsdsd" data="{{$cat2->s()->d()->s()->d()->id()}}" disabled><br>{{$cat2->s()->d()->s()->d()->l($generations)}}<br>{{ $cat2->s()->d()->s()->d()->breed }} {{ $cat2->s()->d()->s()->d()->ems_color }} {{ $cat2->s()->d()->s()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->d()->s()->d()->id != "1")
    {{ $cat2->s()->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat2->s()->d()->s()->d()->last_reg_num }}<br> {{ $cat2->s()->d()->s()->d()->reg_num_2 }}<br> {{ $cat2->s()->d()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->s()->d()->s()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}," @endphp
                                                                                @endif
                                                                                @if ($cat2->s()->d()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="dsdd" type="text" data="{{$cat2->s()->d()->d()->id()}}" disabled><br>{{$cat2->s()->d()->d()->l($generations)}}<br>{{ $cat2->s()->d()->d()->breed }} {{ $cat2->s()->d()->d()->ems_color }} {{ $cat2->s()->d()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->d()->d()->id != "1")
    {{ $cat2->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat2->s()->d()->d()->last_reg_num }}<br> {{ $cat2->s()->d()->d()->reg_num_2 }}<br> {{ $cat2->s()->d()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat2->s()->d()->d()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat2->s()->d()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dsdds" data="{{$cat2->s()->d()->d()->s()->id()}}" disabled><br>{{$cat2->s()->d()->d()->s()->l($generations)}}<br>{{ $cat2->s()->d()->d()->s()->breed }} {{ $cat2->s()->d()->d()->s()->ems_color }} {{ $cat2->s()->d()->d()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->d()->d()->s()->id != "1")
    {{ $cat2->s()->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat2->s()->d()->d()->s()->last_reg_num }}<br> {{ $cat2->s()->d()->d()->s()->reg_num_2 }}<br> {{ $cat2->s()->d()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->s()->d()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat2->s()->d()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dsddd" data="{{$cat2->s()->d()->d()->d()->id()}}" disabled><br>{{$cat2->s()->d()->d()->d()->l($generations)}}<br>{{ $cat2->s()->d()->d()->d()->breed }} {{ $cat2->s()->d()->d()->d()->ems_color }} {{ $cat2->s()->d()->d()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->s()->d()->d()->d()->id != "1")
    {{ $cat2->s()->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat2->s()->d()->d()->d()->last_reg_num }}<br> {{ $cat2->s()->d()->d()->d()->reg_num_2 }}<br> {{ $cat2->s()->d()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->s()->d()->d()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}" @endphp
                                                                                @endif
                                                                            </tbody>

                                                                        </table>



                                                                    </td>

                                                                </tr>
                                                                @php $json .= "}" @endphp
                                                                @endif
                                                            </tbody>

                                                        </table>



                                                    </td>

                                                </tr>
                                                @php $json .= "}," @endphp
                                                @endif
                                                @if ($cat2->d() && $generations >= 2)
                                                @php $json .= "\"d\": {" @endphp
                                                <tr class="d">

                                                    <td> <input class="ind" id="dd" type="text" data="{{$cat2->d()->id()}}" disabled><br>{{$cat2->d()->l($generations)}}<br>{{ $cat2->d()->breed }} {{ $cat2->d()->ems_color }} {{ $cat2->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->id != "1")
    {{ $cat2->d()->original_reg_num }}<br> 
        {{ $cat2->d()->last_reg_num }}<br> {{ $cat2->d()->reg_num_2 }}<br> {{ $cat2->d()->reg_num_3 }}
    @endif
    </div>
            @if ($cat2->d()->genetic_tests_file)
    <a href="{{ $cat2->d()->genetic_tests_file }}">{{ trans('cat.download_genetic_tests_file') }}</a><br>
    @endif
            @if ($cat2->d()->vet_confirmation)
    <a href="{{ $cat2->d()->vet_confirmation }}">{{ trans('cat.download_vet_confirmation') }}</a>
    @endif
                                                        @php $json .= "\"name\": \"" . $cat2->d()->id() . ($generations >= 3 ? "\"," : "\"") @endphp

                                                    </td>

                                                    <td class="anc">

                                                        <table data-level="3">

                                                            <tbody>

                                                                @if ($cat2->d()->s() && $generations >= 3)
                                                                @php $json .= "\"s\": {" @endphp
                                                                <tr class="s">

                                                                    <td> <input class="ind" id="dds" type="text" data="{{$cat2->d()->s()->id()}}" disabled><br>{{$cat2->d()->s()->l($generations)}}<br>{{ $cat2->d()->s()->breed }} {{ $cat2->d()->s()->ems_color }} {{ $cat2->d()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->s()->id != "1")
    {{ $cat2->d()->s()->original_reg_num }}<br> 
        {{ $cat2->d()->s()->last_reg_num }}<br> {{ $cat2->d()->s()->reg_num_2 }}<br> {{ $cat2->d()->s()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat2->d()->s()->id() . ($generations >= 4 ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>

                                                                                @if ($cat2->d()->s()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="ddss" type="text" data="{{$cat2->d()->s()->s()->id()}}" disabled><br>{{$cat2->d()->s()->s()->l($generations)}}<br>{{ $cat2->d()->s()->s()->breed }} {{ $cat2->d()->s()->s()->ems_color }} {{ $cat2->d()->s()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->s()->s()->id != "1")
    {{ $cat2->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat2->d()->s()->s()->last_reg_num }}<br> {{ $cat2->d()->s()->s()->reg_num_2 }}<br> {{ $cat2->d()->s()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat2->d()->s()->s()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat2->d()->s()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ddsss" data="{{$cat2->d()->s()->s()->s()->id()}}" disabled><br>{{$cat2->d()->s()->s()->s()->l($generations)}}<br>{{ $cat2->d()->s()->s()->s()->breed }} {{ $cat2->d()->s()->s()->s()->ems_color }} {{ $cat2->d()->s()->s()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->s()->s()->s()->id != "1")
    {{ $cat2->d()->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat2->d()->s()->s()->s()->last_reg_num }}<br> {{ $cat2->d()->s()->s()->s()->reg_num_2 }}<br> {{ $cat2->d()->s()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->d()->s()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif

                                                                                                @if ($cat2->d()->s()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ddssd" data="{{$cat2->d()->s()->s()->d()->id()}}" disabled><br>{{$cat2->d()->s()->s()->d()->l($generations)}}<br>{{ $cat2->d()->s()->s()->d()->breed }} {{ $cat2->d()->s()->s()->d()->ems_color }} {{ $cat2->d()->s()->s()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->s()->s()->d()->id != "1")
    {{ $cat2->d()->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat2->d()->s()->s()->d()->last_reg_num }}<br> {{ $cat2->d()->s()->s()->d()->reg_num_2 }}<br> {{ $cat2->d()->s()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->d()->s()->s()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}," @endphp
                                                                                @endif

                                                                                @if ($cat2->d()->s()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="ddsd" type="text" data="{{$cat2->d()->s()->d()->id()}}" disabled><br>{{$cat2->d()->s()->d()->l($generations)}}<br>{{ $cat2->d()->s()->d()->breed }} {{ $cat2->d()->s()->d()->ems_color }} {{ $cat2->d()->s()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->s()->d()->id != "1")
    {{ $cat2->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat2->d()->s()->d()->last_reg_num }}<br> {{ $cat2->d()->s()->d()->reg_num_2 }}<br> {{ $cat2->d()->s()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat2->d()->s()->d()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>

                                                                                                @if ($cat2->d()->s()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ddsds" data="{{$cat2->d()->s()->d()->s()->id()}}" disabled><br>{{$cat2->d()->s()->d()->s()->l($generations)}}<br>{{ $cat2->d()->s()->d()->s()->breed }} {{ $cat2->d()->s()->d()->s()->ems_color }} {{ $cat2->d()->s()->d()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->s()->d()->s()->id != "1")
    {{ $cat2->d()->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat2->d()->s()->d()->s()->last_reg_num }}<br> {{ $cat2->d()->s()->d()->s()->reg_num_2 }}<br> {{ $cat2->d()->s()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->d()->s()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat2->d()->s()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ddsdd" data="{{$cat2->d()->s()->d()->d()->id()}}" disabled><br>{{$cat2->d()->s()->d()->d()->l($generations)}}<br>{{ $cat2->d()->s()->d()->d()->breed }} {{ $cat2->d()->s()->d()->d()->ems_color }} {{ $cat2->d()->s()->d()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->s()->d()->d()->id != "1")
    {{ $cat2->d()->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat2->d()->s()->d()->d()->last_reg_num }}<br> {{ $cat2->d()->s()->d()->d()->reg_num_2 }}<br> {{ $cat2->d()->s()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->d()->s()->d()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}" @endphp
                                                                                @endif
                                                                            </tbody>

                                                                        </table>



                                                                    </td>

                                                                </tr>
                                                                @php $json .= "}," @endphp
                                                                @endif
                                                                @if ($cat2->d()->d() && $generations >= 3)
                                                                @php $json .= "\"d\": {" @endphp
                                                                <tr class="d">

                                                                    <td> <input class="ind" id="ddd" type="text" data="{{$cat2->d()->d()->id()}}" disabled><br>{{$cat2->d()->d()->l($generations)}}<br>{{ $cat2->d()->d()->breed }} {{ $cat2->d()->d()->ems_color }} {{ $cat2->d()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->d()->id != "1")
    {{ $cat2->d()->d()->original_reg_num }}<br> 
        {{ $cat2->d()->d()->last_reg_num }}<br> {{ $cat2->d()->d()->reg_num_2 }}<br> {{ $cat2->d()->d()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat2->d()->d()->id() . ($generations >= 4 ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>
                                                                                @if ($cat2->d()->d()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="ddds" type="text" data="{{$cat2->d()->d()->s()->id()}}" disabled><br>{{$cat2->d()->d()->s()->l($generations)}}<br>{{ $cat2->d()->d()->s()->breed }} {{ $cat2->d()->d()->s()->ems_color }} {{ $cat2->d()->d()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->d()->s()->id != "1")
    {{ $cat2->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat2->d()->d()->s()->last_reg_num }}<br> {{ $cat2->d()->d()->s()->reg_num_2 }}<br> {{ $cat2->d()->d()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat2->d()->d()->s()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat2->d()->d()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dddss" data="{{$cat2->d()->d()->s()->s()->id()}}" disabled><br>{{$cat2->d()->d()->s()->s()->l($generations)}}<br>{{ $cat2->d()->d()->s()->s()->breed }} {{ $cat2->d()->d()->s()->s()->ems_color }} {{ $cat2->d()->d()->s()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->d()->s()->s()->id != "1")
    {{ $cat2->d()->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat2->d()->d()->s()->s()->last_reg_num }}<br> {{ $cat2->d()->d()->s()->s()->reg_num_2 }}<br> {{ $cat2->d()->d()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->d()->d()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif

                                                                                                @if ($cat2->d()->d()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dddsd" data="{{$cat2->d()->d()->s()->d()->id()}}" disabled><br>{{$cat2->d()->d()->s()->d()->l($generations)}}<br>{{ $cat2->d()->d()->s()->d()->breed }} {{ $cat2->d()->d()->s()->d()->ems_color }} {{ $cat2->d()->d()->s()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->d()->s()->d()->id != "1")
    {{ $cat2->d()->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat2->d()->d()->s()->d()->last_reg_num }}<br> {{ $cat2->d()->d()->s()->d()->reg_num_2 }}<br> {{ $cat2->d()->d()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->d()->d()->s()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}," @endphp
                                                                                @endif
                                                                                @if ($cat2->d()->d()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="dddd" type="text" data="{{$cat2->d()->d()->d()->id()}}" disabled><br>{{$cat2->d()->d()->d()->l($generations)}}<br>{{ $cat2->d()->d()->d()->breed }} {{ $cat2->d()->d()->d()->ems_color }} {{ $cat2->d()->d()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->d()->d()->id != "1")
    {{ $cat2->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat2->d()->d()->d()->last_reg_num }}<br> {{ $cat2->d()->d()->d()->reg_num_2 }}<br> {{ $cat2->d()->d()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat2->d()->d()->d()->id() . ($generations >= 5 ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat2->d()->d()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dddds" data="{{$cat2->d()->d()->d()->s()->id()}}" disabled><br>{{$cat2->d()->d()->d()->s()->l($generations)}}<br>{{ $cat2->d()->d()->d()->s()->breed }} {{ $cat2->d()->d()->d()->s()->ems_color }} {{ $cat2->d()->d()->d()->s()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->d()->d()->s()->id != "1")
    {{ $cat2->d()->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat2->d()->d()->d()->s()->last_reg_num }}<br> {{ $cat2->d()->d()->d()->s()->reg_num_2 }}<br> {{ $cat2->d()->d()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->d()->d()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif

                                                                                                @if ($cat2->d()->d()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ddddd" data="{{$cat2->d()->d()->d()->d()->id()}}" disabled><br>{{$cat2->d()->d()->d()->d()->l($generations)}}<br>{{ $cat2->d()->d()->d()->d()->breed }} {{ $cat2->d()->d()->d()->d()->ems_color }} {{ $cat2->d()->d()->d()->d()->dob() }}<br>
                                        <div class="reg_num">
    @if ($cat2->d()->d()->d()->d()->id != "1")
    {{ $cat2->d()->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat2->d()->d()->d()->d()->last_reg_num }}<br> {{ $cat2->d()->d()->d()->d()->reg_num_2 }}<br> {{ $cat2->d()->d()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat2->d()->d()->d()->d()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}" @endphp
                                                                                                @endif
                                                                                            </tbody>
                                                                                        </table>


                                                                                    </td>

                                                                                </tr>
                                                                                @php $json .= "}" @endphp
                                                                                @endif
                                                                            </tbody>

                                                                        </table>



                                                                    </td>

                                                                </tr>
                                                                @php $json .= "}" @endphp
                                                                @endif

                                                            </tbody>

                                                        </table>



                                                    </td>

                                                </tr>
                                                @php $json .= "}" @endphp
                                                @endif

                                            </tbody>

                                        </table>



                                    </td>

                                </tr>
                                @php $json .= "}" @endphp
                                @endif
                            </tbody>

                        </table>



                    </td>

                </tr>
                @php $json .= "}" @endphp
            </tbody>

        </table>
        <textarea id="textarea" style="display: none;">@php echo($json) @endphp</textarea>
    </div>
</div>
<hr>
@endif
@endif
@endsection

@section ('ext_css')
<link rel="stylesheet" href="{{ asset('css/tree.css') }}">
@endsection