@extends('layouts.admin')

@push('styles')
    @stack('styles')
@endpush

@section('css')
    @yield('css')
@endsection

@section('content')
    @yield('content')
@endsection

@push('scripts')
    @stack('scripts')
@endpush

@section('js')
    @yield('js')
@endsection
