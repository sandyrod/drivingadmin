{{-- Ledger "+" → assign a package (enrollment package, or an additional one with its own invoice). Opened by openPackageModal() (scripts-package-modal). --}}
<div id="package-modal" class="hidden fixed inset-0 z-[70] flex items-start sm:items-center justify-center bg-black/60 p-2 sm:p-4"
    onclick="if (event.target === this) closePackageModal()" role="dialog" aria-modal="true" aria-labelledby="pkm-title">
    <form id="pkm-form" onsubmit="savePackageModal(event)" class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden">
        <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-slate-800">
            <div class="min-w-0">
                <h3 id="pkm-title" class="text-base font-bold text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-box-open text-amber-500"></i> {{ __('Asignar paquete') }}
                </h3>
                <p id="pkm-student" class="text-xs text-slate-400 mt-0.5 truncate"></p>
            </div>
            <button type="button" onclick="closePackageModal()" class="w-8 h-8 shrink-0 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-slate-100" title="{{ __('Cerrar') }}">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <div>
                <label for="pkm-package" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Paquete') }}</label>
                <select id="pkm-package" required onchange="renderPackageSummary()" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-amber-500"></select>
            </div>

            <div id="pkm-summary" class="rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-xs space-y-1"></div>

            <div id="pkm-extra-fields" class="space-y-4">
                <div>
                    <label for="pkm-date" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Fecha') }}</label>
                    <input type="date" id="pkm-date" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label for="pkm-notes" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Notas') }}</label>
                    <input type="text" id="pkm-notes" maxlength="1000" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-amber-500">
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" id="pkm-print" checked class="accent-amber-500"> {{ __('Abrir la factura al guardar') }}
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" id="pkm-pay" class="accent-amber-500"> {{ __('Registrar un pago a continuación') }}
                </label>
            </div>
        </div>

        <div class="flex justify-end gap-2 px-5 py-3 border-t border-slate-800 bg-slate-950/40">
            <button type="button" onclick="closePackageModal()" class="px-3 py-2 rounded-lg text-xs font-bold bg-slate-800 text-slate-200 hover:bg-slate-700">{{ __('Cancelar') }}</button>
            <button type="submit" id="pkm-save" class="px-4 py-2 rounded-lg text-xs font-bold bg-amber-500 text-slate-950 hover:bg-amber-600 disabled:opacity-50">
                <i class="fa-solid fa-file-invoice-dollar"></i> {{ __('Asignar y facturar') }}
            </button>
        </div>
    </form>
</div>
