@extends('documents.layout')

@php
    $student = $payment->student;
    $number = 'ABO-' . str_pad($payment->id, 4, '0', STR_PAD_LEFT);
@endphp

@section('title', 'Recibo de Pago ' . $number)
@section('doc_type', 'Recibo de Pago')

@section('doc_meta')
    <p class="text-xs font-bold mt-2">Nº {{ $number }}</p>
    <p class="text-[11px] text-white/80 mt-0.5">Fecha: {{ date('Y-m-d', strtotime($payment->payment_date)) }}</p>
@endsection

@section('content')
    <!-- Student -->
    <div class="bg-brand-light/50 rounded-xl p-5 mb-6 border border-brand/10">
        <h4 class="text-[10px] font-black uppercase tracking-wider text-brand mb-2">Detalles del Alumno</h4>
        <h3 class="text-lg font-bold text-slate-800">{{ $student?->first_name }} {{ $student?->last_name }}</h3>
        <p class="text-xs text-slate-500 mt-1">ID Alumno: EST-{{ str_pad($payment->student_id, 3, '0', STR_PAD_LEFT) }}</p>
        @if($student?->email)
            <p class="text-xs text-slate-500 mt-0.5">Email: {{ $student->email }}</p>
        @endif
    </div>

    <!-- Details -->
    <h4 class="text-[10px] font-black uppercase tracking-wider text-brand border-b border-brand/15 pb-2 mb-3">Concepto e Importe</h4>

    <div class="flex justify-between items-center text-sm py-1.5 gap-4">
        <span class="text-slate-500">Paquete Contratado:</span>
        <span class="font-bold text-slate-800 text-right">{{ $student?->package ?: '—' }}</span>
    </div>
    <div class="flex justify-between items-center text-sm py-1.5 border-t border-slate-100">
        <span class="text-slate-500">Método de Pago:</span>
        <span class="font-bold text-slate-800">{{ \App\Models\StudentPayment::METHOD_LABELS[$payment->payment_method] ?? $payment->payment_method }}</span>
    </div>

    @if($payment->notes)
        <div class="bg-brand-light/50 text-slate-600 rounded-lg p-3 text-xs border border-brand/10 my-3">
            <strong>Notas:</strong> {{ $payment->notes }}
        </div>
    @endif

    <div class="border-t-2 border-dashed border-brand/20 mt-5 pt-4 space-y-2">
        <div class="flex justify-between items-center py-1">
            <span class="font-semibold text-slate-700">Payment:</span>
            <span class="text-xl font-black text-brand">${{ number_format($payment->amount, 2) }} USD</span>
        </div>
        <div class="flex justify-between items-center text-sm py-1">
            <span class="text-slate-500">Balance:</span>
            <span class="font-bold {{ $payment->remaining_debt > 0 ? 'text-red-600' : 'text-green-600' }}">
                ${{ number_format($payment->remaining_debt, 2) }} USD
            </span>
        </div>
    </div>
@endsection

@section('footer_note', 'Este documento sirve como comprobante de pago oficial.')

@section('extra_buttons')
    <a href="{{ route('students.invoice', $payment->student_id) }}" class="bg-white border border-brand text-brand hover:bg-brand-light font-bold px-6 py-2.5 rounded-xl text-sm transition">
        Ver Invoice del alumno
    </a>
@endsection

@section('scripts')
    <script>
        // Open the print dialog right away (the receipt is opened right after registering a payment)
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    </script>
@endsection
