{{-- Edit / reschedule one lesson from the student's "Lessons" tab: week calendar of the instructor
     with the busy, off-schedule and past hours blocked. Filled by openLessonEditor() (scripts-lesson-modal). --}}
<style>
    .lem-col { background-image: linear-gradient(to bottom, rgb(var(--slate-700) / .55) 1px, transparent 1px), linear-gradient(to bottom, rgb(var(--slate-800) / .5) 1px, transparent 1px); }
    .lem-off { background: repeating-linear-gradient(45deg, rgb(var(--slate-800) / .6) 0 6px, rgb(var(--slate-950) / .35) 6px 12px); }
</style>
<div id="lesson-modal" class="hidden fixed inset-0 z-[70] flex items-start sm:items-center justify-center bg-black/60 p-2 sm:p-4"
    onclick="if (event.target === this) closeLessonEditor()" role="dialog" aria-modal="true" aria-labelledby="lem-title">
    <div class="w-full max-w-6xl max-h-[96vh] flex flex-col bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-slate-800">
            <div class="min-w-0">
                <h3 id="lem-title" class="text-base font-bold text-slate-100 flex items-center gap-2"></h3>
                <p id="lem-subtitle" class="text-xs text-slate-400 mt-0.5"></p>
            </div>
            <button type="button" onclick="closeLessonEditor()" class="w-8 h-8 shrink-0 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-slate-100" title="{{ __('Cerrar') }}">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="lem-body" class="overflow-y-auto"></div>

        <!-- Footer -->
        <div id="lem-footer" class="flex flex-wrap items-center gap-2 px-5 py-3 border-t border-slate-800 bg-slate-950/40"></div>
    </div>
</div>
