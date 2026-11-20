@extends('layouts.app', ['isTabbed' => true])

@section('title', 'NY Driving School - Dashboard Demo')

@section('content')
    @include('driving.panels-1')
    @include('driving.panels-2')
    @include('driving.panels-3')
    @include('driving.lesson-modal')
@endsection

@push('scripts')
    <script>
@include('driving.scripts-1')
@include('driving.scripts-2')
@include('driving.scripts-3')
@include('driving.scripts-4')
@include('driving.scripts-lesson-modal')
    </script>
@endpush
