{{-- Printable documents (invoice, payment receipt): blue and white, NY Driving Academy branding --}}
@php
    $schoolName = config('app.school_name', 'NY Driving Academy');
    $schoolAddress = config('app.school_address') ?: '123 Main St, Queens, NY 11101';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ $schoolName }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: {
                        brand: { DEFAULT: '#1976d2', dark: '#0d47a1', light: '#e3f2fd' },
                    },
                },
            },
        }
    </script>
    <style>
        /* Keep the blue header and table colors when printing / saving as PDF */
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @page { margin: 12mm; }
        @media print {
            body { background: #fff !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .doc { box-shadow: none !important; border: none !important; max-width: 100% !important; margin: 0 !important; }
        }
    </style>
</head>
<body class="bg-brand-light/60 text-slate-800 min-h-screen py-8 px-4">
    <div class="doc mx-auto w-full @yield('width', 'max-w-lg') bg-white rounded-2xl shadow-xl overflow-hidden border border-brand/20">
        <!-- Header -->
        <div class="bg-brand text-white px-8 py-6 flex justify-between items-start gap-6">
            <div>
                <h1 class="text-2xl font-black tracking-tight">{{ $schoolName }}</h1>
                <p class="text-xs text-white/80 mt-1">{{ $schoolAddress }}</p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-xl font-black uppercase tracking-wider">@yield('doc_type')</p>
                @yield('doc_meta')
            </div>
        </div>

        <div class="px-8 py-7">
            @yield('content')

            <div class="text-center border-t border-brand/15 pt-5 mt-8">
                <p class="text-xs text-brand font-semibold">¡Gracias por entrenar con nosotros!</p>
                <p class="text-[10px] text-slate-400 mt-1">@yield('footer_note')</p>
            </div>
        </div>
    </div>

    <!-- Buttons (screen only) -->
    <div class="no-print mt-6 flex justify-center gap-3 flex-wrap">
        <button onclick="window.print()" class="bg-brand hover:bg-brand-dark text-white font-bold px-6 py-2.5 rounded-xl text-sm transition shadow-lg">
            Imprimir / Guardar PDF
        </button>
        @yield('extra_buttons')
        <button onclick="window.close()" class="bg-white border border-brand/30 text-brand hover:bg-brand-light font-semibold px-6 py-2.5 rounded-xl text-sm transition">
            Cerrar
        </button>
    </div>

    @yield('scripts')
</body>
</html>
