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
            <h3 class="text-lg font-bold text-slate-100" id="student-form-title">Ficha de Inscripción Digital</h3>
            <p class="text-xs text-slate-400 mt-1" id="student-form-subtitle">Crea nuevos expedientes que se sincronizarán directamente con la base de datos.</p>
        </div>

        <form id="student-form" class="p-8 space-y-6" onsubmit="handleStudentSubmit(event)">
            <!-- 1. Datos Personales -->
            <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 border-b border-slate-800 pb-2">Información del Estudiante</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">N° de Contrato</label>
                    <input type="text" id="stu-contract-number" maxlength="30"
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. 8293">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Apellido <span class="text-amber-500">*</span></label>
                    <input type="text" id="stu-last-name" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. Pérez">
                </div>
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
                <div class="mt-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Foto de Perfil (Opcional)</label>
                    <div class="relative group border-2 border-dashed border-slate-800 hover:border-amber-500/60 bg-slate-950 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer min-h-[140px] transition text-center" id="stu-photo-zone" onclick="document.getElementById('stu-photo-input').click()">
                        <input type="file" id="stu-photo-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'stu-photo-preview', 'stu-photo-zone', 'stu-photo-base64')">
                        <input type="hidden" id="stu-photo-base64" value="">
                        
                        <!-- Placeholder State -->
                        <div class="upload-placeholder flex flex-col items-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                            <span class="text-xs text-slate-400 font-medium">Subir Foto de Perfil</span>
                            <span class="text-[10px] text-slate-600">JPG, PNG o WEBP (Máx. 4MB)</span>
                        </div>
                        
                        <!-- Preview State -->
                        <img id="stu-photo-preview" src="" class="hidden max-h-[120px] rounded-lg object-contain shadow-md" alt="Vista previa">
                        
                        <!-- Remove Button -->
                        <button type="button" onclick="clearUpload(event, 'stu-photo-input', 'stu-photo-preview', 'stu-photo-zone', 'stu-photo-base64')" 
                            class="absolute top-2 right-2 bg-red-500/20 hover:bg-red-500 text-red-400 hover:text-slate-950 p-1.5 rounded-lg text-xs transition z-10 hidden" id="stu-photo-remove">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Foto de la Licencia (Anverso)</label>
                        <div class="relative group border-2 border-dashed border-slate-800 hover:border-amber-500/60 bg-slate-950 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer min-h-[140px] transition duration-200 text-center" id="lic-front-zone" onclick="document.getElementById('lic-front-input').click()">
                            <input type="file" id="lic-front-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'lic-front-preview', 'lic-front-zone', 'lic-front-base64')">
                            <input type="hidden" id="lic-front-base64" value="">
                            
                            <!-- Placeholder -->
                            <div class="upload-placeholder flex flex-col items-center gap-2">
                                <i class="fa-solid fa-id-card text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                                <span class="text-xs text-slate-400 font-medium">Subir Licencia (Anverso)</span>
                                <span class="text-[10px] text-slate-600">JPG, PNG o WEBP (Máx. 4MB)</span>
                            </div>
                            
                            <!-- Preview -->
                            <img id="lic-front-preview" src="" class="hidden max-h-[120px] rounded-lg object-contain shadow-md" alt="Licencia Anverso">
                            
                            <!-- Remove Button -->
                            <button type="button" onclick="clearUpload(event, 'lic-front-input', 'lic-front-preview', 'lic-front-zone', 'lic-front-base64')" 
                                class="absolute top-2 right-2 bg-red-500/20 hover:bg-red-500 text-red-400 hover:text-slate-950 p-1.5 rounded-lg text-xs transition z-10 hidden" id="lic-front-remove">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Foto de la Licencia (Reverso)</label>
                        <div class="relative group border-2 border-dashed border-slate-800 hover:border-amber-500/60 bg-slate-950 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer min-h-[140px] transition duration-200 text-center" id="lic-back-zone" onclick="document.getElementById('lic-back-input').click()">
                            <input type="file" id="lic-back-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'lic-back-preview', 'lic-back-zone', 'lic-back-base64')">
                            <input type="hidden" id="lic-back-base64" value="">
                            
                            <!-- Placeholder -->
                            <div class="upload-placeholder flex flex-col items-center gap-2">
                                <i class="fa-solid fa-id-card text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                                <span class="text-xs text-slate-400 font-medium">Subir Licencia (Reverso)</span>
                                <span class="text-[10px] text-slate-600">JPG, PNG o WEBP (Máx. 4MB)</span>
                            </div>
                            
                            <!-- Preview -->
                            <img id="lic-back-preview" src="" class="hidden max-h-[120px] rounded-lg object-contain shadow-md" alt="Licencia Reverso">
                            
                            <!-- Remove Button -->
                            <button type="button" onclick="clearUpload(event, 'lic-back-input', 'lic-back-preview', 'lic-back-zone', 'lic-back-base64')" 
                                class="absolute top-2 right-2 bg-red-500/20 hover:bg-red-500 text-red-400 hover:text-slate-950 p-1.5 rounded-lg text-xs transition z-10 hidden" id="lic-back-remove">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
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
                <button type="submit" id="student-submit-btn" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-save" id="student-submit-btn-icon"></i> <span id="student-submit-btn-text">Registrar Alumno</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ================= PANELS: REGISTRO DE ABONOS ================= -->
    <div id="view-abonos" class="hidden max-w-4xl mx-auto space-y-8">
        <!-- REGISTRO DE ABONO FORM -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
            <div class="p-8 bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-transparent border-b border-slate-800">
                <h3 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar text-emerald-500"></i> Registrar Abono Estudiante
                </h3>
                <p class="text-xs text-slate-400 mt-1">Registra un nuevo cobro, asigna el método de pago y actualiza el saldo del alumno.</p>
            </div>

            <form id="payment-form" class="p-8 space-y-6" onsubmit="handlePaymentSubmit(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Seleccionar Estudiante <span class="text-amber-500">*</span></label>
                        <select id="pay-student-id" required onchange="handlePaymentStudentChange()"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="">Seleccione un alumno con deuda...</option>
                            <!-- Dynamically loaded -->
                        </select>
                        <p id="pay-student-debt-info" class="text-xs text-slate-400 mt-2 hidden">
                            Deuda actual: <span class="font-bold text-red-500" id="pay-debt-amount-text">$0 USD</span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Forma de Pago <span class="text-amber-500">*</span></label>
                        <select id="pay-method" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="Cash">Efectivo (Cash)</option>
                            <option value="Credit Card">Tarjeta de Crédito</option>
                            <option value="Zelle">Zelle</option>
                            <option value="Venmo">Venmo</option>
                            <option value="Check">Cheque (Check)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Monto del Abono ($ USD) <span class="text-amber-500">*</span></label>
                        <input type="number" id="pay-amount" step="0.01" min="0.01" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm font-bold"
                            placeholder="Ej. 150.00">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Fecha de Pago <span class="text-amber-500">*</span></label>
                        <input type="date" id="pay-date" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Notas u Observaciones</label>
                    <textarea id="pay-notes"
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        rows="2" placeholder="Información adicional del abono (opcional)..."></textarea>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-800/60 pt-6">
                    <button type="reset" class="px-5 py-2.5 rounded-lg text-sm font-medium border border-slate-800 hover:bg-slate-800 transition">Limpiar</button>
                    <button type="submit" id="payment-submit-btn" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-receipt"></i> <span>Registrar Abono</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- MOVIMIENTOS / HISTORY TABLE -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6 border-b border-slate-800 pb-4">
                <h4 class="text-base font-bold text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-list text-emerald-500"></i> Historial de Movimientos (Abonos)
                </h4>
                <span class="text-xs bg-emerald-500/10 text-emerald-400 px-3 py-1 rounded-full font-medium" id="movements-count-badge">0 abonos</span>
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

    <!-- ================= PANELS: REGISTRO INSTRUCTOR ================= -->
    <div id="view-registro-instructor" class="hidden max-w-3xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        <div class="p-8 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-b border-slate-800">
            <h3 class="text-lg font-bold text-slate-100" id="instructor-form-title">Ficha de Registro de Instructor</h3>
            <p class="text-xs text-slate-400 mt-1" id="instructor-form-subtitle">Crea nuevos expedientes de instructores que se sincronizarán directamente con la base de datos.</p>
        </div>

        <form id="instructor-form" class="p-8 space-y-6" onsubmit="handleInstructorSubmit(event)">
            <!-- 1. Datos Personales -->
            <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 border-b border-slate-800 pb-2">Información del Instructor</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Apellido <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-last-name" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. Rodríguez">
                </div>    
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Primer Nombre <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-first-name" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. Luis">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Segundo Nombre</label>
                    <input type="text" id="ins-middle-name"
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. Felipe">
                </div>
                
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Fecha de Nacimiento <span class="text-amber-500">*</span></label>
                    <input type="date" id="ins-birth-date" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Género <span class="text-amber-500">*</span></label>
                    <select id="ins-gender" required
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
                    <input type="email" id="ins-email" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="ejemplo@correo.com">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Teléfono de Contacto <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-phone" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. +1 914-555-7788">
                </div>
            </div>

            <!-- 2. Ubicación -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Dirección de Residencia <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-address" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. 123 Main St">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Ciudad <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-city" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. Queens">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Estado <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-state" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        value="NY" placeholder="Ej. NY">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Cód. Postal <span class="text-amber-500">*</span></label>
                    <input type="text" id="ins-zip-code" required
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
                        <input type="text" id="ins-height"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                            placeholder="Ej. 5'11&quot; o 1.80m">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Color de Ojos</label>
                        <select id="ins-eye-color"
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
                            <input type="checkbox" id="ins-organ-donor" value="1" class="accent-amber-500">
                            <span class="text-sm">¿Donante de Órganos?</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Foto de Perfil (Opcional)</label>
                    <div class="relative group border-2 border-dashed border-slate-800 hover:border-amber-500/60 bg-slate-950 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer min-h-[140px] transition text-center" id="ins-photo-zone" onclick="document.getElementById('ins-photo-input').click()">
                        <input type="file" id="ins-photo-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'ins-photo-preview', 'ins-photo-zone', 'ins-photo-base64')">
                        <input type="hidden" id="ins-photo-base64" value="">
                        
                        <!-- Placeholder State -->
                        <div class="upload-placeholder flex flex-col items-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                            <span class="text-xs text-slate-400 font-medium">Subir Foto de Perfil</span>
                            <span class="text-[10px] text-slate-600">JPG, PNG o WEBP (Máx. 4MB)</span>
                        </div>
                        
                        <!-- Preview State -->
                        <img id="ins-photo-preview" src="" class="hidden max-h-[120px] rounded-lg object-contain shadow-md" alt="Vista previa">
                        
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
                <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Licencia de Instructor NY DMV (Opcional)</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nº de Documento (License)</label>
                        <input type="text" id="ins-lic-number"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                            placeholder="Ej. INS-LIC-001">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Fecha de Expiración</label>
                        <input type="date" id="ins-lic-expiration"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Clase de Documento</label>
                        <select id="ins-lic-class"
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Foto de la Licencia (Anverso)</label>
                        <div class="relative group border-2 border-dashed border-slate-800 hover:border-amber-500/60 bg-slate-950 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer min-h-[140px] transition duration-200 text-center" id="ins-lic-front-zone" onclick="document.getElementById('ins-lic-front-input').click()">
                            <input type="file" id="ins-lic-front-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'ins-lic-front-preview', 'ins-lic-front-zone', 'ins-lic-front-base64')">
                            <input type="hidden" id="ins-lic-front-base64" value="">
                            
                            <!-- Placeholder -->
                            <div class="upload-placeholder flex flex-col items-center gap-2">
                                <i class="fa-solid fa-id-card text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                                <span class="text-xs text-slate-400 font-medium">Subir Licencia (Anverso)</span>
                                <span class="text-[10px] text-slate-600">JPG, PNG o WEBP (Máx. 4MB)</span>
                            </div>
                            
                            <!-- Preview -->
                            <img id="ins-lic-front-preview" src="" class="hidden max-h-[120px] rounded-lg object-contain shadow-md" alt="Licencia Anverso">
                            
                            <!-- Remove Button -->
                            <button type="button" onclick="clearUpload(event, 'ins-lic-front-input', 'ins-lic-front-preview', 'ins-lic-front-zone', 'ins-lic-front-base64')" 
                                class="absolute top-2 right-2 bg-red-500/20 hover:bg-red-500 text-red-400 hover:text-slate-950 p-1.5 rounded-lg text-xs transition z-10 hidden" id="ins-lic-front-remove">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Foto de la Licencia (Reverso)</label>
                        <div class="relative group border-2 border-dashed border-slate-800 hover:border-amber-500/60 bg-slate-950 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer min-h-[140px] transition duration-200 text-center" id="ins-lic-back-zone" onclick="document.getElementById('ins-lic-back-input').click()">
                            <input type="file" id="ins-lic-back-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'ins-lic-back-preview', 'ins-lic-back-zone', 'ins-lic-back-base64')">
                            <input type="hidden" id="ins-lic-back-base64" value="">
                            
                            <!-- Placeholder -->
                            <div class="upload-placeholder flex flex-col items-center gap-2">
                                <i class="fa-solid fa-id-card text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                                <span class="text-xs text-slate-400 font-medium">Subir Licencia (Reverso)</span>
                                <span class="text-[10px] text-slate-600">JPG, PNG o WEBP (Máx. 4MB)</span>
                            </div>
                            
                            <!-- Preview -->
                            <img id="ins-lic-back-preview" src="" class="hidden max-h-[120px] rounded-lg object-contain shadow-md" alt="Licencia Reverso">
                            
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
                <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Detalles Operativos (Instructor)</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Vehículo Asignado <span class="text-amber-500">*</span></label>
                        <select id="ins-vehicle" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="">Selecciona vehículo...</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tipo de Transmisión <span class="text-amber-500">*</span></label>
                        <div class="flex gap-4 mt-1">
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-950 px-4 py-2.5 rounded-lg border border-slate-800 hover:border-amber-500/60 flex-1">
                                <input type="radio" name="ins-transmission" value="Automático" checked class="accent-amber-500">
                                <span class="text-sm">Automático</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-950 px-4 py-2.5 rounded-lg border border-slate-800 hover:border-amber-500/60 flex-1">
                                <input type="radio" name="ins-transmission" value="Mecánico" class="accent-amber-500">
                                <span class="text-sm">Mecánico (Stick Shift)</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Turno Horario <span class="text-amber-500">*</span></label>
                        <select id="ins-shift" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="Mañana (8am-12pm)">Mañana (8am - 12pm)</option>
                            <option value="Tarde (1pm-5pm)">Tarde (1pm - 5pm)</option>
                            <option value="Completo (8am-5pm)">Completo (8am - 5pm)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-800/60 pt-6 flex justify-end gap-3">
                <button type="reset" class="px-5 py-2.5 rounded-lg text-sm font-medium border border-slate-800 hover:bg-slate-800 transition">Reiniciar Formulario</button>
                <button type="submit" id="instructor-submit-btn" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-save" id="instructor-submit-btn-icon"></i> <span id="instructor-submit-btn-text">Registrar Instructor</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ================= PANELS: REGISTRO VEHICULO ================= -->
    <div id="view-registro-vehiculo" class="hidden max-w-3xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        <div class="p-8 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-b border-slate-800">
            <h3 class="text-lg font-bold text-slate-100" id="vehicle-form-title">Ficha de Registro de Vehículo</h3>
            <p class="text-xs text-slate-400 mt-1" id="vehicle-form-subtitle">Crea nuevos expedientes de vehículos que se sincronizarán directamente con la base de datos.</p>
        </div>

        <form id="vehicle-form" class="p-8 space-y-6" onsubmit="handleVehicleSubmit(event)">
            <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 border-b border-slate-800 pb-2">Información del Vehículo</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Marca / Modelo <span class="text-amber-500">*</span></label>
                    <input type="text" id="veh-brand" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. Toyota Corolla (2022)">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Placa Patente <span class="text-amber-500">*</span></label>
                    <input type="text" id="veh-plate" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                        placeholder="Ej. AB123CD">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Transmisión <span class="text-amber-500">*</span></label>
                    <select id="veh-transmission" required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                        <option value="Automático">Automático</option>
                        <option value="Mecánico">Mecánico (Stick Shift)</option>
                    </select>
                </div>
            </div>

            <div class="border-t border-slate-800/60 pt-6 flex justify-end gap-3">
                <button type="reset" class="px-5 py-2.5 rounded-lg text-sm font-medium border border-slate-800 hover:bg-slate-800 transition">Reiniciar Formulario</button>
                <button type="submit" id="vehicle-submit-btn" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-save" id="vehicle-submit-btn-icon"></i> <span id="vehicle-submit-btn-text">Registrar Vehículo</span>
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
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">4. Tu nombre <span class="normal-case font-normal text-slate-500">(opcional, se mostrará en la web si se publica tu reseña)</span></label>
                <input type="text" id="srv-author" maxlength="100"
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm"
                    placeholder="Ej: María G.">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">5. ¿Tienes alguna queja, observación o sugerencia?</label>
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

    <!-- ================= PANELS: REGISTRO DE ABONOS ================= -->
    <div id="view-abonos" class="hidden space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form Card -->
            <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl h-fit">
                <div class="p-6 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-b border-slate-800">
                    <h3 class="text-base font-bold text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-amber-500"></i>
                        Registrar Nuevo Abono
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Registra pagos de estudiantes con deuda pendiente.</p>
                </div>
                
                <form id="payment-form" class="p-6 space-y-4" onsubmit="handlePaymentSubmit(event)">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Seleccionar Estudiante <span class="text-amber-500">*</span></label>
                        <select id="pay-student-id" required onchange="handlePaymentStudentChange()"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="">Selecciona un estudiante...</option>
                        </select>
                        <p id="pay-student-debt-help" class="text-xs text-slate-400 mt-1.5 hidden">
                            Costo total: <span id="pay-student-cost-val" class="font-semibold text-slate-200"></span> | 
                            Abonado: <span id="pay-student-paid-val" class="font-semibold text-emerald-400"></span> | 
                            Deuda: <span id="pay-student-debt-val" class="font-semibold text-red-400"></span>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Monto a Abonar <span class="text-amber-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-slate-500 text-sm">$</span>
                                <input type="number" id="pay-amount" step="0.01" min="0.01" required
                                    class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-8 pr-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm placeholder-slate-600"
                                    placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Forma de Pago <span class="text-amber-500">*</span></label>
                            <select id="pay-method" required
                                class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                                <option value="Cash">Efectivo</option>
                                <option value="Credit Card">Tarjeta de Crédito</option>
                                <option value="Zelle">Zelle</option>
                                <option value="Venmo">Venmo</option>
                                <option value="Check">Cheque</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Fecha del Abono <span class="text-amber-500">*</span></label>
                        <input type="date" id="pay-date" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Notas / Concepto</label>
                        <textarea id="pay-notes" rows="2"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm placeholder-slate-600"
                            placeholder="Notas adicionales sobre el pago..."></textarea>
                    </div>

                    <button type="submit"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold py-3 rounded-xl transition flex items-center justify-center gap-2 text-sm shadow-lg shadow-amber-500/10">
                        <i class="fa-solid fa-save"></i>
                        Registrar Abono
                    </button>
                </form>
            </div>

            <!-- History Card -->
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex flex-col">
                <div class="p-6 border-b border-slate-800 flex justify-between items-center bg-gradient-to-r from-slate-900 to-slate-950">
                    <div>
                        <h3 class="text-base font-bold text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-amber-500"></i>
                            Historial de Movimientos
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">Lista de todos los abonos registrados en el sistema.</p>
                    </div>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-400 uppercase tracking-wider border-b border-slate-800 bg-slate-950/40">
                            <tr>
                                <th class="px-6 py-4">ID</th>
                                <th class="px-6 py-4">Estudiante</th>
                                <th class="px-6 py-4">Fecha</th>
                                <th class="px-6 py-4">Método</th>
                                <th class="px-6 py-4 text-right">Monto</th>
                                <th class="px-6 py-4 text-right">Deuda Restante</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
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
                <button onclick="switchSheetTab('payments')" id="sheet-btn-payments"
                    class="sheet-tab px-3 py-1.5 rounded text-xs font-bold transition text-slate-400 hover:text-slate-100">📁 Historial Abonos</button>
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

        let instructors = @json($instructors);

        let vehicles = @json($vehicles);

        let reviews = @json($reviews);

        let payments = @json($payments);

        // Active Sheet Tab indicator
        let activeSheetTab = 'students';

        // Editing state tracking
        let editingStudentId = null;
        let editingInstructorId = null;
        let editingVehicleId = null;

        function handleFileSelect(inputElement, previewImgId, containerId, base64HolderId) {
            const file = inputElement.files[0];
            if (file) {
                if (file.size > 4 * 1024 * 1024) {
                    showToast("La imagen supera los 4MB.", "warning");
                    inputElement.value = '';
                    return;
                }
                if (!file.type.startsWith("image/")) {
                    showToast("El archivo seleccionado debe ser una imagen.", "warning");
                    inputElement.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const base64 = e.target.result;
                    const img = document.getElementById(previewImgId);
                    img.src = base64;
                    img.classList.remove('hidden');
                    document.getElementById(containerId).querySelector('.upload-placeholder').classList.add('hidden');
                    document.getElementById(base64HolderId).value = base64;
                    
                    // Show remove button
                    const removeId = inputElement.id.replace('-input', '-remove');
                    const removeBtn = document.getElementById(removeId);
                    if (removeBtn) removeBtn.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        function clearUpload(event, inputId, previewId, zoneId, base64HolderId) {
            if (event) event.stopPropagation();
            const input = document.getElementById(inputId);
            if (input) input.value = '';
            
            const holder = document.getElementById(base64HolderId);
            if (holder) holder.value = '';
            
            const img = document.getElementById(previewId);
            if (img) {
                img.src = '';
                img.classList.add('hidden');
            }
            
            const zone = document.getElementById(zoneId);
            if (zone) {
                const placeholder = zone.querySelector('.upload-placeholder');
                if (placeholder) placeholder.classList.remove('hidden');
            }
            
            // Hide remove button
            const removeId = inputId.replace('-input', '-remove');
            const removeBtn = document.getElementById(removeId);
            if (removeBtn) removeBtn.classList.add('hidden');
        }

        function switchTab(tabId, isEditing = false) {
            // Hide all views
            document.getElementById('view-dashboard').classList.add('hidden');
            document.getElementById('view-inscripcion').classList.add('hidden');
            document.getElementById('view-abonos').classList.add('hidden');
            document.getElementById('view-registro-instructor').classList.add('hidden');
            document.getElementById('view-registro-vehiculo').classList.add('hidden');
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
                if (!isEditing) {
                    editingStudentId = null;
                    document.getElementById('student-form').reset();
                    document.getElementById('student-form-title').innerText = "Ficha de Inscripción Digital";
                    document.getElementById('student-form-subtitle').innerText = "Crea nuevos expedientes que se sincronizarán directamente con la base de datos.";
                    document.getElementById('student-submit-btn-text').innerText = "Registrar Alumno";
                    document.getElementById('student-submit-btn-icon').className = "fa-solid fa-save";
                    
                    // Clear upload previews
                    clearUpload(null, 'stu-photo-input', 'stu-photo-preview', 'stu-photo-zone', 'stu-photo-base64');
                    clearUpload(null, 'lic-front-input', 'lic-front-preview', 'lic-front-zone', 'lic-front-base64');
                    clearUpload(null, 'lic-back-input', 'lic-back-preview', 'lic-back-zone', 'lic-back-base64');
                }
            } else if (tabId === 'abonos') {
                sectionPre.innerText = "Control de Caja";
                sectionTitle.innerText = "Registro de Abonos";
                
                // Populate students with debt > 0
                populatePaymentStudentsDropdown();
                
                // Set default date to today
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('pay-date').value = today;
                
                // Reset form
                document.getElementById('payment-form').reset();
                document.getElementById('pay-student-debt-help').classList.add('hidden');
                
                // Render movements table
                renderPaymentsTable();
            } else if (tabId === 'registro-instructor') {
                sectionPre.innerText = "Instructores";
                sectionTitle.innerText = "Registrar Instructor";
                if (!isEditing) {
                    editingInstructorId = null;
                    document.getElementById('instructor-form').reset();
                    document.getElementById('instructor-form-title').innerText = "Ficha de Registro de Instructor";
                    document.getElementById('instructor-form-subtitle').innerText = "Crea nuevos expedientes de instructores que se sincronizarán directamente con la base de datos.";
                    document.getElementById('instructor-submit-btn-text').innerText = "Registrar Instructor";
                    document.getElementById('instructor-submit-btn-icon').className = "fa-solid fa-save";
                    
                    // Clear upload previews
                    clearUpload(null, 'ins-photo-input', 'ins-photo-preview', 'ins-photo-zone', 'ins-photo-base64');
                    clearUpload(null, 'ins-lic-front-input', 'ins-lic-front-preview', 'ins-lic-front-zone', 'ins-lic-front-base64');
                    clearUpload(null, 'ins-lic-back-input', 'ins-lic-back-preview', 'ins-lic-back-zone', 'ins-lic-back-base64');
                }
            } else if (tabId === 'registro-vehiculo') {
                sectionPre.innerText = "Vehículos";
                sectionTitle.innerText = "Registrar Vehículo";
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
                        <td class="py-3.5 font-semibold text-slate-200">${esc(s.name)}${s.contract_number ? `<span class="block text-[10px] font-normal text-slate-500">Contrato ${esc(s.contract_number)}</span>` : ''}</td>
                        <td class="py-3.5 text-xs text-slate-400">${esc(s.plan)}</td>
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
                            <h5 class="text-xs font-bold text-slate-200">Instructor: ${esc(r.instructor)}</h5>
                            <span class="text-xs text-red-400 font-bold">${'★'.repeat(r.rating)}${'☆'.repeat(5 - r.rating)}</span>
                        </div>
                        <p class="text-xs text-slate-300 italic">"${esc(r.comment)}"</p>
                        <div class="flex justify-end gap-2 pt-1">
                            <button data-name="${esc(r.instructor)}" onclick="simulateCallInstructor(this.dataset.name)" class="bg-slate-950 hover:bg-slate-900 border border-slate-800 text-[10px] text-slate-300 px-2 py-1 rounded font-bold transition">Contactar Instructor</button>
                        </div>
                    `;
                    alertContainer.appendChild(card);
                });
            }
        }

        function simulateCobro(studentId) {
            const student = students.find(s => s.id === studentId);
            if (student) {
                // Switch to Abonos tab
                switchTab('abonos');
                
                // Select student in dropdown
                const select = document.getElementById('pay-student-id');
                if (select) {
                    select.value = student.db_id;
                    // Trigger change to load helper text
                    handlePaymentStudentChange();
                }
                
                // Focus amount input
                setTimeout(() => {
                    const amountInput = document.getElementById('pay-amount');
                    if (amountInput) amountInput.focus();
                }, 100);
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
            const contract_number = document.getElementById('stu-contract-number').value.trim();
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

            // Image uploads
            const student_photo = document.getElementById('stu-photo-base64').value;
            const license_photo = document.getElementById('lic-front-base64').value;
            const license_photo_back = document.getElementById('lic-back-base64').value;

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
                student_photo,
                license_photo,
                license_photo_back,
                contract_number,
                package: plan,
                transmission: trans,
                plan_cost: cost,
                paid_amount: paid,
                debt_amount: debt
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const url = editingStudentId ? `{{ url('students') }}/${editingStudentId}` : '{{ url('students') }}';
            const method = editingStudentId ? 'PUT' : 'POST';

            // Send AJAX Request
            fetch(url, {
                method: method,
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
                    
                    if (method === 'PUT') {
                        const idx = students.findIndex(s => s.db_id === student.db_id);
                        if (idx !== -1) {
                            students[idx] = student;
                        }
                        showToast(`¡Estudiante ${student.name} actualizado con éxito en la Base de Datos!`, "success");
                    } else {
                        students.push(student);
                        showToast(`¡Estudiante ${student.name} registrado con éxito en la Base de Datos!`, "success");
                    }

                    // Logs Simulator Console
                    const consoleLogs = document.getElementById('console-logs');
                    if (method === 'PUT') {
                        consoleLogs.innerHTML += `
                            <p class="text-green-400">[DB_SUCCESS] Event: FormSubmit (Modificación de Alumnos)</p>
                            <p class="text-slate-300">↳ Updated DB Record ID: ${student.db_id} [Student ID: ${student.id}]</p>
                            <p class="text-slate-500">----------------------------------------------------</p>
                        `;
                    } else {
                        consoleLogs.innerHTML += `
                            <p class="text-green-400">[DB_SUCCESS] Event: FormSubmit (Inscripción de Alumnos)</p>
                            <p class="text-slate-300">↳ Inserted DB Record ID: ${student.db_id} [Student ID: ${student.id}]</p>
                            <p class="text-cyan-400">↳ Triggering 'generarFacturaPDF()' para ${esc(student.name)}...</p>
                            <p class="text-slate-400">↳ File 'Factura_${esc(student.id)}_${esc(student.name.replace(/ /g, "_"))}.pdf' creado en Drive/Facturas</p>
                            <p class="text-emerald-400">↳ Notificación automatizada enviada a ${esc(student.phone)}</p>
                            <p class="text-slate-500">----------------------------------------------------</p>
                        `;
                    }

                    // Reset Form and update calculations
                    document.getElementById('student-form').reset();
                    clearUpload(null, 'stu-photo-input', 'stu-photo-preview', 'stu-photo-zone', 'stu-photo-base64');
                    clearUpload(null, 'lic-front-input', 'lic-front-preview', 'lic-front-zone', 'lic-front-base64');
                    clearUpload(null, 'lic-back-input', 'lic-back-preview', 'lic-back-zone', 'lic-back-base64');
                    updatePlanCost();
                    updateDashboardMetrics();
                    renderSheetTable();

                    // Switch to sheets-db automatically to show dynamic insertion
                    setTimeout(() => {
                        switchTab('sheets-db');
                        switchSheetTab('students');
                        showToast(method === 'PUT' ? "Los datos se actualizaron en la base de datos MySQL" : "Los datos se guardaron en la base de datos MySQL y se sincronizaron con Google Sheets", "info");
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

        function handleInstructorSubmit(e) {
            e.preventDefault();

            // Collect variables
            const first_name = document.getElementById('ins-first-name').value;
            const middle_name = document.getElementById('ins-middle-name').value;
            const last_name = document.getElementById('ins-last-name').value;
            const birth_date = document.getElementById('ins-birth-date').value;
            const gender = document.getElementById('ins-gender').value;
            const email = document.getElementById('ins-email').value;
            const phone = document.getElementById('ins-phone').value;
            const address = document.getElementById('ins-address').value;
            const city = document.getElementById('ins-city').value;
            const state = document.getElementById('ins-state').value;
            const zip_code = document.getElementById('ins-zip-code').value;
            
            // Optional DMV physical specs
            const height = document.getElementById('ins-height').value;
            const eye_color = document.getElementById('ins-eye-color').value;
            const is_organ_donor = document.getElementById('ins-organ-donor').checked ? 1 : 0;

            // Optional License permit info
            const license_number = document.getElementById('ins-lic-number').value;
            const expiration_date = document.getElementById('ins-lic-expiration').value;
            const license_class = document.getElementById('ins-lic-class').value;

            // Instructor-specific
            const vehicle_id = document.getElementById('ins-vehicle').value;
            const transmission = document.querySelector('input[name="ins-transmission"]:checked').value;
            const shift = document.getElementById('ins-shift').value;

            // Image uploads
            const instructor_photo = document.getElementById('ins-photo-base64').value;
            const license_photo = document.getElementById('ins-lic-front-base64').value;
            const license_photo_back = document.getElementById('ins-lic-back-base64').value;

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
                instructor_photo,
                license_photo,
                license_photo_back,
                vehicle_id,
                transmission,
                shift
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const url = editingInstructorId ? `{{ url('instructors') }}/${editingInstructorId}` : '{{ url('instructors') }}';
            const method = editingInstructorId ? 'PUT' : 'POST';

            // Send AJAX Request
            fetch(url, {
                method: method,
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
                    const instructor = res.instructor;
                    
                    if (method === 'PUT') {
                        const idx = instructors.findIndex(i => i.db_id === instructor.db_id);
                        if (idx !== -1) {
                            instructors[idx] = instructor;
                        }
                        showToast(`¡Instructor ${instructor.name} actualizado con éxito en la Base de Datos!`, "success");
                    } else {
                        instructors.push(instructor);
                        showToast(`¡Instructor ${instructor.name} registrado con éxito en la Base de Datos!`, "success");
                    }

                    // Logs Simulator Console
                    const consoleLogs = document.getElementById('console-logs');
                    if (method === 'PUT') {
                        consoleLogs.innerHTML += `
                            <p class="text-green-400">[DB_SUCCESS] Event: FormSubmit (Modificación de Instructores)</p>
                            <p class="text-slate-300">↳ Updated DB Record ID: ${instructor.db_id} [Instructor ID: ${instructor.id}]</p>
                            <p class="text-slate-500">----------------------------------------------------</p>
                        `;
                    } else {
                        consoleLogs.innerHTML += `
                            <p class="text-green-400">[DB_SUCCESS] Event: FormSubmit (Registro de Instructores)</p>
                            <p class="text-slate-300">↳ Inserted DB Record ID: ${instructor.db_id} [Instructor ID: ${instructor.id}]</p>
                            <p class="text-emerald-400">↳ Instructor asignado al turno: ${esc(instructor.shift)}</p>
                            <p class="text-slate-500">----------------------------------------------------</p>
                        `;
                    }

                    // Reset Form
                    document.getElementById('instructor-form').reset();
                    clearUpload(null, 'ins-photo-input', 'ins-photo-preview', 'ins-photo-zone', 'ins-photo-base64');
                    clearUpload(null, 'ins-lic-front-input', 'ins-lic-front-preview', 'ins-lic-front-zone', 'ins-lic-front-base64');
                    clearUpload(null, 'ins-lic-back-input', 'ins-lic-back-preview', 'ins-lic-back-zone', 'ins-lic-back-base64');
                    populateInstructorDropdown();
                    updateDashboardMetrics();
                    renderSheetTable();

                    // Switch to sheets-db automatically to show dynamic insertion/update
                    setTimeout(() => {
                        switchTab('sheets-db');
                        switchSheetTab('instructors');
                        showToast(method === 'PUT' ? "Los datos se actualizaron en la base de datos MySQL" : "Los datos se guardaron en la base de datos MySQL y se sincronizaron con Google Sheets", "info");
                    }, 1000);
                } else {
                    showToast("Error de validación: " + (res.message || "Campos incompletos."), "warning");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Error de red o de servidor al registrar instructor.", "warning");
            });
        }

        function deleteInstructor(instructorId) {
            const instructor = instructors.find(i => i.id === instructorId);
            if (instructor) {
                if (!confirm(`¿Estás seguro de que deseas eliminar permanentemente a ${instructor.name} de la base de datos?`)) return;

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`{{ url('instructors') }}/${instructor.db_id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        instructors = instructors.filter(i => i.id !== instructorId);
                        showToast(`Instructor ${instructor.name} eliminado de la base de datos (Soft Deleted).`, "success");

                        const log = document.getElementById('console-logs');
                        log.innerHTML += `<p class="text-red-500">[DB_TRIGGER] Instructor eliminado en BD ID: ${instructor.db_id} [Instructor ID: ${instructorId}]</p>`;

                        populateInstructorDropdown();
                        updateDashboardMetrics();
                        renderSheetTable();
                    } else {
                        showToast("Error al eliminar de la BD: " + (res.message || ""), "warning");
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("Error al eliminar instructor en el servidor.", "warning");
                });
            }
        }

        function populateVehicleDropdown() {
            const insVehicleDropdown = document.getElementById('ins-vehicle');
            if (insVehicleDropdown) {
                insVehicleDropdown.innerHTML = '<option value="">Selecciona vehículo...</option>';
                vehicles.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.db_id;
                    opt.innerText = `${v.brand} [${v.plate}]`;
                    insVehicleDropdown.appendChild(opt);
                });
            }
        }

        function handleVehicleSubmit(e) {
            e.preventDefault();

            const brand = document.getElementById('veh-brand').value;
            const plate = document.getElementById('veh-plate').value;
            const transmission = document.getElementById('veh-transmission').value;

            const data = { brand, plate, transmission };
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const url = editingVehicleId ? `{{ url('vehicles') }}/${editingVehicleId}` : '{{ url('vehicles') }}';
            const method = editingVehicleId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
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
                    const vehicle = res.vehicle;
                    
                    if (method === 'PUT') {
                        const idx = vehicles.findIndex(v => v.db_id === vehicle.db_id);
                        if (idx !== -1) {
                            vehicles[idx] = vehicle;
                        }
                        showToast(`¡Vehículo ${vehicle.brand} actualizado con éxito en la Base de Datos!`, "success");
                    } else {
                        vehicles.push(vehicle);
                        showToast(`¡Vehículo ${vehicle.brand} registrado con éxito en la Base de Datos!`, "success");
                    }

                    const consoleLogs = document.getElementById('console-logs');
                    if (method === 'PUT') {
                        consoleLogs.innerHTML += `
                            <p class="text-green-400">[DB_SUCCESS] Event: FormSubmit (Modificación de Vehículos)</p>
                            <p class="text-slate-300">↳ Updated DB Record ID: ${vehicle.db_id} [Vehicle ID: ${vehicle.id}]</p>
                            <p class="text-slate-500">----------------------------------------------------</p>
                        `;
                    } else {
                        consoleLogs.innerHTML += `
                            <p class="text-green-400">[DB_SUCCESS] Event: FormSubmit (Registro de Vehículos)</p>
                            <p class="text-slate-300">↳ Inserted DB Record ID: ${vehicle.db_id} [Vehicle ID: ${vehicle.id}]</p>
                            <p class="text-slate-500">----------------------------------------------------</p>
                        `;
                    }

                    document.getElementById('vehicle-form').reset();
                    populateVehicleDropdown();
                    updateDashboardMetrics();
                    renderSheetTable();

                    setTimeout(() => {
                        switchTab('sheets-db');
                        switchSheetTab('vehicles');
                        showToast(method === 'PUT' ? "Los datos se actualizaron en la base de datos MySQL" : "Los datos se guardaron en la base de datos MySQL y se sincronizaron con Google Sheets", "info");
                    }, 1000);
                } else {
                    showToast("Error de validación: " + (res.message || "Campos incompletos."), "warning");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Error de red o de servidor al registrar vehículo.", "warning");
            });
        }

        function deleteVehicle(vehicleId) {
            const vehicle = vehicles.find(v => v.id === vehicleId);
            if (vehicle) {
                if (!confirm(`¿Estás seguro de que deseas eliminar permanentemente el vehículo ${vehicle.brand} [${vehicle.plate}] de la base de datos?`)) return;

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`{{ url('vehicles') }}/${vehicle.db_id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        vehicles = vehicles.filter(v => v.id !== vehicleId);
                        showToast(`Vehículo ${vehicle.brand} eliminado de la base de datos (Soft Deleted).`, "success");

                        const log = document.getElementById('console-logs');
                        log.innerHTML += `<p class="text-red-500">[DB_TRIGGER] Vehículo eliminado en BD ID: ${vehicle.db_id} [Vehicle ID: ${vehicleId}]</p>`;

                        populateVehicleDropdown();
                        updateDashboardMetrics();
                        renderSheetTable();
                    } else {
                        showToast("Error al eliminar de la BD: " + (res.message || ""), "warning");
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("Error al eliminar vehículo en el servidor.", "warning");
                });
            }
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
                    opt.value = ins.db_id;
                    opt.innerText = ins.name;
                    srvDropdown.appendChild(opt);
                });
            }
        }

        function handleSurveySubmit(e) {
            e.preventDefault();

            const instructorId = document.getElementById('srv-instructor').value;
            const rating = parseInt(document.getElementById('srv-rating').value);
            const vehRating = parseInt(document.getElementById('srv-vehicle-rating').value);
            const comment = document.getElementById('srv-comment').value;
            const authorName = document.getElementById('srv-author').value.trim();

            if (!instructorId) {
                showToast("Por favor selecciona un instructor.", "warning");
                return;
            }

            if (rating === 0 || vehRating === 0) {
                showToast("Por favor califica ambas opciones con estrellas antes de enviar.", "warning");
                return;
            }

            const data = {
                instructor_id: instructorId,
                author_name: authorName || null,
                rating: rating,
                vehicle_rating: vehRating,
                comment: comment
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('{{ url('reviews') }}', {
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
                    const review = res.review;
                    reviews.push(review);

                    showToast("¡Muchas gracias por evaluar nuestro servicio!", "success");

                    // Logs Simulator
                    const consoleLogs = document.getElementById('console-logs');
                    consoleLogs.innerHTML += `
                        <p class="text-green-400">[DB_SUCCESS] Event: FormSubmit (Encuesta Calidad - Guardado en BD)</p>
                        <p class="text-slate-300">↳ Row added in 'reviews' table. Calificación: ${rating}/5</p>
                        ${rating <= 2 ? `<p class="text-red-500">↳ ALERT [CRITICAL RATING]: Correo de alerta de calidad enviado a Gerencia. Instructor implicado: ${esc(review.instructor)}</p>` : ''}
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
                } else {
                    showToast("Error de validación al enviar encuesta: " + (res.message || ""), "warning");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Error al enviar la evaluación al servidor.", "warning");
            });
        }

        function toggleReviewVisibility(dbId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(`{{ url('reviews') }}/${dbId}/visibility`, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const review = reviews.find(r => r.db_id === dbId);
                    if (review) review.is_public = res.is_public;
                    renderSheetTable();
                    showToast(res.is_public ? "Reseña publicada en la web." : "Reseña ocultada de la web.", "success");
                } else {
                    showToast("Error al cambiar la visibilidad: " + (res.message || ""), "warning");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Error al cambiar la visibilidad de la reseña.", "warning");
            });
        }

        function switchSheetTab(tabKey) {
            activeSheetTab = tabKey;

            document.querySelectorAll('.sheet-tab').forEach(btn => {
                btn.className = "sheet-tab px-3 py-1.5 rounded text-xs font-bold transition text-slate-400 hover:text-slate-100";
            });

            document.getElementById(`sheet-btn-${tabKey}`).className = "sheet-tab px-3 py-1.5 rounded text-xs font-bold transition bg-slate-800 text-slate-100";

            renderSheetTable();
        }

        // ================= TABLAS: búsqueda, filtros, orden y paginación (lado cliente) =================
        const DATA_TABLE_PAGE_SIZES = [25, 50, 100, 0]; // 0 = todos
        const DATA_TABLE_INPUT = 'bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-100 focus:outline-none focus:border-amber-500 transition';
        const dataTableStates = {};
        const dataTableConfigs = {};

        function getDataTableState(key) {
            if (!dataTableStates[key]) {
                dataTableStates[key] = { page: 1, pageSize: 50, search: '', sortCol: null, sortDir: 'asc', filters: {}, dateFrom: '', dateTo: '' };
            }
            return dataTableStates[key];
        }

        function normalizeText(value) {
            return String(value ?? '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
        }

        function isEmptyValue(v) {
            return v === null || v === undefined || v === '';
        }

        function escapeAttr(v) {
            return String(v ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        // Escape any user-provided value before inserting it into HTML
        function esc(v) {
            return escapeAttr(v);
        }

        function compareTableValues(a, b, dir) {
            // Los vacíos van siempre al final, sin importar la dirección
            if (isEmptyValue(a) || isEmptyValue(b)) return isEmptyValue(a) - isEmptyValue(b);
            const result = (typeof a === 'number' && typeof b === 'number')
                ? a - b
                : String(a).localeCompare(String(b), 'es', { numeric: true, sensitivity: 'base' });
            return dir === 'desc' ? -result : result;
        }

        function getDataTableRows(cfg, state) {
            let rows = cfg.rows().slice();

            const terms = normalizeText(state.search).split(/\s+/).filter(Boolean);
            if (terms.length) {
                rows = rows.filter(r => {
                    const haystack = normalizeText([
                        ...cfg.columns.filter(c => c.value).map(c => c.value(r)),
                        ...(cfg.searchExtra ? cfg.searchExtra(r) : [])
                    ].join(' '));
                    return terms.every(t => haystack.includes(t));
                });
            }

            (cfg.filters || []).forEach(f => {
                const selected = state.filters[f.id];
                if (!isEmptyValue(selected)) rows = rows.filter(r => String(f.value(r) ?? '') === selected);
            });

            if (cfg.dateField) {
                if (state.dateFrom) rows = rows.filter(r => (cfg.dateField(r) || '') >= state.dateFrom);
                if (state.dateTo) rows = rows.filter(r => (cfg.dateField(r) || '') <= state.dateTo);
            }

            const sortCol = state.sortCol !== null ? cfg.columns[state.sortCol] : null;
            if (sortCol && sortCol.value) {
                const getter = sortCol.sortValue || sortCol.value;
                rows.sort((a, b) => compareTableValues(getter(a), getter(b), state.sortDir));
            }
            return rows;
        }

        function renderDataTable(cfg, rebuildToolbar = true) {
            dataTableConfigs[cfg.key] = cfg;
            const state = getDataTableState(cfg.key);
            const head = document.getElementById(cfg.headId);
            const body = document.getElementById(cfg.bodyId);
            if (!head || !body) return;

            if (rebuildToolbar) renderDataTableToolbar(cfg, state);

            head.innerHTML = '<tr>' + cfg.columns.map((c, i) => {
                const cls = c.thClass ?? cfg.thClass ?? '';
                if (!c.value) return `<th class="${cls}">${c.label}</th>`;
                const active = state.sortCol === i;
                const icon = active ? (state.sortDir === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
                return `<th class="${cls} cursor-pointer select-none hover:text-slate-100" onclick="sortDataTable('${cfg.key}', ${i})" title="Ordenar por ${escapeAttr(c.label)}">`
                    + `${c.label} <i class="fa-solid ${icon} ml-1 ${active ? 'text-amber-500' : 'opacity-30'}"></i></th>`;
            }).join('') + '</tr>';

            const rows = getDataTableRows(cfg, state);
            const total = rows.length;
            const pageSize = state.pageSize || total || 1;
            const pages = Math.max(1, Math.ceil(total / pageSize));
            state.page = Math.min(Math.max(1, state.page), pages);
            const start = (state.page - 1) * pageSize;
            const pageRows = rows.slice(start, start + pageSize);

            body.innerHTML = '';
            if (total === 0) {
                const hasCriteria = state.search || state.dateFrom || state.dateTo || Object.values(state.filters).some(v => v);
                const msg = hasCriteria ? 'No hay registros que coincidan con la búsqueda o los filtros.' : (cfg.emptyText || 'No hay registros.');
                body.innerHTML = `<tr><td colspan="${cfg.columns.length}" class="px-6 py-6 text-center text-xs text-slate-500">${msg}</td></tr>`;
            } else {
                pageRows.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.className = cfg.rowClass || '';
                    tr.innerHTML = cfg.row(r);
                    body.appendChild(tr);
                });
            }

            renderDataTablePager(cfg, state, total, start, pageRows.length, pages);
            if (cfg.afterRender) cfg.afterRender(total);
        }

        function renderDataTableToolbar(cfg, state) {
            const el = document.getElementById(cfg.toolbarId);
            if (!el) return;
            const allRows = cfg.rows();

            const filtersHtml = (cfg.filters || []).map(f => {
                const options = [...new Set(allRows.map(r => f.value(r)).filter(v => !isEmptyValue(v)).map(String))]
                    .sort((a, b) => a.localeCompare(b, 'es', { numeric: true }));
                const current = state.filters[f.id] || '';
                if (current && !options.includes(current)) options.push(current);
                return `<select class="${DATA_TABLE_INPUT}" onchange="setDataTableFilter('${cfg.key}', '${f.id}', this.value)" title="Filtrar por ${escapeAttr(f.label)}">
                        <option value="">${f.label}: Todos</option>
                        ${options.map(o => `<option value="${escapeAttr(o)}" ${o === current ? 'selected' : ''}>${escapeAttr(f.format ? f.format(o) : o)}</option>`).join('')}
                    </select>`;
            }).join('');

            const dateHtml = cfg.dateField ? `
                    <label class="flex items-center gap-1 text-[11px] text-slate-400">Desde
                        <input type="date" class="${DATA_TABLE_INPUT}" value="${state.dateFrom}" onchange="setDataTableDate('${cfg.key}', 'dateFrom', this.value)">
                    </label>
                    <label class="flex items-center gap-1 text-[11px] text-slate-400">Hasta
                        <input type="date" class="${DATA_TABLE_INPUT}" value="${state.dateTo}" onchange="setDataTableDate('${cfg.key}', 'dateTo', this.value)">
                    </label>` : '';

            el.innerHTML = `
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative flex-1 min-w-[200px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                        <input type="search" class="${DATA_TABLE_INPUT} w-full pl-8" placeholder="${escapeAttr(cfg.searchPlaceholder || 'Buscar...')}"
                            value="${escapeAttr(state.search)}" oninput="setDataTableSearch('${cfg.key}', this.value)">
                    </div>
                    ${filtersHtml}
                    ${dateHtml}
                    <select class="${DATA_TABLE_INPUT}" onchange="setDataTablePageSize('${cfg.key}', this.value)" title="Registros por página">
                        ${DATA_TABLE_PAGE_SIZES.map(n => `<option value="${n}" ${n === state.pageSize ? 'selected' : ''}>${n === 0 ? 'Todos' : n + ' por página'}</option>`).join('')}
                    </select>
                    <button type="button" onclick="resetDataTable('${cfg.key}')" title="Limpiar búsqueda, filtros y orden"
                        class="px-3 py-2 rounded-lg text-xs font-bold border border-slate-800 text-slate-400 hover:bg-slate-800 hover:text-slate-100 transition">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Limpiar
                    </button>
                </div>`;
        }

        function renderDataTablePager(cfg, state, total, start, count, pages) {
            const el = document.getElementById(cfg.pagerId);
            if (!el) return;

            const info = total === 0 ? '0 registros' : `Mostrando ${start + 1}–${start + count} de ${total} registros`;
            const btn = (label, page, disabled, active = false) => `<button type="button" ${disabled ? 'disabled' : `onclick="goToDataTablePage('${cfg.key}', ${page})"`}
                class="min-w-[2rem] px-2 py-1 rounded text-xs font-bold transition ${active ? 'bg-amber-500 text-slate-950' : disabled ? 'text-slate-700 cursor-not-allowed' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'}">${label}</button>`;
            const dots = '<span class="px-1 text-slate-600">…</span>';

            const nums = [];
            const from = Math.max(1, state.page - 2);
            const to = Math.min(pages, state.page + 2);
            if (from > 1) { nums.push(btn('1', 1, false)); if (from > 2) nums.push(dots); }
            for (let p = from; p <= to; p++) nums.push(btn(String(p), p, false, p === state.page));
            if (to < pages) { if (to < pages - 1) nums.push(dots); nums.push(btn(String(pages), pages, false)); }

            el.innerHTML = `
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs text-slate-500">${info}</span>
                    <div class="flex items-center gap-1">
                        ${btn('<i class="fa-solid fa-angles-left"></i>', 1, state.page === 1)}
                        ${btn('<i class="fa-solid fa-angle-left"></i>', state.page - 1, state.page === 1)}
                        ${nums.join('')}
                        ${btn('<i class="fa-solid fa-angle-right"></i>', state.page + 1, state.page === pages)}
                        ${btn('<i class="fa-solid fa-angles-right"></i>', pages, state.page === pages)}
                    </div>
                </div>`;
        }

        function refreshDataTable(key, rebuildToolbar = false) {
            const cfg = dataTableConfigs[key];
            if (cfg) renderDataTable(cfg, rebuildToolbar);
        }

        function setDataTableSearch(key, value) {
            const s = getDataTableState(key);
            s.search = value;
            s.page = 1;
            refreshDataTable(key);
        }

        function setDataTableFilter(key, filterId, value) {
            const s = getDataTableState(key);
            s.filters[filterId] = value;
            s.page = 1;
            refreshDataTable(key);
        }

        function setDataTableDate(key, field, value) {
            const s = getDataTableState(key);
            s[field] = value;
            s.page = 1;
            refreshDataTable(key);
        }

        function setDataTablePageSize(key, value) {
            const s = getDataTableState(key);
            s.pageSize = parseInt(value, 10) || 0;
            s.page = 1;
            refreshDataTable(key);
        }

        function sortDataTable(key, colIndex) {
            // Ciclo: ascendente → descendente → sin orden
            const s = getDataTableState(key);
            if (s.sortCol === colIndex) {
                if (s.sortDir === 'asc') {
                    s.sortDir = 'desc';
                } else {
                    s.sortCol = null;
                    s.sortDir = 'asc';
                }
            } else {
                s.sortCol = colIndex;
                s.sortDir = 'asc';
            }
            refreshDataTable(key);
        }

        function goToDataTablePage(key, page) {
            getDataTableState(key).page = page;
            refreshDataTable(key);
        }

        function resetDataTable(key) {
            delete dataTableStates[key];
            refreshDataTable(key, true);
        }

        function paymentMethodLabel(method) {
            return method === 'Cash' ? 'Efectivo' : method === 'Credit Card' ? 'Tarjeta de Crédito' : method;
        }

        // ---------- Configuración de cada pestaña de la hoja ----------
        const SHEET_TH = 'px-4 py-3 border-r border-slate-800';
        const SHEET_TD = 'px-4 py-2.5 border-r border-slate-800/40';

        function getSheetTableConfig(tab) {
            const base = {
                key: `sheet-${tab}`,
                headId: 'sheet-table-head',
                bodyId: 'sheet-table-body',
                toolbarId: 'sheet-table-toolbar',
                pagerId: 'sheet-table-pager',
                thClass: SHEET_TH,
                rowClass: 'border-b border-slate-800/20 hover:bg-slate-800/30',
            };

            if (tab === 'students') return {
                ...base,
                rows: () => students,
                emptyText: 'No hay estudiantes registrados.',
                searchPlaceholder: 'Buscar por nombre, contrato, teléfono, email o permiso...',
                searchExtra: s => [s.email, s.middle_name, s.city, s.license ? s.license.license_number : ''],
                filters: [
                    { id: 'plan', label: 'Plan', value: s => s.plan },
                    { id: 'status', label: 'Estatus', value: s => s.status },
                    { id: 'transmission', label: 'Transmisión', value: s => s.transmission },
                ],
                columns: [
                    { label: 'A - ID', value: s => s.id, sortValue: s => s.db_id },
                    { label: 'B - Contrato', value: s => s.contract_number },
                    { label: 'C - Fecha Reg', value: s => s.date },
                    { label: 'D - Estudiante', value: s => s.name },
                    { label: 'E - Teléfono', value: s => s.phone },
                    { label: 'F - Plan', value: s => s.plan },
                    { label: 'G - Costo', value: s => s.cost },
                    { label: 'H - Abonado', value: s => s.paid },
                    { label: 'I - Deuda', value: s => s.debt },
                    { label: 'J - Transmisión', value: s => s.transmission },
                    { label: 'K - Estatus', value: s => s.status },
                    { label: 'Acción', thClass: 'px-4 py-3' },
                ],
                row: s => `
                        <td class="${SHEET_TD} text-amber-500 font-bold">${s.id}</td>
                        <td class="${SHEET_TD} text-slate-300">${esc(s.contract_number || '—')}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(s.date)}</td>
                        <td class="${SHEET_TD} text-slate-100 font-semibold">${esc(s.name)}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(s.phone)}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(s.plan)}</td>
                        <td class="${SHEET_TD} text-slate-400 font-bold">$${s.cost}</td>
                        <td class="${SHEET_TD} text-slate-400">$${s.paid}</td>
                        <td class="${SHEET_TD} font-bold ${s.debt > 0 ? 'text-red-500' : 'text-green-500'}">$${s.debt}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(s.transmission)}</td>
                        <td class="${SHEET_TD}">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${s.status === 'Al Día' ? 'bg-green-500/10 text-green-400' : 'bg-amber-500/10 text-amber-500'}">${s.status}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center space-x-1">
                            <button onclick="editStudent('${s.id}')" class="bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button onclick="deleteStudent('${s.id}')" class="bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>`,
            };

            if (tab === 'instructors') return {
                ...base,
                rows: () => instructors,
                emptyText: 'No hay instructores registrados.',
                searchPlaceholder: 'Buscar por nombre, licencia, teléfono, auto o placa...',
                searchExtra: ins => [ins.email],
                filters: [
                    { id: 'status', label: 'Estatus', value: ins => ins.status },
                    { id: 'shift', label: 'Turno', value: ins => ins.shift },
                    { id: 'vehicle', label: 'Auto', value: ins => ins.vehicle },
                ],
                columns: [
                    { label: 'A - ID', value: ins => ins.id, sortValue: ins => ins.db_id },
                    { label: 'B - Nombre Instructor', value: ins => ins.name },
                    { label: 'C - Licencia Certificado', value: ins => ins.license_text || ins.license },
                    { label: 'D - Teléfono', value: ins => ins.phone },
                    { label: 'E - Auto Asignado', value: ins => ins.vehicle },
                    { label: 'F - Placa', value: ins => ins.plate },
                    { label: 'G - Turno Horario', value: ins => ins.shift },
                    { label: 'H - Estatus', value: ins => ins.status },
                    { label: 'Acción', thClass: 'px-4 py-3' },
                ],
                row: ins => `
                        <td class="${SHEET_TD} text-amber-500 font-bold">${ins.id}</td>
                        <td class="${SHEET_TD} text-slate-100 font-semibold">${esc(ins.name)}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(ins.license_text || ins.license)}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(ins.phone)}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(ins.vehicle)}</td>
                        <td class="${SHEET_TD} text-slate-400 font-bold">${esc(ins.plate)}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(ins.shift)}</td>
                        <td class="${SHEET_TD}">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${ins.status === 'Disponible' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-500'}">${esc(ins.status)}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center space-x-1">
                            <button onclick="editInstructor('${ins.id}')" class="bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button onclick="deleteInstructor('${ins.id}')" class="bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>`,
            };

            if (tab === 'vehicles') return {
                ...base,
                rows: () => vehicles,
                emptyText: 'No hay vehículos registrados.',
                searchPlaceholder: 'Buscar por marca, modelo o placa...',
                filters: [
                    { id: 'transmission', label: 'Transmisión', value: v => v.transmission },
                    { id: 'status', label: 'Estatus', value: v => v.status },
                ],
                columns: [
                    { label: 'A - ID', value: v => v.id, sortValue: v => v.db_id },
                    { label: 'B - Marca / Modelo', value: v => v.brand },
                    { label: 'C - Placa Patente', value: v => v.plate },
                    { label: 'D - Transmisión', value: v => v.transmission },
                    { label: 'E - Estatus Operación', value: v => v.status },
                    { label: 'Acción', thClass: 'px-4 py-3' },
                ],
                row: v => `
                        <td class="${SHEET_TD} text-amber-500 font-bold">${v.id}</td>
                        <td class="${SHEET_TD} text-slate-100 font-semibold">${esc(v.brand)}</td>
                        <td class="${SHEET_TD} text-slate-400 font-bold">${esc(v.plate)}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(v.transmission)}</td>
                        <td class="${SHEET_TD}">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${v.status === 'Disponible' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-500'}">${esc(v.status)}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center space-x-1">
                            <button onclick="editVehicle('${v.id}')" class="bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button onclick="deleteVehicle('${v.id}')" class="bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>`,
            };

            if (tab === 'reviews') return {
                ...base,
                rows: () => reviews,
                emptyText: 'No hay encuestas recibidas.',
                searchPlaceholder: 'Buscar por instructor, comentario o autor...',
                filters: [
                    { id: 'instructor', label: 'Instructor', value: r => r.instructor },
                    { id: 'rating', label: 'Trato', value: r => r.rating, format: o => `${o} ★` },
                    { id: 'vehicleRating', label: 'Auto', value: r => r.vehicleRating, format: o => `${o} ★` },
                    { id: 'public', label: 'Web', value: r => r.is_public ? 'Pública' : 'Oculta' },
                ],
                columns: [
                    { label: 'A - ID', value: r => r.id, sortValue: r => r.db_id },
                    { label: 'B - Instructor Evaluado', value: r => r.instructor },
                    { label: 'C - Calificación Trato', value: r => r.rating },
                    { label: 'D - Calificación Auto', value: r => r.vehicleRating },
                    { label: 'E - Comentarios Alumno', value: r => r.comment },
                    { label: 'F - Autor', value: r => r.author_name },
                    { label: 'G - Publicada en Web', value: r => r.is_public ? 'Pública' : 'Oculta', thClass: 'px-4 py-3 text-center' },
                ],
                row: r => `
                        <td class="${SHEET_TD} text-amber-500 font-bold">${r.id}</td>
                        <td class="${SHEET_TD} text-slate-100 font-semibold">${esc(r.instructor)}</td>
                        <td class="${SHEET_TD} text-amber-400 font-bold">${'★'.repeat(r.rating)} (${r.rating}/5)</td>
                        <td class="${SHEET_TD} text-amber-400 font-bold">${'★'.repeat(r.vehicleRating)} (${r.vehicleRating}/5)</td>
                        <td class="${SHEET_TD} text-slate-300 italic truncate max-w-xs">${esc(r.comment)}</td>
                        <td class="${SHEET_TD} text-slate-300">${r.author_name ? esc(r.author_name) : '<span class="text-slate-500">Anónimo</span>'}</td>
                        <td class="px-4 py-2.5 text-center">
                            <button onclick="toggleReviewVisibility(${r.db_id})" title="${r.is_public ? 'Ocultar de la web' : 'Publicar en la web'}"
                                class="${r.is_public ? 'bg-green-500/10 text-green-400 hover:bg-green-500' : 'bg-slate-800 text-slate-400 hover:bg-amber-500'} hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition">
                                <i class="fa-solid ${r.is_public ? 'fa-eye' : 'fa-eye-slash'}"></i> ${r.is_public ? 'Pública' : 'Oculta'}
                            </button>
                        </td>`,
            };

            // payments
            return {
                ...base,
                rows: () => payments,
                emptyText: 'No hay abonos registrados.',
                searchPlaceholder: 'Buscar por ID, estudiante, método o notas...',
                dateField: p => p.payment_date,
                filters: [
                    { id: 'method', label: 'Método', value: p => p.payment_method, format: paymentMethodLabel },
                ],
                columns: [
                    { label: 'A - ID', value: p => p.id, sortValue: p => p.db_id },
                    { label: 'B - Estudiante', value: p => p.student_name },
                    { label: 'C - Fecha Pago', value: p => p.payment_date },
                    { label: 'D - Método Pago', value: p => paymentMethodLabel(p.payment_method) },
                    { label: 'E - Monto', value: p => p.amount },
                    { label: 'F - Deuda Restante', value: p => p.remaining_debt },
                    { label: 'G - Notas', value: p => p.notes, thClass: 'px-4 py-3' },
                ],
                row: p => `
                        <td class="${SHEET_TD} text-amber-500 font-bold">${p.id}</td>
                        <td class="${SHEET_TD} text-slate-100 font-semibold">${esc(p.student_name)}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(p.payment_date)}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(paymentMethodLabel(p.payment_method))}</td>
                        <td class="${SHEET_TD} text-slate-400 font-bold">$${p.amount.toFixed(2)}</td>
                        <td class="${SHEET_TD} text-slate-400 font-bold">$${p.remaining_debt.toFixed(2)}</td>
                        <td class="px-4 py-2.5 text-slate-300 italic truncate max-w-xs">${esc(p.notes || '-')}</td>`,
            };
        }

        function renderSheetTable() {
            renderDataTable(getSheetTableConfig(activeSheetTab));
        }

        function deleteStudent(studentId) {
            const student = students.find(s => s.id === studentId);
            if (student) {
                if (!confirm(`¿Estás seguro de que deseas eliminar permanentemente a ${student.name} de la base de datos?`)) return;

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`{{ url('students') }}/${student.db_id}`, {
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

        function editStudent(studentId) {
            const student = students.find(s => s.id === studentId);
            if (!student) return;

            editingStudentId = student.db_id;

            // Pre-fill form fields
            document.getElementById('stu-contract-number').value = student.contract_number || '';
            document.getElementById('stu-first-name').value = student.first_name || '';
            document.getElementById('stu-middle-name').value = student.middle_name || '';
            document.getElementById('stu-last-name').value = student.last_name || '';
            document.getElementById('stu-birth-date').value = student.birth_date || '';
            document.getElementById('stu-gender').value = student.gender || 'M';
            document.getElementById('stu-email').value = student.email || '';
            document.getElementById('stu-phone').value = student.phone || '';
            document.getElementById('stu-address').value = student.address || '';
            document.getElementById('stu-city').value = student.city || '';
            document.getElementById('stu-state').value = student.state || 'NY';
            document.getElementById('stu-zip-code').value = student.zip_code || '';
            
            // Optional DMV physical specs
            document.getElementById('stu-height').value = student.height || '';
            document.getElementById('stu-eye-color').value = student.eye_color || '';
            document.getElementById('stu-organ-donor').checked = !!student.is_organ_donor;

            // Optional License permit info
            if (student.license) {
                document.getElementById('lic-number').value = student.license.license_number || '';
                document.getElementById('lic-expiration').value = student.license.expiration_date || '';
                document.getElementById('lic-class').value = student.license.license_class || '';
            } else {
                document.getElementById('lic-number').value = '';
                document.getElementById('lic-expiration').value = '';
                document.getElementById('lic-class').value = '';
            }

            // Plan details
            document.getElementById('stu-plan').value = student.plan || 'Package 2 (10 Hours + Test)';
            document.getElementById('stu-cost').value = student.cost || 350;
            document.getElementById('stu-paid').value = student.paid || 0;
            document.getElementById('stu-debt').value = student.debt || 0;

            const radioAutom = document.querySelector('input[name="stu-transmission"][value="Automático"]');
            const radioMecan = document.querySelector('input[name="stu-transmission"][value="Mecánico"]');
            if (student.transmission === 'Mecánico') {
                if (radioMecan) radioMecan.checked = true;
            } else {
                if (radioAutom) radioAutom.checked = true;
            }

            // Pre-fill photos
            if (student.student_photo_url) {
                document.getElementById('stu-photo-preview').src = student.student_photo_url;
                document.getElementById('stu-photo-preview').classList.remove('hidden');
                document.getElementById('stu-photo-zone').querySelector('.upload-placeholder').classList.add('hidden');
                document.getElementById('stu-photo-base64').value = student.student_photo_url;
                document.getElementById('stu-photo-remove').classList.remove('hidden');
            } else {
                clearUpload(null, 'stu-photo-input', 'stu-photo-preview', 'stu-photo-zone', 'stu-photo-base64');
            }

            if (student.license && student.license.license_photo_url) {
                document.getElementById('lic-front-preview').src = student.license.license_photo_url;
                document.getElementById('lic-front-preview').classList.remove('hidden');
                document.getElementById('lic-front-zone').querySelector('.upload-placeholder').classList.add('hidden');
                document.getElementById('lic-front-base64').value = student.license.license_photo_url;
                document.getElementById('lic-front-remove').classList.remove('hidden');
            } else {
                clearUpload(null, 'lic-front-input', 'lic-front-preview', 'lic-front-zone', 'lic-front-base64');
            }

            if (student.license && student.license.license_photo_back_url) {
                document.getElementById('lic-back-preview').src = student.license.license_photo_back_url;
                document.getElementById('lic-back-preview').classList.remove('hidden');
                document.getElementById('lic-back-zone').querySelector('.upload-placeholder').classList.add('hidden');
                document.getElementById('lic-back-base64').value = student.license.license_photo_back_url;
                document.getElementById('lic-back-remove').classList.remove('hidden');
            } else {
                clearUpload(null, 'lic-back-input', 'lic-back-preview', 'lic-back-zone', 'lic-back-base64');
            }

            // Toggle title and submit button texts to Edit Mode
            document.getElementById('student-form-title').innerText = "Editar Expediente de Estudiante";
            document.getElementById('student-form-subtitle').innerText = `Modificando datos del alumno ${student.name} [ID: ${student.id}] en la base de datos.`;
            document.getElementById('student-submit-btn-text').innerText = "Guardar Cambios";
            document.getElementById('student-submit-btn-icon').className = "fa-solid fa-pen-to-square";

            // Switch to form tab
            switchTab('inscripcion', true);
        }

        function editInstructor(instructorId) {
            const instructor = instructors.find(i => i.id === instructorId);
            if (!instructor) return;

            editingInstructorId = instructor.db_id;

            // Pre-fill form fields
            document.getElementById('ins-first-name').value = instructor.first_name || '';
            document.getElementById('ins-middle-name').value = instructor.middle_name || '';
            document.getElementById('ins-last-name').value = instructor.last_name || '';
            document.getElementById('ins-birth-date').value = instructor.birth_date || '';
            document.getElementById('ins-gender').value = instructor.gender || 'M';
            document.getElementById('ins-email').value = instructor.email || '';
            document.getElementById('ins-phone').value = instructor.phone || '';
            document.getElementById('ins-address').value = instructor.address || '';
            document.getElementById('ins-city').value = instructor.city || '';
            document.getElementById('ins-state').value = instructor.state || 'NY';
            document.getElementById('ins-zip-code').value = instructor.zip_code || '';
            
            // Optional DMV physical specs
            document.getElementById('ins-height').value = instructor.height || '';
            document.getElementById('ins-eye-color').value = instructor.eye_color || '';
            document.getElementById('ins-organ-donor').checked = !!instructor.is_organ_donor;

            // Optional License/Certificate info
            if (instructor.license) {
                document.getElementById('ins-lic-number').value = instructor.license.license_number || '';
                document.getElementById('ins-lic-expiration').value = instructor.license.expiration_date || '';
                document.getElementById('ins-lic-class').value = instructor.license.license_class || '';
            } else {
                document.getElementById('ins-lic-number').value = '';
                document.getElementById('ins-lic-expiration').value = '';
                document.getElementById('ins-lic-class').value = '';
            }

            // Instructor-specific
            document.getElementById('ins-vehicle').value = instructor.vehicle_id || '';
            document.getElementById('ins-shift').value = instructor.shift || 'Mañana (8am-12pm)';

            const radioAutom = document.querySelector('input[name="ins-transmission"][value="Automático"]');
            const radioMecan = document.querySelector('input[name="ins-transmission"][value="Mecánico"]');
            if (instructor.transmission === 'Mecánico') {
                if (radioMecan) radioMecan.checked = true;
            } else {
                if (radioAutom) radioAutom.checked = true;
            }

            // Pre-fill photos
            if (instructor.instructor_photo_url) {
                document.getElementById('ins-photo-preview').src = instructor.instructor_photo_url;
                document.getElementById('ins-photo-preview').classList.remove('hidden');
                document.getElementById('ins-photo-zone').querySelector('.upload-placeholder').classList.add('hidden');
                document.getElementById('ins-photo-base64').value = instructor.instructor_photo_url;
                document.getElementById('ins-photo-remove').classList.remove('hidden');
            } else {
                clearUpload(null, 'ins-photo-input', 'ins-photo-preview', 'ins-photo-zone', 'ins-photo-base64');
            }

            if (instructor.license && instructor.license.license_photo_url) {
                document.getElementById('ins-lic-front-preview').src = instructor.license.license_photo_url;
                document.getElementById('ins-lic-front-preview').classList.remove('hidden');
                document.getElementById('ins-lic-front-zone').querySelector('.upload-placeholder').classList.add('hidden');
                document.getElementById('ins-lic-front-base64').value = instructor.license.license_photo_url;
                document.getElementById('ins-lic-front-remove').classList.remove('hidden');
            } else {
                clearUpload(null, 'ins-lic-front-input', 'ins-lic-front-preview', 'ins-lic-front-zone', 'ins-lic-front-base64');
            }

            if (instructor.license && instructor.license.license_photo_back_url) {
                document.getElementById('ins-lic-back-preview').src = instructor.license.license_photo_back_url;
                document.getElementById('ins-lic-back-preview').classList.remove('hidden');
                document.getElementById('ins-lic-back-zone').querySelector('.upload-placeholder').classList.add('hidden');
                document.getElementById('ins-lic-back-base64').value = instructor.license.license_photo_back_url;
                document.getElementById('ins-lic-back-remove').classList.remove('hidden');
            } else {
                clearUpload(null, 'ins-lic-back-input', 'ins-lic-back-preview', 'ins-lic-back-zone', 'ins-lic-back-base64');
            }

            // Toggle title and submit button texts to Edit Mode
            document.getElementById('instructor-form-title').innerText = "Editar Expediente de Instructor";
            document.getElementById('instructor-form-subtitle').innerText = `Modificando datos del instructor ${instructor.name} [ID: ${instructor.id}] en la base de datos.`;
            document.getElementById('instructor-submit-btn-text').innerText = "Guardar Cambios";
            document.getElementById('instructor-submit-btn-icon').className = "fa-solid fa-pen-to-square";

            // Switch to form tab
            switchTab('registro-instructor', true);
        }

        function editVehicle(vehicleId) {
            const vehicle = vehicles.find(v => v.id === vehicleId);
            if (!vehicle) return;

            editingVehicleId = vehicle.db_id;

            // Pre-fill form fields
            document.getElementById('veh-brand').value = vehicle.brand || '';
            document.getElementById('veh-plate').value = vehicle.plate || '';
            document.getElementById('veh-transmission').value = vehicle.transmission || 'Automático';

            // Toggle title and submit button texts to Edit Mode
            document.getElementById('vehicle-form-title').innerText = "Editar Vehículo";
            document.getElementById('vehicle-form-subtitle').innerText = `Modificando datos del vehículo ${vehicle.brand} [Placa: ${vehicle.plate}] en la base de datos.`;
            document.getElementById('vehicle-submit-btn-text').innerText = "Guardar Cambios";
            document.getElementById('vehicle-submit-btn-icon').className = "fa-solid fa-pen-to-square";

            // Switch to form tab
            switchTab('registro-vehiculo');
        }

        function populatePaymentStudentsDropdown() {
            const dropdown = document.getElementById('pay-student-id');
            if (dropdown) {
                dropdown.innerHTML = '<option value="">Selecciona un estudiante...</option>';
                // Sort students alphabetically by name for better UX
                const sortedStudents = [...students].sort((a, b) => a.name.localeCompare(b.name));
                sortedStudents.forEach(s => {
                    if (s.debt > 0) {
                        const opt = document.createElement('option');
                        opt.value = s.db_id;
                        opt.innerText = `${s.name} (${s.id}) - Deuda: $${s.debt}`;
                        dropdown.appendChild(opt);
                    }
                });
            }
        }

        function handlePaymentStudentChange() {
            const studentId = document.getElementById('pay-student-id').value;
            const helpBlock = document.getElementById('pay-student-debt-help');
            
            if (!studentId) {
                if (helpBlock) helpBlock.classList.add('hidden');
                return;
            }

            const student = students.find(s => s.db_id == studentId);
            if (student) {
                document.getElementById('pay-student-cost-val').innerText = `$${student.cost} USD`;
                document.getElementById('pay-student-paid-val').innerText = `$${student.paid} USD`;
                document.getElementById('pay-student-debt-val').innerText = `$${student.debt} USD`;
                
                // Set default max value for payment amount
                const amountInput = document.getElementById('pay-amount');
                if (amountInput) {
                    amountInput.max = student.debt;
                    amountInput.placeholder = `Máx: ${student.debt}`;
                }
                
                if (helpBlock) helpBlock.classList.remove('hidden');
            } else {
                if (helpBlock) helpBlock.classList.add('hidden');
            }
        }

        function renderPaymentsTable() {
            const th = 'px-4 py-3 border-r border-slate-800';
            renderDataTable({
                key: 'payments-history',
                headId: 'payment-movements-head',
                bodyId: 'payment-movements-rows',
                toolbarId: 'payment-movements-toolbar',
                pagerId: 'payment-movements-pager',
                thClass: th,
                rowClass: 'border-b border-slate-800/40 hover:bg-slate-800/20 transition duration-150',
                rows: () => payments,
                emptyText: 'No hay movimientos de abonos registrados',
                searchPlaceholder: 'Buscar por ID, estudiante, método o notas...',
                searchExtra: p => [p.notes],
                dateField: p => p.payment_date,
                filters: [
                    { id: 'method', label: 'Forma de Pago', value: p => p.payment_method, format: paymentMethodLabel },
                ],
                columns: [
                    { label: 'ID Abono', value: p => p.id, sortValue: p => p.db_id },
                    { label: 'Estudiante', value: p => p.student_name },
                    { label: 'Fecha Pago', value: p => p.payment_date },
                    { label: 'Forma de Pago', value: p => paymentMethodLabel(p.payment_method) },
                    { label: 'Monto', value: p => p.amount, thClass: `${th} text-right` },
                    { label: 'Deuda Restante', value: p => p.remaining_debt, thClass: `${th} text-right` },
                    { label: 'Acciones', thClass: 'px-4 py-3 text-center' },
                ],
                row: p => `
                    <td class="px-6 py-4 text-amber-500 font-bold font-mono">${p.id}</td>
                    <td class="px-6 py-4 font-semibold text-slate-200">${esc(p.student_name)}</td>
                    <td class="px-6 py-4 text-slate-400 font-mono text-xs">${esc(p.payment_date)}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-xs text-slate-300 font-medium">${esc(paymentMethodLabel(p.payment_method))}</span>
                    </td>
                    <td class="px-6 py-4 text-right font-extrabold text-emerald-400 font-mono">$${p.amount.toFixed(2)}</td>
                    <td class="px-6 py-4 text-right font-bold font-mono text-slate-400">$${p.remaining_debt.toFixed(2)}</td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="printReceipt(${p.db_id})"
                            class="bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-slate-950 p-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 mx-auto"
                            title="Imprimir Recibo PDF">
                            <i class="fa-solid fa-print"></i>
                            <span>Recibo</span>
                        </button>
                    </td>`,
                afterRender: () => {
                    const badge = document.getElementById('movements-count-badge');
                    if (badge) badge.textContent = `${payments.length} abonos`;
                },
            });
        }

        function printReceipt(paymentDbId) {
            window.open(`{{ url('payments') }}/${paymentDbId}/receipt`, '_blank');
        }

        function handlePaymentSubmit(e) {
            e.preventDefault();

            const student_id = document.getElementById('pay-student-id').value;
            const amount = parseFloat(document.getElementById('pay-amount').value);
            const payment_method = document.getElementById('pay-method').value;
            const payment_date = document.getElementById('pay-date').value;
            const notes = document.getElementById('pay-notes').value;

            if (!student_id) {
                showToast("Por favor selecciona un estudiante.", "warning");
                return;
            }

            const student = students.find(s => s.db_id == student_id);
            if (!student) return;

            if (amount <= 0) {
                showToast("El monto del abono debe ser mayor a cero.", "warning");
                return;
            }

            if (amount > student.debt) {
                showToast(`El monto no puede superar la deuda del estudiante ($${student.debt} USD).`, "warning");
                return;
            }

            const data = {
                student_id: parseInt(student_id),
                amount: amount,
                payment_method: payment_method,
                payment_date: payment_date,
                notes: notes
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Send fetch request
            fetch('{{ url('payments') }}', {
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
                    const newPayment = res.payment;
                    const updatedStudent = res.student;

                    // Add to payments array (at the beginning to keep sort order desc)
                    payments.unshift(newPayment);

                    // Update student in local list
                    const idx = students.findIndex(s => s.db_id === updatedStudent.db_id);
                    if (idx !== -1) {
                        students[idx].paid = updatedStudent.paid;
                        students[idx].debt = updatedStudent.debt;
                        students[idx].status = updatedStudent.status;
                    }

                    showToast(`¡Abono registrado con éxito! Folio: ${newPayment.id}`, "success");

                    // Logs Simulator Console
                    const consoleLogs = document.getElementById('console-logs');
                    consoleLogs.innerHTML += `
                        <p class="text-green-400">[DB_SUCCESS] Event: PaymentSubmit (Registro de Abono)</p>
                        <p class="text-slate-300">↳ Inserted Payment ID: ${newPayment.db_id} [Folio: ${newPayment.id}]</p>
                        <p class="text-slate-400">↳ Recibo de pago generado exitosamente.</p>
                        <p class="text-emerald-400">↳ Saldo actualizado del alumno: $${updatedStudent.debt} USD</p>
                        <p class="text-slate-500">----------------------------------------------------</p>
                    `;

                    // Reset form and UI
                    document.getElementById('payment-form').reset();
                    document.getElementById('pay-student-debt-help').classList.add('hidden');
                    
                    // Repopulate dropdown, update metrics, render sheets, render payments
                    populatePaymentStudentsDropdown();
                    updateDashboardMetrics();
                    renderSheetTable();
                    renderPaymentsTable();

                    // Print PDF Receipt automatically in new tab
                    setTimeout(() => {
                        printReceipt(newPayment.db_id);
                    }, 500);
                } else {
                    showToast("Error de validación: " + (res.message || "Campos incompletos."), "warning");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Error de red o de servidor al registrar abono.", "warning");
            });
        }

        window.onload = function () {
            updateDashboardMetrics();
            populateInstructorDropdown();
            populateVehicleDropdown();
            renderSheetTable();
            renderPaymentsTable();

            // Set up form reset listeners
            document.getElementById('student-form').addEventListener('reset', () => {
                editingStudentId = null;
                document.getElementById('student-form-title').innerText = "Ficha de Inscripción Digital";
                document.getElementById('student-form-subtitle').innerText = "Crea nuevos expedientes que se sincronizarán directamente con la base de datos.";
                document.getElementById('student-submit-btn-text').innerText = "Registrar Alumno";
                document.getElementById('student-submit-btn-icon').className = "fa-solid fa-save";
                setTimeout(() => {
                    updatePlanCost();
                }, 0);
            });

            document.getElementById('instructor-form').addEventListener('reset', () => {
                editingInstructorId = null;
                document.getElementById('instructor-form-title').innerText = "Ficha de Registro de Instructor";
                document.getElementById('instructor-form-subtitle').innerText = "Crea nuevos expedientes de instructores que se sincronizarán directamente con la base de datos.";
                document.getElementById('instructor-submit-btn-text').innerText = "Registrar Instructor";
                document.getElementById('instructor-submit-btn-icon').className = "fa-solid fa-save";
            });

            document.getElementById('vehicle-form').addEventListener('reset', () => {
                editingVehicleId = null;
                document.getElementById('vehicle-form-title').innerText = "Ficha de Registro de Vehículo";
                document.getElementById('vehicle-form-subtitle').innerText = "Crea nuevos expedientes de vehículos que se sincronizarán directamente con la base de datos.";
                document.getElementById('vehicle-submit-btn-text').innerText = "Registrar Vehículo";
                document.getElementById('vehicle-submit-btn-icon').className = "fa-solid fa-save";
            });
        };
    </script>
@endpush