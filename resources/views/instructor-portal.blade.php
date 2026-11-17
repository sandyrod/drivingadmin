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
    $statusLabels = [
        'scheduled' => ['Programada', 'bg-amber-100 text-amber-700'],
        'completed' => ['Completada', 'bg-green-100 text-green-700'],
        'no_show' => ['No asistió', 'bg-red-100 text-red-700'],
    ];
    $days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    $dayText = fn ($l) => $days[$l->starts_at->dayOfWeek] . ' ' . $l->starts_at->format('m/d');
    $timeText = fn ($l) => $l->starts_at->format('g:i A') . ' – ' . $l->ends_at->format('g:i A');
@endphp
<body class="bg-slate-100 text-slate-800 min-h-screen">
    <header class="bg-brand text-white">
        <div class="max-w-2xl mx-auto px-4 py-5">
            <p class="text-[11px] font-bold uppercase tracking-widest text-white/70">{{ config('app.school_name') }}</p>
            <h1 class="text-2xl font-black">Mis clases</h1>
            <p class="text-sm text-white/90">{{ $instructor->first_name }} {{ $instructor->last_name }}</p>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-5 space-y-6">
        @if(session('ok'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-semibold"><i class="fa-solid fa-circle-check"></i> {{ session('ok') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm font-semibold"><i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}</div>
        @endif

        @foreach([
            ['Por marcar', 'Clases que ya empezaron. Márcalas al terminar.', $pending, 'fa-list-check', true],
            ['Hoy', null, $today, 'fa-sun', false],
            ['Próximos días', null, $upcoming, 'fa-calendar-days', false],
            ['Marcadas recientemente', null, $recent, 'fa-clock-rotate-left', false],
        ] as [$title, $help, $list, $icon, $actions])
            @if($list->isNotEmpty() || $actions)
                <section>
                    <h2 class="text-xs font-black uppercase tracking-widest text-brand mb-1 flex items-center gap-2">
                        <i class="fa-solid {{ $icon }}"></i> {{ $title }}
                        @if($list->isNotEmpty())<span class="bg-brand text-white rounded-full px-2 py-0.5 text-[10px]">{{ $list->count() }}</span>@endif
                    </h2>
                    @if($help)<p class="text-xs text-slate-500 mb-2">{{ $help }}</p>@endif

                    @if($list->isEmpty())
                        <p class="bg-white rounded-xl border border-slate-200 px-4 py-4 text-sm text-slate-500 text-center">
                            <i class="fa-regular fa-face-smile text-green-600"></i> No tienes clases pendientes por marcar.
                        </p>
                    @endif

                    <div class="space-y-3">
                        @foreach($list as $l)
                            @php
                                $student = $l->student;
                                $roadTest = $l->isRoadTest();
                                $pickup = $student ? ($student->pickup_location ?: trim(implode(', ', array_filter([$student->address, $student->city, $student->state, $student->zip_code])))) : null;
                                $place = $roadTest ? $l->location : $pickup;
                                [$statusText, $statusCls] = $statusLabels[$l->status] ?? [$l->status, 'bg-slate-100 text-slate-600'];
                            @endphp
                            <article class="bg-white rounded-2xl border {{ $roadTest ? 'border-violet-300 ring-1 ring-violet-200' : 'border-slate-200' }} shadow-sm overflow-hidden">
                                <div class="px-4 pt-3 pb-2 flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-bold text-slate-500">{{ $dayText($l) }}</p>
                                        <p class="text-lg font-black text-slate-900 leading-tight">{{ $timeText($l) }}</p>
                                    </div>
                                    <div class="text-right space-y-1">
                                        @if($roadTest)<span class="inline-block bg-violet-600 text-white text-[10px] font-black px-2 py-0.5 rounded">ROAD TEST</span>@endif
                                        <span class="block text-[10px] font-bold px-2 py-0.5 rounded-full {{ $statusCls }}">{{ $statusText }}</span>
                                    </div>
                                </div>

                                <div class="px-4 pb-3 space-y-1.5 text-sm">
                                    <p class="font-bold text-slate-800"><i class="fa-solid fa-user text-brand w-4"></i>
                                        {{ $student ? trim($student->first_name . ' ' . $student->last_name) : 'Estudiante eliminado' }}
                                        @if($student?->transmission)<span class="text-xs font-normal text-slate-500">· {{ $student->transmission }}</span>@endif
                                    </p>
                                    @if($student?->phone)
                                        <p><i class="fa-solid fa-phone text-brand w-4"></i>
                                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $student->phone) }}" class="text-brand font-semibold">{{ $student->phone }}</a></p>
                                    @endif
                                    @if($place)
                                        <p class="text-slate-600"><i class="fa-solid fa-location-dot text-brand w-4"></i>
                                            <span class="text-xs font-bold uppercase text-slate-400">{{ $roadTest ? 'Examen' : 'Recoger en' }}:</span> {{ $place }}</p>
                                        <p class="flex gap-4 text-xs pl-5">
                                            <a href="{{ \App\Models\Lesson::googleMapsUrl($place) }}" target="_blank" rel="noopener" class="text-brand font-semibold"><i class="fa-solid fa-map-location-dot"></i> Google Maps</a>
                                            <a href="{{ \App\Models\Lesson::appleMapsUrl($place) }}" target="_blank" rel="noopener" class="text-brand font-semibold"><i class="fa-brands fa-apple"></i> Apple Maps</a>
                                        </p>
                                    @endif
                                    @if($l->vehicle)
                                        <p class="text-slate-600"><i class="fa-solid fa-car text-brand w-4"></i> {{ $l->vehicle->brand }} [{{ $l->vehicle->plate }}]</p>
                                    @endif
                                    @if($l->notes)
                                        <p class="text-slate-600"><i class="fa-solid fa-note-sticky text-brand w-4"></i> {{ $l->notes }}</p>
                                    @endif
                                    @if($student?->restrictions)
                                        <p class="text-slate-600"><i class="fa-solid fa-glasses text-brand w-4"></i> Restricción: {{ $student->restrictions }}</p>
                                    @endif
                                </div>

                                @if($actions)
                                    <div class="grid grid-cols-2 border-t border-slate-100">
                                        <form method="POST" action="{{ route('instructor.portal.status', [$token, $l->id]) }}"
                                            data-msg="¿Marcar la clase de {{ $student?->first_name ?: 'este estudiante' }} como COMPLETADA?" onsubmit="return confirm(this.dataset.msg)">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button class="w-full py-3.5 bg-green-600 hover:bg-green-700 text-white font-bold text-sm"><i class="fa-solid fa-check"></i> Completada</button>
                                        </form>
                                        <form method="POST" action="{{ route('instructor.portal.status', [$token, $l->id]) }}"
                                            data-msg="¿Marcar que {{ $student?->first_name ?: 'el estudiante' }} NO ASISTIÓ?" onsubmit="return confirm(this.dataset.msg)">
                                            @csrf
                                            <input type="hidden" name="status" value="no_show">
                                            <button class="w-full py-3.5 bg-white hover:bg-red-50 text-red-600 font-bold text-sm border-l border-slate-100"><i class="fa-solid fa-user-xmark"></i> No asistió</button>
                                        </form>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        @if($today->isEmpty() && $upcoming->isEmpty())
            <p class="text-center text-sm text-slate-500">No tienes clases agendadas para los próximos días.</p>
        @endif

        <p class="text-center text-[11px] text-slate-400 pt-4">Este enlace es personal. No lo compartas.<br>
            Si marcaste una clase por error, avisa a la oficina.</p>
    </main>
</body>
</html>
