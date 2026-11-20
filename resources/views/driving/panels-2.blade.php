{{-- Dashboard panels: payments, agenda, calendar, packages, instructor form --}}
    <!-- ================= PANELS: REGISTRO DE ABONOS ================= -->
    <div id="view-abonos" class="hidden max-w-4xl mx-auto space-y-8">
        <!-- REGISTRO DE ABONO FORM -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
            <div class="p-8 bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-transparent border-b border-slate-800">
                <h3 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar text-emerald-500"></i> {{ __('Registrar Abono Estudiante') }}
                </h3>
                <p class="text-xs text-slate-400 mt-1">{{ __('Registra un nuevo cobro, asigna el método de pago y actualiza el saldo del alumno.') }}</p>
            </div>

            <form id="payment-form" class="p-8 space-y-6" onsubmit="handlePaymentSubmit(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Seleccionar Estudiante') }} <span class="text-amber-500">*</span></label>
                        <select id="pay-student-id" required onchange="handlePaymentStudentChange()"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="">{{ __('Seleccione un alumno con deuda...') }}</option>
                            <!-- Dynamically loaded -->
                        </select>
                        <p id="pay-student-debt-info" class="text-xs text-slate-400 mt-2 hidden">
                            {{ __('Deuda actual:') }} <span class="font-bold text-red-500" id="pay-debt-amount-text">$0 USD</span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Forma de Pago') }} <span class="text-amber-500">*</span></label>
                        <select id="pay-method" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="Cash">{{ __('Efectivo (Cash)') }}</option>
                            <option value="Credit Card">{{ __('Tarjeta de Crédito') }}</option>
                            <option value="Zelle">Zelle</option>
                            <option value="Venmo">Venmo</option>
                            <option value="Check">{{ __('Cheque (Check)') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Monto del Abono ($ USD)') }} <span class="text-amber-500">*</span></label>
                        <input type="number" id="pay-amount" step="0.01" min="0.01" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm font-bold"
                            placeholder="{{ __('Ej. 150.00') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Fecha de Pago') }} <span class="text-amber-500">*</span></label>
                        <input type="date" id="pay-date" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Notas u Observaciones') }}</label>
                    <textarea id="pay-notes"
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        rows="2" placeholder="{{ __('Información adicional del abono (opcional)...') }}"></textarea>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-800/60 pt-6">
                    <button type="reset" class="px-5 py-2.5 rounded-lg text-sm font-medium border border-slate-800 hover:bg-slate-800 transition">{{ __('Limpiar') }}</button>
                    <button type="submit" id="payment-submit-btn" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-receipt"></i> <span>{{ __('Registrar Abono') }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- MOVIMIENTOS / HISTORY TABLE -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6 border-b border-slate-800 pb-4">
                <h4 class="text-base font-bold text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-list text-emerald-500"></i> {{ __('Historial de Movimientos (Abonos)') }}
                </h4>
                <span class="text-xs bg-emerald-500/10 text-emerald-400 px-3 py-1 rounded-full font-medium" id="movements-count-badge">{{ __('0 abonos') }}</span>
            </div>

            <div id="payment-movements-toolbar" class="mb-4"></div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead id="payment-movements-head" class="text-xs text-slate-400 uppercase tracking-wider border-b border-slate-800">
                        <!-- Encabezados generados por renderPaymentsTable() -->
                    </thead>
                    <tbody id="payment-movements-rows" class="divide-y divide-slate-800/50">
                        <!-- Dynamic rows loaded here -->
                    </tbody>
                </table>
            </div>
            <div id="payment-movements-pager" class="mt-4"></div>
        </div>
    </div>

    <!-- ================= PANELS: AGENDA DE CLASES ================= -->
    <div id="view-agenda" class="hidden space-y-8">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- BOOKING FORM -->
            <div class="xl:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden h-fit">
                <div class="p-6 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-b border-slate-800">
                    <h3 class="text-base font-bold text-slate-100 flex items-center gap-2">
                        <i id="lesson-form-icon" class="fa-solid fa-calendar-plus text-amber-500"></i>
                        <span id="lesson-form-title">{{ __('Agendar Clase de Manejo') }}</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1" id="lesson-form-subtitle">{{ __('Asigna instructor, fecha y hora según las clases del paquete del estudiante.') }}</p>
                </div>

                <form id="lesson-form" class="p-6 space-y-5" onsubmit="handleLessonSubmit(event)">
                    <!-- Shown while rescheduling an existing lesson -->
                    <div id="lesson-edit-banner" class="hidden rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-slate-300 flex items-start justify-between gap-3">
                        <span id="lesson-edit-banner-text"></span>
                        <button type="button" onclick="cancelLessonEdit()" class="shrink-0 font-bold text-amber-500 hover:underline">{{ __('Cancelar') }}</button>
                    </div>

                    <!-- Type: driving lesson or road test -->
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Tipo') }}</span>
                        <div class="grid grid-cols-2 gap-2" id="lesson-type-group">
                            <label class="cursor-pointer">
                                <input type="radio" name="lesson-type" value="lesson" checked onchange="handleLessonTypeChange()" class="peer sr-only">
                                <span class="flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-slate-950">
                                    <i class="fa-solid fa-car"></i> {{ __('Clase de manejo') }}
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="lesson-type" value="road_test" onchange="handleLessonTypeChange()" class="peer sr-only">
                                <span class="flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-violet-500/40 text-xs font-bold text-violet-400 transition peer-checked:bg-violet-600 peer-checked:border-violet-600 peer-checked:text-white">
                                    <i class="fa-solid fa-flag-checkered"></i> Road Test
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Student -->
                    <div>
                        <label for="lesson-student" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Estudiante') }} <span class="text-amber-500">*</span></label>
                        <select id="lesson-student" required onchange="handleAgendaStudentChange()"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="">{{ __('Selecciona un estudiante...') }}</option>
                        </select>
                    </div>

                    <!-- Package status (filled by renderAgendaPackagePanel) -->
                    <div id="lesson-package-panel" class="hidden"></div>

                    <!-- Lesson details: disabled until the student has hours available -->
                    <fieldset id="lesson-details" disabled class="space-y-5 disabled:opacity-40">
                        <div>
                            <label for="lesson-instructor" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Instructor') }} <span class="text-amber-500">*</span></label>
                            <select id="lesson-instructor" required onchange="renderAgendaBusySlots()"
                                class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                                <option value="">{{ __('Selecciona un instructor...') }}</option>
                            </select>
                            <p id="lesson-instructor-help" class="text-xs text-slate-500 mt-1.5 hidden"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="lesson-date" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Fecha') }} <span class="text-amber-500">*</span></label>
                                <input type="date" id="lesson-date" required onchange="renderAgendaBusySlots()"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            </div>
                            <div>
                                <label for="lesson-time" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Hora') }} <span class="text-amber-500">*</span></label>
                                <input type="time" id="lesson-time" required step="900"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="lesson-duration" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Duración') }} <span class="text-amber-500">*</span></label>
                            <select id="lesson-duration" required
                                class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                                <!-- Options generated by renderDurationOptions() -->
                            </select>
                        </div>

                        <!-- Road test site (only for road tests) -->
                        <div id="lesson-location-box" class="hidden">
                            <label for="lesson-location" class="block text-xs font-bold uppercase tracking-wider text-violet-400 mb-2">{{ __('Sitio del examen') }} <span class="text-violet-400">*</span></label>
                            <input type="text" id="lesson-location" maxlength="255" oninput="renderLocationLinks()"
                                class="w-full bg-slate-950 border border-violet-500/40 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-violet-500 transition text-sm placeholder-slate-600"
                                placeholder="{{ __('Ej: DMV Road Test Site, 1 Main St, Queens, NY') }}">
                            <div id="lesson-location-links" class="flex flex-wrap gap-3 mt-2 text-xs"></div>
                            <p class="text-xs text-slate-500 mt-1.5">{{ __('El Road Test no descuenta clases del paquete y deja al menos :min min libres antes y después.', ['min' => \App\Models\Lesson::ROAD_TEST_GAP_MINUTES]) }}</p>
                        </div>

                        <!-- Instructor / student agenda for the chosen day -->
                        <div id="lesson-busy-slots" class="hidden"></div>

                        <div>
                            <label for="lesson-notes" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Notas') }}</label>
                            <textarea id="lesson-notes" rows="2" maxlength="1000"
                                class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm placeholder-slate-600"
                                placeholder="{{ __('Punto de encuentro, objetivos de la clase...') }}"></textarea>
                        </div>

                        <button type="submit" id="lesson-submit-btn"
                            class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold py-3 rounded-xl transition flex items-center justify-center gap-2 text-sm">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span id="lesson-submit-text">{{ __('Agendar Clase') }}</span>
                        </button>
                    </fieldset>
                </form>
            </div>

            <!-- LESSONS LIST -->
            <div class="xl:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6 border-b border-slate-800 pb-4">
                    <h4 class="text-base font-bold text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-amber-500"></i> {{ __('Clases Agendadas') }}
                    </h4>
                    <span class="text-xs bg-amber-500/10 text-amber-500 px-3 py-1 rounded-full font-medium" id="lessons-count-badge">{{ __('0 próximas') }}</span>
                </div>

                <div id="lessons-toolbar" class="mb-4"></div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead id="lessons-head" class="text-xs text-slate-400 uppercase tracking-wider border-b border-slate-800"></thead>
                        <tbody id="lessons-rows" class="divide-y divide-slate-800/50"></tbody>
                    </table>
                </div>
                <div id="lessons-pager" class="mt-4"></div>
            </div>
        </div>
    </div>

    <!-- ================= PANELS: CALENDARIO (week / day by instructor, drag & drop) ================= -->
    <style>
        .cal-col { background-image: linear-gradient(to bottom, rgb(var(--slate-700) / .55) 1px, transparent 1px), linear-gradient(to bottom, rgb(var(--slate-800) / .5) 1px, transparent 1px); }
        .cal-off { background: repeating-linear-gradient(45deg, rgb(var(--slate-800) / .55) 0 6px, rgb(var(--slate-950) / .35) 6px 12px); }
    </style>
    <div id="view-calendario" class="hidden space-y-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <!-- Toolbar -->
            <div class="flex flex-wrap items-center gap-3 px-4 py-3 border-b border-slate-800">
                <div class="flex items-center gap-1">
                    <button type="button" onclick="calMove(-1)" class="w-8 h-8 rounded-lg hover:bg-slate-800 text-slate-300" title="{{ __('Anterior') }}"><i class="fa-solid fa-chevron-left"></i></button>
                    <button type="button" onclick="calMove(1)" class="w-8 h-8 rounded-lg hover:bg-slate-800 text-slate-300" title="{{ __('Siguiente') }}"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
                <label class="relative flex items-center gap-2 font-bold text-slate-100 cursor-pointer">
                    <span id="cal-label"></span>
                    <i class="fa-solid fa-caret-down text-slate-400 text-xs"></i>
                    <input type="date" id="cal-date-input" onchange="calGoTo(this.value)" class="absolute inset-0 opacity-0 cursor-pointer" aria-label="{{ __('Ir a la fecha') }}">
                </label>
                <div class="ml-auto flex items-center gap-2">
                    <button type="button" onclick="calGoTo(todayLocal())" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-300 hover:bg-slate-800 uppercase">{{ __('Hoy') }}</button>
                    <div class="flex rounded-lg border border-slate-800 overflow-hidden text-xs font-bold uppercase">
                        <button type="button" id="cal-view-day" onclick="calSetView('day')" class="px-3 py-1.5">{{ __('Día') }}</button>
                        <button type="button" id="cal-view-week" onclick="calSetView('week')" class="px-3 py-1.5">{{ __('Semana') }}</button>
                    </div>
                </div>
            </div>

            <!-- Instructor filter + legend -->
            <div class="flex flex-wrap items-center gap-2 px-4 py-2.5 border-b border-slate-800 text-xs">
                <span class="font-bold uppercase tracking-wider text-slate-500 mr-1">{{ __('Instructores:') }}</span>
                <div id="cal-instructor-chips" class="flex flex-wrap gap-1.5"></div>
                <div class="ml-auto flex flex-wrap items-center gap-3 text-slate-400">
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded border border-slate-700 bg-slate-900"></span> {{ __('Disponible') }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded border border-slate-700 cal-off"></span> {{ __('Fuera de horario') }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded" style="background:#7c3aed"></span> Road Test</span>
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded" style="background:#64748b"></span> {{ __('Completada') }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded" style="background:#dc2626"></span> {{ __('No asistió') }}</span>
                </div>
            </div>

            <p class="px-4 py-2 text-[11px] text-slate-500 border-b border-slate-800">
                <i class="fa-solid fa-hand-pointer text-amber-500"></i> {{ __('Arrastra una clase programada para cambiar su hora, día o instructor.') }}
                {{ __('Haz clic en un espacio libre para agendar ahí, o en una clase para ver sus opciones.') }}
            </p>

            <!-- Selected lesson -->
            <div id="cal-detail" class="hidden border-b border-slate-800"></div>

            <!-- Grid (filled by renderCalendar) -->
            <div id="cal-scroll" class="overflow-auto" style="max-height: 72vh">
                <div id="cal-grid"></div>
            </div>
        </div>
    </div>

    <!-- ================= PANELS: PAQUETES ================= -->
    <div id="view-paquetes" class="hidden space-y-8">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- PACKAGE FORM -->
            <div class="xl:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden h-fit">
                <div class="p-6 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-b border-slate-800">
                    <h3 class="text-base font-bold text-slate-100 flex items-center gap-2">
                        <i id="package-form-icon" class="fa-solid fa-box text-amber-500"></i>
                        <span id="package-form-title">{{ __('Nuevo Paquete') }}</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">{{ __('Cada clase de manejo (hora de servicio) dura') }} <span class="font-bold text-slate-200">{{ __('45 minutos') }}</span>.</p>
                </div>

                <form id="package-form" class="p-6 space-y-5" onsubmit="handlePackageSubmit(event)">
                    <div id="package-edit-banner" class="hidden rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-slate-300 flex items-start justify-between gap-3">
                        <span id="package-edit-banner-text"></span>
                        <button type="button" onclick="resetPackageForm()" class="shrink-0 font-bold text-amber-500 hover:underline">{{ __('Cancelar') }}</button>
                    </div>

                    <div>
                        <label for="pkg-name" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Nombre') }} <span class="text-amber-500">*</span></label>
                        <input type="text" id="pkg-name" required maxlength="100"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm placeholder-slate-600"
                            placeholder="{{ __('Ej: PRINCIPIANTE package 12 Lessons') }}">
                    </div>

                    <div>
                        <label for="pkg-description" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Descripción') }}</label>
                        <input type="text" id="pkg-description" maxlength="255"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm placeholder-slate-600"
                            placeholder="{{ __('Qué incluye el paquete (opcional)') }}">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="pkg-lessons" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Clases') }} <span class="text-amber-500">*</span></label>
                            <input type="number" id="pkg-lessons" required min="0" max="200" step="1" value="1" oninput="updatePackageDurationHint()"
                                class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                        </div>
                        <div>
                            <label for="pkg-price" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Precio') }} <span class="text-amber-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-slate-500 text-sm">$</span>
                                <input type="number" id="pkg-price" required min="0" max="99999.99" step="0.01"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-8 pr-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm placeholder-slate-600"
                                    placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <p id="pkg-duration-hint" class="text-xs text-slate-500 -mt-2"></p>

                    <label class="flex items-center gap-2 text-sm text-slate-300 select-none cursor-pointer">
                        <input type="checkbox" id="pkg-active" checked class="w-4 h-4 accent-amber-500">
                        {{ __('Disponible para la venta (activo)') }}
                    </label>

                    <div id="pkg-existing-buyers" class="hidden rounded-xl border border-slate-800 bg-slate-950 p-3 text-xs text-slate-400 flex gap-2">
                        <i class="fa-solid fa-circle-info text-amber-500 mt-0.5"></i>
                        <span id="pkg-existing-buyers-text"></span>
                    </div>

                    <button type="submit" id="package-submit-btn"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold py-3 rounded-xl transition flex items-center justify-center gap-2 text-sm">
                        <i class="fa-solid fa-save"></i>
                        <span id="package-submit-text">{{ __('Crear Paquete') }}</span>
                    </button>
                </form>
            </div>

            <!-- PACKAGE LIST -->
            <div class="xl:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-2 border-b border-slate-800 pb-4">
                    <h4 class="text-base font-bold text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-amber-500"></i> {{ __('Catálogo de Paquetes') }}
                    </h4>
                    <span class="text-xs bg-amber-500/10 text-amber-500 px-3 py-1 rounded-full font-medium" id="packages-count-badge">{{ __('0 activos') }}</span>
                </div>
                <p class="text-xs text-slate-500 mb-4">{{ __('Los cambios de precio o de clases solo aplican a las nuevas compras. Los estudiantes que ya compraron un paquete conservan el precio y las clases que pagaron.') }}</p>

                <div id="packages-toolbar" class="mb-4"></div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead id="packages-head" class="text-xs text-slate-400 uppercase tracking-wider border-b border-slate-800"></thead>
                        <tbody id="packages-rows" class="divide-y divide-slate-800/50"></tbody>
                    </table>
                </div>
                <div id="packages-pager" class="mt-4"></div>
            </div>
        </div>
    </div>

    <!-- ================= PANELS: REGISTRO INSTRUCTOR ================= -->
    <div id="view-registro-instructor" class="hidden max-w-3xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        <div class="p-8 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-b border-slate-800">
            <h3 class="text-lg font-bold text-slate-100" id="instructor-form-title">{{ __('Ficha de Registro de Instructor') }}</h3>
            <p class="text-xs text-slate-400 mt-1" id="instructor-form-subtitle">{{ __('Crea nuevos expedientes de instructores que se sincronizarán directamente con la base de datos.') }}</p>
        </div>

        <form id="instructor-form" class="p-8 space-y-6" onsubmit="handleInstructorSubmit(event)">
            <!-- 1. Datos Personales -->
            <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 border-b border-slate-800 pb-2">{{ __('Información del Instructor') }}</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Apellido') }} <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-last-name" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="{{ __('Ej. Rodríguez') }}">
                </div>    
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Primer Nombre') }} <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-first-name" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="{{ __('Ej. Luis') }}">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Segundo Nombre') }}</label>
                    <input type="text" id="ins-middle-name"
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="{{ __('Ej. Felipe') }}">
                </div>
                
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Fecha de Nacimiento') }} <span class="text-amber-500">*</span></label>
                    <input type="date" id="ins-birth-date" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Género') }} <span class="text-amber-500">*</span></label>
                    <select id="ins-gender" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                        <option value="M">{{ __('Masculino') }}</option>
                        <option value="F">{{ __('Femenino') }}</option>
                        <option value="O">{{ __('Otro') }}</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Correo Electrónico') }} <span class="text-amber-500">*</span></label>
                    <input type="email" id="ins-email" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="{{ __('ejemplo@correo.com') }}">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Teléfono de Contacto') }} <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-phone" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="{{ __('Ej. +1 914-555-7788') }}">
                </div>
            </div>

            <!-- 2. Ubicación -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Dirección de Residencia') }} <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-address" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="{{ __('Ej. 123 Main St') }}">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Ciudad') }} <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-city" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="{{ __('Ej. Queens') }}">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Estado (dirección)') }} <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-state" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        value="NY" placeholder="{{ __('Ej. NY') }}">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Cód. Postal') }} <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-zip-code" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="{{ __('Ej. 11101') }}">
                </div>
            </div>

            <!-- 3. Características DMV Físicas -->
            <div class="border-t border-slate-800/60 pt-6">
                <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">{{ __('Detalles DMV Físicos (Opcional)') }}</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Estatura') }}</label>
                        <input type="text" id="ins-height"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                            placeholder="{{ __('Ej. 5\'11" o 1.80m') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Color de Ojos') }}</label>
                        <select id="ins-eye-color"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="">{{ __('Selecciona...') }}</option>
                            <option value="BLU">{{ __('Azul (BLU)') }}</option>
                            <option value="BRN">{{ __('Marrón (BRN)') }}</option>
                            <option value="GRN">{{ __('Verde (GRN)') }}</option>
                            <option value="HAZ">{{ __('Avellana (HAZ)') }}</option>
                            <option value="GRY">{{ __('Gris (GRY)') }}</option>
                        </select>
                    </div>
                    <div class="flex items-center h-full pt-6">
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-950 px-4 py-2.5 rounded-lg border border-slate-800 hover:border-amber-500/60 w-full">
                            <input type="checkbox" id="ins-organ-donor" value="1" class="accent-amber-500">
                            <span class="text-sm">{{ __('¿Donante de Órganos?') }}</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Foto de Perfil (Opcional)') }}</label>
                    <div class="relative group border-2 border-dashed border-slate-800 hover:border-amber-500/60 bg-slate-950 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer min-h-[140px] transition text-center" id="ins-photo-zone" onclick="document.getElementById('ins-photo-input').click()">
                        <input type="file" id="ins-photo-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'ins-photo-preview', 'ins-photo-zone', 'ins-photo-base64')">
                        <input type="hidden" id="ins-photo-base64" value="">
                        
                        <!-- Placeholder State -->
                        <div class="upload-placeholder flex flex-col items-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                            <span class="text-xs text-slate-400 font-medium">{{ __('Subir Foto de Perfil') }}</span>
                            <span class="text-[10px] text-slate-600">{{ __('JPG, PNG o WEBP (Máx. 4MB)') }}</span>
                        </div>
                        
                        <!-- Preview State -->
                        <img id="ins-photo-preview" src="" class="hidden max-h-[120px] rounded-lg object-contain shadow-md" alt="{{ __('Vista previa') }}">
                        
                        <!-- Remove Button -->
                        <button type="button" onclick="clearUpload(event, 'ins-photo-input', 'ins-photo-preview', 'ins-photo-zone', 'ins-photo-base64')" 
                            class="absolute top-2 right-2 bg-red-500/20 hover:bg-red-500 text-red-400 hover:text-slate-950 p-1.5 rounded-lg text-xs transition z-10 hidden" id="ins-photo-remove">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 4. Licencia de Instructor NY DMV -->
            <div class="border-t border-slate-800/60 pt-6">
                <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">{{ __('Licencia de Instructor NY DMV (Opcional)') }}</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Nº de Documento (License)') }}</label>
                        <input type="text" id="ins-lic-number"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                            placeholder="{{ __('Ej. INS-LIC-001') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Fecha de Expiración') }}</label>
                        <input type="date" id="ins-lic-expiration"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Clase de Documento') }}</label>
                        <select id="ins-lic-class"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="">{{ __('Selecciona clase...') }}</option>
                            <option value="Class D">Class D (Passenger)</option>
                            <option value="Class DJ">Class DJ (Junior Operator)</option>
                            <option value="Class E">Class E (For-Hire Taxi/Limo)</option>
                            <option value="Class M">Class M (Motorcycle)</option>
                            <option value="Commercial">Commercial (CDL)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Foto de la Licencia (Anverso)') }}</label>
                        <div class="relative group border-2 border-dashed border-slate-800 hover:border-amber-500/60 bg-slate-950 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer min-h-[140px] transition duration-200 text-center" id="ins-lic-front-zone" onclick="document.getElementById('ins-lic-front-input').click()">
                            <input type="file" id="ins-lic-front-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'ins-lic-front-preview', 'ins-lic-front-zone', 'ins-lic-front-base64')">
                            <input type="hidden" id="ins-lic-front-base64" value="">
                            
                            <!-- Placeholder -->
                            <div class="upload-placeholder flex flex-col items-center gap-2">
                                <i class="fa-solid fa-id-card text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                                <span class="text-xs text-slate-400 font-medium">{{ __('Subir Licencia (Anverso)') }}</span>
                                <span class="text-[10px] text-slate-600">{{ __('JPG, PNG o WEBP (Máx. 4MB)') }}</span>
                            </div>
                            
                            <!-- Preview -->
                            <img id="ins-lic-front-preview" src="" class="hidden max-h-[120px] rounded-lg object-contain shadow-md" alt="{{ __('Licencia Anverso') }}">
                            
                            <!-- Remove Button -->
                            <button type="button" onclick="clearUpload(event, 'ins-lic-front-input', 'ins-lic-front-preview', 'ins-lic-front-zone', 'ins-lic-front-base64')" 
                                class="absolute top-2 right-2 bg-red-500/20 hover:bg-red-500 text-red-400 hover:text-slate-950 p-1.5 rounded-lg text-xs transition z-10 hidden" id="ins-lic-front-remove">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Foto de la Licencia (Reverso)') }}</label>
                        <div class="relative group border-2 border-dashed border-slate-800 hover:border-amber-500/60 bg-slate-950 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer min-h-[140px] transition duration-200 text-center" id="ins-lic-back-zone" onclick="document.getElementById('ins-lic-back-input').click()">
                            <input type="file" id="ins-lic-back-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'ins-lic-back-preview', 'ins-lic-back-zone', 'ins-lic-back-base64')">
                            <input type="hidden" id="ins-lic-back-base64" value="">
                            
                            <!-- Placeholder -->
                            <div class="upload-placeholder flex flex-col items-center gap-2">
                                <i class="fa-solid fa-id-card text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                                <span class="text-xs text-slate-400 font-medium">{{ __('Subir Licencia (Reverso)') }}</span>
                                <span class="text-[10px] text-slate-600">{{ __('JPG, PNG o WEBP (Máx. 4MB)') }}</span>
                            </div>
                            
                            <!-- Preview -->
                            <img id="ins-lic-back-preview" src="" class="hidden max-h-[120px] rounded-lg object-contain shadow-md" alt="{{ __('Licencia Reverso') }}">
                            
                            <!-- Remove Button -->
                            <button type="button" onclick="clearUpload(event, 'ins-lic-back-input', 'ins-lic-back-preview', 'ins-lic-back-zone', 'ins-lic-back-base64')" 
                                class="absolute top-2 right-2 bg-red-500/20 hover:bg-red-500 text-red-400 hover:text-slate-950 p-1.5 rounded-lg text-xs transition z-10 hidden" id="ins-lic-back-remove">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Vehículo Asignado y Turno -->
            <div class="border-t border-slate-800/60 pt-6">
                <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">{{ __('Detalles Operativos (Instructor)') }}</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Vehículo Asignado') }} <span class="text-amber-500">*</span></label>
                        <select id="ins-vehicle" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="">{{ __('Selecciona vehículo...') }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Tipo de Transmisión') }} <span class="text-amber-500">*</span></label>
                        <div class="flex gap-4 mt-1">
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-950 px-4 py-2.5 rounded-lg border border-slate-800 hover:border-amber-500/60 flex-1">
                                <input type="radio" name="ins-transmission" value="Automático" checked class="accent-amber-500">
                                <span class="text-sm">{{ __('Automático') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-950 px-4 py-2.5 rounded-lg border border-slate-800 hover:border-amber-500/60 flex-1">
                                <input type="radio" name="ins-transmission" value="Mecánico" class="accent-amber-500">
                                <span class="text-sm">{{ __('Mecánico (Stick Shift)') }}</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label for="ins-language" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Idioma preferido') }}</label>
                        <select id="ins-language"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">@foreach(config('app.languages') as $code => $name)<option value="{{ $code }}" @selected($code === config('app.default_language'))>{{ $name }}</option>@endforeach</select>
                        <p class="text-xs text-slate-500 mt-1.5">{{ __('Idioma de sus correos, SMS y de la página "Mis clases".') }}</p>
                    </div>
                    <!-- Working schedule: the agenda only allows lessons inside it -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Horario de Trabajo') }} <span class="text-amber-500">*</span></label>
                        <div class="flex flex-wrap gap-2" id="ins-work-days">
                            @foreach (\App\Models\Instructor::DAY_NAMES as $dayNumber => $dayName)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="ins-work-day" value="{{ $dayNumber }}" class="peer sr-only" @checked($dayNumber <= 6)>
                                    <span class="inline-block px-3 py-1.5 rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-slate-950 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-500">{{ __($dayName) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4 max-w-sm">
                            <div>
                                <label for="ins-work-start" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Desde') }}</label>
                                <input type="time" id="ins-work-start" required value="08:00" step="900"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            </div>
                            <div>
                                <label for="ins-work-end" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Hasta') }}</label>
                                <input type="time" id="ins-work-end" required value="17:00" step="900"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">{{ __('En la Agenda solo se podrán programar clases dentro de este horario.') }}</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-800/60 pt-6 flex justify-end gap-3">
                <button type="reset" class="px-5 py-2.5 rounded-lg text-sm font-medium border border-slate-800 hover:bg-slate-800 transition">{{ __('Reiniciar Formulario') }}</button>
                <button type="submit" id="instructor-submit-btn" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-save" id="instructor-submit-btn-icon"></i> <span id="instructor-submit-btn-text">{{ __('Registrar Instructor') }}</span>
                </button>
            </div>
        </form>
    </div>

