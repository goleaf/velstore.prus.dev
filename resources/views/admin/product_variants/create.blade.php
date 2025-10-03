@extends('admin.layouts.admin')

@section('content')
    @include('admin.product_variants.form', ['isEdit' => false])
@endsection
