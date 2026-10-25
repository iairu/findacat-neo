@extends('layouts.cat-profile-wide')

@section('ext_css')
<script src="/js/jquery.min.js"></script>
<script src="/js/inbreeding.js"></script>
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
    .reg_num {
        font-size: 12px;
    }
</style>
@endsection


@section('script')
<script>
    function regnumCheckboxBehavior() {
        var checkbox = document.querySelector('input[name="reg_num"]');
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
    function loadScripts() {
        regnumCheckboxBehavior()
    }
    loadScripts()
</script>
@endsection

@section('title', $cat->full_name())
@section('subtitle', trans('app.family_tree'))

@section('cat-content')
<div id="generations"><strong>Generations:</strong> <span class="generations">{{$generations}}</span> (
    <a href="./1">1</a>
    <a href="./2">2</a>
    <a href="./3">3</a>
    <a href="./4">4</a>
    <a href="./5">5</a>
)</div>
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
                @if ($cat)
                @php $json .= "{" @endphp
                <tr class="offspring">

                    <td> Offspring:<br><input class="ind" id="offspring" type="text" data="{{$cat->id()}}" disabled><br>{{$cat->l($generations)}}<br>{{ $cat->breed }} {{ $cat->ems_color }} {{ $cat->dob() }}
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
                        @php $json .= "\"name\": \"" . $cat->id() . ($generations >= 1 && $cat->s() && $cat->d() ? "\"," : "\"") @endphp

                    </td>

                    <td class="anc">

                        <table data-level="1">

                            <tbody>
                                @if ($cat->s() && $generations >= 1)
                                @php $json .= "\"s\": {" @endphp
                                <tr class="s">
                                    <td> Sire:<br><input class="ind" id="s" type="text" data="{{$cat->s()->id()}}" disabled><br>{{$cat->s()->l($generations)}}<br>{{ $cat->s()->breed }} {{ $cat->s()->ems_color }} {{ $cat->s()->dob() }}
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
                                        @php $json .= "\"name\": \"" . $cat->s()->id() . ($generations >= 2 && $cat->s()->s() && $cat->s()->d() ? "\"," : "\"") @endphp

                                    </td>

                                    <td class="anc">

                                        <table data-level="2">

                                            <tbody>
                                                @if ($cat->s()->s() && $generations >= 2)
                                                @php $json .= "\"s\": {" @endphp
                                                <tr class="s">

                                                    <td> <input class="ind" id="ss" type="text" data="{{$cat->s()->s()->id()}}" disabled><br>{{$cat->s()->s()->l($generations)}}<br>{{ $cat->s()->s()->breed }} {{ $cat->s()->s()->ems_color }} {{ $cat->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->id != "1")
    {{ $cat->s()->s()->original_reg_num }}<br> 
        {{ $cat->s()->s()->last_reg_num }}<br> {{ $cat->s()->s()->reg_num_2 }}<br> {{ $cat->s()->s()->reg_num_3 }}
    @endif
    </div>
                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->id() . ($generations >= 3 && $cat->s()->s()->s() && $cat->s()->s()->d() ? "\"," : "\"") @endphp

                                                    </td>

                                                    <td class="anc">

                                                        <table data-level="3">

                                                            <tbody>
                                                                @if ($cat->s()->s()->s() && $generations >= 3)
                                                                @php $json .= "\"s\": {" @endphp
                                                                <tr class="s">

                                                                    <td> <input class="ind" id="sss" type="text" data="{{$cat->s()->s()->s()->id()}}" disabled><br>{{$cat->s()->s()->s()->l($generations)}}<br>{{ $cat->s()->s()->s()->breed }} {{ $cat->s()->s()->s()->ems_color }} {{ $cat->s()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->s()->id != "1")
    {{ $cat->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat->s()->s()->s()->last_reg_num }}<br> {{ $cat->s()->s()->s()->reg_num_2 }}<br> {{ $cat->s()->s()->s()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->s()->id() . ($generations >= 4 && $cat->s()->s()->s()->s() && $cat->s()->s()->s()->d() ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>
                                                                                @if ($cat->s()->s()->s()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="ssss" type="text" data="{{$cat->s()->s()->s()->s()->id()}}" disabled><br>{{$cat->s()->s()->s()->s()->l($generations)}}<br>{{ $cat->s()->s()->s()->s()->breed }} {{ $cat->s()->s()->s()->s()->ems_color }} {{ $cat->s()->s()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->s()->s()->id != "1")
    {{ $cat->s()->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat->s()->s()->s()->s()->last_reg_num }}<br> {{ $cat->s()->s()->s()->s()->reg_num_2 }}<br> {{ $cat->s()->s()->s()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->s()->s()->id() . ($generations >= 5 && $cat->s()->s()->s()->s()->s() && $cat->s()->s()->s()->s()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->s()->s()->s()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sssss" data="{{$cat->s()->s()->s()->s()->s()->id()}}" disabled><br>{{$cat->s()->s()->s()->s()->s()->l($generations)}}<br>{{ $cat->s()->s()->s()->s()->s()->breed }} {{ $cat->s()->s()->s()->s()->s()->ems_color }} {{ $cat->s()->s()->s()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->s()->s()->s()->id != "1")
    {{ $cat->s()->s()->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat->s()->s()->s()->s()->s()->last_reg_num }}<br> {{ $cat->s()->s()->s()->s()->s()->reg_num_2 }}<br> {{ $cat->s()->s()->s()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->s()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->s()->s()->s()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ssssd" data="{{$cat->s()->s()->s()->s()->d()->id()}}" disabled><br>{{$cat->s()->s()->s()->s()->d()->l($generations)}}<br>{{ $cat->s()->s()->s()->s()->d()->breed }} {{ $cat->s()->s()->s()->s()->d()->ems_color }} {{ $cat->s()->s()->s()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->s()->s()->d()->id != "1")
    {{ $cat->s()->s()->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat->s()->s()->s()->s()->d()->last_reg_num }}<br> {{ $cat->s()->s()->s()->s()->d()->reg_num_2 }}<br> {{ $cat->s()->s()->s()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->s()->s()->d()->id() . "\"" @endphp

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
                                                                                @if ($cat->s()->s()->s()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="sssd" type="text" data="{{$cat->s()->s()->s()->d()->id()}}" disabled><br>{{$cat->s()->s()->s()->d()->l($generations)}}<br>{{ $cat->s()->s()->s()->d()->breed }} {{ $cat->s()->s()->s()->d()->ems_color }} {{ $cat->s()->s()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->s()->d()->id != "1")
    {{ $cat->s()->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat->s()->s()->s()->d()->last_reg_num }}<br> {{ $cat->s()->s()->s()->d()->reg_num_2 }}<br> {{ $cat->s()->s()->s()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->s()->d()->id() . ($generations >= 5 && $cat->s()->s()->s()->d()->s() && $cat->s()->s()->s()->d()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->s()->s()->s()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sssds" data="{{$cat->s()->s()->s()->d()->s()->id()}}" disabled><br>{{$cat->s()->s()->s()->d()->s()->l($generations)}}<br>{{ $cat->s()->s()->s()->d()->s()->breed }} {{ $cat->s()->s()->s()->d()->s()->ems_color }} {{ $cat->s()->s()->s()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->s()->d()->s()->id != "1")
    {{ $cat->s()->s()->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat->s()->s()->s()->d()->s()->last_reg_num }}<br> {{ $cat->s()->s()->s()->d()->s()->reg_num_2 }}<br> {{ $cat->s()->s()->s()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->s()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->s()->s()->s()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sssdd" data="{{$cat->s()->s()->s()->d()->d()->id()}}" disabled><br>{{$cat->s()->s()->s()->d()->d()->l($generations)}}<br>{{ $cat->s()->s()->s()->d()->d()->breed }} {{ $cat->s()->s()->s()->d()->d()->ems_color }} {{ $cat->s()->s()->s()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->s()->d()->d()->id != "1")
    {{ $cat->s()->s()->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat->s()->s()->s()->d()->d()->last_reg_num }}<br> {{ $cat->s()->s()->s()->d()->d()->reg_num_2 }}<br> {{ $cat->s()->s()->s()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->s()->d()->d()->id() . "\"" @endphp

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
                                                                @if ($cat->s()->s()->d() && $generations >= 3)
                                                                @php $json .= "\"d\": {" @endphp
                                                                <tr class="d">

                                                                    <td> <input class="ind" id="ssd" type="text" data="{{$cat->s()->s()->d()->id()}}" disabled><br>{{$cat->s()->s()->d()->l($generations)}}<br>{{ $cat->s()->s()->d()->breed }} {{ $cat->s()->s()->d()->ems_color }} {{ $cat->s()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->d()->id != "1")
    {{ $cat->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat->s()->s()->d()->last_reg_num }}<br> {{ $cat->s()->s()->d()->reg_num_2 }}<br> {{ $cat->s()->s()->d()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->d()->id() . ($generations >= 4 && $cat->s()->s()->d()->s() && $cat->s()->s()->d()->d() ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>

                                                                                @if ($cat->s()->s()->d()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="ssds" type="text" data="{{$cat->s()->s()->d()->s()->id()}}" disabled><br>{{$cat->s()->s()->d()->s()->l($generations)}}<br>{{ $cat->s()->s()->d()->s()->breed }} {{ $cat->s()->s()->d()->s()->ems_color }} {{ $cat->s()->s()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->d()->s()->id != "1")
    {{ $cat->s()->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat->s()->s()->d()->s()->last_reg_num }}<br> {{ $cat->s()->s()->d()->s()->reg_num_2 }}<br> {{ $cat->s()->s()->d()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->d()->s()->id() . ($generations >= 5 && $cat->s()->s()->d()->s()->s() && $cat->s()->s()->d()->s()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->s()->s()->d()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ssdss" data="{{$cat->s()->s()->d()->s()->s()->id()}}" disabled><br>{{$cat->s()->s()->d()->s()->s()->l($generations)}}<br>{{ $cat->s()->s()->d()->s()->s()->breed }} {{ $cat->s()->s()->d()->s()->s()->ems_color }} {{ $cat->s()->s()->d()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->d()->s()->s()->id != "1")
    {{ $cat->s()->s()->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat->s()->s()->d()->s()->s()->last_reg_num }}<br> {{ $cat->s()->s()->d()->s()->s()->reg_num_2 }}<br> {{ $cat->s()->s()->d()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->d()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->s()->s()->d()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ssdsd" data="{{$cat->s()->s()->d()->s()->d()->id()}}" disabled><br>{{$cat->s()->s()->d()->s()->d()->l($generations)}}<br>{{ $cat->s()->s()->d()->s()->d()->breed }} {{ $cat->s()->s()->d()->s()->d()->ems_color }} {{ $cat->s()->s()->d()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->d()->s()->d()->id != "1")
    {{ $cat->s()->s()->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat->s()->s()->d()->s()->d()->last_reg_num }}<br> {{ $cat->s()->s()->d()->s()->d()->reg_num_2 }}<br> {{ $cat->s()->s()->d()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->d()->s()->d()->id() . "\"" @endphp

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
                                                                                @if ($cat->s()->s()->d()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="ssdd" type="text" data="{{$cat->s()->s()->d()->d()->id()}}" disabled><br>{{$cat->s()->s()->d()->d()->l($generations)}}<br>{{ $cat->s()->s()->d()->d()->breed }} {{ $cat->s()->s()->d()->d()->ems_color }} {{ $cat->s()->s()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->d()->d()->id != "1")
    {{ $cat->s()->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat->s()->s()->d()->d()->last_reg_num }}<br> {{ $cat->s()->s()->d()->d()->reg_num_2 }}<br> {{ $cat->s()->s()->d()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->d()->d()->id() . ($generations >= 5 && $cat->s()->s()->d()->d()->s() && $cat->s()->s()->d()->d()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->s()->s()->d()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ssdds" data="{{$cat->s()->s()->d()->d()->s()->id()}}" disabled><br>{{$cat->s()->s()->d()->d()->s()->l($generations)}}<br>{{ $cat->s()->s()->d()->d()->s()->breed }} {{ $cat->s()->s()->d()->d()->s()->ems_color }} {{ $cat->s()->s()->d()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->d()->d()->s()->id != "1")
    {{ $cat->s()->s()->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat->s()->s()->d()->d()->s()->last_reg_num }}<br> {{ $cat->s()->s()->d()->d()->s()->reg_num_2 }}<br> {{ $cat->s()->s()->d()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->d()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->s()->s()->d()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ssddd" data="{{$cat->s()->s()->d()->d()->d()->id()}}" disabled><br>{{$cat->s()->s()->d()->d()->d()->l($generations)}}<br>{{ $cat->s()->s()->d()->d()->d()->breed }} {{ $cat->s()->s()->d()->d()->d()->ems_color }} {{ $cat->s()->s()->d()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->s()->d()->d()->d()->id != "1")
    {{ $cat->s()->s()->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat->s()->s()->d()->d()->d()->last_reg_num }}<br> {{ $cat->s()->s()->d()->d()->d()->reg_num_2 }}<br> {{ $cat->s()->s()->d()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->s()->d()->d()->d()->id() . "\"" @endphp

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
                                                @if ($cat->s()->d() && $generations >= 2)
                                                @php $json .= "\"d\": {" @endphp
                                                <tr class="d">

                                                    <td> <input class="ind" id="sd" type="text" data="{{$cat->s()->d()->id()}}" disabled><br>{{$cat->s()->d()->l($generations)}}<br>{{ $cat->s()->d()->breed }} {{ $cat->s()->d()->ems_color }} {{ $cat->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->id != "1")
    {{ $cat->s()->d()->original_reg_num }}<br> 
        {{ $cat->s()->d()->last_reg_num }}<br> {{ $cat->s()->d()->reg_num_2 }}<br> {{ $cat->s()->d()->reg_num_3 }}
    @endif
    </div>
                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->id() . ($generations >= 3 && $cat->s()->d()->s() && $cat->s()->d()->d() ? "\"," : "\"") @endphp

                                                    </td>

                                                    <td class="anc">

                                                        <table data-level="3">

                                                            <tbody>

                                                                @if ($cat->s()->d()->s() && $generations >= 3)
                                                                @php $json .= "\"s\": {" @endphp
                                                                <tr class="s">

                                                                    <td> <input class="ind" id="sds" type="text" data="{{$cat->s()->d()->s()->id()}}" disabled><br>{{$cat->s()->d()->s()->l($generations)}}<br>{{ $cat->s()->d()->s()->breed }} {{ $cat->s()->d()->s()->ems_color }} {{ $cat->s()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->s()->id != "1")
    {{ $cat->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat->s()->d()->s()->last_reg_num }}<br> {{ $cat->s()->d()->s()->reg_num_2 }}<br> {{ $cat->s()->d()->s()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->s()->id() . ($generations >= 4 && $cat->s()->d()->s()->s() && $cat->s()->d()->s()->d() ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>
                                                                                @if ($cat->s()->d()->s()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="sdss" type="text" data="{{$cat->s()->d()->s()->s()->id()}}" disabled><br>{{$cat->s()->d()->s()->s()->l($generations)}}<br>{{ $cat->s()->d()->s()->s()->breed }} {{ $cat->s()->d()->s()->s()->ems_color }} {{ $cat->s()->d()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->s()->s()->id != "1")
    {{ $cat->s()->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat->s()->d()->s()->s()->last_reg_num }}<br> {{ $cat->s()->d()->s()->s()->reg_num_2 }}<br> {{ $cat->s()->d()->s()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->s()->s()->id() . ($generations >= 5 && $cat->s()->d()->s()->s()->s() && $cat->s()->d()->s()->s()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->s()->d()->s()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sdsss" data="{{$cat->s()->d()->s()->s()->s()->id()}}" disabled><br>{{$cat->s()->d()->s()->s()->s()->l($generations)}}<br>{{ $cat->s()->d()->s()->s()->s()->breed }} {{ $cat->s()->d()->s()->s()->s()->ems_color }} {{ $cat->s()->d()->s()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->s()->s()->s()->id != "1")
    {{ $cat->s()->d()->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat->s()->d()->s()->s()->s()->last_reg_num }}<br> {{ $cat->s()->d()->s()->s()->s()->reg_num_2 }}<br> {{ $cat->s()->d()->s()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->s()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->s()->d()->s()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sdssd" data="{{$cat->s()->d()->s()->s()->d()->id()}}" disabled><br>{{$cat->s()->d()->s()->s()->d()->l($generations)}}<br>{{ $cat->s()->d()->s()->s()->d()->breed }} {{ $cat->s()->d()->s()->s()->d()->ems_color }} {{ $cat->s()->d()->s()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->s()->s()->d()->id != "1")
    {{ $cat->s()->d()->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat->s()->d()->s()->s()->d()->last_reg_num }}<br> {{ $cat->s()->d()->s()->s()->d()->reg_num_2 }}<br> {{ $cat->s()->d()->s()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->s()->s()->d()->id() . "\"" @endphp

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

                                                                                @if ($cat->s()->d()->s()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="sdsd" type="text" data="{{$cat->s()->d()->s()->d()->id()}}" disabled><br>{{$cat->s()->d()->s()->d()->l($generations)}}<br>{{ $cat->s()->d()->s()->d()->breed }} {{ $cat->s()->d()->s()->d()->ems_color }} {{ $cat->s()->d()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->s()->d()->id != "1")
    {{ $cat->s()->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat->s()->d()->s()->d()->last_reg_num }}<br> {{ $cat->s()->d()->s()->d()->reg_num_2 }}<br> {{ $cat->s()->d()->s()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->s()->d()->id() . ($generations >= 5 && $cat->s()->d()->s()->d()->s() && $cat->s()->d()->s()->d()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->s()->d()->s()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sdsds" data="{{$cat->s()->d()->s()->d()->s()->id()}}" disabled><br>{{$cat->s()->d()->s()->d()->s()->l($generations)}}<br>{{ $cat->s()->d()->s()->d()->s()->breed }} {{ $cat->s()->d()->s()->d()->s()->ems_color }} {{ $cat->s()->d()->s()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->s()->d()->s()->id != "1")
    {{ $cat->s()->d()->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat->s()->d()->s()->d()->s()->last_reg_num }}<br> {{ $cat->s()->d()->s()->d()->s()->reg_num_2 }}<br> {{ $cat->s()->d()->s()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->s()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->s()->d()->s()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sdsdd" data="{{$cat->s()->d()->s()->d()->d()->id()}}" disabled><br>{{$cat->s()->d()->s()->d()->d()->l($generations)}}<br>{{ $cat->s()->d()->s()->d()->d()->breed }} {{ $cat->s()->d()->s()->d()->d()->ems_color }} {{ $cat->s()->d()->s()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->s()->d()->d()->id != "1")
    {{ $cat->s()->d()->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat->s()->d()->s()->d()->d()->last_reg_num }}<br> {{ $cat->s()->d()->s()->d()->d()->reg_num_2 }}<br> {{ $cat->s()->d()->s()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->s()->d()->d()->id() . "\"" @endphp

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
                                                                @if ($cat->s()->d()->d() && $generations >= 3)
                                                                @php $json .= "\"d\": {" @endphp
                                                                <tr class="d">

                                                                    <td> <input class="ind" id="sdd" type="text" data="{{$cat->s()->d()->d()->id()}}" disabled><br>{{$cat->s()->d()->d()->l($generations)}}<br>{{ $cat->s()->d()->d()->breed }} {{ $cat->s()->d()->d()->ems_color }} {{ $cat->s()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->d()->id != "1")
    {{ $cat->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat->s()->d()->d()->last_reg_num }}<br> {{ $cat->s()->d()->d()->reg_num_2 }}<br> {{ $cat->s()->d()->d()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->d()->id() . ($generations >= 4 && $cat->s()->d()->d()->s() && $cat->s()->d()->d()->d() ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>

                                                                                @if ($cat->s()->d()->d()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="sdds" type="text" data="{{$cat->s()->d()->d()->s()->id()}}" disabled><br>{{$cat->s()->d()->d()->s()->l($generations)}}<br>{{ $cat->s()->d()->d()->s()->breed }} {{ $cat->s()->d()->d()->s()->ems_color }} {{ $cat->s()->d()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->d()->s()->id != "1")
    {{ $cat->s()->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat->s()->d()->d()->s()->last_reg_num }}<br> {{ $cat->s()->d()->d()->s()->reg_num_2 }}<br> {{ $cat->s()->d()->d()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->d()->s()->id() . ($generations >= 5 && $cat->s()->d()->d()->s()->s() && $cat->s()->d()->d()->s()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->s()->d()->d()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sddss" data="{{$cat->s()->d()->d()->s()->s()->id()}}" disabled><br>{{$cat->s()->d()->d()->s()->s()->l($generations)}}<br>{{ $cat->s()->d()->d()->s()->s()->breed }} {{ $cat->s()->d()->d()->s()->s()->ems_color }} {{ $cat->s()->d()->d()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->d()->s()->s()->id != "1")
    {{ $cat->s()->d()->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat->s()->d()->d()->s()->s()->last_reg_num }}<br> {{ $cat->s()->d()->d()->s()->s()->reg_num_2 }}<br> {{ $cat->s()->d()->d()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->d()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->s()->d()->d()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sddsd" data="{{$cat->s()->d()->d()->s()->d()->id()}}" disabled><br>{{$cat->s()->d()->d()->s()->d()->l($generations)}}<br>{{ $cat->s()->d()->d()->s()->d()->breed }} {{ $cat->s()->d()->d()->s()->d()->ems_color }} {{ $cat->s()->d()->d()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->d()->s()->d()->id != "1")
    {{ $cat->s()->d()->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat->s()->d()->d()->s()->d()->last_reg_num }}<br> {{ $cat->s()->d()->d()->s()->d()->reg_num_2 }}<br> {{ $cat->s()->d()->d()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->d()->s()->d()->id() . "\"" @endphp

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
                                                                                @if ($cat->s()->d()->d()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="sddd" type="text" data="{{$cat->s()->d()->d()->d()->id()}}" disabled><br>{{$cat->s()->d()->d()->d()->l($generations)}}<br>{{ $cat->s()->d()->d()->d()->breed }} {{ $cat->s()->d()->d()->d()->ems_color }} {{ $cat->s()->d()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->d()->d()->id != "1")
    {{ $cat->s()->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat->s()->d()->d()->d()->last_reg_num }}<br> {{ $cat->s()->d()->d()->d()->reg_num_2 }}<br> {{ $cat->s()->d()->d()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->d()->d()->id() . ($generations >= 5 && $cat->s()->d()->d()->d()->s() && $cat->s()->d()->d()->d()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->s()->d()->d()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sddds" data="{{$cat->s()->d()->d()->d()->s()->id()}}" disabled><br>{{$cat->s()->d()->d()->d()->s()->l($generations)}}<br>{{ $cat->s()->d()->d()->d()->s()->breed }} {{ $cat->s()->d()->d()->d()->s()->ems_color }} {{ $cat->s()->d()->d()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->d()->d()->s()->id != "1")
    {{ $cat->s()->d()->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat->s()->d()->d()->d()->s()->last_reg_num }}<br> {{ $cat->s()->d()->d()->d()->s()->reg_num_2 }}<br> {{ $cat->s()->d()->d()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->d()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->s()->d()->d()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="sdddd" data="{{$cat->s()->d()->d()->d()->d()->id()}}" disabled><br>{{$cat->s()->d()->d()->d()->d()->l($generations)}}<br>{{ $cat->s()->d()->d()->d()->d()->breed }} {{ $cat->s()->d()->d()->d()->d()->ems_color }} {{ $cat->s()->d()->d()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->s()->d()->d()->d()->d()->id != "1")
    {{ $cat->s()->d()->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat->s()->d()->d()->d()->d()->last_reg_num }}<br> {{ $cat->s()->d()->d()->d()->d()->reg_num_2 }}<br> {{ $cat->s()->d()->d()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->s()->d()->d()->d()->d()->id() . "\"" @endphp

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
                                @if ($cat->d() && $generations >= 1)
                                @php $json .= "\"d\": {" @endphp
                                <tr class="d">

                                    <td> Dam:<br>

                                        <input class="ind" id="d" type="text" data="{{$cat->d()->id()}}" disabled><br>{{$cat->d()->l($generations)}}<br>{{ $cat->d()->breed }} {{ $cat->d()->ems_color }} {{ $cat->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->id != "1")
    {{ $cat->d()->original_reg_num }}<br> 
        {{ $cat->d()->last_reg_num }}<br> {{ $cat->d()->reg_num_2 }}<br> {{ $cat->d()->reg_num_3 }}
    @endif
    </div><br>
            @if ($cat->d()->genetic_tests_file)
    <a href="{{ $cat->d()->genetic_tests_file }}">{{ trans('cat.download_genetic_tests_file') }}</a><br>
    @endif
            @if ($cat->d()->vet_confirmation)
    <a href="{{ $cat->d()->vet_confirmation }}">{{ trans('cat.download_vet_confirmation') }}</a>
    @endif
                                        @php $json .= "\"name\": \"" . $cat->d()->id() . ($generations >= 2 && $cat->d()->s() && $cat->d()->d() ? "\"," : "\"") @endphp



                                    </td>

                                    <td class="anc">

                                        <table data-level="2">

                                            <tbody>
                                                @if ($cat->d()->s() && $generations >= 2)
                                                @php $json .= "\"s\": {" @endphp
                                                <tr class="s">

                                                    <td> <input class="ind" id="ds" type="text" data="{{$cat->d()->s()->id()}}" disabled><br>{{$cat->d()->s()->l($generations)}}<br>{{ $cat->d()->s()->breed }} {{ $cat->d()->s()->ems_color }} {{ $cat->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->id != "1")
    {{ $cat->d()->s()->original_reg_num }}<br> 
        {{ $cat->d()->s()->last_reg_num }}<br> {{ $cat->d()->s()->reg_num_2 }}<br> {{ $cat->d()->s()->reg_num_3 }}
    @endif
    </div>
                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->id() . ($generations >= 3 && $cat->d()->s()->s() && $cat->d()->s()->d() ? "\"," : "\"") @endphp

                                                    </td>

                                                    <td class="anc">

                                                        <table data-level="3">

                                                            <tbody>
                                                                @if ($cat->d()->s()->s() && $generations >= 3)
                                                                @php $json .= "\"s\": {" @endphp
                                                                <tr class="s">

                                                                    <td> <input class="ind" id="dss" type="text" data="{{$cat->d()->s()->s()->id()}}" disabled><br>{{$cat->d()->s()->s()->l($generations)}}<br>{{ $cat->d()->s()->s()->breed }} {{ $cat->d()->s()->s()->ems_color }} {{ $cat->d()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->s()->id != "1")
    {{ $cat->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat->d()->s()->s()->last_reg_num }}<br> {{ $cat->d()->s()->s()->reg_num_2 }}<br> {{ $cat->d()->s()->s()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->s()->id() . ($generations >= 4 && $cat->d()->s()->s()->s() && $cat->d()->s()->s()->d() ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>
                                                                                @if ($cat->d()->s()->s()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="dsss" type="text" data="{{$cat->d()->s()->s()->s()->id()}}" disabled><br>{{$cat->d()->s()->s()->s()->l($generations)}}<br>{{ $cat->d()->s()->s()->s()->breed }} {{ $cat->d()->s()->s()->s()->ems_color }} {{ $cat->d()->s()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->s()->s()->id != "1")
    {{ $cat->d()->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat->d()->s()->s()->s()->last_reg_num }}<br> {{ $cat->d()->s()->s()->s()->reg_num_2 }}<br> {{ $cat->d()->s()->s()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->s()->s()->id() . ($generations >= 5 && $cat->d()->s()->s()->s()->s() && $cat->d()->s()->s()->s()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->d()->s()->s()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dssss" data="{{$cat->d()->s()->s()->s()->s()->id()}}" disabled><br>{{$cat->d()->s()->s()->s()->s()->l($generations)}}<br>{{ $cat->d()->s()->s()->s()->s()->breed }} {{ $cat->d()->s()->s()->s()->s()->ems_color }} {{ $cat->d()->s()->s()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->s()->s()->s()->id != "1")
    {{ $cat->d()->s()->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat->d()->s()->s()->s()->s()->last_reg_num }}<br> {{ $cat->d()->s()->s()->s()->s()->reg_num_2 }}<br> {{ $cat->d()->s()->s()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->s()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->d()->s()->s()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dsssd" data="{{$cat->d()->s()->s()->s()->d()->id()}}" disabled><br>{{$cat->d()->s()->s()->s()->d()->l($generations)}}<br>{{ $cat->d()->s()->s()->s()->d()->breed }} {{ $cat->d()->s()->s()->s()->d()->ems_color }} {{ $cat->d()->s()->s()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->s()->s()->d()->id != "1")
    {{ $cat->d()->s()->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat->d()->s()->s()->s()->d()->last_reg_num }}<br> {{ $cat->d()->s()->s()->s()->d()->reg_num_2 }}<br> {{ $cat->d()->s()->s()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->s()->s()->d()->id() . "\"" @endphp

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
                                                                                @if ($cat->d()->s()->s()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="dssd" type="text" data="{{$cat->d()->s()->s()->d()->id()}}" disabled><br>{{$cat->d()->s()->s()->d()->l($generations)}}<br>{{ $cat->d()->s()->s()->d()->breed }} {{ $cat->d()->s()->s()->d()->ems_color }} {{ $cat->d()->s()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->s()->d()->id != "1")
    {{ $cat->d()->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat->d()->s()->s()->d()->last_reg_num }}<br> {{ $cat->d()->s()->s()->d()->reg_num_2 }}<br> {{ $cat->d()->s()->s()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->s()->d()->id() . ($generations >= 5 && $cat->d()->s()->s()->d()->s() && $cat->d()->s()->s()->d()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->d()->s()->s()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dssds" data="{{$cat->d()->s()->s()->d()->s()->id()}}" disabled><br>{{$cat->d()->s()->s()->d()->s()->l($generations)}}<br>{{ $cat->d()->s()->s()->d()->s()->breed }} {{ $cat->d()->s()->s()->d()->s()->ems_color }} {{ $cat->d()->s()->s()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->s()->d()->s()->id != "1")
    {{ $cat->d()->s()->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat->d()->s()->s()->d()->s()->last_reg_num }}<br> {{ $cat->d()->s()->s()->d()->s()->reg_num_2 }}<br> {{ $cat->d()->s()->s()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->s()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->d()->s()->s()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dssdd" data="{{$cat->d()->s()->s()->d()->d()->id()}}" disabled><br>{{$cat->d()->s()->s()->d()->d()->l($generations)}}<br>{{ $cat->d()->s()->s()->d()->d()->breed }} {{ $cat->d()->s()->s()->d()->d()->ems_color }} {{ $cat->d()->s()->s()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->s()->d()->d()->id != "1")
    {{ $cat->d()->s()->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat->d()->s()->s()->d()->d()->last_reg_num }}<br> {{ $cat->d()->s()->s()->d()->d()->reg_num_2 }}<br> {{ $cat->d()->s()->s()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->s()->d()->d()->id() . "\"" @endphp

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
                                                                @if ($cat->d()->s()->d() && $generations >= 3)
                                                                @php $json .= "\"d\": {" @endphp
                                                                <tr class="d">

                                                                    <td> <input class="ind" id="dsd" type="text" data="{{$cat->d()->s()->d()->id()}}" disabled><br>{{$cat->d()->s()->d()->l($generations)}}<br>{{ $cat->d()->s()->d()->breed }} {{ $cat->d()->s()->d()->ems_color }} {{ $cat->d()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->d()->id != "1")
    {{ $cat->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat->d()->s()->d()->last_reg_num }}<br> {{ $cat->d()->s()->d()->reg_num_2 }}<br> {{ $cat->d()->s()->d()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->d()->id() . ($generations >= 4 && $cat->d()->s()->d()->s() && $cat->d()->s()->d()->d() ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>

                                                                                @if ($cat->d()->s()->d()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="dsds" type="text" data="{{$cat->d()->s()->d()->s()->id()}}" disabled><br>{{$cat->d()->s()->d()->s()->l($generations)}}<br>{{ $cat->d()->s()->d()->s()->breed }} {{ $cat->d()->s()->d()->s()->ems_color }} {{ $cat->d()->s()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->d()->s()->id != "1")
    {{ $cat->d()->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat->d()->s()->d()->s()->last_reg_num }}<br> {{ $cat->d()->s()->d()->s()->reg_num_2 }}<br> {{ $cat->d()->s()->d()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->d()->s()->id() . ($generations >= 5 && $cat->d()->s()->d()->s()->s() && $cat->d()->s()->d()->s()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->d()->s()->d()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dsdss" data="{{$cat->d()->s()->d()->s()->s()->id()}}" disabled><br>{{$cat->d()->s()->d()->s()->s()->l($generations)}}<br>{{ $cat->d()->s()->d()->s()->s()->breed }} {{ $cat->d()->s()->d()->s()->s()->ems_color }} {{ $cat->d()->s()->d()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->d()->s()->s()->id != "1")
    {{ $cat->d()->s()->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat->d()->s()->d()->s()->s()->last_reg_num }}<br> {{ $cat->d()->s()->d()->s()->s()->reg_num_2 }}<br> {{ $cat->d()->s()->d()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->d()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->d()->s()->d()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dsdsd" data="{{$cat->d()->s()->d()->s()->d()->id()}}" disabled><br>{{$cat->d()->s()->d()->s()->d()->l($generations)}}<br>{{ $cat->d()->s()->d()->s()->d()->breed }} {{ $cat->d()->s()->d()->s()->d()->ems_color }} {{ $cat->d()->s()->d()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->d()->s()->d()->id != "1")
    {{ $cat->d()->s()->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat->d()->s()->d()->s()->d()->last_reg_num }}<br> {{ $cat->d()->s()->d()->s()->d()->reg_num_2 }}<br> {{ $cat->d()->s()->d()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->d()->s()->d()->id() . "\"" @endphp

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
                                                                                @if ($cat->d()->s()->d()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="dsdd" type="text" data="{{$cat->d()->s()->d()->d()->id()}}" disabled><br>{{$cat->d()->s()->d()->d()->l($generations)}}<br>{{ $cat->d()->s()->d()->d()->breed }} {{ $cat->d()->s()->d()->d()->ems_color }} {{ $cat->d()->s()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->d()->d()->id != "1")
    {{ $cat->d()->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat->d()->s()->d()->d()->last_reg_num }}<br> {{ $cat->d()->s()->d()->d()->reg_num_2 }}<br> {{ $cat->d()->s()->d()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->d()->d()->id() . ($generations >= 5 && $cat->d()->s()->d()->d()->s() && $cat->d()->s()->d()->d()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->d()->s()->d()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dsdds" data="{{$cat->d()->s()->d()->d()->s()->id()}}" disabled><br>{{$cat->d()->s()->d()->d()->s()->l($generations)}}<br>{{ $cat->d()->s()->d()->d()->s()->breed }} {{ $cat->d()->s()->d()->d()->s()->ems_color }} {{ $cat->d()->s()->d()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->d()->d()->s()->id != "1")
    {{ $cat->d()->s()->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat->d()->s()->d()->d()->s()->last_reg_num }}<br> {{ $cat->d()->s()->d()->d()->s()->reg_num_2 }}<br> {{ $cat->d()->s()->d()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->d()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->d()->s()->d()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dsddd" data="{{$cat->d()->s()->d()->d()->d()->id()}}" disabled><br>{{$cat->d()->s()->d()->d()->d()->l($generations)}}<br>{{ $cat->d()->s()->d()->d()->d()->breed }} {{ $cat->d()->s()->d()->d()->d()->ems_color }} {{ $cat->d()->s()->d()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->s()->d()->d()->d()->id != "1")
    {{ $cat->d()->s()->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat->d()->s()->d()->d()->d()->last_reg_num }}<br> {{ $cat->d()->s()->d()->d()->d()->reg_num_2 }}<br> {{ $cat->d()->s()->d()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->s()->d()->d()->d()->id() . "\"" @endphp

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
                                                @if ($cat->d()->d() && $generations >= 2)
                                                @php $json .= "\"d\": {" @endphp
                                                <tr class="d">

                                                    <td> <input class="ind" id="dd" type="text" data="{{$cat->d()->d()->id()}}" disabled><br>{{$cat->d()->d()->l($generations)}}<br>{{ $cat->d()->d()->breed }} {{ $cat->d()->d()->ems_color }} {{ $cat->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->id != "1")
    {{ $cat->d()->d()->original_reg_num }}<br> 
        {{ $cat->d()->d()->last_reg_num }}<br> {{ $cat->d()->d()->reg_num_2 }}<br> {{ $cat->d()->d()->reg_num_3 }}
    @endif
    </div>
                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->id() . ($generations >= 3 && $cat->d()->d()->s() && $cat->d()->d()->d() ? "\"," : "\"") @endphp

                                                    </td>

                                                    <td class="anc">

                                                        <table data-level="3">

                                                            <tbody>

                                                                @if ($cat->d()->d()->s() && $generations >= 3)
                                                                @php $json .= "\"s\": {" @endphp
                                                                <tr class="s">

                                                                    <td> <input class="ind" id="dds" type="text" data="{{$cat->d()->d()->s()->id()}}" disabled><br>{{$cat->d()->d()->s()->l($generations)}}<br>{{ $cat->d()->d()->s()->breed }} {{ $cat->d()->d()->s()->ems_color }} {{ $cat->d()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->s()->id != "1")
    {{ $cat->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat->d()->d()->s()->last_reg_num }}<br> {{ $cat->d()->d()->s()->reg_num_2 }}<br> {{ $cat->d()->d()->s()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->s()->id() . ($generations >= 4 && $cat->d()->d()->s()->s() && $cat->d()->d()->s()->d() ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>

                                                                                @if ($cat->d()->d()->s()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="ddss" type="text" data="{{$cat->d()->d()->s()->s()->id()}}" disabled><br>{{$cat->d()->d()->s()->s()->l($generations)}}<br>{{ $cat->d()->d()->s()->s()->breed }} {{ $cat->d()->d()->s()->s()->ems_color }} {{ $cat->d()->d()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->s()->s()->id != "1")
    {{ $cat->d()->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat->d()->d()->s()->s()->last_reg_num }}<br> {{ $cat->d()->d()->s()->s()->reg_num_2 }}<br> {{ $cat->d()->d()->s()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->s()->s()->id() . ($generations >= 5 && $cat->d()->d()->s()->s()->s() && $cat->d()->d()->s()->s()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->d()->d()->s()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ddsss" data="{{$cat->d()->d()->s()->s()->s()->id()}}" disabled><br>{{$cat->d()->d()->s()->s()->s()->l($generations)}}<br>{{ $cat->d()->d()->s()->s()->s()->breed }} {{ $cat->d()->d()->s()->s()->s()->ems_color }} {{ $cat->d()->d()->s()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->s()->s()->s()->id != "1")
    {{ $cat->d()->d()->s()->s()->s()->original_reg_num }}<br> 
        {{ $cat->d()->d()->s()->s()->s()->last_reg_num }}<br> {{ $cat->d()->d()->s()->s()->s()->reg_num_2 }}<br> {{ $cat->d()->d()->s()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->s()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif

                                                                                                @if ($cat->d()->d()->s()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ddssd" data="{{$cat->d()->d()->s()->s()->d()->id()}}" disabled><br>{{$cat->d()->d()->s()->s()->d()->l($generations)}}<br>{{ $cat->d()->d()->s()->s()->d()->breed }} {{ $cat->d()->d()->s()->s()->d()->ems_color }} {{ $cat->d()->d()->s()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->s()->s()->d()->id != "1")
    {{ $cat->d()->d()->s()->s()->d()->original_reg_num }}<br> 
        {{ $cat->d()->d()->s()->s()->d()->last_reg_num }}<br> {{ $cat->d()->d()->s()->s()->d()->reg_num_2 }}<br> {{ $cat->d()->d()->s()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->s()->s()->d()->id() . "\"" @endphp

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

                                                                                @if ($cat->d()->d()->s()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="ddsd" type="text" data="{{$cat->d()->d()->s()->d()->id()}}" disabled><br>{{$cat->d()->d()->s()->d()->l($generations)}}<br>{{ $cat->d()->d()->s()->d()->breed }} {{ $cat->d()->d()->s()->d()->ems_color }} {{ $cat->d()->d()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->s()->d()->id != "1")
    {{ $cat->d()->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat->d()->d()->s()->d()->last_reg_num }}<br> {{ $cat->d()->d()->s()->d()->reg_num_2 }}<br> {{ $cat->d()->d()->s()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->s()->d()->id() . ($generations >= 5 && $cat->d()->d()->s()->d()->s() && $cat->d()->d()->s()->d()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>

                                                                                                @if ($cat->d()->d()->s()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ddsds" data="{{$cat->d()->d()->s()->d()->s()->id()}}" disabled><br>{{$cat->d()->d()->s()->d()->s()->l($generations)}}<br>{{ $cat->d()->d()->s()->d()->s()->breed }} {{ $cat->d()->d()->s()->d()->s()->ems_color }} {{ $cat->d()->d()->s()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->s()->d()->s()->id != "1")
    {{ $cat->d()->d()->s()->d()->s()->original_reg_num }}<br> 
        {{ $cat->d()->d()->s()->d()->s()->last_reg_num }}<br> {{ $cat->d()->d()->s()->d()->s()->reg_num_2 }}<br> {{ $cat->d()->d()->s()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->s()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif
                                                                                                @if ($cat->d()->d()->s()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ddsdd" data="{{$cat->d()->d()->s()->d()->d()->id()}}" disabled><br>{{$cat->d()->d()->s()->d()->d()->l($generations)}}<br>{{ $cat->d()->d()->s()->d()->d()->breed }} {{ $cat->d()->d()->s()->d()->d()->ems_color }} {{ $cat->d()->d()->s()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->s()->d()->d()->id != "1")
    {{ $cat->d()->d()->s()->d()->d()->original_reg_num }}<br> 
        {{ $cat->d()->d()->s()->d()->d()->last_reg_num }}<br> {{ $cat->d()->d()->s()->d()->d()->reg_num_2 }}<br> {{ $cat->d()->d()->s()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->s()->d()->d()->id() . "\"" @endphp

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
                                                                @if ($cat->d()->d()->d() && $generations >= 3)
                                                                @php $json .= "\"d\": {" @endphp
                                                                <tr class="d">

                                                                    <td> <input class="ind" id="ddd" type="text" data="{{$cat->d()->d()->d()->id()}}" disabled><br>{{$cat->d()->d()->d()->l($generations)}}<br>{{ $cat->d()->d()->d()->breed }} {{ $cat->d()->d()->d()->ems_color }} {{ $cat->d()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->d()->id != "1")
    {{ $cat->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat->d()->d()->d()->last_reg_num }}<br> {{ $cat->d()->d()->d()->reg_num_2 }}<br> {{ $cat->d()->d()->d()->reg_num_3 }}
    @endif
    </div>
                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->d()->id() . ($generations >= 4 && $cat->d()->d()->d()->s() && $cat->d()->d()->d()->d() ? "\"," : "\"") @endphp

                                                                    </td>

                                                                    <td class="anc">

                                                                        <table data-level="4">

                                                                            <tbody>
                                                                                @if ($cat->d()->d()->d()->s() && $generations >= 4)
                                                                                @php $json .= "\"s\": {" @endphp
                                                                                <tr class="s">

                                                                                    <td>

                                                                                        <input class="ind" id="ddds" type="text" data="{{$cat->d()->d()->d()->s()->id()}}" disabled><br>{{$cat->d()->d()->d()->s()->l($generations)}}<br>{{ $cat->d()->d()->d()->s()->breed }} {{ $cat->d()->d()->d()->s()->ems_color }} {{ $cat->d()->d()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->d()->s()->id != "1")
    {{ $cat->d()->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat->d()->d()->d()->s()->last_reg_num }}<br> {{ $cat->d()->d()->d()->s()->reg_num_2 }}<br> {{ $cat->d()->d()->d()->s()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->d()->s()->id() . ($generations >= 5 && $cat->d()->d()->d()->s()->s() && $cat->d()->d()->d()->s()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->d()->d()->d()->s()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dddss" data="{{$cat->d()->d()->d()->s()->s()->id()}}" disabled><br>{{$cat->d()->d()->d()->s()->s()->l($generations)}}<br>{{ $cat->d()->d()->d()->s()->s()->breed }} {{ $cat->d()->d()->d()->s()->s()->ems_color }} {{ $cat->d()->d()->d()->s()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->d()->s()->s()->id != "1")
    {{ $cat->d()->d()->d()->s()->s()->original_reg_num }}<br> 
        {{ $cat->d()->d()->d()->s()->s()->last_reg_num }}<br> {{ $cat->d()->d()->d()->s()->s()->reg_num_2 }}<br> {{ $cat->d()->d()->d()->s()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->d()->s()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif

                                                                                                @if ($cat->d()->d()->d()->s()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dddsd" data="{{$cat->d()->d()->d()->s()->d()->id()}}" disabled><br>{{$cat->d()->d()->d()->s()->d()->l($generations)}}<br>{{ $cat->d()->d()->d()->s()->d()->breed }} {{ $cat->d()->d()->d()->s()->d()->ems_color }} {{ $cat->d()->d()->d()->s()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->d()->s()->d()->id != "1")
    {{ $cat->d()->d()->d()->s()->d()->original_reg_num }}<br> 
        {{ $cat->d()->d()->d()->s()->d()->last_reg_num }}<br> {{ $cat->d()->d()->d()->s()->d()->reg_num_2 }}<br> {{ $cat->d()->d()->d()->s()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->d()->s()->d()->id() . "\"" @endphp

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
                                                                                @if ($cat->d()->d()->d()->d() && $generations >= 4)
                                                                                @php $json .= "\"d\": {" @endphp
                                                                                <tr class="d">

                                                                                    <td>

                                                                                        <input class="ind" id="dddd" type="text" data="{{$cat->d()->d()->d()->d()->id()}}" disabled><br>{{$cat->d()->d()->d()->d()->l($generations)}}<br>{{ $cat->d()->d()->d()->d()->breed }} {{ $cat->d()->d()->d()->d()->ems_color }} {{ $cat->d()->d()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->d()->d()->id != "1")
    {{ $cat->d()->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat->d()->d()->d()->d()->last_reg_num }}<br> {{ $cat->d()->d()->d()->d()->reg_num_2 }}<br> {{ $cat->d()->d()->d()->d()->reg_num_3 }}
    @endif
    </div>


                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->d()->d()->id() . ($generations >= 5 && $cat->d()->d()->d()->d()->s() && $cat->d()->d()->d()->d()->d() ? "\"," : "\"") @endphp

                                                                                    </td>

                                                                                    <td class="anc">
                                                                                        <table data-level="5">
                                                                                            <tbody>
                                                                                                @if ($cat->d()->d()->d()->d()->s() && $generations >= 5)
                                                                                                @php $json .= "\"s\": {" @endphp
                                                                                                <tr class="s">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="dddds" data="{{$cat->d()->d()->d()->d()->s()->id()}}" disabled><br>{{$cat->d()->d()->d()->d()->s()->l($generations)}}<br>{{ $cat->d()->d()->d()->d()->s()->breed }} {{ $cat->d()->d()->d()->d()->s()->ems_color }} {{ $cat->d()->d()->d()->d()->s()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->d()->d()->s()->id != "1")
    {{ $cat->d()->d()->d()->d()->s()->original_reg_num }}<br> 
        {{ $cat->d()->d()->d()->d()->s()->last_reg_num }}<br> {{ $cat->d()->d()->d()->d()->s()->reg_num_2 }}<br> {{ $cat->d()->d()->d()->d()->s()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->d()->d()->s()->id() . "\"" @endphp

                                                                                                    </td>
                                                                                                    <td class="anc">

                                                                                                    </td>
                                                                                                </tr>
                                                                                                @php $json .= "}," @endphp
                                                                                                @endif

                                                                                                @if ($cat->d()->d()->d()->d()->d() && $generations >= 5)
                                                                                                @php $json .= "\"d\": {" @endphp
                                                                                                <tr class="d">
                                                                                                    <td>
                                                                                                        <input type="text" class="ind" id="ddddd" data="{{$cat->d()->d()->d()->d()->d()->id()}}" disabled><br>{{$cat->d()->d()->d()->d()->d()->l($generations)}}<br>{{ $cat->d()->d()->d()->d()->d()->breed }} {{ $cat->d()->d()->d()->d()->d()->ems_color }} {{ $cat->d()->d()->d()->d()->d()->dob() }}
                                        <div class="reg_num">
    @if ($cat->d()->d()->d()->d()->d()->id != "1")
    {{ $cat->d()->d()->d()->d()->d()->original_reg_num }}<br> 
        {{ $cat->d()->d()->d()->d()->d()->last_reg_num }}<br> {{ $cat->d()->d()->d()->d()->d()->reg_num_2 }}<br> {{ $cat->d()->d()->d()->d()->d()->reg_num_3 }}
    @endif
    </div>

                                                                                                        @php $json .= "\"name\": \"" . $cat->d()->d()->d()->d()->d()->id() . "\"" @endphp

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
                @endif
            </tbody>

        </table>
        <textarea id="textarea" style="display: none;">@php echo($json) @endphp</textarea>
    </div>
</div>
<hr>

@endsection

@section ('ext_css')
<link rel="stylesheet" href="{{ asset('css/tree.css') }}">
@endsection