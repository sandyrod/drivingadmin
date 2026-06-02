@extends('layouts.app', ['isTabbed' => true])

@section('title', 'NY Driving School - Dashboard Demo')

@section('content')
    <!-- ================= PANELS: DASHBOARD ================= -->
    <div id="view-dashboard" class="space-y-8">
        <!-- KPI CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl relative overflow-hidden group hover:border-amber-500/40 transition">
                <div class="absolute -right-3 -bottom-3 text-slate-800/40 text-7xl group-hover:scale-110 transition duration-300">
                    <i class="fa-solid fa-users"></i>
                </div>
                <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Estudiantes Activos</p>
                <h3 class="text-3xl font-extrabold mt-2 text-slate-100" id="kpi-active">0</h3>
                <p class="text-xs text-amber-500 mt-1"><i class="fa-solid fa-circle-check"></i> En formación actual</p>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl relative overflow-hidden group hover:border-red-500/40 transition">
                <div class="absolute -right-3 -bottom-3 text-slate-800/40 text-7xl group-hover:scale-110 transition duration-300">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Pendiente por Cobrar</p>
                <h3 class="text-3xl font-extrabold mt-2 text-red-500" id="kpi-debts">$0</h3>
                <p class="text-xs text-slate-400 mt-1"><i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Alumnos con deudas</p>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl relative overflow-hidden group hover:border-amber-500/40 transition">
                <div class="absolute -right-3 -bottom-3 text-slate-800/40 text-7xl group-hover:scale-110 transition duration-300">
                    <i class="fa-solid fa-ranking-star"></i>
                </div>
                <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Satisfacción General</p>
                <h3 class="text-3xl font-extrabold mt-2 text-slate-100" id="kpi-rating">4.8 / 5</h3>
                <p class="text-xs text-amber-500 mt-1">
                    <span id="kpi-stars">⭐⭐⭐⭐⭐</span>
                </p>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl relative overflow-hidden group hover:border-amber-500/40 transition">
                <div class="absolute -right-3 -bottom-3 text-slate-800/40 text-7xl group-hover:scale-110 transition duration-300">
                    <i class="fa-solid fa-car-side"></i>
                </div>
                <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Flota de Práctica</p>
                <h3 class="text-3xl font-extrabold mt-2 text-slate-100" id="kpi-fleet">0</h3>
                <p class="text-xs text-slate-400 mt-1"><i class="fa-solid fa-check text-green-500"></i> Unidades activas hoy</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- REPORT: ACTIVE STUDENTS & DEBTS -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-base font-bold text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-amber-500"></i>
                        Reporte: Estudiantes con Saldos Pendientes
                    </h4>
                    <span class="text-xs bg-red-500/10 text-red-400 px-3 py-1 rounded-full font-medium">Saldos vencidos</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-400 uppercase tracking-wider border-b border-slate-800">
                            <tr>
                                <th class="pb-3">Estudiante</th>
                                <th class="pb-3">Plan</th>
                                <th class="pb-3 text-right">Saldo Deuda</th>
                                <th class="pb-3 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="dashboard-debt-rows" class="divide-y divide-slate-800/50">
                            <!-- Dynamic rows -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- REPORT: PERFORMANCE STAR ALERT SYSTEM -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-base font-bold text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                        Alertas de Satisfacción (1-2 Estrellas)
                    </h4>
                    <span class="text-xs bg-amber-500/10 text-amber-500 px-3 py-1 rounded-full font-medium">Calificaciones Críticas</span>
                </div>
                <div class="space-y-4" id="dashboard-alert-container">
                    <!-- Dynamic alert cards -->
                </div>
            </div>
        </div>
    </div>

    <!-- ================= PANELS: REGISTRO/INSCRIPCION ================= -->
    <div id="view-inscripcion" class="hidden max-w-3xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        <div class="p-8 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-b border-slate-800">
            <h3 class="text-lg font-bold text-slate-100">Ficha de Inscripción Digital</h3>
            <p class="text-xs text-slate-400 mt-1">Crea nuevos expedientes que se sincronizarán directamente con la base de datos.</p>
        </div>

        <form id="student-form" class="p-8 space-y-6" onsubmit="handleStudentSubmit(event)">
            <!-- 1. Datos Personales -->
            <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 border-b border-slate-800 pb-2">Información del Estudiante</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Primer Nombre <span class="text-amber-500">*</span></label>
                    <input type="text" id="stu-first-name" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. Carlos">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Segundo Nombre</label>
                    <input type="text" id="stu-middle-name"
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. Andrés">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Apellido <span class="text-amber-500">*</span></label>
                    <input type="text" id="stu-last-name" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. Pérez">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Fecha de Nacimiento <span class="text-amber-500">*</span></label>
                    <input type="date" id="stu-birth-date" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Género <span class="text-amber-500">*</span></label>
                    <select id="stu-gender" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                        <option value="O">Otro</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Correo Electrónico <span class="text-amber-500">*</span></label>
                    <input type="email" id="stu-email" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="ejemplo@correo.com">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Teléfono de Contacto <span class="text-amber-500">*</span></label>
                    <input type="text" id="stu-phone" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. +1 914-555-0199">
                </div>
            </div>

            <!-- 2. Ubicación -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Dirección de Residencia <span class="text-amber-500">*</span></label>
                    <input type="text" id="stu-address" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. 123 Main St">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Ciudad <span class="text-amber-500">*</span></label>
                    <input type="text" id="stu-city" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. Queens">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Estado <span class="text-amber-500">*</span></label>
                    <input type="text" id="stu-state" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        value="NY" placeholder="Ej. NY">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Cód. Postal <span class="text-amber-500">*</span></label>
                    <input type="text" id="stu-zip-code" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. 11101">
                </div>
            </div>

            <!-- 3. Características DMV Físicas -->
            <div class="border-t border-slate-800/60 pt-6">
                <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Detalles DMV Físicos (Opcional)</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Estatura</label>
                        <input type="text" id="stu-height"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                            placeholder="Ej. 5'11&quot; o 1.80m">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Color de Ojos</label>
                        <select id="stu-eye-color"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="">Selecciona...</option>
                            <option value="BLU">Azul (BLU)</option>
                            <option value="BRN">Marrón (BRN)</option>
                            <option value="GRN">Verde (GRN)</option>
                            <option value="HAZ">Avellana (HAZ)</option>
                            <option value="GRY">Gris (GRY)</option>
                        </select>
                    </div>
                    <div class="flex items-center h-full pt-6">
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-950 px-4 py-2.5 rounded-lg border border-slate-800 hover:border-amber-500/60 w-full">
                            <input type="checkbox" id="stu-organ-donor" value="1" class="accent-amber-500">
                            <span class="text-sm">¿Donante de Órganos?</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- 4. Licencia o Permiso NY DMV -->
            <div class="border-t border-slate-800/60 pt-6">
                <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Licencia o Permiso de Aprendizaje NY DMV (Opcional)</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nº de Documento (Permit/License)</label>
                        <input type="text" id="lic-number"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                            placeholder="Ej. 123-456-789">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Fecha de Expiración</label>
                        <input type="date" id="lic-expiration"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Clase de Documento</label>
                        <select id="lic-class"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="">Selecciona clase...</option>
                            <option value="Class D">Class D (Passenger)</option>
                            <option value="Class DJ">Class DJ (Junior Operator)</option>
                            <option value="Class E">Class E (For-Hire Taxi/Limo)</option>
                            <option value="Class M">Class M (Motorcycle)</option>
                            <option value="Commercial">Commercial (CDL)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 5. Paquete y Transmisión -->
            <div class="border-t border-slate-800/60 pt-6">
                <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Servicios Contratados</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Paquetes NY Driving School <span class="text-amber-500">*</span></label>
                        <select id="stu-plan" onchange="updatePlanCost()" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="Package 1 (5 Hours + Test)" data-price="180">Package 1: 5 Hours Lessons & Road Test - $180</option>
                            <option value="Package 2 (10 Hours + Test)" data-price="350" selected>Package 2: 10 Hours Lessons & Car Rental - $350</option>
                            <option value="Package 3 (15 Hours Full Course)" data-price="500">Package 3: 15 Hours Premium - $500</option>
                            <option value="5-Hour Pre-Licensing Class Only" data-price="40">Mandatory 5-Hour Course Only - $40</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tipo de Transmisión <span class="text-amber-500">*</span></label>
                        <div class="flex gap-4 mt-1">
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-950 px-4 py-2.5 rounded-lg border border-slate-800 hover:border-amber-500/60 flex-1">
                                <input type="radio" name="stu-transmission" value="Automático" checked class="accent-amber-500">
                                <span class="text-sm">Automático</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-950 px-4 py-2.5 rounded-lg border border-slate-800 hover:border-amber-500/60 flex-1">
                                <input type="radio" name="stu-transmission" value="Mecánico" class="accent-amber-500">
                                <span class="text-sm">Mecánico (Stick Shift)</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. Costos y Abonos -->
            <div class="border-t border-slate-800/60 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Costo Total del Plan ($)</label>
                        <input type="number" id="stu-cost" readonly
                            class="w-full bg-slate-900 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-500 cursor-not-allowed text-sm font-bold"
                            value="350">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Pago Inicial (Abono / Total)</label>
                        <input type="number" id="stu-paid" oninput="calculateInscripcionDebt()" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm font-bold"
                            placeholder="Ej. 200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Saldo Pendiente (Deuda)</label>
                        <input type="number" id="stu-debt" readonly
                            class="w-full bg-slate-900 border border-slate-800 rounded-lg px-4 py-2.5 text-red-500 cursor-not-allowed text-sm font-bold"
                            value="0">
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-800/60 pt-6 flex justify-end gap-3">
                <button type="reset" class="px-5 py-2.5 rounded-lg text-sm font-medium border border-slate-800 hover:bg-slate-800 transition">Reiniciar Formulario</button>
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> Registrar Alumno
                </button>
            </div>
        </form>
    </div>

    <!-- ================= PANELS: ENCUESTA POST-SERVICIO ================= -->
    <div id="view-encuesta" class="hidden max-w-xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        <div class="p-8 bg-gradient-to-r from-yellow-500/10 via-amber-500/5 to-transparent border-b border-slate-800 text-center">
            <div class="w-12 h-12 bg-amber-500 text-slate-950 rounded-full flex items-center justify-center mx-auto text-xl mb-3 font-extrabold">★</div>
            <h3 class="text-lg font-bold text-slate-100">Evaluación de Calidad del Servicio</h3>
            <p class="text-xs text-slate-400 mt-1">Queremos conocer tu opinión. Califícanos utilizando estrellas.</p>
        </div>

        <form id="survey-form" class="p-8 space-y-6" onsubmit="handleSurveySubmit(event)">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">1. Selecciona a tu Instructor de Práctica <span class="text-amber-500">*</span></label>
                <select id="srv-instructor" required
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                    <option value="">Selecciona un profesional...</option>
                    <!-- Dynamically loaded -->
                </select>
            </div>

            <!-- STAR RATING ATENCION -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">2. Atención y Paciencia del Instructor <span class="text-amber-500">*</span></label>
                <p class="text-xs text-slate-500 mb-3">(1 Estrella es Deficiente | 5 Estrellas es Excelente)</p>
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
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">3. Estado de Limpieza y Funcionamiento del Vehículo <span class="text-amber-500">*</span></label>
                <p class="text-xs text-slate-500 mb-3">(1 Estrella es Deficiente | 5 Estrellas es Excelente)</p>
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
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">4. ¿Tienes alguna queja, observación o sugerencia?</label>
                <textarea id="srv-comment"
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                    rows="3" placeholder="Por favor detalla cualquier observación aquí..."></textarea>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-3 rounded-lg text-sm transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Enviar Evaluación
                </button>
            </div>
        </form>
    </div>

    <!-- ================= PANELS: SIMULADOR GOOGLE SHEETS ================= -->
    <div id="view-sheets-db" class="hidden space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-900 p-4 border border-slate-800 rounded-2xl">
            <div class="flex items-center gap-3">
                <div class="bg-emerald-600 text-white p-2 rounded-lg text-lg flex items-center justify-center font-bold">
                    <i class="fa-solid fa-file-excel"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">Libro: Sistema_Academia_Manejo</h3>
                    <p class="text-xs text-slate-400">Pestañas relacionales actualizadas dinámicamente</p>
                </div>
            </div>
            <!-- SHEET TABS -->
            <div class="flex flex-wrap gap-1 bg-slate-950 p-1.5 rounded-lg border border-slate-800">
                <button onclick="switchSheetTab('students')" id="sheet-btn-students"
                    class="sheet-tab px-3 py-1.5 rounded text-xs font-bold transition bg-slate-800 text-slate-100">📁 Estudiantes</button>
                <button onclick="switchSheetTab('instructors')" id="sheet-btn-instructors"
                    class="sheet-tab px-3 py-1.5 rounded text-xs font-bold transition text-slate-400 hover:text-slate-100">📁 Instructores</button>
                <button onclick="switchSheetTab('vehicles')" id="sheet-btn-vehicles"
                    class="sheet-tab px-3 py-1.5 rounded text-xs font-bold transition text-slate-400 hover:text-slate-100">📁 Vehículos</button>
                <button onclick="switchSheetTab('reviews')" id="sheet-btn-reviews"
                    class="sheet-tab px-3 py-1.5 rounded text-xs font-bold transition text-slate-400 hover:text-slate-100">📁 Encuestas Recibidas</button>
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
                    <h3 class="font-bold text-base">Automatizaciones con Google Apps Script (Trigger Engine)</h3>
                    <p class="text-xs text-slate-400">Cómo funciona el Backend automatizado en Google Sheets sin servidores externos.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-amber-500 uppercase tracking-wider">Flujos del Sistema:</h4>

                    <div class="bg-slate-950 p-4 border border-slate-800 rounded-xl flex items-start gap-3">
                        <div class="bg-amber-500/10 text-amber-500 p-2 rounded-lg mt-0.5"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                        <div class="space-y-1">
                            <h5 class="text-xs font-bold text-slate-200">Emisión de Factura Automatizada</h5>
                            <p class="text-xs text-slate-400">Al registrar un estudiante nuevo, un script duplica una plantilla de Google Docs, inserta los datos dinámicos, la exporta a PDF en Google Drive y la envía al correo.</p>
                            <button onclick="triggerInvoiceSimulation()"
                                class="mt-2 bg-amber-500/10 text-amber-500 border border-amber-500/20 hover:bg-amber-500 hover:text-slate-950 font-bold px-3 py-1 rounded text-xs transition">
                                Simular Trigger Factura
                            </button>
                        </div>
                    </div>

                    <div class="bg-slate-950 p-4 border border-slate-800 rounded-xl flex items-start gap-3">
                        <div class="bg-emerald-500/10 text-emerald-500 p-2 rounded-lg mt-0.5"><i class="fa-brands fa-whatsapp"></i></div>
                        <div class="space-y-1">
                            <h5 class="text-xs font-bold text-slate-200">Notificación de Bienvenida por Whatsapp</h5>
                            <p class="text-xs text-slate-400">Cuando un nuevo registro de Google Forms ingresa, envía un aviso con la información del plan contratado y el link de Google Classroom de teoría.</p>
                            <button onclick="triggerWelcomeSimulation()"
                                class="mt-2 bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 hover:bg-emerald-500 hover:text-slate-950 font-bold px-3 py-1 rounded text-xs transition">
                                Simular Notificación
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
                        <button onclick="clearConsole()" class="text-xs text-slate-500 hover:text-slate-200 transition">Limpiar</button>
                    </div>
                    <div class="p-4 font-mono text-[11px] text-emerald-400 space-y-2 flex-1 overflow-y-auto" id="console-logs">
                        <p class="text-slate-500">// Consola inicializada. Esperando triggers de eventos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // MOCKUP DATABASES (POPULATED WITH REAL DB DATA FROM CONTROLLER)
        let students = @json($students);

        let instructors = [
            { id: 'INS-01', name: 'Luis Rodríguez', license: 'NYS DMV Certified', phone: '+1 914-555-7788', vehicle: 'Toyota Corolla', plate: 'AB123CD', transmission: 'Automático', shift: 'Mañana (8am-12pm)', status: 'Disponible' },
            { id: 'INS-02', name: 'Pedro Fuentes', license: 'NYS DMV Certified', phone: '+1 914-555-9900', vehicle: 'Chevrolet Aveo', plate: 'EF456GH', transmission: 'Mecánico', shift: 'Tarde (1pm-5pm)', status: 'En Clase' },
            { id: 'INS-03', name: 'Sarah Evans', license: 'NYS DMV Senior Certified', phone: '+1 718-555-1122', vehicle: 'Nissan Sentra', plate: 'JK789LM', transmission: 'Automático', shift: 'Mañana (8am-12pm)', status: 'Disponible' }
        ];

        let vehicles = [
            { id: 'VEH-01', brand: 'Toyota Corolla (2022)', plate: 'AB123CD', transmission: 'Automático', status: 'Disponible' },
            { id: 'VEH-02', brand: 'Chevrolet Aveo (2021)', plate: 'EF456GH', transmission: 'Mecánico', status: 'En Clase' },
            { id: 'VEH-03', brand: 'Nissan Sentra (2023)', plate: 'JK789LM', transmission: 'Automático', status: 'Disponible' }
        ];

        let reviews = [
            { id: 'REV-001', instructor: 'Luis Rodríguez', rating: 5, vehicleRating: 5, comment: 'Excelente paciencia al enseñar, muy profesional en todo.' },
            { id: 'REV-002', instructor: 'Pedro Fuentes', rating: 2, vehicleRating: 3, comment: 'El aire acondicionado del carro no enfriaba bien durante la clase práctica.' },
            { id: 'REV-003', instructor: 'Sarah Evans', rating: 5, vehicleRating: 5, comment: 'La mejor instructora de Nueva York. Aprobé el examen de ruta en mi primer intento.' }
        ];

        // Active Sheet Tab indicator
        let activeSheetTab = 'students';

        function switchTab(tabId) {
            // Hide all views
            document.getElementById('view-dashboard').classList.add('hidden');
            document.getElementById('view-inscripcion').classList.add('hidden');
            document.getElementById('view-encuesta').classList.add('hidden');
            document.getElementById('view-sheets-db').classList.add('hidden');
            document.getElementById('view-apps-script').classList.add('hidden');

            // Show active
            document.getElementById(`view-${tabId}`).classList.remove('hidden');

            // Update Titles
            const sectionPre = document.getElementById('section-pre-title');
            const sectionTitle = document.getElementById('section-title');

            if (tabId === 'dashboard') {
                sectionPre.innerText = "Gestión de operaciones";
                sectionTitle.innerText = "Dashboard Principal";
            } else if (tabId === 'inscripcion') {
                sectionPre.innerText = "Inscripciones";
                sectionTitle.innerText = "Registrar Estudiante";
            } else if (tabId === 'encuesta') {
                sectionPre.innerText = "Post-Venta / Feedback";
                sectionTitle.innerText = "Encuesta de Servicio";
            } else if (tabId === 'sheets-db') {
                sectionPre.innerText = "Base de datos en la nube";
                sectionTitle.innerText = "Visor de Google Sheets";
            } else if (tabId === 'apps-script') {
                sectionPre.innerText = "Motor de flujos";
                sectionTitle.innerText = "Google Apps Script Console";
            }

            // Styling Buttons
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.className = "nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-slate-100 transition";
            });

            const activeBtn = document.getElementById(`btn-${tabId}`);
            if (activeBtn) {
                activeBtn.className = "nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition bg-slate-800 text-amber-500 border-l-4 border-amber-500";
            }
        }

        function updateDashboardMetrics() {
            // Estudiantes Activos
            const activeCount = students.filter(s => s.status === 'Activo' || s.status === 'Al Día').length;
            document.getElementById('kpi-active').innerText = activeCount;

            // Deudas totales
            const totalDebts = students.reduce((acc, current) => acc + current.debt, 0);
            document.getElementById('kpi-debts').innerText = `$${totalDebts} USD`;

            // Flota de práctica activa
            document.getElementById('kpi-fleet').innerText = `${vehicles.length} Vehículos`;

            // Satisfacción Promedio de estrellas
            if (reviews.length > 0) {
                const avg = reviews.reduce((acc, curr) => acc + curr.rating, 0) / reviews.length;
                document.getElementById('kpi-rating').innerText = `${avg.toFixed(1)} / 5`;

                // Generar estrellas visuales
                let starStr = '';
                const rounded = Math.round(avg);
                for (let i = 0; i < 5; i++) {
                    starStr += i < rounded ? '★' : '☆';
                }
                document.getElementById('kpi-stars').innerText = starStr;
            } else {
                document.getElementById('kpi-rating').innerText = "0 / 5";
                document.getElementById('kpi-stars').innerText = "☆☆☆☆☆";
            }

            // Rellenar tabla de deudas del Dashboard
            const debtContainer = document.getElementById('dashboard-debt-rows');
            debtContainer.innerHTML = '';

            const debtors = students.filter(s => s.debt > 0);
            if (debtors.length === 0) {
                debtContainer.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-xs text-slate-500">No hay estudiantes con saldos deudores</td></tr>`;
            } else {
                debtors.forEach(s => {
                    const tr = document.createElement('tr');
                    tr.className = "border-b border-slate-800/40 hover:bg-slate-800/20 transition duration-150";
                    tr.innerHTML = `
                        <td class="py-3.5 font-semibold text-slate-200">${s.name}</td>
                        <td class="py-3.5 text-xs text-slate-400">${s.plan}</td>
                        <td class="py-3.5 text-right font-bold text-red-500">$${s.debt}</td>
                        <td class="py-3.5 text-center">
                            <button onclick="simulateCobro('${s.id}')" class="bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 px-2.5 py-1 rounded text-xs font-bold transition">Abonar</button>
                        </td>
                    `;
                    debtContainer.appendChild(tr);
                });
            }

            // Rellenar Alertas de Satisfacción (1-2 Estrellas)
            const alertContainer = document.getElementById('dashboard-alert-container');
            alertContainer.innerHTML = '';

            const badReviews = reviews.filter(r => r.rating <= 2);
            if (badReviews.length === 0) {
                alertContainer.innerHTML = `
                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 text-center py-6">
                        <i class="fa-regular fa-face-smile text-amber-500 text-2xl mb-2"></i>
                        <p class="text-xs text-slate-400">¡Excelente! No hay calificaciones críticas registradas recientemente.</p>
                    </div>
                `;
            } else {
                badReviews.forEach(r => {
                    const card = document.createElement('div');
                    card.className = "bg-red-500/10 border border-red-500/20 p-4 rounded-xl space-y-2 relative";
                    card.innerHTML = `
                        <div class="flex items-center justify-between">
                            <h5 class="text-xs font-bold text-slate-200">Instructor: ${r.instructor}</h5>
                            <span class="text-xs text-red-400 font-bold">${'★'.repeat(r.rating)}${'☆'.repeat(5 - r.rating)}</span>
                        </div>
                        <p class="text-xs text-slate-300 italic">"${r.comment}"</p>
                        <div class="flex justify-end gap-2 pt-1">
                            <button onclick="simulateCallInstructor('${r.instructor}')" class="bg-slate-950 hover:bg-slate-900 border border-slate-800 text-[10px] text-slate-300 px-2 py-1 rounded font-bold transition">Contactar Instructor</button>
                        </div>
                    `;
                    alertContainer.appendChild(card);
                });
            }
        }

        function simulateCobro(studentId) {
            const student = students.find(s => s.id === studentId);
            if (student) {
                const amount = prompt(`¿Cuánto va a abonar ${student.name}? (Deuda actual: $${student.debt}):`, student.debt);
                if (amount === null) return; // Cancelled

                const parsed = parseFloat(amount);
                if (isNaN(parsed) || parsed <= 0 || parsed > student.debt) {
                    showToast("Por favor introduce un monto de abono válido.", "warning");
                    return;
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/students/${student.db_id}/payment`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ amount: parsed })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        student.paid = res.paid;
                        student.debt = res.debt;
                        student.status = res.status;

                        showToast(`Se registró abono de $${parsed} USD en la BD para ${student.name}`, "success");

                        // Log Apps Script console
                        const log = document.getElementById('console-logs');
                        log.innerHTML += `<p class="text-amber-500">[DB_TRIGGER] Cobro registrado en BD: ${student.name} pagó $${parsed}. Deuda restante: $${student.debt}</p>`;

                        updateDashboardMetrics();
                        renderSheetTable();
                    } else {
                        showToast("Error al registrar pago en BD: " + (res.message || ""), "warning");
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("Error al realizar abono en el servidor.", "warning");
                });
            }
        }

        function simulateCallInstructor(name) {
            showToast(`Contactando al instructor ${name} vía WhatsApp interno para feedback del vehículo...`, "info");
        }

        function updatePlanCost() {
            const planSelector = document.getElementById('stu-plan');
            const selectedOption = planSelector.options[planSelector.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-price'));

            document.getElementById('stu-cost').value = price;
            calculateInscripcionDebt();
        }

        function calculateInscripcionDebt() {
            const cost = parseFloat(document.getElementById('stu-cost').value) || 0;
            const paid = parseFloat(document.getElementById('stu-paid').value) || 0;
            let debt = cost - paid;

            if (debt < 0) debt = 0;
            document.getElementById('stu-debt').value = debt;
        }

        function handleStudentSubmit(e) {
            e.preventDefault();

            // Collect variables
            const first_name = document.getElementById('stu-first-name').value;
            const middle_name = document.getElementById('stu-middle-name').value;
            const last_name = document.getElementById('stu-last-name').value;
            const birth_date = document.getElementById('stu-birth-date').value;
            const gender = document.getElementById('stu-gender').value;
            const email = document.getElementById('stu-email').value;
            const phone = document.getElementById('stu-phone').value;
            const address = document.getElementById('stu-address').value;
            const city = document.getElementById('stu-city').value;
            const state = document.getElementById('stu-state').value;
            const zip_code = document.getElementById('stu-zip-code').value;
            
            // Optional DMV physical specs
            const height = document.getElementById('stu-height').value;
            const eye_color = document.getElementById('stu-eye-color').value;
            const is_organ_donor = document.getElementById('stu-organ-donor').checked ? 1 : 0;

            // Optional License permit info
            const license_number = document.getElementById('lic-number').value;
            const expiration_date = document.getElementById('lic-expiration').value;
            const license_class = document.getElementById('lic-class').value;

            // Plan details
            const plan = document.getElementById('stu-plan').value;
            const cost = parseFloat(document.getElementById('stu-cost').value);
            const paid = parseFloat(document.getElementById('stu-paid').value);
            const debt = parseFloat(document.getElementById('stu-debt').value);
            const trans = document.querySelector('input[name="stu-transmission"]:checked').value;

            const data = {
                first_name,
                middle_name,
                last_name,
                birth_date,
                gender,
                email,
                phone,
                address,
                city,
                state,
                zip_code,
                height,
                eye_color,
                is_organ_donor,
                license_number,
                expiration_date,
                license_class,
                package: plan,
                transmission: trans,
                plan_cost: cost,
                paid_amount: paid,
                debt_amount: debt
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Send AJAX Request
            fetch('/students', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const student = res.student;
                    students.push(student);
                    
                    showToast(`¡Estudiante ${student.name} registrado con éxito en la Base de Datos!`, "success");

                    // Logs Simulator Console
                    const consoleLogs = document.getElementById('console-logs');
                    consoleLogs.innerHTML += `
                        <p class="text-green-400">[DB_SUCCESS] Event: FormSubmit (Inscripción de Alumnos)</p>
                        <p class="text-slate-300">↳ Inserted DB Record ID: ${student.db_id} [Student ID: ${student.id}]</p>
                        <p class="text-cyan-400">↳ Triggering 'generarFacturaPDF()' para ${student.name}...</p>
                        <p class="text-slate-400">↳ File 'Factura_${student.id}_${student.name.replace(/ /g, "_")}.pdf' creado en Drive/Facturas</p>
                        <p class="text-emerald-400">↳ Notificación automatizada enviada a ${student.phone}</p>
                        <p class="text-slate-500">----------------------------------------------------</p>
                    `;

                    // Reset Form and update calculations
                    document.getElementById('student-form').reset();
                    updatePlanCost();
                    updateDashboardMetrics();
                    renderSheetTable();

                    // Switch to sheets-db automatically to show dynamic insertion
                    setTimeout(() => {
                        switchTab('sheets-db');
                        switchSheetTab('students');
                        showToast("Los datos se guardaron en la base de datos MySQL y se sincronizaron con Google Sheets", "info");
                    }, 1000);
                } else {
                    showToast("Error de validación: " + (res.message || "Campos incompletos."), "warning");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Error de red o de servidor al registrar estudiante.", "warning");
            });
        }

        function setStarRating(id, rating) {
            document.getElementById(id).value = rating;

            // Select classes based on ID
            const buttons = document.querySelectorAll(`.star-btn-${id}`);
            buttons.forEach((btn, index) => {
                if (index < rating) {
                    btn.className = `star-btn-${id} text-3xl text-amber-500 hover:scale-110 transition`;
                } else {
                    btn.className = `star-btn-${id} text-3xl text-slate-700 hover:scale-110 transition`;
                }
            });
        }

        function populateInstructorDropdown() {
            const srvDropdown = document.getElementById('srv-instructor');
            if (srvDropdown) {
                srvDropdown.innerHTML = '<option value="">Selecciona un profesional...</option>';
                instructors.forEach(ins => {
                    const opt = document.createElement('option');
                    opt.value = ins.name;
                    opt.innerText = ins.name;
                    srvDropdown.appendChild(opt);
                });
            }
        }

        function handleSurveySubmit(e) {
            e.preventDefault();

            const instructor = document.getElementById('srv-instructor').value;
            const rating = parseInt(document.getElementById('srv-rating').value);
            const vehRating = parseInt(document.getElementById('srv-vehicle-rating').value);
            const comment = document.getElementById('srv-comment').value;

            if (rating === 0 || vehRating === 0) {
                showToast("Por favor califica ambas opciones con estrellas antes de enviar.", "warning");
                return;
            }

            const newRevId = `REV-00${reviews.length + 1}`;
            reviews.push({
                id: newRevId,
                instructor: instructor,
                rating: rating,
                vehicleRating: vehRating,
                comment: comment || 'Sin comentarios adicionales'
            });

            showToast("¡Muchas gracias por evaluar nuestro servicio!", "success");

            // Logs Simulator
            const consoleLogs = document.getElementById('console-logs');
            consoleLogs.innerHTML += `
                <p class="text-green-400">[INFO] Event: FormSubmit (Encuesta Calidad)</p>
                <p class="text-slate-300">↳ Row added in 'Encuestas' Tab. Calificación: ${rating}/5</p>
                ${rating <= 2 ? `<p class="text-red-500">↳ ALERT [CRITICAL RATING]: Correo de alerta de calidad enviado a Gerencia. Instructor implicado: ${instructor}</p>` : ''}
                <p class="text-slate-500">----------------------------------------------------</p>
            `;

            // Reset
            document.getElementById('survey-form').reset();
            setStarRating('srv-rating', 0);
            setStarRating('srv-vehicle-rating', 0);
            updateDashboardMetrics();
            renderSheetTable();

            // Auto navigate to see results
            setTimeout(() => {
                switchTab('dashboard');
                if (rating <= 2) {
                    showToast("Atención: Alerta roja de satisfacción generada en tu Dashboard.", "warning");
                }
            }, 1000);
        }

        function switchSheetTab(tabKey) {
            activeSheetTab = tabKey;

            document.querySelectorAll('.sheet-tab').forEach(btn => {
                btn.className = "sheet-tab px-3 py-1.5 rounded text-xs font-bold transition text-slate-400 hover:text-slate-100";
            });

            document.getElementById(`sheet-btn-${tabKey}`).className = "sheet-tab px-3 py-1.5 rounded text-xs font-bold transition bg-slate-800 text-slate-100";

            renderSheetTable();
        }

        function renderSheetTable() {
            const tableHead = document.getElementById('sheet-table-head');
            const tableBody = document.getElementById('sheet-table-body');

            tableHead.innerHTML = '';
            tableBody.innerHTML = '';

            if (activeSheetTab === 'students') {
                tableHead.innerHTML = `
                    <tr>
                        <th class="px-4 py-3 border-r border-slate-800">A - ID</th>
                        <th class="px-4 py-3 border-r border-slate-800">B - Fecha Reg</th>
                        <th class="px-4 py-3 border-r border-slate-800">C - Estudiante</th>
                        <th class="px-4 py-3 border-r border-slate-800">D - Teléfono</th>
                        <th class="px-4 py-3 border-r border-slate-800">E - Plan</th>
                        <th class="px-4 py-3 border-r border-slate-800">F - Costo</th>
                        <th class="px-4 py-3 border-r border-slate-800">G - Abonado</th>
                        <th class="px-4 py-3 border-r border-slate-800">H - Deuda</th>
                        <th class="px-4 py-3 border-r border-slate-800">I - Transmisión</th>
                        <th class="px-4 py-3 border-r border-slate-800">J - Estatus</th>
                        <th class="px-4 py-3">Acción</th>
                    </tr>
                `;
                students.forEach(s => {
                    const row = document.createElement('tr');
                    row.className = "border-b border-slate-800/20 hover:bg-slate-800/30";
                    row.innerHTML = `
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-amber-500 font-bold">${s.id}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400">${s.date}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-100 font-semibold">${s.name}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400">${s.phone}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400">${s.plan}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400 font-bold">$${s.cost}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400">$${s.paid}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 font-bold ${s.debt > 0 ? 'text-red-500' : 'text-green-500'}">$${s.debt}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400">${s.transmission}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${s.status === 'Al Día' ? 'bg-green-500/10 text-green-400' : 'bg-amber-500/10 text-amber-500'}">${s.status}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <button onclick="deleteStudent('${s.id}')" class="bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });

            } else if (activeSheetTab === 'instructors') {
                tableHead.innerHTML = `
                    <tr>
                        <th class="px-4 py-3 border-r border-slate-800">A - ID</th>
                        <th class="px-4 py-3 border-r border-slate-800">B - Nombre Instructor</th>
                        <th class="px-4 py-3 border-r border-slate-800">C - Licencia Certificado</th>
                        <th class="px-4 py-3 border-r border-slate-800">D - Teléfono</th>
                        <th class="px-4 py-3 border-r border-slate-800">E - Auto Asignado</th>
                        <th class="px-4 py-3 border-r border-slate-800">F - Placa</th>
                        <th class="px-4 py-3 border-r border-slate-800">G - Turno Horario</th>
                        <th class="px-4 py-3">H - Estatus</th>
                    </tr>
                `;
                instructors.forEach(ins => {
                    const row = document.createElement('tr');
                    row.className = "border-b border-slate-800/20 hover:bg-slate-800/30";
                    row.innerHTML = `
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-amber-500 font-bold">${ins.id}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-100 font-semibold">${ins.name}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400">${ins.license}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400">${ins.phone}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400">${ins.vehicle}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400 font-bold">${ins.plate}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400">${ins.shift}</td>
                        <td class="px-4 py-2.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${ins.status === 'Disponible' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-500'}">${ins.status}</span>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });

            } else if (activeSheetTab === 'vehicles') {
                tableHead.innerHTML = `
                    <tr>
                        <th class="px-4 py-3 border-r border-slate-800">A - ID</th>
                        <th class="px-4 py-3 border-r border-slate-800">B - Marca / Modelo</th>
                        <th class="px-4 py-3 border-r border-slate-800">C - Placa Patente</th>
                        <th class="px-4 py-3 border-r border-slate-800">D - Transmisión</th>
                        <th class="px-4 py-3">E - Estatus Operación</th>
                    </tr>
                `;
                vehicles.forEach(v => {
                    const row = document.createElement('tr');
                    row.className = "border-b border-slate-800/20 hover:bg-slate-800/30";
                    row.innerHTML = `
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-amber-500 font-bold">${v.id}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-100 font-semibold">${v.brand}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400 font-bold">${v.plate}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-400">${v.transmission}</td>
                        <td class="px-4 py-2.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${v.status === 'Disponible' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-500'}">${v.status}</span>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });

            } else if (activeSheetTab === 'reviews') {
                tableHead.innerHTML = `
                    <tr>
                        <th class="px-4 py-3 border-r border-slate-800">A - ID</th>
                        <th class="px-4 py-3 border-r border-slate-800">B - Instructor Evaluado</th>
                        <th class="px-4 py-3 border-r border-slate-800">C - Calificación Trato</th>
                        <th class="px-4 py-3 border-r border-slate-800">D - Calificación Auto</th>
                        <th class="px-4 py-3">E - Comentarios Alumno</th>
                    </tr>
                `;
                reviews.forEach(r => {
                    const row = document.createElement('tr');
                    row.className = "border-b border-slate-800/20 hover:bg-slate-800/30";
                    row.innerHTML = `
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-amber-500 font-bold">${r.id}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-slate-100 font-semibold">${r.instructor}</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-amber-400 font-bold">${'★'.repeat(r.rating)} (${r.rating}/5)</td>
                        <td class="px-4 py-2.5 border-r border-slate-800/40 text-amber-400 font-bold">${'★'.repeat(r.vehicleRating)} (${r.vehicleRating}/5)</td>
                        <td class="px-4 py-2.5 text-slate-300 italic truncate max-w-xs">${r.comment}</td>
                    `;
                    tableBody.appendChild(row);
                });
            }
        }

        function deleteStudent(studentId) {
            const student = students.find(s => s.id === studentId);
            if (student) {
                if (!confirm(`¿Estás seguro de que deseas eliminar permanentemente a ${student.name} de la base de datos?`)) return;

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/students/${student.db_id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        students = students.filter(s => s.id !== studentId);
                        showToast(`Estudiante ${student.name} eliminado de la base de datos (Soft Deleted).`, "success");

                        const log = document.getElementById('console-logs');
                        log.innerHTML += `<p class="text-red-500">[DB_TRIGGER] Estudiante eliminado en BD ID: ${student.db_id} [Student ID: ${studentId}]</p>`;

                        updateDashboardMetrics();
                        renderSheetTable();
                    } else {
                        showToast("Error al eliminar de la BD: " + (res.message || ""), "warning");
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("Error al eliminar estudiante en el servidor.", "warning");
                });
            }
        }

        function triggerInvoiceSimulation() {
            showToast("Ejecutando Google Apps Script: 'generarFacturaPDF()'...", "info");

            const log = document.getElementById('console-logs');
            log.innerHTML += `
                <p class="text-amber-500 mt-2">[RUNNING] function generarFacturaPDF()</p>
                <p class="text-slate-400">↳ Leyendo última fila registrada de 'Estudiantes'...</p>
                <p class="text-slate-400">↳ Tomando plantilla de Google Docs ID: '1Gf9hU-tempDoc'</p>
                <p class="text-slate-400">↳ Reemplazando marcadores {ID_EST}, {NAME}, {PLAN_COST}...</p>
                <p class="text-slate-400">↳ Renderizando archivo PDF de Factura...</p>
                <p class="text-green-400">↳ [SUCCESS] PDF generado en Drive: 'GoogleDrive/Academia/Facturas/Factura_Simulada.pdf'</p>
                <p class="text-emerald-400">↳ [API] Email enviado al estudiante con documento adjunto.</p>
                <p class="text-slate-500">----------------------------------------------------</p>
            `;

            setTimeout(() => {
                showToast("Plantilla duplicada y Factura en PDF guardada en Google Drive.", "success");
            }, 1500);
        }

        function triggerWelcomeSimulation() {
            showToast("Ejecutando Google Apps Script: 'enviarMensajeTeoria()'...", "info");

            const log = document.getElementById('console-logs');
            log.innerHTML += `
                <p class="text-amber-500 mt-2">[RUNNING] function enviarMensajeTeoria()</p>
                <p class="text-slate-400">↳ Buscando teléfonos activos en 'Estudiantes'...</p>
                <p class="text-slate-400">↳ Generando enlace de acceso a Google Classroom de Teoría y Google Meet...</p>
                <p class="text-slate-400">↳ WhatsApp API Trigger: enviando plantilla de bienvenida...</p>
                <p class="text-green-400">↳ [SUCCESS] Notificación de bienvenida y recursos teóricos enviados.</p>
                <p class="text-slate-500">----------------------------------------------------</p>
            `;

            setTimeout(() => {
                showToast("WhatsApp enviado automáticamente con acceso a Google Classroom.", "success");
            }, 1500);
        }

        function clearConsole() {
            document.getElementById('console-logs').innerHTML = '<p class="text-slate-500">// Consola limpia. Esperando nuevos triggers de eventos...</p>';
        }

        window.onload = function () {
            updateDashboardMetrics();
            populateInstructorDropdown();
            renderSheetTable();
        };
    </script>
@endpush