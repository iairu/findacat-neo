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
.comparison-table {
    margin: 20px 0;
}
.comparison-table th {
    background-color: #f5f5f5;
    font-weight: bold;
    padding: 10px;
}
.comparison-table td {
    padding: 10px;
    border: 1px solid #ddd;
}
.existing-row {
    background-color: #fff3cd;
}
.new-row {
    background-color: #d1ecf1;
}
.warning-box {
    background-color: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 5px;
    padding: 20px;
    margin: 20px 0;
}
.warning-box h4 {
    color: #856404;
    margin-top: 0;
}
</style>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">⚠️ {{ trans('cat.duplicate_warning_title') }}</h3>
                </div>
                <div class="panel-body">
                    <div class="warning-box">
                        <h4>{{ trans('cat.similar_cat_exists') }}</h4>
                        <p>{{ trans('cat.duplicate_warning_message') }}</p>
                    </div>

                    <h4 style="margin-top: 30px;">{{ trans('cat.comparison') }}:</h4>
                    
                    <table class="table table-bordered comparison-table">
                        <thead>
                            <tr>
                                <th style="width: 20%;">{{ trans('cat.field') }}</th>
                                <th style="width: 40%;">{{ trans('cat.existing_cat') }}</th>
                                <th style="width: 40%;">{{ trans('cat.new_cat') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>{{ trans('cat.titles_before_name') }}</strong></td>
                                <td class="existing-row">{{ $existingCat->titles_before_name ?: '-' }}</td>
                                <td class="new-row">{{ $newCat->titles_before_name ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('cat.full_name') }}</strong></td>
                                <td class="existing-row">{{ $existingCat->full_name }}</td>
                                <td class="new-row">{{ $newCat->full_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('cat.titles_after_name') }}</strong></td>
                                <td class="existing-row">{{ $existingCat->titles_after_name ?: '-' }}</td>
                                <td class="new-row">{{ $newCat->titles_after_name ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('cat.gender') }}</strong></td>
                                <td class="existing-row">{{ $existingCat->gender_id == 1 ? trans('app.male') : trans('app.female') }}</td>
                                <td class="new-row">{{ $newCat->gender_id == 1 ? trans('app.male') : trans('app.female') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('cat.breed') }}</strong></td>
                                <td class="existing-row">{{ $existingCat->breed ?: '-' }}</td>
                                <td class="new-row">{{ $newCat->breed ?: '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="margin-top: 30px; text-align: center;">
                        <h4 style="color: #856404; margin-bottom: 20px;">{{ trans('cat.confirm_registration_question') }}</h4>
                        
                        <form method="POST" action="{{ route('register-cat') }}" style="display: inline-block; margin-right: 10px;">
                            @csrf
                            
                            <!-- Pass all form data through hidden fields -->
                            @foreach($formData as $key => $value)
                                @if($key !== '_token')
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            
                            <!-- Confirmation flag -->
                            <input type="hidden" name="confirm_duplicate" value="1">
                            
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="glyphicon glyphicon-ok"></i> {{ trans('cat.yes_continue_registration') }}
                            </button>
                        </form>

                        <a href="{{ route('register-cat') }}" class="btn btn-default btn-lg">
                            <i class="glyphicon glyphicon-remove"></i> {{ trans('cat.no_cancel') }}
                        </a>
                    </div>

                    <div style="margin-top: 30px; padding: 15px; background-color: #e7f3ff; border-left: 4px solid #2196F3; border-radius: 3px;">
                        <p style="margin: 0;">
                            <strong>{{ trans('cat.tip') }}:</strong> {{ trans('cat.duplicate_tip_message') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection