{{-- New payment from the student's "Receipts" tab. Opened by openPaymentModal() (scripts-payment-modal). --}}
<div id="payment-modal" class="hidden fixed inset-0 z-[70] flex items-start sm:items-center justify-center bg-black/60 p-2 sm:p-4"
    onclick="if (event.target === this) closePaymentModal()" role="dialog" aria-modal="true" aria-labelledby="pm-title">
    <form id="pm-form" onsubmit="savePaymentModal(event)" class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden">
        <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-slate-800">
            <div class="min-w-0">
                <h3 id="pm-title" class="text-base font-bold text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-plus text-emerald-500"></i> {{ __('Nuevo pago') }}
                </h3>
                <p id="pm-student" class="text-xs text-slate-400 mt-0.5 truncate"></p>
            </div>
            <button type="button" onclick="closePaymentModal()" class="w-8 h-8 shrink-0 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-slate-100" title="{{ __('Cerrar') }}">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <div class="flex items-center justify-between rounded-xl border border-slate-800 bg-slate-950 px-4 py-3">
                <span class="text-[10px] uppercase font-bold text-slate-500">{{ __('Saldo pendiente') }}</span>
                <span id="pm-debt" class="text-lg font-black text-red-500"></span>
            </div>

            <div>
                <label for="pm-amount" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Monto (USD)') }}</label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">$</span>
                        <input type="number" id="pm-amount" required min="0.01" step="0.01" inputmode="decimal"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-7 pr-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-amber-500">
                    </div>
                    <button type="button" onclick="payFullDebt()" class="shrink-0 px-3 rounded-lg text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">{{ __('Saldo completo') }}</button>
                </div>
                <p id="pm-after" class="text-[11px] text-slate-500 mt-1"></p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="pm-method" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Método') }}</label>
                    <select id="pm-method" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-amber-500">
                        <option value="Cash">{{ __('Efectivo') }}</option>
                        <option value="Credit Card">{{ __('Tarjeta de Crédito') }}</option>
                        <option value="Zelle">Zelle</option>
                        <option value="Venmo">Venmo</option>
                        <option value="Check">{{ __('Cheque') }}</option>
                    </select>
                </div>
                <div>
                    <label for="pm-date" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Fecha') }}</label>
                    <input type="date" id="pm-date" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-amber-500">
                </div>
            </div>

            <div>
                <label for="pm-notes" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Notas') }}</label>
                <input type="text" id="pm-notes" maxlength="1000" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-amber-500">
            </div>

            <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                <input type="checkbox" id="pm-print" checked class="accent-amber-500"> {{ __('Abrir el recibo al guardar') }}
            </label>
        </div>

        <div class="flex justify-end gap-2 px-5 py-3 border-t border-slate-800 bg-slate-950/40">
            <button type="button" onclick="closePaymentModal()" class="px-3 py-2 rounded-lg text-xs font-bold bg-slate-800 text-slate-200 hover:bg-slate-700">{{ __('Cancelar') }}</button>
            <button type="submit" id="pm-save" class="px-4 py-2 rounded-lg text-xs font-bold bg-emerald-500 text-slate-950 hover:bg-emerald-600 disabled:opacity-50">
                <i class="fa-solid fa-floppy-disk"></i> {{ __('Guardar pago') }}
            </button>
        </div>
    </form>
</div>
