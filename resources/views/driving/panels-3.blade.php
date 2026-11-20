{{-- Dashboard panels: vehicle form, survey, sheets viewer, automations --}}
    <!-- ================= PANELS: REGISTRO VEHICULO ================= -->
    <div id="view-registro-vehiculo" class="hidden max-w-3xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        <div class="p-8 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-b border-slate-800">
            <h3 class="text-lg font-bold text-slate-100" id="vehicle-form-title">{{ __('Ficha de Registro de Vehículo') }}</h3>
            <p class="text-xs text-slate-400 mt-1" id="vehicle-form-subtitle">{{ __('Crea nuevos expedientes de vehículos que se sincronizarán directamente con la base de datos.') }}</p>
        </div>

        <form id="vehicle-form" class="p-8 space-y-6" onsubmit="handleVehicleSubmit(event)">
            <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 border-b border-slate-800 pb-2">{{ __('Información del Vehículo') }}</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Marca / Modelo') }} <span class="text-amber-500">*</span></label>
                    <input type="text" id="veh-brand" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="{{ __('Ej. Toyota Corolla (2022)') }}">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Placa Patente') }} <span class="text-amber-500">*</span></label>
                    <input type="text" id="veh-plate" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="{{ __('Ej. AB123CD') }}">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Transmisión') }} <span class="text-amber-500">*</span></label>
                    <select id="veh-transmission" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                        <option value="Automático">{{ __('Automático') }}</option>
                        <option value="Mecánico">{{ __('Mecánico (Stick Shift)') }}</option>
                    </select>
                </div>
            </div>

            <div class="border-t border-slate-800/60 pt-6 flex justify-end gap-3">
                <button type="reset" class="px-5 py-2.5 rounded-lg text-sm font-medium border border-slate-800 hover:bg-slate-800 transition">{{ __('Reiniciar Formulario') }}</button>
                <button type="submit" id="vehicle-submit-btn" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-save" id="vehicle-submit-btn-icon"></i> <span id="vehicle-submit-btn-text">{{ __('Registrar Vehículo') }}</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ================= PANELS: ENCUESTA POST-SERVICIO ================= -->
    <div id="view-encuesta" class="hidden max-w-xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        <div class="p-8 bg-gradient-to-r from-yellow-500/10 via-amber-500/5 to-transparent border-b border-slate-800 text-center">
            <div class="w-12 h-12 bg-amber-500 text-slate-950 rounded-full flex items-center justify-center mx-auto text-xl mb-3 font-extrabold">★</div>
            <h3 class="text-lg font-bold text-slate-100">{{ __('Evaluación de Calidad del Servicio') }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ __('Queremos conocer tu opinión. Califícanos utilizando estrellas.') }}</p>
        </div>

        <form id="survey-form" class="p-8 space-y-6" onsubmit="handleSurveySubmit(event)">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('1. Selecciona a tu Instructor de Práctica') }} <span class="text-amber-500">*</span></label>
                <select id="srv-instructor" required
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                    <option value="">{{ __('Selecciona un profesional...') }}</option>
                    <!-- Dynamically loaded -->
                </select>
            </div>

            <!-- STAR RATING ATENCION -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">{{ __('2. Atención y Paciencia del Instructor') }} <span class="text-amber-500">*</span></label>
                <p class="text-xs text-slate-500 mb-3">{{ __('(1 Estrella es Deficiente | 5 Estrellas es Excelente)') }}</p>
                <div class="flex items-center gap-2 justify-center py-4 bg-slate-950 rounded-xl border border-slate-800/80">
                    <input type="hidden" id="srv-rating" value="0" required>
                    <button type="button" onclick="setStarRating('srv-rating', 1)" class="star-btn-srv-rating text-3xl text-slate-700 hover:scale-110 transition"><i class="fa-solid fa-star"></i></button>
                    <button type="button" onclick="setStarRating('srv-rating', 2)" class="star-btn-srv-rating text-3xl text-slate-700 hover:scale-110 transition"><i class="fa-solid fa-star"></i></button>
                    <button type="button" onclick="setStarRating('srv-rating', 3)" class="star-btn-srv-rating text-3xl text-slate-700 hover:scale-110 transition"><i class="fa-solid fa-star"></i></button>
                    <button type="button" onclick="setStarRating('srv-rating', 4)" class="star-btn-srv-rating text-3xl text-slate-700 hover:scale-110 transition"><i class="fa-solid fa-star"></i></button>
                    <button type="button" onclick="setStarRating('srv-rating', 5)" class="star-btn-srv-rating text-3xl text-slate-700 hover:scale-110 transition"><i class="fa-solid fa-star"></i></button>
                </div>
            </div>

            <!-- STAR RATING ESTADO VEHICULO -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">{{ __('3. Estado de Limpieza y Funcionamiento del Vehículo') }} <span class="text-amber-500">*</span></label>
                <p class="text-xs text-slate-500 mb-3">{{ __('(1 Estrella es Deficiente | 5 Estrellas es Excelente)') }}</p>
                <div class="flex items-center gap-2 justify-center py-4 bg-slate-950 rounded-xl border border-slate-800/80">
                    <input type="hidden" id="srv-vehicle-rating" value="0" required>
                    <button type="button" onclick="setStarRating('srv-vehicle-rating', 1)" class="star-btn-srv-vehicle-rating text-3xl text-slate-700 hover:scale-110 transition"><i class="fa-solid fa-star"></i></button>
                    <button type="button" onclick="setStarRating('srv-vehicle-rating', 2)" class="star-btn-srv-vehicle-rating text-3xl text-slate-700 hover:scale-110 transition"><i class="fa-solid fa-star"></i></button>
                    <button type="button" onclick="setStarRating('srv-vehicle-rating', 3)" class="star-btn-srv-vehicle-rating text-3xl text-slate-700 hover:scale-110 transition"><i class="fa-solid fa-star"></i></button>
                    <button type="button" onclick="setStarRating('srv-vehicle-rating', 4)" class="star-btn-srv-vehicle-rating text-3xl text-slate-700 hover:scale-110 transition"><i class="fa-solid fa-star"></i></button>
                    <button type="button" onclick="setStarRating('srv-vehicle-rating', 5)" class="star-btn-srv-vehicle-rating text-3xl text-slate-700 hover:scale-110 transition"><i class="fa-solid fa-star"></i></button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('4. Tu nombre') }} <span class="normal-case font-normal text-slate-500">{{ __('(opcional, se mostrará en la web si se publica tu reseña)') }}</span></label>
                <input type="text" id="srv-author" maxlength="100"
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                    placeholder="{{ __('Ej: María G.') }}">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('5. ¿Tienes alguna queja, observación o sugerencia?') }}</label>
                <textarea id="srv-comment"
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                    rows="3" placeholder="{{ __('Por favor detalla cualquier observación aquí...') }}"></textarea>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-3 rounded-lg text-sm transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> {{ __('Enviar Evaluación') }}
                </button>
            </div>
        </form>
    </div>

    <!-- ================= PANELS: REGISTRO DE ABONOS ================= -->
    <div id="view-abonos" class="hidden space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form Card -->
            <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl h-fit">
                <div class="p-6 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-b border-slate-800">
                    <h3 class="text-base font-bold text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-amber-500"></i>
                        {{ __('Registrar Nuevo Abono') }}
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">{{ __('Registra pagos de estudiantes con deuda pendiente.') }}</p>
                </div>
                
                <form id="payment-form" class="p-6 space-y-4" onsubmit="handlePaymentSubmit(event)">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Seleccionar Estudiante') }} <span class="text-amber-500">*</span></label>
                        <select id="pay-student-id" required onchange="handlePaymentStudentChange()"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="">{{ __('Selecciona un estudiante...') }}</option>
                        </select>
                        <p id="pay-student-debt-help" class="text-xs text-slate-400 mt-1.5 hidden">
                            {{ __('Costo total:') }} <span id="pay-student-cost-val" class="font-semibold text-slate-200"></span> | 
                            {{ __('Abonado:') }} <span id="pay-student-paid-val" class="font-semibold text-emerald-400"></span> | 
                            {{ __('Deuda:') }} <span id="pay-student-debt-val" class="font-semibold text-red-400"></span>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Monto a Abonar') }} <span class="text-amber-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-slate-500 text-sm">$</span>
                                <input type="number" id="pay-amount" step="0.01" min="0.01" required
                                    class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-8 pr-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm placeholder-slate-600"
                                    placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Forma de Pago') }} <span class="text-amber-500">*</span></label>
                            <select id="pay-method" required
                                class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                                <option value="Cash">{{ __('Efectivo') }}</option>
                                <option value="Credit Card">{{ __('Tarjeta de Crédito') }}</option>
                                <option value="Zelle">Zelle</option>
                                <option value="Venmo">Venmo</option>
                                <option value="Check">{{ __('Cheque') }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Fecha del Abono') }} <span class="text-amber-500">*</span></label>
                        <input type="date" id="pay-date" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Notas / Concepto') }}</label>
                        <textarea id="pay-notes" rows="2"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm placeholder-slate-600"
                            placeholder="{{ __('Notas adicionales sobre el pago...') }}"></textarea>
                    </div>

                    <button type="submit"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold py-3 rounded-xl transition flex items-center justify-center gap-2 text-sm shadow-lg shadow-amber-500/10">
                        <i class="fa-solid fa-save"></i>
                        {{ __('Registrar Abono') }}
                    </button>
                </form>
            </div>

            <!-- History Card -->
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex flex-col">
                <div class="p-6 border-b border-slate-800 flex justify-between items-center bg-gradient-to-r from-slate-900 to-slate-950">
                    <div>
                        <h3 class="text-base font-bold text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-amber-500"></i>
                            {{ __('Historial de Movimientos') }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">{{ __('Lista de todos los abonos registrados en el sistema.') }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-400 uppercase tracking-wider border-b border-slate-800 bg-slate-950/40">
                            <tr>
                                <th class="px-6 py-4">ID</th>
                                <th class="px-6 py-4">{{ __('Estudiante') }}</th>
                                <th class="px-6 py-4">{{ __('Fecha') }}</th>
                                <th class="px-6 py-4">{{ __('Método') }}</th>
                                <th class="px-6 py-4 text-right">{{ __('Monto') }}</th>
                                <th class="px-6 py-4 text-right">{{ __('Deuda Restante') }}</th>
                                <th class="px-6 py-4 text-center">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody id="payment-movements-rows" class="divide-y divide-slate-800/50">
                            <!-- Dynamic row items -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= PANELS: SIMULADOR GOOGLE SHEETS ================= -->
    <div id="view-sheets-db" class="hidden space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-900 p-4 border border-slate-800 rounded-2xl">
            <div class="flex items-center gap-3">
                <div class="bg-emerald-600 text-white p-2 rounded-lg text-lg flex items-center justify-center font-bold">
                    <i class="fa-solid fa-file-excel"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">{{ __('Libro: Sistema_Academia_Manejo') }}</h3>
                    <p class="text-xs text-slate-400">{{ __('Pestañas relacionales actualizadas dinámicamente') }}</p>
                </div>
            </div>
            <!-- SHEET TABS -->
            <div class="flex flex-wrap gap-1 bg-slate-950 p-1.5 rounded-lg border border-slate-800">
                <button onclick="switchSheetTab('students')" id="sheet-btn-students"
                    class="sheet-tab px-3 py-1.5 rounded text-xs font-bold transition bg-slate-800 text-slate-100">📁 {{ __('Estudiantes') }}</button>
                <button onclick="switchSheetTab('instructors')" id="sheet-btn-instructors"
                    class="sheet-tab px-3 py-1.5 rounded text-xs font-bold transition text-slate-400 hover:text-slate-100">📁 {{ __('Instructores') }}</button>
                <button onclick="switchSheetTab('vehicles')" id="sheet-btn-vehicles"
                    class="sheet-tab px-3 py-1.5 rounded text-xs font-bold transition text-slate-400 hover:text-slate-100">📁 {{ __('Vehículos') }}</button>
                <button onclick="switchSheetTab('reviews')" id="sheet-btn-reviews"
                    class="sheet-tab px-3 py-1.5 rounded text-xs font-bold transition text-slate-400 hover:text-slate-100">📁 {{ __('Encuestas Recibidas') }}</button>
                <button onclick="switchSheetTab('payments')" id="sheet-btn-payments"
                    class="sheet-tab px-3 py-1.5 rounded text-xs font-bold transition text-slate-400 hover:text-slate-100">📁 {{ __('Historial Abonos') }}</button>
            </div>
        </div>

        <!-- GOOGLE SHEETS WINDOW MOCKUP -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="bg-slate-950 px-4 py-2 border-b border-slate-800 flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-xs text-slate-400 ml-2 font-mono">https://docs.google.com/spreadsheets/d/1A-m...</span>
            </div>

            <!-- Búsqueda, filtros y registros por página -->
            <div id="sheet-table-toolbar" class="px-4 py-3 border-b border-slate-800"></div>

            <!-- TABLE VIEWPORT FOR SHEET TABS -->
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left font-mono whitespace-nowrap">
                    <thead class="bg-slate-800/40 text-slate-400 uppercase tracking-wider border-b border-slate-800" id="sheet-table-head">
                        <!-- Headers -->
                    </thead>
                    <tbody class="divide-y divide-slate-800/40" id="sheet-table-body">
                        <!-- Rows -->
                    </tbody>
                </table>
            </div>

            <!-- Paginador -->
            <div id="sheet-table-pager" class="px-4 py-3 border-t border-slate-800"></div>
        </div>
    </div>

    <!-- ================= PANELS: GOOGLE APPS SCRIPT AUTOMATION ================= -->
    <div id="view-apps-script" class="hidden space-y-8 max-w-4xl mx-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-center gap-3 border-b border-slate-800 pb-4 mb-6">
                <div class="bg-amber-500 text-slate-950 w-10 h-10 rounded-lg flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-code"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base">{{ __('Automatizaciones con Google Apps Script (Trigger Engine)') }}</h3>
                    <p class="text-xs text-slate-400">{{ __('Cómo funciona el Backend automatizado en Google Sheets sin servidores externos.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-amber-500 uppercase tracking-wider">{{ __('Flujos del Sistema:') }}</h4>

                    <div class="bg-slate-950 p-4 border border-slate-800 rounded-xl flex items-start gap-3">
                        <div class="bg-amber-500/10 text-amber-500 p-2 rounded-lg mt-0.5"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                        <div class="space-y-1">
                            <h5 class="text-xs font-bold text-slate-200">{{ __('Emisión de Factura Automatizada') }}</h5>
                            <p class="text-xs text-slate-400">{{ __('Al registrar un estudiante nuevo, un script duplica una plantilla de Google Docs, inserta los datos dinámicos, la exporta a PDF en Google Drive y la envía al correo.') }}</p>
                            <button onclick="triggerInvoiceSimulation()"
                                class="mt-2 bg-amber-500/10 text-amber-500 border border-amber-500/20 hover:bg-amber-500 hover:text-slate-950 font-bold px-3 py-1 rounded text-xs transition">
                                {{ __('Simular Trigger Factura') }}
                            </button>
                        </div>
                    </div>

                    <div class="bg-slate-950 p-4 border border-slate-800 rounded-xl flex items-start gap-3">
                        <div class="bg-emerald-500/10 text-emerald-500 p-2 rounded-lg mt-0.5"><i class="fa-brands fa-whatsapp"></i></div>
                        <div class="space-y-1">
                            <h5 class="text-xs font-bold text-slate-200">{{ __('Notificación de Bienvenida por Whatsapp') }}</h5>
                            <p class="text-xs text-slate-400">{{ __('Cuando un nuevo registro de Google Forms ingresa, envía un aviso con la información del plan contratado y el link de Google Classroom de teoría.') }}</p>
                            <button onclick="triggerWelcomeSimulation()"
                                class="mt-2 bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 hover:bg-emerald-500 hover:text-slate-950 font-bold px-3 py-1 rounded text-xs transition">
                                {{ __('Simular Notificación') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- APPS SCRIPT LOG CONSOLE -->
                <div class="flex flex-col bg-slate-950 border border-slate-800 rounded-xl overflow-hidden min-h-[300px]">
                    <div class="bg-slate-900 px-4 py-2 border-b border-slate-800 flex items-center justify-between">
                        <span class="text-xs font-mono font-bold text-amber-500 flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                            google-script-console.log
                        </span>
                        <button onclick="clearConsole()" class="text-xs text-slate-500 hover:text-slate-200 transition">{{ __('Limpiar') }}</button>
                    </div>
                    <div class="p-4 font-mono text-[11px] text-emerald-400 space-y-2 flex-1 overflow-y-auto" id="console-logs">
                        <p class="text-slate-500">{{ __('// Consola inicializada. Esperando triggers de eventos...') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

