@extends('layouts.app')

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
</style>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Register</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('register-cat') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('titles_before_name') ? ' has-error' : '' }}">
                            <label for="titles_before_name" class="col-md-4 control-label">{{ trans('cat.titles_before_name') }}</label>

                            <div class="col-md-6">
                                <input id="titles_before_name" type="text" class="form-control" name="titles_before_name" value="{{ old('titles_before_name') }}">

                                @if ($errors->has('titles_before_name'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('titles_before_name') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('full_name') ? ' has-error' : '' }}">
                            <label for="full_name" class="col-md-4 control-label">{{ trans('cat.full_name') }} <span style="color:red;">*</span></label>

                            <div class="col-md-6">
                                <input id="full_name" type="text" class="form-control" name="full_name" value="{{ old('full_name') }}" required>

                                @if ($errors->has('full_name'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('full_name') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('titles_after_name') ? ' has-error' : '' }}">
                            <label for="titles_after_name" class="col-md-4 control-label">{{ trans('cat.titles_after_name') }}</label>

                            <div class="col-md-6">
                                <input id="titles_after_name" type="text" class="form-control" name="titles_after_name" value="{{ old('titles_after_name') }}">

                                @if ($errors->has('titles_after_name'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('titles_after_name') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('ems_color') ? ' has-error' : '' }}">
                            <label for="ems_color" class="col-md-4 control-label">{{ trans('cat.ems_color') }}</label>

                            <div class="col-md-6">
                                <input id="ems_color" type="text" class="form-control" name="ems_color" value="{{ old('ems_color') }}">

                                @if ($errors->has('ems_color'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('ems_color') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('breed') ? ' has-error' : '' }}">
                            <label for="breed" class="col-md-4 control-label">{{ trans('cat.breed') }}</label>

                            <div class="col-md-6">
                                <input id="breed" type="text" class="form-control" name="breed" value="{{ old('breed') }}">

                                @if ($errors->has('breed'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('breed') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('gender_id') ? ' has-error' : '' }}">
                            <label for="gender_id" class="col-md-4 control-label">{{ trans('cat.gender') }} <span style="color:red;">*</span></label>

                            <div class="col-md-4" style="padding-left: 2em;">
                                {!! FormField::radios('gender_id', [1 => trans('app.male'), 2 => trans('app.female')], ['label' => false]) !!}
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('dob') ? ' has-error' : '' }}">
                            <label for="dob" class="col-md-4 control-label">{{ trans('cat.dob') }}</label>

                            <div class="col-md-6">
                                <input id="dob" type="text" class="form-control" name="dob" value="{{ old('dob') }}">

                                @if ($errors->has('dob'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('dob') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('ems_color') ? ' has-error' : '' }}">
                            <label for="ems_color" class="col-md-4 control-label">{{ trans('cat.ems_color') }}</label>

                            <div class="col-md-6">
                                <input id="ems_color" type="text" class="form-control" name="ems_color" value="{{ old('ems_color') }}">

                                @if ($errors->has('ems_color'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('ems_color') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('breed') ? ' has-error' : '' }}">
                            <label for="breed" class="col-md-4 control-label">{{ trans('cat.breed') }}</label>

                            <div class="col-md-6">
                                <input id="breed" type="text" class="form-control" name="breed" value="{{ old('breed') }}">

                                @if ($errors->has('breed'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('breed') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('genetic_tests') ? ' has-error' : '' }}">
                            <label for="genetic_tests" class="col-md-4 control-label">{{ trans('cat.genetic_tests') }}</label>

                            <div class="col-md-6">
                                <input id="genetic_tests" type="text" class="form-control" name="genetic_tests" value="{{ old('genetic_tests') }}">

                                @if ($errors->has('genetic_tests'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('genetic_tests') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('breeding_station') ? ' has-error' : '' }}">
                            <label for="breeding_station" class="col-md-4 control-label">{{ trans('cat.breeding_station') }}</label>

                            <div class="col-md-6">
                                <input id="breeding_station" type="text" class="form-control" name="breeding_station" value="{{ old('breeding_station') }}">

                                @if ($errors->has('breeding_station'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('breeding_station') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('country_code') ? ' has-error' : '' }}">
                            <label for="country_code" class="col-md-4 control-label">{{ trans('cat.country_code') }}</label>

                            <div class="col-md-6">
                                <input id="country_code" type="text" class="form-control" name="country_code" value="{{ old('country_code') }}">

                                @if ($errors->has('country_code'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('country_code') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('alternative_name') ? ' has-error' : '' }}">
                            <label for="alternative_name" class="col-md-4 control-label">{{ trans('cat.alternative_name') }}</label>

                            <div class="col-md-6">
                                <input id="alternative_name" type="text" class="form-control" name="alternative_name" value="{{ old('alternative_name') }}">

                                @if ($errors->has('alternative_name'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('alternative_name') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('print_name_r1') ? ' has-error' : '' }}">
                            <label for="print_name_r1" class="col-md-4 control-label">{{ trans('cat.print_name_r1') }}</label>

                            <div class="col-md-6">
                                <input id="print_name_r1" type="text" class="form-control" name="print_name_r1" value="{{ old('print_name_r1') }}">

                                @if ($errors->has('print_name_r1'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('print_name_r1') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('print_name_r2') ? ' has-error' : '' }}">
                            <label for="print_name_r2" class="col-md-4 control-label">{{ trans('cat.print_name_r2') }}</label>

                            <div class="col-md-6">
                                <input id="print_name_r2" type="text" class="form-control" name="print_name_r2" value="{{ old('print_name_r2') }}">

                                @if ($errors->has('print_name_r2'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('print_name_r2') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('dod') ? ' has-error' : '' }}">
                            <label for="dod" class="col-md-4 control-label">{{ trans('cat.dod') }}</label>

                            <div class="col-md-6">
                                <input id="dod" type="text" class="form-control" name="dod" value="{{ old('dod') }}">

                                @if ($errors->has('dod'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('dod') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('original_reg_num') ? ' has-error' : '' }}">
                            <label for="original_reg_num" class="col-md-4 control-label">{{ trans('cat.original_reg_num') }}</label>

                            <div class="col-md-6">
                                <input id="original_reg_num" type="text" class="form-control" name="original_reg_num" value="{{ old('original_reg_num') }}">

                                @if ($errors->has('original_reg_num'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('original_reg_num') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('last_reg_num') ? ' has-error' : '' }}">
                            <label for="last_reg_num" class="col-md-4 control-label">{{ trans('cat.last_reg_num') }}</label>

                            <div class="col-md-6">
                                <input id="last_reg_num" type="text" class="form-control" name="last_reg_num" value="{{ old('last_reg_num') }}">

                                @if ($errors->has('last_reg_num'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('last_reg_num') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('reg_num_2') ? ' has-error' : '' }}">
                            <label for="reg_num_2" class="col-md-4 control-label">{{ trans('cat.reg_num_2') }}</label>

                            <div class="col-md-6">
                                <input id="reg_num_2" type="text" class="form-control" name="reg_num_2" value="{{ old('reg_num_2') }}">

                                @if ($errors->has('reg_num_2'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('reg_num_2') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('reg_num_3') ? ' has-error' : '' }}">
                            <label for="reg_num_3" class="col-md-4 control-label">{{ trans('cat.reg_num_3') }}</label>

                            <div class="col-md-6">
                                <input id="reg_num_3" type="text" class="form-control" name="reg_num_3" value="{{ old('reg_num_3') }}">

                                @if ($errors->has('reg_num_3'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('reg_num_3') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('notes') ? ' has-error' : '' }}">
                            <label for="notes" class="col-md-4 control-label">{{ trans('cat.notes') }}</label>

                            <div class="col-md-6">
                                <input id="notes" type="text" class="form-control" name="notes" value="{{ old('notes') }}">

                                @if ($errors->has('notes'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('notes') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('breeder') ? ' has-error' : '' }}">
                            <label for="breeder" class="col-md-4 control-label">{{ trans('cat.breeder') }}</label>

                            <div class="col-md-6">
                                <input id="breeder" type="text" class="form-control" name="breeder" value="{{ old('breeder') }}">

                                @if ($errors->has('breeder'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('breeder') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('current_owner') ? ' has-error' : '' }}">
                            <label for="current_owner" class="col-md-4 control-label">{{ trans('cat.current_owner') }}</label>

                            <div class="col-md-6">
                                <input id="current_owner" type="text" class="form-control" name="current_owner" value="{{ old('current_owner') }}">

                                @if ($errors->has('current_owner'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('current_owner') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('country_of_origin') ? ' has-error' : '' }}">
                            <label for="country_of_origin" class="col-md-4 control-label">{{ trans('cat.country_of_origin') }}</label>

                            <div class="col-md-6">
                                <input id="country_of_origin" type="text" class="form-control" name="country_of_origin" value="{{ old('country_of_origin') }}">

                                @if ($errors->has('country_of_origin'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('country_of_origin') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('country') ? ' has-error' : '' }}">
                            <label for="country" class="col-md-4 control-label">{{ trans('cat.country') }}</label>

                            <div class="col-md-6">
                                <input id="country" type="text" class="form-control" name="country" value="{{ old('country') }}">

                                @if ($errors->has('country'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('country') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('ownership_notes') ? ' has-error' : '' }}">
                            <label for="ownership_notes" class="col-md-4 control-label">{{ trans('cat.ownership_notes') }}</label>

                            <div class="col-md-6">
                                <input id="ownership_notes" type="text" class="form-control" name="ownership_notes" value="{{ old('ownership_notes') }}">

                                @if ($errors->has('ownership_notes'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('ownership_notes') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('personal_info') ? ' has-error' : '' }}">
                            <label for="personal_info" class="col-md-4 control-label">{{ trans('cat.personal_info') }}</label>

                            <div class="col-md-6">
                                <input id="personal_info" type="text" class="form-control" name="personal_info" value="{{ old('personal_info') }}">

                                @if ($errors->has('personal_info'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('personal_info') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('doo') ? ' has-error' : '' }}">
                            <label for="doo" class="col-md-4 control-label">{{ trans('cat.doo') }}</label>

                            <div class="col-md-6">
                                <input id="doo" type="text" class="form-control" name="doo" value="{{ old('doo') }}">

                                @if ($errors->has('doo'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('doo') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Register
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection