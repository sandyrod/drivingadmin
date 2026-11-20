@extends('documents.layout')

@php
    $number = 'INV-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);
    $total = (float) $student->plan_cost;
    $paid = (float) $student->paid_amount;
    $balance = max(0, $total - $paid);
    $lessons = (int) $student->package_lessons;
    $minutes = \App\Models\Lesson::SLOT_MINUTES;
@endphp

@section('title', 'Invoice ' . $number)
@section('width', 'max-w-3xl')
@section('doc_type', 'Invoice')

@section('doc_meta')
    <p class="text-xs font-bold mt-2">Nº {{ $number }}</p>
    <p class="text-[11px] text-white/80 mt-0.5">{{ __('Fecha:') }} {{ now(config('app.school_timezone'))->format('Y-m-d') }}</p>
    <p class="text-[11px] text-white/80">{{ __('Inscripción:') }} {{ $student->created_at?->format('Y-m-d') }}</p>
@endsection

@section('content')
    <!-- Bill to -->
    <div class="grid sm:grid-cols-2 gap-4 mb-7">
        <div class="bg-brand-light/50 rounded-xl p-5 border border-brand/10">
            <h4 class="text-[10px] font-black uppercase tracking-wider text-brand mb-2">Bill To</h4>
            <h3 class="text-lg font-bold text-slate-800">{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</h3>
            @if($student->address)
                <p class="text-xs text-slate-500 mt-1">{{ $student->address }}{{ $student->city ? ', ' . $student->city : '' }}{{ $student->state ? ', ' . $student->state : '' }} {{ $student->zip_code }}</p>
            @endif
            @if($student->phone)<p class="text-xs text-slate-500 mt-0.5">Tel: {{ $student->phone }}</p>@endif
            @if($student->email)<p class="text-xs text-slate-500 mt-0.5">Email: {{ $student->email }}</p>@endif
        </div>
        <div class="rounded-xl p-5 border border-brand/10 text-sm space-y-1.5">
            <div class="flex justify-between gap-3"><span class="text-slate-500">{{ __('ID Alumno:') }}</span><span class="font-bold">EST-{{ str_pad($student->id, 3, '0', STR_PAD_LEFT) }}</span></div>
            @if($student->contract_number)
                <div class="flex justify-between gap-3"><span class="text-slate-500">{{ __('Contrato:') }}</span><span class="font-bold">{{ $student->contract_number }}</span></div>
            @endif
            <div class="flex justify-between gap-3"><span class="text-slate-500">Balance:</span>
                <span class="font-black {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">${{ number_format($balance, 2) }}</span></div>
            <div class="flex justify-between gap-3"><span class="text-slate-500">{{ __('Estado:') }}</span>
                <span class="font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $balance > 0 ? __('Pendiente') : __('Pagado') }}</span></div>
        </div>
    </div>

    <!-- Items -->
    <table class="w-full text-sm mb-6">
        <thead>
            <tr class="bg-brand text-white text-[11px] uppercase tracking-wider">
                <th class="text-left px-4 py-2.5 rounded-l-lg">Description</th>
                <th class="text-right px-4 py-2.5 rounded-r-lg">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr class="border-b border-brand/10">
                <td class="px-4 py-3">
                    <p class="font-bold text-slate-800">{{ $student->package ?: __('Sin paquete asignado') }}</p>
                    @if($student->package_id)
                        <p class="text-xs text-slate-500">
                            {{ $lessons > 0 ? ($lessons === 1 ? __('1 clase de manejo de :minutes min', ['minutes' => $minutes]) : __(':n clases de manejo de :minutes min', ['n' => $lessons, 'minutes' => $minutes])) : __('Sin clases de manejo') }}
                        </p>
                    @endif
                </td>
                <td class="px-4 py-3 text-right font-bold">${{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Payments -->
    <h4 class="text-[10px] font-black uppercase tracking-wider text-brand mb-2">Payments</h4>
    <table class="w-full text-sm mb-6">
        <thead>
            <tr class="bg-brand-light text-brand text-[11px] uppercase tracking-wider">
                <th class="text-left px-4 py-2">{{ __('Fecha') }}</th>
                <th class="text-left px-4 py-2">{{ __('Recibo') }}</th>
                <th class="text-left px-4 py-2">{{ __('Método') }}</th>
                <th class="text-right px-4 py-2">Payment</th>
            </tr>
        </thead>
        <tbody>
            @if($initialPayment > 0)
                <tr class="border-b border-slate-100">
                    <td class="px-4 py-2 text-slate-500">{{ $student->created_at?->format('Y-m-d') }}</td>
                    <td class="px-4 py-2 text-slate-500" colspan="2">{{ __('Pago inicial (inscripción)') }}</td>
                    <td class="px-4 py-2 text-right font-semibold">${{ number_format($initialPayment, 2) }}</td>
                </tr>
            @endif
            @foreach($payments as $p)
                <tr class="border-b border-slate-100">
                    <td class="px-4 py-2 text-slate-500">{{ date('Y-m-d', strtotime($p->payment_date)) }}</td>
                    <td class="px-4 py-2 text-slate-500">ABO-{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 py-2 text-slate-500">{{ __(\App\Models\StudentPayment::METHOD_LABELS[$p->payment_method] ?? $p->payment_method) }}</td>
                    <td class="px-4 py-2 text-right font-semibold">${{ number_format($p->amount, 2) }}</td>
                </tr>
            @endforeach
            @if($initialPayment <= 0 && $payments->isEmpty())
                <tr><td colspan="4" class="px-4 py-3 text-center text-slate-400 text-xs">{{ __('Sin pagos registrados') }}</td></tr>
            @endif
        </tbody>
    </table>

    <!-- Totals -->
    <div class="ml-auto w-full sm:w-72 space-y-1.5 text-sm">
        <div class="flex justify-between"><span class="text-slate-500">Total:</span><span class="font-bold">${{ number_format($total, 2) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Payments:</span><span class="font-bold text-green-600">-${{ number_format($paid, 2) }}</span></div>
        <div class="flex justify-between items-center border-t-2 border-brand pt-2 mt-2">
            <span class="font-black text-brand uppercase tracking-wider">Balance:</span>
            <span class="text-xl font-black {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">${{ number_format($balance, 2) }} USD</span>
        </div>
    </div>
@endsection

@section('footer_note', __('Invoice generado con los datos actuales del alumno y sus pagos registrados.'))
