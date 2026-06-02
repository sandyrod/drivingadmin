@extends('layouts.app')

@section('title', 'Página de Prueba - NY Driving School')

@section('section-pre-title', 'Módulo de Pruebas')
@section('section-title', 'Pantalla de Prueba de Diseño')

@section('content')
<div class="bg-slate-900 border border-slate-800 p-8 rounded-2xl max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <div class="bg-amber-500 text-slate-950 p-3 rounded-xl text-xl font-bold">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-slate-100">¡Layout Heredado Correctamente!</h3>
            <p class="text-sm text-slate-400">Esta pantalla de prueba hereda todo el diseño premium de driving.blade automáticamente.</p>
        </div>
    </div>

    <div class="border-t border-slate-800 pt-6">
        <p class="text-sm text-slate-300 leading-relaxed">
            Cualquier pantalla nueva que crees en tu aplicación Laravel solo necesitará extender del layout global para mantener la consistencia estética y funcional (incluyendo los toasts de notificación y la barra de navegación premium).
        </p>
    </div>

    <div class="flex gap-3 pt-2">
        <button onclick="showToast('¡Funciona el sistema de notificaciones!', 'success')" 
            class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-4 py-2 rounded-lg text-sm transition">
            Probar Toast Exitoso
        </button>
        <button onclick="showToast('Alerta de prueba', 'warning')" 
            class="bg-slate-800 hover:bg-slate-750 text-slate-200 font-bold px-4 py-2 rounded-lg text-sm border border-slate-700 transition">
            Probar Toast Alerta
        </button>
    </div>
</div>
@endsection
