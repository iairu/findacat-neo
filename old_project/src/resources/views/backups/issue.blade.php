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

@section('title',trans('backup.index_title'))

@section('content')
<h3 class="page-header">{{ trans('backup.index_title') }}</h3>
<div class="row">
  <div class="text-danger text-right">There was an issue with your upload, please check your PHP settings that they allow for large file uploads.</div>
  <div class="text-danger text-right">Also make sure that the UUIDs are not yet present in the database or reset the database.</div>
</div>
@endsection