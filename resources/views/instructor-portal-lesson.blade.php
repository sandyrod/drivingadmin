{{-- One lesson of "Mis clases". $canMark: buttons enabled; otherwise they are shown disabled with $lockedHint --}}
@php
    $statusLabels = [
        'scheduled' => [__('Programada'), 'bg-amber-100 text-amber-700'],
        'completed' => [__('Completada'), 'bg-green-100 text-green-700'],
        'no_show' => [__('No asistió'), 'bg-red-100 text-red-700'],
    ];
    $days = [__('Dom'), __('Lun'), __('Mar'), __('Mié'), __('Jue'), __('Vie'), __('Sáb')];
    $student = $l->student;
    $roadTest = $l->isRoadTest();
    $pickup = $student ? ($student->pickup_location ?: trim(implode(', ', array_filter([$student->address, $student->city, $student->state, $student->zip_code])))) : null;
    $place = $roadTest ? $l->location : $pickup;
    [$statusText, $statusCls] = $statusLabels[$l->status] ?? [$l->status, 'bg-slate-100 text-slate-600'];
@endphp
<article class="bg-white rounded-2xl border {{ $roadTest ? 'border-violet-300 ring-1 ring-violet-200' : 'border-slate-200' }} shadow-sm overflow-hidden">
    <div class="px-4 pt-3 pb-2 flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-bold text-slate-500">{{ $days[$l->starts_at->dayOfWeek] }} {{ $l->starts_at->format('m/d/Y') }}</p>
            <p class="text-lg font-black text-slate-900 leading-tight">{{ $l->starts_at->format('g:i A') }} – {{ $l->ends_at->format('g:i A') }}</p>
        </div>
        <div class="text-right space-y-1">
            @if($roadTest)<span class="inline-block bg-violet-600 text-white text-[10px] font-black px-2 py-0.5 rounded">ROAD TEST</span>@endif
            <span class="block text-[10px] font-bold px-2 py-0.5 rounded-full {{ $statusCls }}">{{ $statusText }}</span>
        </div>
    </div>

    <div class="px-4 pb-3 space-y-1.5 text-sm">
        <p class="font-bold text-slate-800"><i class="fa-solid fa-user text-brand w-4"></i>
            {{ $student ? trim($student->first_name . ' ' . $student->last_name) : __('Estudiante eliminado') }}
            @if($student?->transmission)<span class="text-xs font-normal text-slate-500">· {{ __($student->transmission) }}</span>@endif
        </p>
        @if($student?->phone)
            <p><i class="fa-solid fa-phone text-brand w-4"></i>
                <a href="tel:{{ preg_replace('/[^\d+]/', '', $student->phone) }}" class="text-brand font-semibold">{{ $student->phone }}</a></p>
        @endif
        @if($place)
            <p class="text-slate-600"><i class="fa-solid fa-location-dot text-brand w-4"></i>
                <span class="text-xs font-bold uppercase text-slate-400">{{ $roadTest ? __('Examen') : __('Recoger en') }}:</span> {{ $place }}</p>
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
            <p class="text-slate-600"><i class="fa-solid fa-glasses text-brand w-4"></i> {{ __('Restricción:') }} {{ $student->restrictions }}</p>
        @endif
    </div>

    @if($canMark)
        <div class="grid grid-cols-2 border-t border-slate-100">
            <form method="POST" action="{{ route('instructor.portal.status', [$token, $l->id]) }}"
                data-msg="{{ __('¿Marcar la clase de :name como COMPLETADA?', ['name' => $student?->first_name ?: __('este estudiante')]) }}" onsubmit="return confirm(this.dataset.msg)">
                @csrf
                <input type="hidden" name="status" value="completed">
                <button class="w-full py-3.5 bg-green-600 hover:bg-green-700 text-white font-bold text-sm"><i class="fa-solid fa-check"></i> {{ __('Completada') }}</button>
            </form>
            <form method="POST" action="{{ route('instructor.portal.status', [$token, $l->id]) }}"
                data-msg="{{ __('¿Marcar que :name NO ASISTIÓ?', ['name' => $student?->first_name ?: __('el estudiante')]) }}" onsubmit="return confirm(this.dataset.msg)">
                @csrf
                <input type="hidden" name="status" value="no_show">
                <button class="w-full py-3.5 bg-white hover:bg-red-50 text-red-600 font-bold text-sm border-l border-slate-100"><i class="fa-solid fa-user-xmark"></i> {{ __('No asistió') }}</button>
            </form>
        </div>
    @else
        <div class="grid grid-cols-2 border-t border-slate-100" title="{{ $lockedHint }}">
            <button type="button" disabled class="w-full py-3.5 font-bold text-sm cursor-not-allowed {{ $l->status === 'completed' ? 'bg-green-50 text-green-700/60' : 'bg-slate-100 text-slate-400' }}"><i class="fa-solid fa-check"></i> {{ __('Completada') }}</button>
            <button type="button" disabled class="w-full py-3.5 font-bold text-sm cursor-not-allowed border-l border-slate-200 {{ $l->status === 'no_show' ? 'bg-red-50 text-red-600/60' : 'bg-slate-100 text-slate-400' }}"><i class="fa-solid fa-user-xmark"></i> {{ __('No asistió') }}</button>
        </div>
        <p class="text-[11px] text-slate-400 text-center py-1.5 bg-slate-50">{{ $lockedHint }}</p>
    @endif
</article>
