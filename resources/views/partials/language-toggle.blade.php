{{-- ES / EN switch (next to the theme toggle) --}}
@php $other = app()->getLocale() === 'en' ? 'es' : 'en'; @endphp
<button type="button" onclick="setLanguage('{{ $other }}')"
    title="{{ $other === 'en' ? 'Switch to English' : 'Cambiar a español' }}"
    class="theme-toggle h-9 px-2 rounded-lg border border-slate-800 bg-slate-800/40 text-slate-400 hover:text-amber-500 hover:border-amber-500/60 flex items-center gap-1 text-[11px] font-black transition">
    <i class="fa-solid fa-language text-sm"></i>
    <span class="{{ app()->getLocale() === 'es' ? 'underline underline-offset-2' : 'opacity-50' }}">ES</span>
    <span class="opacity-40">|</span>
    <span class="{{ app()->getLocale() === 'en' ? 'underline underline-offset-2' : 'opacity-50' }}">EN</span>
</button>
