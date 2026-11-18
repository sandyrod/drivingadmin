<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <meta name="theme-color" content="#1976d2">
    <title>Mis clases - {{ config('app.school_name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { DEFAULT: '#1976d2', dark: '#0d47a1', light: '#e3f2fd' } } } } };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
@php
    $searching = $q !== '' || $date;
    // Portal URL keeping the current search
    $tabUrl = fn ($key) => route('instructor.portal', array_filter(
        [$token, 'tab' => $key, 'q' => $q, 'date' => $date],
        fn ($v) => $v !== '' && $v !== null
    ));
@endphp
<body class="bg-slate-100 text-slate-800 min-h-screen">
    <header class="bg-brand text-white">
        <div class="max-w-2xl mx-auto px-4 py-5">
            <p class="text-[11px] font-bold uppercase tracking-widest text-white/70">{{ config('app.school_name') }}</p>
            <h1 class="text-2xl font-black">Mis clases</h1>
            <p class="text-sm text-white/90">{{ $instructor->first_name }} {{ $instructor->last_name }}</p>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-5 space-y-5">
        @if(session('ok'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-semibold"><i class="fa-solid fa-circle-check"></i> {{ session('ok') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm font-semibold"><i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}</div>
        @endif

        {{-- Search: any student data and/or a date --}}
        <form method="GET" action="{{ route('instructor.portal', $token) }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3 space-y-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="search" name="q" value="{{ $q }}" placeholder="Buscar estudiante: nombre, teléfono, correo, contrato, dirección…"
                    class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40">
            </div>
            <div class="flex gap-2">
                <input type="date" name="date" value="{{ $date }}" aria-label="Fecha de la clase"
                    class="flex-1 min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40">
                <button class="px-4 py-2.5 rounded-xl bg-brand hover:bg-brand-dark text-white font-bold text-sm"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
                @if($searching)
                    <a href="{{ route('instructor.portal', [$token, 'tab' => $tab]) }}" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm" title="Limpiar búsqueda"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </div>
        </form>

        {{-- Tabs --}}
        <nav class="grid grid-cols-3 gap-1 bg-white rounded-2xl border border-slate-200 p-1 shadow-sm">
            @foreach(['pendientes' => ['Pendientes', 'fa-list-check'], 'completadas' => ['Completadas', 'fa-circle-check'], 'no-asistio' => ['No asistió', 'fa-user-xmark']] as $key => [$label, $tabIcon])
                <a href="{{ $tabUrl($key) }}"
                    class="rounded-xl px-1 py-2 text-center text-xs sm:text-sm font-bold transition {{ $tab === $key ? 'bg-brand text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid {{ $tabIcon }}"></i> {{ $label }}
                    <span class="ml-0.5 rounded-full px-1.5 py-0.5 text-[10px] {{ $tab === $key ? 'bg-white/20' : 'bg-slate-100' }}">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </nav>

        @if($searching)
            <p class="text-xs text-slate-500">
                Resultados{{ $q !== '' ? ' para "' . $q . '"' : '' }}{{ $date ? ' del ' . \Carbon\Carbon::parse($date)->format('m/d/Y') : '' }}.
            </p>
        @endif

        @if($tab === 'pendientes')
            @foreach([
                ['Por marcar', 'Clases que ya empezaron. Márcalas al terminar.', $pending, 'fa-list-check', true],
                ['Hoy', 'Podrás marcarlas cuando empiecen.', $today, 'fa-sun', false],
                [$date ? 'Más tarde' : 'Próximos días', null, $upcoming, 'fa-calendar-days', false],
            ] as [$title, $help, $list, $icon, $canMark])
                @if($list->isNotEmpty() || $canMark)
                    <section>
                        <h2 class="text-xs font-black uppercase tracking-widest text-brand mb-1 flex items-center gap-2">
                            <i class="fa-solid {{ $icon }}"></i> {{ $title }}
                            @if($list->isNotEmpty())<span class="bg-brand text-white rounded-full px-2 py-0.5 text-[10px]">{{ $list->count() }}</span>@endif
                        </h2>
                        @if($help && $list->isNotEmpty())<p class="text-xs text-slate-500 mb-2">{{ $help }}</p>@endif

                        @if($list->isEmpty())
                            <p class="bg-white rounded-xl border border-slate-200 px-4 py-4 text-sm text-slate-500 text-center">
                                @if($searching)
                                    <i class="fa-regular fa-folder-open"></i> No hay clases por marcar que coincidan con la búsqueda.
                                @else
                                    <i class="fa-regular fa-face-smile text-green-600"></i> No tienes clases pendientes por marcar.
                                @endif
                            </p>
                        @endif

                        <div class="space-y-3">
                            @foreach($list as $l)
                                @include('instructor-portal-lesson', ['l' => $l, 'canMark' => $canMark, 'lockedHint' => 'Podrás marcarla cuando empiece la clase.'])
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach

            @if($today->isEmpty() && $upcoming->isEmpty() && !$searching)
                <p class="text-center text-sm text-slate-500">No tienes clases agendadas para los próximos días.</p>
            @endif
        @else
            <section class="space-y-3">
                @forelse($lessons as $l)
                    @include('instructor-portal-lesson', ['l' => $l, 'canMark' => false, 'lockedHint' => 'Clase ya marcada. Si fue un error, avisa a la oficina.'])
                @empty
                    <p class="bg-white rounded-xl border border-slate-200 px-4 py-6 text-sm text-slate-500 text-center">
                        <i class="fa-regular fa-folder-open"></i>
                        @if($searching)
                            No hay clases que coincidan con la búsqueda.
                        @elseif($tab === 'completadas')
                            Aún no tienes clases completadas.
                        @else
                            No tienes clases marcadas como "no asistió".
                        @endif
                    </p>
                @endforelse

                @if($lessons->hasPages())
                    <div class="flex items-center justify-between text-sm pt-1">
                        @if($lessons->onFirstPage())
                            <span class="px-4 py-2 rounded-xl bg-slate-200 text-slate-400 font-bold"><i class="fa-solid fa-chevron-left"></i> Anterior</span>
                        @else
                            <a href="{{ $lessons->previousPageUrl() }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-brand font-bold"><i class="fa-solid fa-chevron-left"></i> Anterior</a>
                        @endif
                        <span class="text-xs text-slate-500">Página {{ $lessons->currentPage() }} de {{ $lessons->lastPage() }}</span>
                        @if($lessons->hasMorePages())
                            <a href="{{ $lessons->nextPageUrl() }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-brand font-bold">Siguiente <i class="fa-solid fa-chevron-right"></i></a>
                        @else
                            <span class="px-4 py-2 rounded-xl bg-slate-200 text-slate-400 font-bold">Siguiente <i class="fa-solid fa-chevron-right"></i></span>
                        @endif
                    </div>
                @endif
            </section>
        @endif

        <p class="text-center text-[11px] text-slate-400 pt-4">Este enlace es personal. No lo compartas.<br>
            Si marcaste una clase por error, avisa a la oficina.</p>
    </main>
</body>
</html>
