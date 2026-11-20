@extends('layouts.app', ['isTabbed' => true])

@section('title', 'NY Driving School - Dashboard Demo')

@section('content')
    @include('driving.panels-1')
    @include('driving.panels-2')
    @include('driving.panels-3')
    @include('driving.lesson-modal')
    @include('driving.payment-modal')
    @include('driving.package-modal')
@endsection

@push('scripts')
    <script>
@include('driving.scripts-1')
@include('driving.scripts-2')
@include('driving.scripts-3')
@include('driving.scripts-4')
@include('driving.scripts-lesson-modal')
@include('driving.scripts-payment-modal')
@include('driving.scripts-package-modal')
@include('driving.scripts-ledger')
    </script>
@endpush
