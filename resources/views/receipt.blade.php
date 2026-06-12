<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Abono - ABO-{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}</title>
    <!-- Outfit Font for premium branding -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            body {
                background-color: white !important;
                color: black !important;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                border: 1px solid #e2e8f0 !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 2rem !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex items-center justify-center p-4">
    <!-- Main Print Receipt Container -->
    <div class="print-card w-full max-w-lg bg-white border border-slate-200 rounded-3xl shadow-xl p-8 relative overflow-hidden">
        <!-- Accent lines -->
        <div class="absolute top-0 left-0 right-0 h-2 bg-amber-500"></div>

        <!-- Header -->
        <div class="flex justify-between items-start mb-8">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">NY Driving School</h1>
                <p class="text-xs text-slate-400 font-semibold tracking-wider uppercase mt-1">School & Academy</p>
                <p class="text-[10px] text-slate-400 mt-1">123 Main St, Queens, NY 11101</p>
            </div>
            <div class="text-right">
                <span class="inline-block bg-amber-500/10 text-amber-600 font-extrabold text-[11px] px-3 py-1 rounded-full uppercase tracking-wider">
                    Recibo de Pago
                </span>
                <p class="text-xs font-bold text-slate-600 mt-2">Nº: ABO-{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}</p>
                <p class="text-[10px] text-slate-400 mt-1">Fecha: {{ date('Y-m-d', strtotime($payment->payment_date)) }}</p>
            </div>
        </div>

        <!-- Customer info -->
        <div class="bg-slate-50 rounded-2xl p-5 mb-6 border border-slate-100">
            <h4 class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Detalles del Alumno</h4>
            <h3 class="text-lg font-bold text-slate-800">{{ $payment->student->first_name }} {{ $payment->student->last_name }}</h3>
            <p class="text-xs text-slate-500 mt-1">ID Alumno: EST-{{ str_pad($payment->student->id, 3, '0', STR_PAD_LEFT) }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Email: {{ $payment->student->email }}</p>
        </div>

        <!-- Receipt Details Table -->
        <div class="space-y-4 mb-8">
            <h4 class="text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">Concepto e Importe</h4>
            
            <div class="flex justify-between items-center text-sm py-1">
                <span class="text-slate-500">Paquete Contratado:</span>
                <span class="font-bold text-slate-800">{{ $payment->student->package }}</span>
            </div>
            
            <div class="flex justify-between items-center text-sm py-1 border-t border-slate-50 pt-2">
                <span class="text-slate-500">Método de Pago:</span>
                <span class="font-bold text-slate-800">
                    @switch($payment->payment_method)
                        @case('Cash') Efectivo @break
                        @case('Credit Card') Tarjeta de Crédito @break
                        @case('Zelle') Zelle @break
                        @case('Venmo') Venmo @break
                        @case('Check') Cheque @break
                        @default {{ $payment->payment_method }}
                    @endswitch
                </span>
            </div>

            @if($payment->notes)
            <div class="bg-amber-50/50 text-amber-800/80 rounded-xl p-3 text-xs border border-amber-100/50 my-2">
                <strong>Notas:</strong> {{ $payment->notes }}
            </div>
            @endif

            <div class="border-t-2 border-dashed border-slate-200 my-6 pt-4 space-y-2">
                <div class="flex justify-between items-center text-base py-1">
                    <span class="font-semibold text-slate-700">Monto Abonado:</span>
                    <span class="text-xl font-black text-amber-500">${{ number_format($payment->amount, 2) }} USD</span>
                </div>
                <div class="flex justify-between items-center text-sm py-1">
                    <span class="text-slate-500">Saldo Restante (Deuda):</span>
                    <span class="font-bold {{ $payment->remaining_debt > 0 ? 'text-red-500' : 'text-green-500' }}">
                        ${{ number_format($payment->remaining_debt, 2) }} USD
                    </span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center border-t border-slate-100 pt-6">
            <p class="text-xs text-slate-400 font-semibold">¡Gracias por entrenar con nosotros!</p>
            <p class="text-[9px] text-slate-400 mt-1">Este documento sirve como comprobante de pago oficial.</p>
        </div>

        <!-- Action Button (only visible on screen) -->
        <div class="no-print mt-8 flex justify-center gap-3">
            <button onclick="window.print()" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 rounded-xl text-sm transition shadow-lg shadow-amber-500/20 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Recibo
            </button>
            <button onclick="window.close()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold px-6 py-2.5 rounded-xl text-sm transition">
                Cerrar Ventana
            </button>
        </div>
    </div>

    <!-- Automatically open browser print dialog on load -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        }
    </script>
</body>
</html>
