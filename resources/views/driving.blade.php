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
    <div id="view-inscripcion" class="hidden space-y-4">
        <form id="student-form" onsubmit="handleStudentSubmit(event)" autocomplete="off">
            <!-- Header: name, flags and balance -->
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <div class="flex-1 min-w-[220px]">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500" id="student-form-title">Nuevo estudiante</p>
                    <h2 class="text-2xl font-black text-slate-100 uppercase leading-tight" id="sf-name">Nuevo estudiante</h2>
                    <p class="text-xs text-slate-500 mt-0.5" id="student-form-subtitle">Completa la ficha y presiona Guardar.</p>
                </div>
                <label class="sf-flag" title="Marcar al estudiante como problemático">
                    <input type="checkbox" id="stu-problematic" class="hidden" onchange="renderProblematicFlag()">
                    <i class="fa-regular fa-face-frown"></i> <span>Problemático</span>
                </label>
                <span id="sf-balance-badge" class="hidden px-3 py-1 rounded-full text-sm font-black"></span>
            </div>

            <!-- Action bar -->
            <div class="flex flex-wrap items-center gap-2 bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 mb-4">
                <button type="submit" id="student-submit-btn" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-4 py-2 rounded-lg text-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-save" id="student-submit-btn-icon"></i> <span id="student-submit-btn-text">Registrar Alumno</span>
                </button>
                <button type="button" class="sf-action sf-needs-student" onclick="studentFormAction('agenda')"><i class="fa-solid fa-calendar-plus"></i> Agendar clase</button>
                <button type="button" class="sf-action sf-needs-student" onclick="studentFormAction('road_test')"><i class="fa-solid fa-road"></i> Road Test</button>
                <button type="button" class="sf-action sf-needs-student" onclick="studentFormAction('pay')"><i class="fa-solid fa-dollar-sign"></i> Registrar pago</button>
                <button type="button" class="sf-action sf-needs-student" onclick="studentFormAction('invoice')"><i class="fa-solid fa-file-invoice"></i> Invoice</button>
                <button type="button" class="sf-action sf-needs-student" onclick="studentFormAction('email')"><i class="fa-solid fa-envelope"></i> Email</button>
                <button type="button" class="sf-action sf-needs-student" onclick="studentFormAction('text')"><i class="fa-solid fa-comment-sms"></i> Text</button>
                <span class="flex-1"></span>
                <button type="button" class="sf-action" onclick="switchTab('inscripcion')" title="Limpiar y registrar otro estudiante"><i class="fa-solid fa-user-plus"></i> Nuevo</button>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <!-- Tabs -->
                <div class="flex gap-1 px-4 pt-2 border-b border-slate-800 overflow-x-auto">
                    <button type="button" data-sf-tab="profile" onclick="switchStudentFormTab('profile')" class="sf-tab">Profile</button>
                    <button type="button" data-sf-tab="lessons" onclick="switchStudentFormTab('lessons')" class="sf-tab sf-needs-student">Lessons <span id="sf-lessons-count"></span></button>
                    <button type="button" data-sf-tab="payments" onclick="switchStudentFormTab('payments')" class="sf-tab sf-needs-student">Receipts <span id="sf-payments-count"></span></button>
                    <button type="button" data-sf-tab="notes" onclick="switchStudentFormTab('notes')" class="sf-tab sf-needs-student">Notes <span id="sf-notes-count"></span></button>
                </div>

                <!-- ===== PROFILE ===== -->
                <div id="sf-tab-profile" class="p-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-start">
                    <!-- Column 1: permit + name + address -->
                    <div class="sf-card">
                        <div class="sf-field">
                            <span class="sf-label">Permit Number</span>
                            <div class="flex items-center gap-2">
                                <input type="text" id="lic-number" class="sf-input" maxlength="50" placeholder="123-456-789">
                                <button type="button" onclick="copyToClipboard(document.getElementById('lic-number').value)" class="text-slate-500 hover:text-amber-500" title="Copiar"><i class="fa-regular fa-copy"></i></button>
                            </div>
                        </div>
                        <label class="sf-field"><span class="sf-label">Permit Expiry</span>
                            <input type="date" id="lic-expiration" class="sf-input" onchange="renderExpiryWarnings()">
                            <span id="sf-permit-expired" class="hidden sf-warn">Vencido</span></label>
                        <label class="sf-field"><span class="sf-label">Permit Class</span>
                            <select id="lic-class" class="sf-input">
                                <option value="">—</option>
                                <option value="Class D">Class D (Passenger)</option>
                                <option value="Class DJ">Class DJ (Junior Operator)</option>
                                <option value="Class E">Class E (For-Hire Taxi/Limo)</option>
                                <option value="Class M">Class M (Motorcycle)</option>
                                <option value="Commercial">Commercial (CDL)</option>
                            </select></label>
                        <label class="sf-field"><span class="sf-label">Certificate # (5 horas / MV-278)</span>
                            <input type="text" id="stu-certificate-number" class="sf-input" maxlength="50"></label>
                        <label class="sf-field"><span class="sf-label">Certificate Expiry</span>
                            <input type="date" id="stu-certificate-expiry" class="sf-input" onchange="renderExpiryWarnings()">
                            <span id="sf-certificate-expired" class="hidden sf-warn">Vencido</span></label>
                        <label class="sf-field"><span class="sf-label">Last Name <b>*</b></span>
                            <input type="text" id="stu-last-name" required maxlength="100" class="sf-input sf-upper" oninput="renderStudentFormName()"></label>
                        <label class="sf-field"><span class="sf-label">First Name <b>*</b></span>
                            <input type="text" id="stu-first-name" required maxlength="100" class="sf-input sf-upper" oninput="renderStudentFormName()"></label>
                        <label class="sf-field"><span class="sf-label">Middle Name</span>
                            <input type="text" id="stu-middle-name" maxlength="100" class="sf-input sf-upper" oninput="renderStudentFormName()"></label>
                        <label class="sf-field"><span class="sf-label">Suffix</span>
                            <input type="text" id="stu-suffix" maxlength="10" class="sf-input sf-upper" placeholder="Jr, Sr, III"></label>
                        <label class="sf-field"><span class="sf-label">Street Address <b>*</b></span>
                            <input type="text" id="stu-address" required maxlength="255" class="sf-input sf-upper"></label>
                        <label class="sf-field"><span class="sf-label">Apt. #</span>
                            <input type="text" id="stu-apt" maxlength="20" class="sf-input sf-upper"></label>
                        <label class="sf-field"><span class="sf-label">City <b>*</b></span>
                            <input type="text" id="stu-city" required maxlength="100" class="sf-input sf-upper"></label>
                        <label class="sf-field"><span class="sf-label">State <b>*</b></span>
                            <input type="text" id="stu-state" required maxlength="100" value="NY" class="sf-input sf-upper"></label>
                        <label class="sf-field"><span class="sf-label">Zip Code <b>*</b></span>
                            <input type="text" id="stu-zip-code" required maxlength="20" class="sf-input"></label>
                    </div>

                    <!-- Column 2: contact + personal -->
                    <div class="space-y-4">
                        <div class="sf-card">
                            <div class="sf-field">
                                <span class="sf-label">Mobile <b>*</b></span>
                                <div class="flex items-center gap-2">
                                    <input type="text" id="stu-phone" required maxlength="30" class="sf-input" placeholder="347-555-0199">
                                    <a id="sf-call-link" href="#" class="text-slate-500 hover:text-amber-500" title="Llamar"><i class="fa-solid fa-phone"></i></a>
                                </div>
                            </div>
                            <label class="sf-field"><span class="sf-label">Other Phone</span>
                                <input type="text" id="stu-other-phone" maxlength="30" class="sf-input"></label>
                            <label class="sf-field"><span class="sf-label">Other Phone Desc</span>
                                <input type="text" id="stu-other-phone-desc" maxlength="50" class="sf-input" placeholder="Casa, trabajo, mamá..."></label>
                            <label class="sf-field"><span class="sf-label">Primary Email <b>*</b></span>
                                <input type="email" id="stu-email" required maxlength="255" class="sf-input"></label>
                            <label class="sf-field"><span class="sf-label">Secondary Email</span>
                                <input type="email" id="stu-secondary-email" maxlength="255" class="sf-input"></label>
                            <label class="sf-field"><span class="sf-label">Parent Name</span>
                                <input type="text" id="stu-parent-name" maxlength="150" class="sf-input sf-upper"></label>
                            <label class="sf-field"><span class="sf-label">Parent Phone</span>
                                <input type="text" id="stu-parent-phone" maxlength="30" class="sf-input"></label>
                            <label class="sf-field"><span class="sf-label">High School Name</span>
                                <input type="text" id="stu-high-school" maxlength="150" class="sf-input sf-upper"></label>
                        </div>

                        <div class="sf-card">
                            <label class="sf-field"><span class="sf-label">Birth Date <span id="sf-age"></span> <b>*</b></span>
                                <input type="date" id="stu-birth-date" required class="sf-input" onchange="renderStudentAge()"></label>
                            <label class="sf-field"><span class="sf-label">Gender <b>*</b></span>
                                <select id="stu-gender" required class="sf-input">
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                    <option value="O">Other</option>
                                </select></label>
                            <label class="sf-field"><span class="sf-label">Restriction</span>
                                <input type="text" id="stu-restrictions" maxlength="100" class="sf-input sf-upper" placeholder="Ej. eye glasses"></label>
                            <label class="sf-field"><span class="sf-label">Endorsements</span>
                                <input type="text" id="stu-endorsements" maxlength="100" class="sf-input sf-upper"></label>
                            <label class="sf-field"><span class="sf-label">Eye Color</span>
                                <select id="stu-eye-color" class="sf-input">
                                    <option value="">—</option>
                                    <option value="BLK">Black (BLK)</option>
                                    <option value="BLU">Blue (BLU)</option>
                                    <option value="BRN">Brown (BRN)</option>
                                    <option value="GRN">Green (GRN)</option>
                                    <option value="HAZ">Hazel (HAZ)</option>
                                    <option value="GRY">Gray (GRY)</option>
                                </select></label>
                            <label class="sf-field"><span class="sf-label">Height</span>
                                <input type="text" id="stu-height" maxlength="10" class="sf-input" placeholder="5'08&quot;"></label>
                            <label class="sf-field sf-check">
                                <input type="checkbox" id="stu-organ-donor" value="1" class="accent-amber-500">
                                <span class="text-xs font-semibold text-slate-300">Organ Donor</span></label>
                            <label class="sf-field"><span class="sf-label">Contract #</span>
                                <input type="text" id="stu-contract-number" maxlength="30" class="sf-input"></label>
                            <div class="sf-field sf-pb">
                                <span class="sf-label">Transmission <b>*</b></span>
                                <div class="flex gap-4 pt-1 text-xs font-semibold text-slate-300">
                                    <label class="flex items-center gap-1.5 cursor-pointer"><input type="radio" name="stu-transmission" value="Automático" checked class="accent-amber-500"> Automático</label>
                                    <label class="flex items-center gap-1.5 cursor-pointer"><input type="radio" name="stu-transmission" value="Mecánico" class="accent-amber-500"> Mecánico</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: road test, pickup, package, instructor -->
                    <div class="space-y-4">
                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-road text-violet-400"></i> Road Test</span>
                                <button type="button" class="sf-icon-btn sf-needs-student" onclick="studentFormAction('road_test')" title="Agendar Road Test"><i class="fa-solid fa-plus"></i></button></h4>
                            <div id="sf-road-test" class="px-4 py-3 text-xs text-slate-500"></div>
                        </div>

                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-location-dot text-amber-500"></i> Preferred Pickup Location</span></h4>
                            <label class="sf-field"><span class="sf-label">Dirección donde se recoge al estudiante</span>
                                <input type="text" id="stu-pickup-location" maxlength="255" class="sf-input" oninput="renderPickupLinks()" placeholder="Igual a la dirección si se deja vacío"></label>
                            <div id="sf-pickup-links" class="flex gap-4 px-4 py-2 text-xs"></div>
                        </div>

                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-box-open text-amber-500"></i> Package</span></h4>
                            <label class="sf-field"><span class="sf-label">Paquete <b>*</b></span>
                                <select id="stu-plan" onchange="updatePlanCost()" required class="sf-input">
                                    <!-- Options generated by renderPlanOptions() from the package catalog -->
                                </select></label>
                            <div class="grid grid-cols-3 gap-2 px-4">
                                <label class="sf-field sf-tight"><span class="sf-label">Total ($)</span>
                                    <input type="number" id="stu-cost" readonly class="sf-input text-slate-400 cursor-not-allowed" value="0"></label>
                                <label class="sf-field sf-tight"><span class="sf-label"><span id="sf-paid-label">Pago inicial</span> <b>*</b></span>
                                    <input type="number" id="stu-paid" oninput="calculateInscripcionDebt()" required min="0" step="0.01" class="sf-input" placeholder="0"></label>
                                <label class="sf-field sf-tight"><span class="sf-label">Balance</span>
                                    <input type="number" id="stu-debt" readonly class="sf-input text-red-500 cursor-not-allowed" value="0"></label>
                            </div>
                            <div id="stu-lessons-summary" class="hidden px-4 py-3"></div>
                        </div>

                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-chalkboard-user text-amber-500"></i> Preferred Instructor</span></h4>
                            <label class="sf-field sf-pb"><span class="sf-label">Se propone al agendar clases</span>
                                <select id="stu-preferred-instructor" class="sf-input"></select></label>
                        </div>
                    </div>

                    <!-- Column 4: permit photos, student photo, last note, appointments -->
                    <div class="space-y-4">
                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-id-card text-amber-500"></i> Learner's Permit</span></h4>
                            <div class="p-3 space-y-3">
                                <div class="sf-upload group" id="lic-front-zone" onclick="document.getElementById('lic-front-input').click()">
                                    <input type="file" id="lic-front-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'lic-front-preview', 'lic-front-zone', 'lic-front-base64')">
                                    <input type="hidden" id="lic-front-base64" value="">
                                    <div class="upload-placeholder flex flex-col items-center gap-1">
                                        <i class="fa-solid fa-id-card text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                                        <span class="text-xs text-slate-400 font-medium">Subir permiso (frente)</span>
                                        <span class="text-[10px] text-slate-600">JPG, PNG o WEBP (máx. 4MB)</span>
                                    </div>
                                    <img id="lic-front-preview" src="" class="hidden w-full max-h-56 rounded-lg object-contain" alt="Permiso (frente)">
                                    <button type="button" onclick="clearUpload(event, 'lic-front-input', 'lic-front-preview', 'lic-front-zone', 'lic-front-base64')"
                                        class="sf-upload-remove hidden" id="lic-front-remove" title="Quitar"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                                <div class="sf-upload group" id="lic-back-zone" onclick="document.getElementById('lic-back-input').click()">
                                    <input type="file" id="lic-back-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'lic-back-preview', 'lic-back-zone', 'lic-back-base64')">
                                    <input type="hidden" id="lic-back-base64" value="">
                                    <div class="upload-placeholder flex flex-col items-center gap-1">
                                        <i class="fa-solid fa-id-card text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                                        <span class="text-xs text-slate-400 font-medium">Subir permiso (reverso)</span>
                                    </div>
                                    <img id="lic-back-preview" src="" class="hidden w-full max-h-56 rounded-lg object-contain" alt="Permiso (reverso)">
                                    <button type="button" onclick="clearUpload(event, 'lic-back-input', 'lic-back-preview', 'lic-back-zone', 'lic-back-base64')"
                                        class="sf-upload-remove hidden" id="lic-back-remove" title="Quitar"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-user text-amber-500"></i> Foto del estudiante</span></h4>
                            <div class="p-3">
                                <div class="sf-upload group" id="stu-photo-zone" onclick="document.getElementById('stu-photo-input').click()">
                                    <input type="file" id="stu-photo-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'stu-photo-preview', 'stu-photo-zone', 'stu-photo-base64')">
                                    <input type="hidden" id="stu-photo-base64" value="">
                                    <div class="upload-placeholder flex flex-col items-center gap-1">
                                        <i class="fa-solid fa-camera text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                                        <span class="text-xs text-slate-400 font-medium">Subir foto</span>
                                    </div>
                                    <img id="stu-photo-preview" src="" class="hidden max-h-40 rounded-lg object-contain" alt="Foto del estudiante">
                                    <button type="button" onclick="clearUpload(event, 'stu-photo-input', 'stu-photo-preview', 'stu-photo-zone', 'stu-photo-base64')"
                                        class="sf-upload-remove hidden" id="stu-photo-remove" title="Quitar"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-note-sticky text-amber-500"></i> Last Note</span>
                                <button type="button" class="sf-icon-btn sf-needs-student" onclick="switchStudentFormTab('notes')" title="Ver / agregar notas"><i class="fa-solid fa-plus"></i></button></h4>
                            <div id="sf-last-note" class="px-4 py-3 text-xs text-slate-500"></div>
                        </div>

                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-calendar-check text-amber-500"></i> Appointments <span id="sf-appointments-badge"></span></span>
                                <button type="button" class="sf-icon-btn sf-needs-student" onclick="switchStudentFormTab('lessons')" title="Ver todas"><i class="fa-solid fa-list"></i></button></h4>
                            <div id="sf-appointments" class="px-4 py-3 text-xs text-slate-500"></div>
                        </div>
                    </div>
                </div>

                <!-- ===== LESSONS / RECEIPTS / NOTES ===== -->
                <div id="sf-tab-lessons" class="hidden p-4"></div>
                <div id="sf-tab-payments" class="hidden p-4"></div>
                <div id="sf-tab-notes" class="hidden p-4">
                    <div class="max-w-3xl">
                        <textarea id="sf-note-body" rows="3" maxlength="2000" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-amber-500"
                            placeholder="Escribe una nota interna (ej. llamé para reagendar el Road Test, no contestó)..."></textarea>
                        <div class="flex justify-end mt-2">
                            <button type="button" onclick="addStudentNote()" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-4 py-2 rounded-lg text-xs transition">
                                <i class="fa-solid fa-plus"></i> Agregar nota
                            </button>
                        </div>
                        <div id="sf-notes-list" class="mt-4 space-y-2"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        .sf-card { background: rgb(var(--slate-900)); border: 1px solid rgb(var(--slate-800)); border-radius: .85rem; overflow: hidden; }
        #sf-tab-profile > .sf-card, #sf-tab-profile .sf-card { box-shadow: 0 1px 2px rgb(0 0 0 / .06); }
        .sf-card-title { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .75rem 1rem;
            border-bottom: 1px solid rgb(var(--slate-800)); font-size: .85rem; font-weight: 700; color: rgb(var(--slate-100)); }
        .sf-card-title > span { display: flex; align-items: center; gap: .5rem; }
        .sf-field { display: block; padding: .45rem 1rem 0; }
        .sf-label { display: block; font-size: .68rem; font-weight: 600; color: rgb(var(--slate-500)); }
        .sf-label b { color: rgb(var(--accent-500)); }
        .sf-input { width: 100%; background: transparent; border: 0; border-bottom: 1px solid rgb(var(--slate-700)); padding: .2rem 0 .35rem;
            font-size: .82rem; font-weight: 600; color: rgb(var(--slate-100)); outline: none; transition: border-color .15s; }
        .sf-input:focus { border-bottom-color: rgb(var(--accent-500)); }
        .sf-input::placeholder { color: rgb(var(--slate-600)); font-weight: 400; text-transform: none; }
        .sf-input option { background: rgb(var(--slate-900)); color: rgb(var(--slate-100)); }
        .sf-field.sf-tight { padding-left: 0; padding-right: 0; }
        .sf-field.sf-pb { padding-bottom: .75rem; }
        .sf-field.sf-check { display: flex; align-items: center; gap: .5rem; padding-top: .75rem; padding-bottom: .5rem; cursor: pointer; }
        .sf-upper { text-transform: uppercase; }
        .sf-warn.hidden { display: none; }
        .sf-warn { display: inline-block; margin-top: .2rem; font-size: .62rem; font-weight: 800; text-transform: uppercase; color: #f87171; }
        .sf-action { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem .75rem; border-radius: .6rem; font-size: .75rem; font-weight: 700;
            color: rgb(var(--slate-300)); transition: all .15s; }
        .sf-action:hover:not(:disabled) { background: rgb(var(--slate-800)); color: rgb(var(--accent-500)); }
        .sf-action:disabled, .sf-tab:disabled, .sf-icon-btn:disabled { opacity: .35; cursor: not-allowed; }
        .sf-icon-btn { width: 1.7rem; height: 1.7rem; border-radius: 999px; border: 1.5px solid rgb(var(--accent-500)); color: rgb(var(--accent-500));
            font-size: .7rem; display: inline-flex; align-items: center; justify-content: center; }
        .sf-icon-btn:hover:not(:disabled) { background: rgb(var(--accent-500)); color: rgb(var(--slate-950)); }
        .sf-tab { padding: .7rem 1rem; font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; white-space: nowrap;
            color: rgb(var(--slate-500)); border-bottom: 2px solid transparent; margin-bottom: -1px; }
        .sf-tab:hover:not(:disabled) { color: rgb(var(--slate-200)); }
        .sf-tab.active { color: rgb(var(--slate-950)); background: rgb(var(--accent-500)); border-radius: .4rem .4rem 0 0; }
        .sf-upload { position: relative; border: 2px dashed rgb(var(--slate-800)); background: rgb(var(--slate-950)); border-radius: .75rem; padding: .75rem;
            min-height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; text-align: center; }
        .sf-upload:hover { border-color: rgb(var(--accent-500) / .6); }
        .sf-upload-remove { position: absolute; top: .4rem; right: .4rem; background: rgb(239 68 68 / .15); color: #f87171; padding: .35rem .5rem;
            border-radius: .5rem; font-size: .7rem; }
        .sf-upload-remove:hover { background: #ef4444; color: #fff; }
        .sf-flag { display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .8rem; border-radius: 999px; font-size: .72rem; font-weight: 700;
            border: 1.5px solid rgb(var(--slate-700)); color: rgb(var(--slate-500)); cursor: pointer; user-select: none; }
        .sf-flag.on { border-color: #ef4444; color: #fff; background: #ef4444; }
    </style>

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

    <!-- ================= PANELS: AGENDA DE CLASES ================= -->
    <div id="view-agenda" class="hidden space-y-8">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- BOOKING FORM -->
            <div class="xl:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden h-fit">
                <div class="p-6 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-b border-slate-800">
                    <h3 class="text-base font-bold text-slate-100 flex items-center gap-2">
                        <i id="lesson-form-icon" class="fa-solid fa-calendar-plus text-amber-500"></i>
                        <span id="lesson-form-title">Agendar Clase de Manejo</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1" id="lesson-form-subtitle">Asigna instructor, fecha y hora según las clases del paquete del estudiante.</p>
                </div>

                <form id="lesson-form" class="p-6 space-y-5" onsubmit="handleLessonSubmit(event)">
                    <!-- Shown while rescheduling an existing lesson -->
                    <div id="lesson-edit-banner" class="hidden rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-slate-300 flex items-start justify-between gap-3">
                        <span id="lesson-edit-banner-text"></span>
                        <button type="button" onclick="cancelLessonEdit()" class="shrink-0 font-bold text-amber-500 hover:underline">Cancelar</button>
                    </div>

                    <!-- Type: driving lesson or road test -->
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tipo</span>
                        <div class="grid grid-cols-2 gap-2" id="lesson-type-group">
                            <label class="cursor-pointer">
                                <input type="radio" name="lesson-type" value="lesson" checked onchange="handleLessonTypeChange()" class="peer sr-only">
                                <span class="flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-slate-950">
                                    <i class="fa-solid fa-car"></i> Clase de manejo
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
                        <label for="lesson-student" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Estudiante <span class="text-amber-500">*</span></label>
                        <select id="lesson-student" required onchange="handleAgendaStudentChange()"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            <option value="">Selecciona un estudiante...</option>
                        </select>
                    </div>

                    <!-- Package status (filled by renderAgendaPackagePanel) -->
                    <div id="lesson-package-panel" class="hidden"></div>

                    <!-- Lesson details: disabled until the student has hours available -->
                    <fieldset id="lesson-details" disabled class="space-y-5 disabled:opacity-40">
                        <div>
                            <label for="lesson-instructor" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Instructor <span class="text-amber-500">*</span></label>
                            <select id="lesson-instructor" required onchange="renderAgendaBusySlots()"
                                class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                                <option value="">Selecciona un instructor...</option>
                            </select>
                            <p id="lesson-instructor-help" class="text-xs text-slate-500 mt-1.5 hidden"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="lesson-date" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Fecha <span class="text-amber-500">*</span></label>
                                <input type="date" id="lesson-date" required onchange="renderAgendaBusySlots()"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            </div>
                            <div>
                                <label for="lesson-time" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Hora <span class="text-amber-500">*</span></label>
                                <input type="time" id="lesson-time" required step="900"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="lesson-duration" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Duración <span class="text-amber-500">*</span></label>
                            <select id="lesson-duration" required
                                class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                                <!-- Options generated by renderDurationOptions() -->
                            </select>
                        </div>

                        <!-- Road test site (only for road tests) -->
                        <div id="lesson-location-box" class="hidden">
                            <label for="lesson-location" class="block text-xs font-bold uppercase tracking-wider text-violet-400 mb-2">Sitio del examen <span class="text-violet-400">*</span></label>
                            <input type="text" id="lesson-location" maxlength="255" oninput="renderLocationLinks()"
                                class="w-full bg-slate-950 border border-violet-500/40 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-violet-500 transition text-sm placeholder-slate-600"
                                placeholder="Ej: DMV Road Test Site, 1 Main St, Queens, NY">
                            <div id="lesson-location-links" class="flex flex-wrap gap-3 mt-2 text-xs"></div>
                            <p class="text-xs text-slate-500 mt-1.5">El Road Test no descuenta clases del paquete y deja al menos {{ \App\Models\Lesson::ROAD_TEST_GAP_MINUTES }} min libres antes y después.</p>
                        </div>

                        <!-- Instructor / student agenda for the chosen day -->
                        <div id="lesson-busy-slots" class="hidden"></div>

                        <div>
                            <label for="lesson-notes" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Notas</label>
                            <textarea id="lesson-notes" rows="2" maxlength="1000"
                                class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm placeholder-slate-600"
                                placeholder="Punto de encuentro, objetivos de la clase..."></textarea>
                        </div>

                        <button type="submit" id="lesson-submit-btn"
                            class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold py-3 rounded-xl transition flex items-center justify-center gap-2 text-sm">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span id="lesson-submit-text">Agendar Clase</span>
                        </button>
                    </fieldset>
                </form>
            </div>

            <!-- LESSONS LIST -->
            <div class="xl:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6 border-b border-slate-800 pb-4">
                    <h4 class="text-base font-bold text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-amber-500"></i> Clases Agendadas
                    </h4>
                    <span class="text-xs bg-amber-500/10 text-amber-500 px-3 py-1 rounded-full font-medium" id="lessons-count-badge">0 próximas</span>
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
                    <button type="button" onclick="calMove(-1)" class="w-8 h-8 rounded-lg hover:bg-slate-800 text-slate-300" title="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
                    <button type="button" onclick="calMove(1)" class="w-8 h-8 rounded-lg hover:bg-slate-800 text-slate-300" title="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
                <label class="relative flex items-center gap-2 font-bold text-slate-100 cursor-pointer">
                    <span id="cal-label"></span>
                    <i class="fa-solid fa-caret-down text-slate-400 text-xs"></i>
                    <input type="date" id="cal-date-input" onchange="calGoTo(this.value)" class="absolute inset-0 opacity-0 cursor-pointer" aria-label="Ir a la fecha">
                </label>
                <div class="ml-auto flex items-center gap-2">
                    <button type="button" onclick="calGoTo(todayLocal())" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-300 hover:bg-slate-800 uppercase">Hoy</button>
                    <div class="flex rounded-lg border border-slate-800 overflow-hidden text-xs font-bold uppercase">
                        <button type="button" id="cal-view-day" onclick="calSetView('day')" class="px-3 py-1.5">Día</button>
                        <button type="button" id="cal-view-week" onclick="calSetView('week')" class="px-3 py-1.5">Semana</button>
                    </div>
                </div>
            </div>

            <!-- Instructor filter + legend -->
            <div class="flex flex-wrap items-center gap-2 px-4 py-2.5 border-b border-slate-800 text-xs">
                <span class="font-bold uppercase tracking-wider text-slate-500 mr-1">Instructores:</span>
                <div id="cal-instructor-chips" class="flex flex-wrap gap-1.5"></div>
                <div class="ml-auto flex flex-wrap items-center gap-3 text-slate-400">
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded border border-slate-700 bg-slate-900"></span> Disponible</span>
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded border border-slate-700 cal-off"></span> Fuera de horario</span>
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded" style="background:#7c3aed"></span> Road Test</span>
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded" style="background:#64748b"></span> Completada</span>
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded" style="background:#dc2626"></span> No asistió</span>
                </div>
            </div>

            <p class="px-4 py-2 text-[11px] text-slate-500 border-b border-slate-800">
                <i class="fa-solid fa-hand-pointer text-amber-500"></i> Arrastra una clase programada para cambiar su hora, día o instructor.
                Haz clic en un espacio libre para agendar ahí, o en una clase para ver sus opciones.
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
                        <span id="package-form-title">Nuevo Paquete</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Cada clase de manejo (hora de servicio) dura <span class="font-bold text-slate-200">45 minutos</span>.</p>
                </div>

                <form id="package-form" class="p-6 space-y-5" onsubmit="handlePackageSubmit(event)">
                    <div id="package-edit-banner" class="hidden rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-slate-300 flex items-start justify-between gap-3">
                        <span id="package-edit-banner-text"></span>
                        <button type="button" onclick="resetPackageForm()" class="shrink-0 font-bold text-amber-500 hover:underline">Cancelar</button>
                    </div>

                    <div>
                        <label for="pkg-name" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nombre <span class="text-amber-500">*</span></label>
                        <input type="text" id="pkg-name" required maxlength="100"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm placeholder-slate-600"
                            placeholder="Ej: PRINCIPIANTE package 12 Lessons">
                    </div>

                    <div>
                        <label for="pkg-description" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Descripción</label>
                        <input type="text" id="pkg-description" maxlength="255"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm placeholder-slate-600"
                            placeholder="Qué incluye el paquete (opcional)">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="pkg-lessons" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Clases <span class="text-amber-500">*</span></label>
                            <input type="number" id="pkg-lessons" required min="0" max="200" step="1" value="1" oninput="updatePackageDurationHint()"
                                class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                        </div>
                        <div>
                            <label for="pkg-price" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Precio <span class="text-amber-500">*</span></label>
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
                        Disponible para la venta (activo)
                    </label>

                    <div id="pkg-existing-buyers" class="hidden rounded-xl border border-slate-800 bg-slate-950 p-3 text-xs text-slate-400 flex gap-2">
                        <i class="fa-solid fa-circle-info text-amber-500 mt-0.5"></i>
                        <span id="pkg-existing-buyers-text"></span>
                    </div>

                    <button type="submit" id="package-submit-btn"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold py-3 rounded-xl transition flex items-center justify-center gap-2 text-sm">
                        <i class="fa-solid fa-save"></i>
                        <span id="package-submit-text">Crear Paquete</span>
                    </button>
                </form>
            </div>

            <!-- PACKAGE LIST -->
            <div class="xl:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-2 border-b border-slate-800 pb-4">
                    <h4 class="text-base font-bold text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-amber-500"></i> Catálogo de Paquetes
                    </h4>
                    <span class="text-xs bg-amber-500/10 text-amber-500 px-3 py-1 rounded-full font-medium" id="packages-count-badge">0 activos</span>
                </div>
                <p class="text-xs text-slate-500 mb-4">Los cambios de precio o de clases solo aplican a las nuevas compras. Los estudiantes que ya compraron un paquete conservan el precio y las clases que pagaron.</p>

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
                    <!-- Working schedule: the agenda only allows lessons inside it -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Horario de Trabajo <span class="text-amber-500">*</span></label>
                        <div class="flex flex-wrap gap-2" id="ins-work-days">
                            @foreach (\App\Models\Instructor::DAY_NAMES as $dayNumber => $dayName)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="ins-work-day" value="{{ $dayNumber }}" class="peer sr-only" @checked($dayNumber <= 6)>
                                    <span class="inline-block px-3 py-1.5 rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-slate-950 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-500">{{ $dayName }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4 max-w-sm">
                            <div>
                                <label for="ins-work-start" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Desde</label>
                                <input type="time" id="ins-work-start" required value="08:00" step="900"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            </div>
                            <div>
                                <label for="ins-work-end" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Hasta</label>
                                <input type="time" id="ins-work-end" required value="17:00" step="900"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">En la Agenda solo se podrán programar clases dentro de este horario.</p>
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

        let packages = @json($packages);

        let lessons = @json($lessons);

        // Length of one driving lesson ("hora de servicio"), in minutes
        const LESSON_MINUTES = {{ \App\Models\Lesson::SLOT_MINUTES }};

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
            document.getElementById('view-agenda').classList.add('hidden');
            document.getElementById('view-calendario').classList.add('hidden');
            document.getElementById('view-paquetes').classList.add('hidden');
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
                sectionPre.innerText = "Estudiantes";
                sectionTitle.innerText = isEditing ? "Ficha del Estudiante" : "Registrar Estudiante";
                if (!isEditing) {
                    editingStudentId = null;
                    document.getElementById('student-form').reset();
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
            } else if (tabId === 'agenda') {
                sectionPre.innerText = "Programación";
                sectionTitle.innerText = "Agenda de Clases";
                cancelLessonEdit();
            } else if (tabId === 'calendario') {
                sectionPre.innerText = "Programación";
                sectionTitle.innerText = "Calendario de Clases";
                renderCalendar();
            } else if (tabId === 'paquetes') {
                sectionPre.innerText = "Catálogo";
                sectionTitle.innerText = "Paquetes";
                resetPackageForm();
                renderPackagesTable();
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
                        <td class="py-3.5 font-semibold text-slate-200"><button type="button" onclick="openStudentProfile(${s.db_id})" class="hover:text-amber-500 hover:underline text-left">${esc(s.name)}</button>${s.contract_number ? `<span class="block text-[10px] font-normal text-slate-500">Contrato ${esc(s.contract_number)}</span>` : ''}</td>
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

        // Fills the enrollment package select with the active catalog packages.
        // When editing, the student's current package is kept even if it is no longer sold.
        function renderPlanOptions(currentPlan = null, currentCost = 0) {
            const select = document.getElementById('stu-plan');
            if (!select) return;
            select.innerHTML = '';

            const active = packages.filter(p => p.is_active);
            if (currentPlan && !active.some(p => p.name === currentPlan)) {
                const opt = document.createElement('option');
                opt.value = currentPlan;
                opt.dataset.price = currentCost;
                opt.textContent = `${currentPlan} (paquete actual) - $${currentCost}`;
                select.appendChild(opt);
            }

            active.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.name;
                opt.dataset.price = p.price;
                opt.textContent = `${p.name} — ${p.lessons ? lessonsText(p.lessons * LESSON_MINUTES) : 'sin clases de manejo'} - $${p.price}`;
                select.appendChild(opt);
            });

            if (!select.options.length) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No hay paquetes activos (configúralos en "Paquetes")';
                select.appendChild(opt);
            }
        }

        function updatePlanCost() {
            const planSelector = document.getElementById('stu-plan');
            const selectedOption = planSelector.options[planSelector.selectedIndex];
            const price = parseFloat(selectedOption?.getAttribute('data-price')) || 0;

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
                suffix: document.getElementById('stu-suffix').value.trim(),
                apt: document.getElementById('stu-apt').value.trim(),
                other_phone: document.getElementById('stu-other-phone').value.trim(),
                other_phone_desc: document.getElementById('stu-other-phone-desc').value.trim(),
                secondary_email: document.getElementById('stu-secondary-email').value.trim(),
                parent_name: document.getElementById('stu-parent-name').value.trim(),
                parent_phone: document.getElementById('stu-parent-phone').value.trim(),
                high_school: document.getElementById('stu-high-school').value.trim(),
                restrictions: document.getElementById('stu-restrictions').value.trim(),
                endorsements: document.getElementById('stu-endorsements').value.trim(),
                certificate_number: document.getElementById('stu-certificate-number').value.trim(),
                certificate_expiry: document.getElementById('stu-certificate-expiry').value || null,
                pickup_location: document.getElementById('stu-pickup-location').value.trim(),
                preferred_instructor_id: Number(document.getElementById('stu-preferred-instructor').value) || null,
                is_problematic: document.getElementById('stu-problematic').checked ? 1 : 0,
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

                    updateDashboardMetrics();
                    renderSheetTable();

                    // Stay on the student's page (now saved) to keep working with it
                    editStudent(student.id);
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
            const work_days = [...document.querySelectorAll('input[name="ins-work-day"]:checked')].map(c => Number(c.value));
            const work_start = document.getElementById('ins-work-start').value;
            const work_end = document.getElementById('ins-work-end').value;
            if (!work_days.length) {
                showToast('Selecciona al menos un día de trabajo del instructor.', 'warning');
                return;
            }
            if (work_end <= work_start) {
                showToast('La hora de salida del instructor debe ser posterior a la de entrada.', 'warning');
                return;
            }

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
                work_days,
                work_start,
                work_end
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
                    tr.className = (typeof cfg.rowClass === 'function' ? cfg.rowClass(r) : cfg.rowClass) || '';
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
                        <td class="${SHEET_TD} text-slate-100 font-semibold"><button type="button" onclick="openStudentProfile(${s.db_id})" class="hover:text-amber-500 hover:underline text-left">${esc(s.name)}</button></td>
                        <td class="${SHEET_TD} text-slate-400">${esc(s.phone)}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(s.plan)}</td>
                        <td class="${SHEET_TD} text-slate-400 font-bold">$${s.cost}</td>
                        <td class="${SHEET_TD} text-slate-400">$${s.paid}</td>
                        <td class="${SHEET_TD} font-bold ${s.debt > 0 ? 'text-red-500' : 'text-green-500'}">$${s.debt}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(s.transmission)}</td>
                        <td class="${SHEET_TD}">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${s.status === 'Al Día' ? 'bg-green-500/10 text-green-400' : 'bg-amber-500/10 text-amber-500'}">${s.status}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center space-x-1 whitespace-nowrap">
                            <button onclick="openStudentProfile(${s.db_id})" class="bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="Ver ficha completa">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button onclick="openInvoice(${s.db_id})" class="bg-sky-500/10 text-sky-400 hover:bg-sky-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="Ver Invoice">
                                <i class="fa-solid fa-file-invoice"></i>
                            </button>
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
                        <td class="px-4 py-2.5 text-center space-x-1 whitespace-nowrap">
                            <button onclick="instructorPortalLink(${ins.db_id}, 'copy')" class="bg-sky-500/10 text-sky-400 hover:bg-sky-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="Copiar enlace 'Mis clases'">
                                <i class="fa-solid fa-link"></i>
                            </button>
                            <button onclick="instructorPortalLink(${ins.db_id}, 'send')" class="bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="Enviar enlace 'Mis clases' por correo/SMS">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                            <button onclick="instructorPortalLink(${ins.db_id}, 'regenerate')" class="bg-slate-500/10 text-slate-400 hover:bg-slate-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="Generar enlace nuevo (el anterior deja de funcionar)">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
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

            // Extra profile data
            const fields = {
                'stu-suffix': 'suffix', 'stu-apt': 'apt', 'stu-other-phone': 'other_phone', 'stu-other-phone-desc': 'other_phone_desc',
                'stu-secondary-email': 'secondary_email', 'stu-parent-name': 'parent_name', 'stu-parent-phone': 'parent_phone',
                'stu-high-school': 'high_school', 'stu-restrictions': 'restrictions', 'stu-endorsements': 'endorsements',
                'stu-certificate-number': 'certificate_number', 'stu-certificate-expiry': 'certificate_expiry',
                'stu-pickup-location': 'pickup_location',
            };
            Object.entries(fields).forEach(([id, key]) => { document.getElementById(id).value = student[key] || ''; });
            document.getElementById('stu-problematic').checked = !!student.is_problematic;
            renderPreferredInstructorOptions(student.preferred_instructor_id);

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
            renderPlanOptions(student.plan, student.cost || 0);
            document.getElementById('stu-plan').value = student.plan || '';
            document.getElementById('stu-cost').value = student.cost || 0;
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
            document.getElementById('student-form-title').innerText = `${student.id}${student.contract_number ? ' · Contrato ' + student.contract_number : ''}`;
            document.getElementById('student-form-subtitle').innerText = `Inscrito el ${student.date}${student.plan ? ' · ' + student.plan : ''}`;
            document.getElementById('student-submit-btn-text').innerText = "Guardar";
            document.getElementById('student-submit-btn-icon').className = "fa-solid fa-save";
            renderStudentFormExtras(student);

            // Switch to form tab
            switchTab('inscripcion', true);
            switchStudentFormTab('profile');
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
            const workDays = instructor.work_days && instructor.work_days.length ? instructor.work_days : [1, 2, 3, 4, 5, 6];
            document.querySelectorAll('input[name="ins-work-day"]').forEach(c => { c.checked = workDays.includes(Number(c.value)); });
            document.getElementById('ins-work-start').value = instructor.work_start || '08:00';
            document.getElementById('ins-work-end').value = instructor.work_end || '17:00';

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
                        <div class="flex items-center justify-center gap-1.5">
                            <button onclick="printReceipt(${p.db_id})"
                                class="bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-slate-950 p-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5"
                                title="Recibo de este pago">
                                <i class="fa-solid fa-print"></i>
                                <span>Recibo</span>
                            </button>
                            <button onclick="openInvoice(${p.student_id})"
                                class="bg-sky-500/10 text-sky-400 hover:bg-sky-500 hover:text-slate-950 p-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5"
                                title="Invoice del alumno (paquete, pagos y balance)">
                                <i class="fa-solid fa-file-invoice"></i>
                                <span>Invoice</span>
                            </button>
                        </div>
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

        // Lessons taken / scheduled / left for a student (shown in the enrollment form)
        function studentLessonStats(student) {
            const own = lessons.filter(l => l.student_id === student.db_id);
            const driving = own.filter(l => !isRoadTest(l));
            const minutes = status => driving.filter(l => l.status === status).reduce((sum, l) => sum + l.duration_minutes, 0);
            const total = (student.package_lessons || 0) * LESSON_MINUTES;
            const taken = minutes('completed');
            const noShow = minutes('no_show');
            const scheduled = minutes('scheduled');

            return {
                total, taken, noShow, scheduled,
                left: Math.max(0, total - taken - noShow - scheduled),
                roadTests: own.filter(l => isRoadTest(l) && l.status !== 'cancelled'),
            };
        }

        function renderStudentLessonSummary(student) {
            const box = document.getElementById('stu-lessons-summary');
            if (!box) return;
            if (!student) {
                box.classList.add('hidden');
                box.innerHTML = '';
                return;
            }

            const st = studentLessonStats(student);
            const count = min => Math.round(min / LESSON_MINUTES * 10) / 10;
            const tile = (label, value, cls, hint = '') => `
                <div class="rounded-xl border border-slate-800 bg-slate-950 p-3 text-center">
                    <p class="text-2xl font-black ${cls}">${value}</p>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mt-0.5">${label}</p>
                    ${hint ? `<p class="text-[10px] text-slate-600">${hint}</p>` : ''}
                </div>`;

            const roadTests = st.roadTests.length
                ? `<ul class="mt-3 space-y-1 text-xs">${st.roadTests.map(l => `
                        <li class="flex flex-wrap items-center gap-2 rounded-lg bg-violet-500/10 border border-violet-500/30 px-3 py-1.5">
                            <span class="bg-violet-600 text-white text-[10px] font-black px-2 py-0.5 rounded">ROAD TEST</span>
                            <span class="font-mono text-slate-200">${esc(l.date)} ${esc(l.time)}</span>
                            <span class="text-slate-400">${esc(lessonStatusLabel(l.status))}</span>
                            ${l.location ? `<span class="text-violet-300">${esc(l.location)}</span>` : ''}
                        </li>`).join('')}</ul>`
                : '';

            box.innerHTML = `
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400">Clases de Manejo</h4>
                    <button type="button" onclick="openAgendaForStudent(${student.db_id})" class="text-xs font-bold text-amber-500 hover:underline">
                        <i class="fa-solid fa-calendar-plus"></i> Agendar clase
                    </button>
                </div>
                ${st.total ? `
                <div class="grid grid-cols-2 gap-2">
                    ${tile('Compradas', count(st.total), 'text-slate-100', `de ${LESSON_MINUTES} min`)}
                    ${tile('Tomadas', count(st.taken), 'text-green-500', st.noShow ? `+ ${count(st.noShow)} no asistió` : '')}
                    ${tile('Agendadas', count(st.scheduled), 'text-amber-500', 'por tomar')}
                    ${tile('Le quedan', count(st.left), st.left ? 'text-sky-400' : 'text-red-500', 'sin agendar')}
                </div>` : `<p class="text-xs text-slate-500">${student.package_id ? 'El paquete del estudiante no incluye clases de manejo.' : 'El estudiante no tiene un paquete asignado.'}</p>`}
                ${roadTests}`;
            box.classList.remove('hidden');
        }

        function openAgendaForStudent(studentDbId, type = 'lesson') {
            switchTab('agenda');
            setLessonType(type);
            document.getElementById('lesson-student').value = studentDbId;

            // Propose the student's preferred instructor
            const student = students.find(s => s.db_id === Number(studentDbId));
            const insSelect = document.getElementById('lesson-instructor');
            if (student?.preferred_instructor_id && [...insSelect.options].some(o => Number(o.value) === student.preferred_instructor_id)) {
                insSelect.value = student.preferred_instructor_id;
            }
            handleAgendaStudentChange();
            if (insSelect.value) renderAgendaBusySlots();
        }

        // ---------- Student page: register / edit / view (same screen) ----------
        function getEditingStudent() {
            return editingStudentId ? students.find(s => s.db_id === editingStudentId) : null;
        }

        // Eye button / student name: open the student's page on a tab
        function openStudentProfile(studentDbId, tab = 'profile') {
            const student = students.find(s => s.db_id === Number(studentDbId));
            if (!student) return showToast('No se encontró el estudiante.', 'warning');
            editStudent(student.id);
            switchStudentFormTab(tab);
        }

        function switchStudentFormTab(tab) {
            if (tab !== 'profile' && !getEditingStudent()) tab = 'profile';
            ['profile', 'lessons', 'payments', 'notes'].forEach(t => {
                document.getElementById(`sf-tab-${t}`).classList.toggle('hidden', t !== tab);
            });
            document.querySelectorAll('[data-sf-tab]').forEach(b => b.classList.toggle('active', b.dataset.sfTab === tab));
        }

        function studentFormAction(action) {
            const student = getEditingStudent();
            if (!student) return;

            if (action === 'agenda') openAgendaForStudent(student.db_id);
            else if (action === 'road_test') openAgendaForStudent(student.db_id, 'road_test');
            else if (action === 'pay') {
                if (student.debt > 0) simulateCobro(student.id);
                else showToast('El estudiante no tiene saldo pendiente.', 'info');
            } else if (action === 'invoice') openInvoice(student.db_id);
            else if (action === 'email') {
                if (student.email) window.location.href = 'mailto:' + student.email;
            } else if (action === 'text') {
                if (student.phone) window.location.href = 'sms:' + String(student.phone).replace(/[^\d+]/g, '');
            }
        }

        function ageFrom(dateStr) {
            if (!dateStr) return null;
            const b = new Date(String(dateStr).slice(0, 10) + 'T00:00:00');
            if (isNaN(b)) return null;
            const t = new Date();
            let age = t.getFullYear() - b.getFullYear();
            if (t.getMonth() < b.getMonth() || (t.getMonth() === b.getMonth() && t.getDate() < b.getDate())) age--;
            return age;
        }

        function money(v) {
            return '$' + Number(v || 0).toFixed(2);
        }

        function copyToClipboard(text) {
            if (!text) return;
            navigator.clipboard?.writeText(text).then(() => showToast('Copiado: ' + text, 'success'));
        }

        function renderStudentFormName() {
            const name = ['stu-last-name', 'stu-first-name', 'stu-middle-name']
                .map(id => document.getElementById(id).value.trim()).filter(Boolean).join(' ');
            document.getElementById('sf-name').textContent = name || 'Nuevo estudiante';
        }

        function renderStudentAge() {
            const age = ageFrom(document.getElementById('stu-birth-date').value);
            document.getElementById('sf-age').textContent = age !== null && age >= 0 ? `(${age} years old)` : '';
        }

        function renderExpiryWarnings() {
            const today = todayLocal();
            [['lic-expiration', 'sf-permit-expired'], ['stu-certificate-expiry', 'sf-certificate-expired']].forEach(([input, warn]) => {
                const v = document.getElementById(input).value;
                document.getElementById(warn).classList.toggle('hidden', !(v && v < today));
            });
        }

        function renderPickupLinks() {
            const pickup = document.getElementById('stu-pickup-location').value.trim();
            const address = [document.getElementById('stu-address').value, document.getElementById('stu-city').value,
                document.getElementById('stu-state').value, document.getElementById('stu-zip-code').value].map(v => v.trim()).filter(Boolean).join(', ');
            document.getElementById('sf-pickup-links').innerHTML = mapLinks(pickup || address);
        }

        function renderProblematicFlag() {
            const box = document.getElementById('stu-problematic');
            box.closest('.sf-flag').classList.toggle('on', box.checked);
        }

        function renderPreferredInstructorOptions(selected = null) {
            const select = document.getElementById('stu-preferred-instructor');
            select.innerHTML = '<option value="">None</option>' + [...instructors]
                .sort((a, b) => a.name.localeCompare(b.name))
                .map(i => `<option value="${i.db_id}">${esc(i.name)}${i.status === 'Suspendido' ? ' (suspendido)' : ''}</option>`).join('');
            select.value = selected ? String(selected) : '';
        }

        // Everything on the student page that is not a form input: header, cards and the other tabs
        function renderStudentFormExtras(student) {
            const has = !!student;
            document.querySelectorAll('#view-inscripcion .sf-needs-student').forEach(b => { b.disabled = !has; });

            const badge = document.getElementById('sf-balance-badge');
            if (has) {
                badge.textContent = student.debt > 0 ? money(student.debt) : 'Al día';
                badge.className = 'px-3 py-1 rounded-full text-sm font-black ' + (student.debt > 0 ? 'bg-red-600 text-white' : 'bg-green-600 text-white');
            } else {
                badge.className = 'hidden';
            }

            document.getElementById('sf-paid-label').textContent = has ? 'Pagado' : 'Pago inicial';

            const phone = document.getElementById('stu-phone').value.trim();
            document.getElementById('sf-call-link').href = phone ? 'tel:' + phone.replace(/[^\d+]/g, '') : '#';

            renderStudentFormName();
            renderStudentAge();
            renderExpiryWarnings();
            renderPickupLinks();
            renderProblematicFlag();
            renderStudentLessonSummary(student);

            const ownLessons = has ? lessons.filter(l => l.student_id === student.db_id)
                .sort((a, b) => (b.date + b.time).localeCompare(a.date + a.time)) : [];
            const ownPayments = has ? payments.filter(p => p.student_id === student.db_id) : [];
            const notes = has ? (student.notes || []) : [];
            const count = n => n ? `(${n})` : '';
            document.getElementById('sf-lessons-count').textContent = count(ownLessons.length);
            document.getElementById('sf-payments-count').textContent = count(ownPayments.length);
            document.getElementById('sf-notes-count').textContent = count(notes.length);

            const today = todayLocal();
            const byStart = (a, b) => (a.date + a.time).localeCompare(b.date + b.time);
            const statusBadge = l => `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${(LESSON_STATUS[l.status] || {}).cls || ''}">${esc(lessonStatusLabel(l.status))}</span>`;

            // Road test card: next scheduled one, otherwise the last one
            const roadTest = ownLessons.filter(l => isRoadTest(l) && l.status === 'scheduled').sort(byStart)[0]
                || ownLessons.find(l => isRoadTest(l) && l.status !== 'cancelled');
            document.getElementById('sf-road-test').innerHTML = !has
                ? 'Guarda el estudiante para poder agendar su Road Test.'
                : roadTest ? `
                    <p class="sf-label">Appointment Time</p>
                    <div class="flex items-center justify-between gap-2"><p class="text-sm font-semibold text-slate-100 font-mono">${esc(roadTest.date)} ${esc(roadTest.time)}</p>${statusBadge(roadTest)}</div>
                    <p class="sf-label mt-2">Test Location</p>
                    <p class="text-sm font-semibold text-slate-100">${esc(roadTest.location || '—')}</p>
                    ${roadTest.location ? `<div class="flex gap-4 mt-1">${mapLinks(roadTest.location)}</div>` : ''}
                    <p class="sf-label mt-2">Instructor</p>
                    <p class="text-sm font-semibold text-slate-100">${esc(roadTest.instructor_name)}</p>`
                : 'Sin Road Test agendado.';

            // Appointments card: upcoming lessons
            const upcoming = ownLessons.filter(l => l.status === 'scheduled' && l.date >= today).sort(byStart);
            document.getElementById('sf-appointments-badge').innerHTML = upcoming.length
                ? `<span class="bg-green-600 text-white text-[10px] font-black rounded-full px-2 py-0.5">${upcoming.length}</span>` : '';
            document.getElementById('sf-appointments').innerHTML = !has ? 'Sin clases todavía.'
                : upcoming.length ? `<ul class="space-y-1.5">${upcoming.slice(0, 5).map(l => `
                    <li class="flex items-center justify-between gap-2 ${isRoadTest(l) ? 'text-violet-500 font-semibold' : 'text-slate-300'}">
                        <span class="font-mono">${esc(l.date)} ${esc(l.time)}</span>
                        <span class="truncate">${isRoadTest(l) ? 'ROAD TEST' : esc(l.instructor_name)}</span>
                    </li>`).join('')}</ul>${upcoming.length > 5 ? `<p class="mt-1 text-slate-500">y ${upcoming.length - 5} más...</p>` : ''}`
                : 'Sin clases próximas.';

            // Last note card
            const last = notes[0];
            document.getElementById('sf-last-note').innerHTML = !has ? 'Guarda el estudiante para agregar notas.'
                : last ? `<p class="text-[11px] text-slate-500">${esc(last.created_at)}${last.author ? ' · ' + esc(last.author) : ''}</p>
                          <p class="text-sm text-slate-200 uppercase whitespace-pre-line mt-0.5">${esc(last.body)}</p>`
                : 'Sin notas.';

            renderStudentNotes(notes);

            // Lessons tab
            document.getElementById('sf-tab-lessons').innerHTML = ownLessons.length ? `
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[10px] text-slate-500 uppercase tracking-wider border-b border-slate-800">
                            <tr><th class="py-2 pr-3">Fecha</th><th class="py-2 pr-3">Hora</th><th class="py-2 pr-3">Tipo</th><th class="py-2 pr-3">Instructor</th><th class="py-2 pr-3">Vehículo</th><th class="py-2 pr-3">Estado</th><th class="py-2">Notas / Lugar</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            ${ownLessons.map(l => `
                                <tr class="${isRoadTest(l) ? 'bg-violet-500/10' : ''}">
                                    <td class="py-2 pr-3 font-mono text-slate-200 whitespace-nowrap">${esc(l.date)}</td>
                                    <td class="py-2 pr-3 font-mono text-slate-400 whitespace-nowrap">${esc(l.time)}–${esc(l.end_time)}</td>
                                    <td class="py-2 pr-3 whitespace-nowrap">${isRoadTest(l)
                                        ? '<span class="bg-violet-600 text-white text-[10px] font-black px-2 py-0.5 rounded">ROAD TEST</span>'
                                        : `<span class="text-xs text-slate-400">${esc(lessonsText(l.duration_minutes))}</span>`}</td>
                                    <td class="py-2 pr-3 text-slate-300">${esc(l.instructor_name)}</td>
                                    <td class="py-2 pr-3 text-xs text-slate-500">${esc(l.vehicle || '—')}</td>
                                    <td class="py-2 pr-3">${statusBadge(l)}</td>
                                    <td class="py-2 text-xs text-slate-400">
                                        ${l.location ? `<span class="text-violet-300">${esc(l.location)}</span><span class="flex gap-3 mt-0.5">${mapLinks(l.location)}</span>` : ''}
                                        ${l.notes ? `<span class="block">${esc(l.notes)}</span>` : ''}
                                    </td>
                                </tr>`).join('')}
                        </tbody>
                    </table>
                </div>` : `<p class="text-sm text-slate-500 text-center py-8">El estudiante no tiene clases agendadas.</p>`;

            // Receipts tab
            if (!has) {
                document.getElementById('sf-tab-payments').innerHTML = '';
                return;
            }
            const initial = Math.max(0, Math.round((student.paid - ownPayments.reduce((sum, p) => sum + p.amount, 0)) * 100) / 100);
            document.getElementById('sf-tab-payments').innerHTML = `
                <div class="flex flex-wrap items-end gap-3 mb-5">
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 min-w-[120px]"><p class="text-[10px] uppercase font-bold text-slate-500">Total</p><p class="text-lg font-black text-slate-100">${money(student.cost)}</p></div>
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 min-w-[120px]"><p class="text-[10px] uppercase font-bold text-slate-500">Payments</p><p class="text-lg font-black text-green-500">${money(student.paid)}</p></div>
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 min-w-[120px]"><p class="text-[10px] uppercase font-bold text-slate-500">Balance</p><p class="text-lg font-black ${student.debt > 0 ? 'text-red-500' : 'text-green-500'}">${money(student.debt)}</p></div>
                    <span class="flex-1"></span>
                    <button type="button" onclick="studentFormAction('invoice')" class="sf-action"><i class="fa-solid fa-file-invoice"></i> Invoice</button>
                    <button type="button" onclick="studentFormAction('pay')" class="bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 px-3 py-2 rounded-lg text-xs font-bold transition"><i class="fa-solid fa-dollar-sign"></i> Registrar pago</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[10px] text-slate-500 uppercase tracking-wider border-b border-slate-800">
                            <tr><th class="py-2 pr-3">Fecha</th><th class="py-2 pr-3">Recibo</th><th class="py-2 pr-3">Método</th><th class="py-2 pr-3 text-right">Payment</th><th class="py-2 pr-3 text-right">Balance</th><th class="py-2"></th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            ${initial > 0 ? `
                                <tr><td class="py-2 pr-3 font-mono text-slate-400">${esc(student.date)}</td><td class="py-2 pr-3 text-slate-400" colspan="2">Pago inicial (inscripción)</td>
                                    <td class="py-2 pr-3 text-right font-bold text-green-500">${money(initial)}</td><td></td><td></td></tr>` : ''}
                            ${ownPayments.map(p => `
                                <tr>
                                    <td class="py-2 pr-3 font-mono text-slate-400">${esc(p.payment_date)}</td>
                                    <td class="py-2 pr-3 font-bold text-amber-500">${esc(p.id)}</td>
                                    <td class="py-2 pr-3 text-slate-300">${esc(paymentMethodLabel(p.payment_method))}${p.notes ? `<span class="block text-[11px] text-slate-500">${esc(p.notes)}</span>` : ''}</td>
                                    <td class="py-2 pr-3 text-right font-bold text-green-500">${money(p.amount)}</td>
                                    <td class="py-2 pr-3 text-right text-slate-400">${money(p.remaining_debt)}</td>
                                    <td class="py-2 text-right"><button type="button" onclick="printReceipt(${p.db_id})" class="text-xs font-bold text-sky-400 hover:underline"><i class="fa-solid fa-receipt"></i> Recibo</button></td>
                                </tr>`).join('')}
                            ${!initial && !ownPayments.length ? '<tr><td colspan="6" class="py-6 text-center text-xs text-slate-500">Sin pagos registrados.</td></tr>' : ''}
                        </tbody>
                    </table>
                </div>`;
        }

        // ---------- Student notes ----------
        function renderStudentNotes(notes) {
            document.getElementById('sf-notes-list').innerHTML = notes.length ? notes.map(n => `
                <div class="bg-slate-950 border border-slate-800 rounded-lg p-3">
                    <div class="flex justify-between gap-2 text-[11px] text-slate-500">
                        <span>${esc(n.created_at)}${n.author ? ' · ' + esc(n.author) : ''}</span>
                        <button type="button" onclick="deleteStudentNote(${n.db_id})" class="hover:text-red-400" title="Eliminar nota"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                    <p class="text-sm text-slate-200 whitespace-pre-line mt-1">${esc(n.body)}</p>
                </div>`).join('') : '<p class="text-xs text-slate-500">Sin notas.</p>';
        }

        function saveStudentNotes(student, res) {
            student.notes = res.notes;
            renderStudentFormExtras(student);
        }

        function addStudentNote() {
            const student = getEditingStudent();
            const box = document.getElementById('sf-note-body');
            const body = box.value.trim();
            if (!student || !body) return showToast('Escribe la nota primero.', 'warning');

            apiRequest(`{{ url('students') }}/${student.db_id}/notes`, 'POST', { body })
                .then(res => {
                    box.value = '';
                    saveStudentNotes(student, res);
                    showToast('Nota agregada.', 'success');
                })
                .catch(err => showToast(err.message, 'warning'));
        }

        function deleteStudentNote(noteId) {
            const student = getEditingStudent();
            if (!student || !confirm('¿Eliminar esta nota?')) return;

            apiRequest(`{{ url('students') }}/${student.db_id}/notes/${noteId}`, 'DELETE')
                .then(res => saveStudentNotes(student, res))
                .catch(err => showToast(err.message, 'warning'));
        }

        // Instructor's personal "Mis clases" link (marks lessons as completed, no login)
        function instructorPortalLink(instructorDbId, mode) {
            const ins = instructors.find(i => i.db_id === instructorDbId);
            if (!ins) return;
            if (mode === 'send' && !confirm(`¿Enviar a ${ins.name} su enlace "Mis clases" por correo/SMS?`)) return;
            if (mode === 'regenerate' && !confirm(`¿Generar un enlace nuevo para ${ins.name}? El enlace anterior dejará de funcionar y habrá que enviarle el nuevo.`)) return;

            apiRequest(`{{ url('instructors') }}/${instructorDbId}/portal-link`, 'POST', {
                send: mode !== 'copy',
                regenerate: mode === 'regenerate',
            }).then(res => {
                if (mode === 'copy' || mode === 'regenerate') {
                    // The clipboard API only works over https; otherwise show the link to copy by hand
                    const manual = () => prompt('Copia el enlace "Mis clases":', res.url);
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(res.url)
                            .then(() => showToast('Enlace "Mis clases" copiado. Pégalo en WhatsApp o SMS al instructor.', 'success'))
                            .catch(manual);
                    } else {
                        manual();
                    }
                }
                if (res.sent.length) showToast(`Enlace enviado a ${ins.name} por ${res.sent.join(' y ')}.`, 'success');
                if (res.failed) showToast(res.failed, 'warning');
            }).catch(err => showToast(err.message, 'warning'));
        }

        // Invoice: package bought, all the student's payments and the balance
        function openInvoice(studentDbId) {
            window.open(`{{ url('students') }}/${studentDbId}/invoice`, '_blank');
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

        // ================= AGENDA DE CLASES =================
        const LESSON_STATUS = {
            scheduled: { label: 'Programada', cls: 'bg-amber-500/10 text-amber-500' },
            completed: { label: 'Completada', cls: 'bg-green-500/10 text-green-400' },
            no_show:   { label: 'No asistió', cls: 'bg-red-500/10 text-red-400' },
            cancelled: { label: 'Cancelada', cls: 'bg-slate-800 text-slate-500' },
        };

        // Lesson being rescheduled (null = booking a new one)
        let editingLessonId = null;

        const LESSON_DURATIONS = @json(\App\Models\Lesson::DURATIONS);
        const ROAD_TEST_DURATIONS = @json(\App\Models\Lesson::ROAD_TEST_DURATIONS);
        const ROAD_TEST_GAP = {{ \App\Models\Lesson::ROAD_TEST_GAP_MINUTES }};

        function getLessonType() {
            return document.querySelector('input[name="lesson-type"]:checked')?.value || 'lesson';
        }

        function setLessonType(type) {
            document.querySelectorAll('input[name="lesson-type"]').forEach(r => { r.checked = r.value === type; });
            handleLessonTypeChange();
        }

        function isRoadTest(l) {
            return l.type === 'road_test';
        }

        // Duration options depend on the type (lessons: 45-min blocks; road test: appointment length)
        function renderDurationOptions() {
            const select = document.getElementById('lesson-duration');
            const roadTest = getLessonType() === 'road_test';
            const current = Number(select.value);
            const values = roadTest ? ROAD_TEST_DURATIONS : LESSON_DURATIONS;

            select.innerHTML = values.map(v => {
                const label = roadTest
                    ? formatMinutes(v)
                    : (v === LESSON_MINUTES ? `1 clase (${v} min)` : `${v / LESSON_MINUTES} clases seguidas (${formatMinutes(v)})`);
                return `<option value="${v}">${label}</option>`;
            }).join('');
            select.value = values.includes(current) ? current : (roadTest ? 60 : LESSON_MINUTES);
        }

        function handleLessonTypeChange() {
            const roadTest = getLessonType() === 'road_test';
            document.getElementById('lesson-location-box').classList.toggle('hidden', !roadTest);
            document.getElementById('lesson-location').required = roadTest;
            renderDurationOptions();
            setLessonFormMode(editingLessonId !== null);
            handleAgendaStudentChange();
        }

        // Google Maps / Apple Maps (iPhone) links for an address
        function mapLinks(address, cls = '') {
            if (!address) return '';
            const q = encodeURIComponent(address);
            return `<a href="https://www.google.com/maps/search/?api=1&query=${q}" target="_blank" rel="noopener" class="${cls} text-sky-400 hover:underline whitespace-nowrap"><i class="fa-solid fa-map-location-dot"></i> Google Maps</a>`
                 + `<a href="https://maps.apple.com/?q=${q}" target="_blank" rel="noopener" class="${cls} text-sky-400 hover:underline whitespace-nowrap"><i class="fa-brands fa-apple"></i> Apple Maps</a>`;
        }

        function renderLocationLinks() {
            const address = document.getElementById('lesson-location').value.trim();
            document.getElementById('lesson-location-links').innerHTML = mapLinks(address);
        }

        function getEditingLesson() {
            return editingLessonId ? lessons.find(l => l.db_id === editingLessonId) : null;
        }

        function lessonStatusLabel(status) {
            return (LESSON_STATUS[status] || { label: status }).label;
        }

        // "1 clase", "3 clases"
        function lessonsText(min) {
            const n = Math.round(min / LESSON_MINUTES * 10) / 10;
            return n === 1 ? '1 clase' : `${n} clases`;
        }

        function formatMinutes(min) {
            const h = Math.floor(min / 60), m = min % 60;
            if (!h) return `${m} min`;
            return m ? `${h} h ${m} min` : `${h} h`;
        }

        // Local (school) date as YYYY-MM-DD
        function todayLocal() {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        }

        function nowLocalTime() {
            const d = new Date();
            return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
        }

        function apiRequest(url, method, data) {
            return fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: data ? JSON.stringify(data) : undefined,
            }).then(async res => {
                const body = await res.json().catch(() => ({}));
                if (!res.ok || body.success === false) {
                    // Laravel validation errors come in "errors"; business-rule errors in "message"
                    const firstError = body.errors ? Object.values(body.errors)[0][0] : null;
                    throw new Error(firstError || body.message || 'Error de red o de servidor.');
                }
                return body;
            });
        }

        function studentRemainingMinutes(s) {
            return Math.max(0, (s.package_lessons || 0) * LESSON_MINUTES - (s.used_minutes || 0));
        }

        function initAgenda() {
            if (!document.getElementById('lesson-duration').options.length) renderDurationOptions();

            // Students (alphabetical)
            const stuSelect = document.getElementById('lesson-student');
            const selectedStudent = stuSelect.value;
            stuSelect.innerHTML = '<option value="">Selecciona un estudiante...</option>';
            [...students].sort((a, b) => a.name.localeCompare(b.name)).forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.db_id;
                opt.textContent = s.package_id
                    ? `${s.name} (${s.id}) — ${lessonsText(studentRemainingMinutes(s))} disponibles`
                    : `${s.name} (${s.id}) — sin paquete`;
                stuSelect.appendChild(opt);
            });
            stuSelect.value = selectedStudent;

            // Instructors (not suspended)
            const insSelect = document.getElementById('lesson-instructor');
            const selectedInstructor = insSelect.value;
            insSelect.innerHTML = '<option value="">Selecciona un instructor...</option>';
            instructors.filter(i => i.status !== 'Suspendido').forEach(i => {
                const opt = document.createElement('option');
                opt.value = i.db_id;
                opt.textContent = `${i.name} — ${i.shift}`;
                insSelect.appendChild(opt);
            });
            insSelect.value = selectedInstructor;

            const dateInput = document.getElementById('lesson-date');
            dateInput.min = todayLocal();
            if (!dateInput.value) dateInput.value = todayLocal();

            handleAgendaStudentChange();
            renderLessonsTable();
            if (calendarVisible()) renderCalendar();
        }

        function getAgendaStudent() {
            const id = document.getElementById('lesson-student').value;
            return id ? students.find(s => s.db_id == id) : null;
        }

        function handleAgendaStudentChange() {
            const student = getAgendaStudent();
            renderAgendaPackagePanel(student);

            const editing = getEditingLesson();
            const durationSelect = document.getElementById('lesson-duration');

            if (getLessonType() === 'road_test') {
                // Road tests don't use package lessons
                document.getElementById('lesson-details').disabled = !student;
                [...durationSelect.options].forEach(o => { o.disabled = false; });
            } else {
                const remaining = student && student.package_id
                    ? studentRemainingMinutes(student) + (editing ? editing.duration_minutes : 0)
                    : 0;
                document.getElementById('lesson-details').disabled = remaining <= 0;

                // Only allow durations that fit in the remaining lessons
                [...durationSelect.options].forEach(o => { o.disabled = Number(o.value) > remaining; });
                if (durationSelect.selectedOptions[0]?.disabled) {
                    const fit = [...durationSelect.options].filter(o => !o.disabled).pop();
                    if (fit) durationSelect.value = fit.value;
                }
            }

            renderAgendaBusySlots();
        }

        function renderAgendaPackagePanel(student) {
            const panel = document.getElementById('lesson-package-panel');
            if (!student) {
                panel.classList.add('hidden');
                panel.innerHTML = '';
                return;
            }
            panel.classList.remove('hidden');

            if (getLessonType() === 'road_test') {
                const tests = lessons.filter(l => isRoadTest(l) && l.student_id === student.db_id && l.status !== 'cancelled');
                panel.innerHTML = `
                    <div class="rounded-xl border border-violet-500/40 bg-violet-500/10 p-4 space-y-1">
                        <p class="text-sm font-semibold text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-flag-checkered text-violet-400"></i> Road Test de ${esc(student.name)}
                        </p>
                        <p class="text-xs text-slate-400">No descuenta clases del paquete. Road Tests registrados: <span class="font-bold text-violet-400">${tests.length}</span></p>
                    </div>`;
                return;
            }

            // No package: let the user assign one
            if (!student.package_id) {
                const activePackages = packages.filter(p => p.is_active);
                const options = activePackages.map(p =>
                    `<option value="${p.id}">${esc(p.name)} — ${p.lessons ? lessonsText(p.lessons * LESSON_MINUTES) : 'sin manejo'} — $${p.price}</option>`).join('');
                if (!activePackages.length) {
                    panel.innerHTML = `
                        <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4 space-y-2">
                            <p class="text-sm font-semibold text-slate-100 flex items-center gap-2">
                                <i class="fa-solid fa-box-open text-amber-500"></i> Este estudiante no tiene paquete
                            </p>
                            <p class="text-xs text-slate-400">No hay paquetes activos para asignar. Ve a <button type="button" onclick="switchTab('paquetes')" class="font-bold text-amber-500 hover:underline">Paquetes</button> y activa al menos uno.</p>
                        </div>`;
                    return;
                }
                panel.innerHTML = `
                    <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4 space-y-3">
                        <p class="text-sm font-semibold text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-box-open text-amber-500"></i> Este estudiante no tiene paquete
                        </p>
                        <p class="text-xs text-slate-400">${student.plan ? `Registro actual: "${esc(student.plan)}". ` : ''}Asígnale un paquete para poder agendar sus clases. El precio del paquete se sumará a su deuda, conservando lo que ya abonó ($${student.paid}).</p>
                        <select id="lesson-assign-package"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            ${options}
                        </select>
                        <button type="button" onclick="assignPackageToStudent()" id="lesson-assign-btn"
                            class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2 rounded-lg text-sm transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-box"></i> Asignar paquete
                        </button>
                    </div>`;
                return;
            }

            const totalMin = (student.package_lessons || 0) * LESSON_MINUTES;
            const used = student.used_minutes || 0;
            const remaining = studentRemainingMinutes(student);
            const pct = totalMin ? Math.min(100, Math.round(used / totalMin * 100)) : 100;

            let footer;
            if (totalMin === 0) {
                footer = `<p class="text-xs text-red-400 font-semibold"><i class="fa-solid fa-circle-info"></i> Este paquete no incluye clases de manejo.</p>`;
            } else if (remaining <= 0) {
                footer = `<p class="text-xs text-red-400 font-semibold"><i class="fa-solid fa-circle-info"></i> El estudiante ya usó todas las clases de su paquete.</p>`;
            } else {
                footer = `<p class="text-xs text-slate-400">Disponibles: <span class="font-bold text-green-500">${lessonsText(remaining)}</span> de ${LESSON_MINUTES} min</p>`;
            }

            panel.innerHTML = `
                <div class="rounded-xl border border-slate-800 bg-slate-950 p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Paquete</p>
                            <p class="text-sm font-semibold text-slate-100">${esc(student.plan)}</p>
                        </div>
                        <span class="text-xs font-bold text-amber-500 whitespace-nowrap">${lessonsText(totalMin)}</span>
                    </div>
                    ${totalMin ? `
                    <div>
                        <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                            <div class="h-full bg-amber-500 rounded-full" style="width: ${pct}%"></div>
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5">Usadas o agendadas: ${lessonsText(used)} de ${lessonsText(totalMin)}</p>
                    </div>` : ''}
                    ${footer}
                </div>`;
        }

        function assignPackageToStudent() {
            const student = getAgendaStudent();
            const packageId = document.getElementById('lesson-assign-package')?.value;
            if (!student || !packageId) return;

            const pkg = packages.find(p => p.id == packageId);
            if (!confirm(`¿Asignar "${pkg.name}" a ${student.name}?\nCosto del plan: $${pkg.price}`)) return;

            const btn = document.getElementById('lesson-assign-btn');
            btn.disabled = true;

            apiRequest(`{{ url('students') }}/${student.db_id}/package`, 'PUT', { package_id: Number(packageId) })
                .then(res => {
                    const idx = students.findIndex(s => s.db_id === res.student.db_id);
                    if (idx !== -1) students[idx] = res.student;
                    showToast(`Paquete asignado a ${res.student.name}.`, 'success');
                    initAgenda();
                    updateDashboardMetrics();
                    renderSheetTable();
                })
                .catch(err => {
                    showToast(err.message, 'warning');
                    btn.disabled = false;
                });
        }

        // Shows what the instructor and the student already have booked on the chosen day
        function renderAgendaBusySlots() {
            const box = document.getElementById('lesson-busy-slots');
            const instructorId = document.getElementById('lesson-instructor').value;
            const studentId = document.getElementById('lesson-student').value;
            const date = document.getElementById('lesson-date').value;

            const help = document.getElementById('lesson-instructor-help');
            const instructor = instructors.find(i => i.db_id == instructorId);
            if (instructor) {
                help.textContent = `Horario: ${instructor.shift} · Vehículo: ${instructor.vehicle}${instructor.plate && instructor.plate !== '-' ? ` [${instructor.plate}]` : ''}`;
                help.classList.remove('hidden');
            } else {
                help.classList.add('hidden');
            }

            if (!date || (!instructorId && !studentId)) {
                box.classList.add('hidden');
                return;
            }

            const dayLessons = lessons
                .filter(l => l.date === date && l.status !== 'cancelled' && l.db_id !== editingLessonId
                    && (l.instructor_id == instructorId || l.student_id == studentId))
                .sort((a, b) => a.time.localeCompare(b.time));

            const items = dayLessons.length
                ? dayLessons.map(l => {
                    const who = l.instructor_id == instructorId && l.student_id == studentId
                        ? 'Instructor y estudiante'
                        : (l.instructor_id == instructorId ? `Instructor · ${esc(l.student_name)}` : `Estudiante · ${esc(l.instructor_name)}`);
                    const rt = isRoadTest(l);
                    return `<li class="flex items-center justify-between gap-2 ${rt ? 'rounded-md bg-violet-500/15 px-2 py-1 -mx-2' : ''}"><span class="font-mono font-bold ${rt ? 'text-violet-300' : 'text-slate-200'}">${l.time}–${l.end_time}${rt ? ' <span class="font-sans text-[10px] font-black">ROAD TEST</span>' : ''}</span><span class="text-slate-500 truncate">${who}</span></li>`;
                }).join('')
                : '<li class="text-slate-500">Sin clases ese día. Todo el horario está libre.</li>';

            box.innerHTML = `
                <div class="rounded-xl border border-slate-800 bg-slate-950 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2"><i class="fa-regular fa-clock"></i> Horarios ocupados ese día</p>
                    <ul class="space-y-1 text-xs">${items}</ul>
                    ${dayLessons.some(isRoadTest) || getLessonType() === 'road_test' ? `<p class="text-[10px] text-violet-400 mt-2"><i class="fa-solid fa-circle-info"></i> Debe quedar al menos ${formatMinutes(ROAD_TEST_GAP)} libre entre un Road Test y otra clase.</p>` : ''}
                </div>`;
            box.classList.remove('hidden');
        }

        function handleLessonSubmit(e) {
            e.preventDefault();
            const student = getAgendaStudent();
            if (!student) return;

            const date = document.getElementById('lesson-date').value;
            const time = document.getElementById('lesson-time').value;
            if (date < todayLocal() || (date === todayLocal() && time < nowLocalTime())) {
                showToast('No se puede agendar una clase en una fecha u hora que ya pasó.', 'warning');
                return;
            }

            const data = {
                student_id: student.db_id,
                instructor_id: Number(document.getElementById('lesson-instructor').value),
                date,
                time,
                duration_minutes: Number(document.getElementById('lesson-duration').value),
                notes: document.getElementById('lesson-notes').value.trim() || null,
                type: getLessonType(),
                location: getLessonType() === 'road_test' ? document.getElementById('lesson-location').value.trim() : null,
            };

            const btn = document.getElementById('lesson-submit-btn');
            btn.disabled = true;

            const rescheduling = editingLessonId !== null;
            const request = rescheduling
                ? apiRequest(`{{ url('lessons') }}/${editingLessonId}`, 'PUT', data)
                : apiRequest('{{ url('lessons') }}', 'POST', data);

            request
                .then(res => {
                    if (rescheduling) {
                        const idx = lessons.findIndex(l => l.db_id === res.lesson.db_id);
                        if (idx !== -1) lessons[idx] = res.lesson;
                    } else {
                        lessons.push(res.lesson);
                    }
                    lessons.sort((a, b) => (a.date + a.time).localeCompare(b.date + b.time));
                    student.used_minutes = res.student.used_minutes;

                    const what = isRoadTest(res.lesson) ? 'Road Test' : 'Clase';
                    showToast(`${what} ${rescheduling ? 'reprogramado(a)' : 'agendado(a)'}: ${res.lesson.student_name} con ${res.lesson.instructor_name}, ${res.lesson.date} ${res.lesson.time}.`, 'success');
                    showLessonNotifications(res.notifications);
                    if (rescheduling) {
                        cancelLessonEdit();
                    } else {
                        document.getElementById('lesson-time').value = '';
                        document.getElementById('lesson-notes').value = '';
                        document.getElementById('lesson-location').value = '';
                        renderLocationLinks();
                        initAgenda();
                    }
                })
                .catch(err => showToast(err.message, 'warning'))
                .finally(() => { btn.disabled = false; });
        }

        function setLessonFormMode(rescheduling) {
            const roadTest = getLessonType() === 'road_test';
            document.getElementById('lesson-form-title').textContent = roadTest
                ? (rescheduling ? 'Reprogramar Road Test' : 'Agendar Road Test')
                : (rescheduling ? 'Reprogramar Clase' : 'Agendar Clase de Manejo');
            document.getElementById('lesson-form-subtitle').textContent = roadTest
                ? 'Examen de carretera: instructor, fecha, hora y sitio del examen.'
                : (rescheduling
                    ? 'Cambia instructor, fecha, hora o duración de la clase.'
                    : 'Asigna instructor, fecha y hora según las clases del paquete del estudiante.');
            document.getElementById('lesson-form-icon').className = `fa-solid ${rescheduling ? 'fa-pen-to-square' : (roadTest ? 'fa-flag-checkered' : 'fa-calendar-plus')} ${roadTest ? 'text-violet-400' : 'text-amber-500'}`;
            document.getElementById('lesson-submit-text').textContent = rescheduling ? 'Guardar Cambios' : (roadTest ? 'Agendar Road Test' : 'Agendar Clase');
            const submitBtn = document.getElementById('lesson-submit-btn');
            submitBtn.classList.toggle('bg-violet-600', roadTest);
            submitBtn.classList.toggle('hover:bg-violet-700', roadTest);
            submitBtn.classList.toggle('text-white', roadTest);
            submitBtn.classList.toggle('bg-amber-500', !roadTest);
            submitBtn.classList.toggle('hover:bg-amber-600', !roadTest);
            submitBtn.classList.toggle('text-slate-950', !roadTest);
            document.querySelectorAll('input[name="lesson-type"]').forEach(r => { r.disabled = rescheduling; });
            document.getElementById('lesson-student').disabled = rescheduling;
            document.getElementById('lesson-edit-banner').classList.toggle('hidden', !rescheduling);
        }

        function startLessonReschedule(dbId) {
            const lesson = lessons.find(l => l.db_id === dbId);
            if (!lesson || lesson.status !== 'scheduled') return;

            editingLessonId = dbId;
            setLessonType(isRoadTest(lesson) ? 'road_test' : 'lesson');
            initAgenda();

            document.getElementById('lesson-student').value = lesson.student_id;
            document.getElementById('lesson-location').value = lesson.location || '';
            renderLocationLinks();
            document.getElementById('lesson-instructor').value = lesson.instructor_id;
            document.getElementById('lesson-date').value = lesson.date < todayLocal() ? todayLocal() : lesson.date;
            document.getElementById('lesson-time').value = lesson.time;
            document.getElementById('lesson-notes').value = lesson.notes || '';

            document.getElementById('lesson-edit-banner-text').textContent =
                `Reprogramando ${lesson.id} de ${lesson.student_name}: actualmente ${lesson.date} ${lesson.time}–${lesson.end_time} con ${lesson.instructor_name}.`;
            setLessonFormMode(true);
            handleAgendaStudentChange();
            document.getElementById('lesson-duration').value = lesson.duration_minutes;

            document.getElementById('lesson-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelLessonEdit() {
            const wasEditing = editingLessonId !== null;
            editingLessonId = null;
            if (wasEditing) setLessonType('lesson');
            setLessonFormMode(false);
            document.getElementById('lesson-time').value = '';
            document.getElementById('lesson-notes').value = '';
            if (wasEditing) {
                document.getElementById('lesson-location').value = '';
                renderLocationLinks();
            }
            initAgenda();
        }

        function setLessonStatus(dbId, status) {
            const lesson = lessons.find(l => l.db_id === dbId);
            if (!lesson) return;

            const what = isRoadTest(lesson) ? 'el Road Test' : 'la clase';
            const question = isRoadTest(lesson) ? {
                completed: `¿Marcar como realizado el Road Test de ${lesson.student_name} (${lesson.date} ${lesson.time})?`,
                no_show: `¿Marcar que ${lesson.student_name} no se presentó al Road Test?`,
                cancelled: `¿Cancelar el Road Test de ${lesson.student_name} (${lesson.date} ${lesson.time})?`,
            }[status] : {
                completed: `¿Marcar como completada la clase de ${lesson.student_name} (${lesson.date} ${lesson.time})?`,
                no_show: `¿Marcar que ${lesson.student_name} no asistió? Las horas de la clase se descontarán del paquete.`,
                cancelled: `¿Cancelar la clase de ${lesson.student_name} (${lesson.date} ${lesson.time})? Las horas volverán a quedar disponibles.`,
            }[status];
            if (!confirm(question)) return;

            apiRequest(`{{ url('lessons') }}/${dbId}/status`, 'PUT', { status })
                .then(res => {
                    const idx = lessons.findIndex(l => l.db_id === dbId);
                    if (idx !== -1) lessons[idx] = res.lesson;
                    const student = students.find(s => s.db_id === res.student.db_id);
                    if (student) student.used_minutes = res.student.used_minutes;

                    showToast(`Clase ${res.lesson.id}: ${lessonStatusLabel(res.lesson.status)}.`, 'success');
                    showLessonNotifications(res.notifications);
                    if (editingLessonId === dbId) cancelLessonEdit(); else initAgenda();
                })
                .catch(err => showToast(err.message, 'warning'));
        }

        // Tells who received the calendar invitation / SMS and who could not be notified
        function showLessonNotifications(n) {
            if (!n) return;
            if (n.sent && n.sent.length) {
                showToast(`Notificado: ${n.sent.join(', ')}.`, 'info');
            }
            if (n.failed && n.failed.length) {
                showToast(`No se pudo notificar a: ${n.failed.join(', ')}. Revisa sus datos de contacto o la configuración de correo.`, 'warning');
            }
        }

        // ---------- Calendar: lessons of every instructor by week / day, drag & drop to reschedule ----------
        const CAL_SLOT_MIN = 15;     // grid step (minutes)
        const CAL_SLOT_PX = 22;      // height of one step
        const CAL_COLORS = ['#2563eb', '#16a34a', '#0891b2', '#ea580c', '#db2777', '#4f46e5', '#65a30d', '#ca8a04', '#0d9488', '#be123c'];
        const CAL_DAY_NAMES = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        const CAL_MONTHS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

        let calView = 'week';
        let calDate = todayLocal();
        let calSelectedId = null;     // lesson shown in the detail bar
        let calDrag = null;           // { lesson, grab, target }
        let calRangeFrom = 7 * 60;    // first minute shown in the grid
        let calHidden = new Set();    // instructor db_ids hidden by the filter
        try {
            calView = localStorage.getItem('nyds-cal-view') === 'day' ? 'day' : 'week';
            calHidden = new Set(JSON.parse(localStorage.getItem('nyds-cal-hidden') || '[]'));
        } catch (e) {}

        function calSave() {
            try {
                localStorage.setItem('nyds-cal-view', calView);
                localStorage.setItem('nyds-cal-hidden', JSON.stringify([...calHidden]));
            } catch (e) {}
        }

        function calParse(ymd) { const [y, m, d] = ymd.split('-').map(Number); return new Date(y, m - 1, d); }
        function calYmd(d) { return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`; }
        function calAddDays(ymd, n) { const d = calParse(ymd); d.setDate(d.getDate() + n); return calYmd(d); }
        function calMin(hhmm) { const [h, m] = hhmm.split(':').map(Number); return h * 60 + m; }
        function calHhmm(min) { return `${String(Math.floor(min / 60)).padStart(2, '0')}:${String(min % 60).padStart(2, '0')}`; }
        function calTime12(min) {
            const h = Math.floor(min / 60), m = min % 60;
            return `${h % 12 || 12}:${String(m).padStart(2, '0')} ${h < 12 ? 'AM' : 'PM'}`;
        }
        function calDayText(ymd) { const d = calParse(ymd); return `${CAL_DAY_NAMES[d.getDay()]} ${String(d.getMonth() + 1).padStart(2, '0')}/${String(d.getDate()).padStart(2, '0')}`; }
        function calNowMin() { const d = new Date(); return d.getHours() * 60 + d.getMinutes(); }

        function calDays() {
            if (calView === 'day') return [calDate];
            const start = calAddDays(calDate, -calParse(calDate).getDay());   // weeks start on Sunday
            return [...Array(7)].map((_, i) => calAddDays(start, i));
        }

        function calColor(ins) {
            const idx = [...instructors].sort((a, b) => a.db_id - b.db_id).findIndex(i => i.db_id === ins.db_id);
            return CAL_COLORS[Math.max(0, idx) % CAL_COLORS.length];
        }

        // Working hours of the instructor that day: null = doesn't work, [from, to] in minutes.
        // Instructors without a schedule can be booked at any time (same rule as the server).
        function calWorkHours(ins, ymd) {
            if (!ins.work_days || !ins.work_days.length || !ins.work_start || !ins.work_end) return [0, 24 * 60];
            const iso = calParse(ymd).getDay() || 7;
            return ins.work_days.includes(iso) ? [calMin(ins.work_start), calMin(ins.work_end)] : null;
        }

        function calActive(l) { return l.status !== 'cancelled'; }

        // Instructors in the calendar: not suspended, or with lessons in the visible days
        function calInstructors(days) {
            return instructors.filter(i => i.status !== 'Suspendido'
                || lessons.some(l => l.instructor_id === i.db_id && calActive(l) && days.includes(l.date)));
        }

        function calMove(step) {
            calDate = calAddDays(calDate, step * (calView === 'day' ? 1 : 7));
            renderCalendar();
        }

        function calGoTo(ymd) {
            if (!ymd) return;
            calDate = ymd;
            renderCalendar();
        }

        function calSetView(view) {
            calView = view;
            calSave();
            renderCalendar();
        }

        function calToggleInstructor(dbId) {
            calHidden.has(dbId) ? calHidden.delete(dbId) : calHidden.add(dbId);
            calSave();
            renderCalendar();
        }

        function renderCalendar() {
            const days = calDays();
            const all = calInstructors(days);
            const shown = all.filter(i => !calHidden.has(i.db_id));
            const today = todayLocal();

            // Toolbar
            const first = calParse(days[0]), last = calParse(days[days.length - 1]);
            document.getElementById('cal-label').textContent = calView === 'day'
                ? `${CAL_DAY_NAMES[first.getDay()]} ${first.getDate()} ${CAL_MONTHS[first.getMonth()]} ${first.getFullYear()}`
                : `${first.getDate()} ${first.getMonth() !== last.getMonth() ? CAL_MONTHS[first.getMonth()] + ' ' : ''}– ${last.getDate()} ${CAL_MONTHS[last.getMonth()]} ${last.getFullYear()}`;
            document.getElementById('cal-date-input').value = calDate;
            ['day', 'week'].forEach(v => {
                document.getElementById(`cal-view-${v}`).className = `px-3 py-1.5 ${calView === v ? 'bg-amber-500 text-slate-950' : 'text-slate-400 hover:bg-slate-800'}`;
            });

            document.getElementById('cal-instructor-chips').innerHTML = all.map(i => {
                const on = !calHidden.has(i.db_id);
                return `<button type="button" onclick="calToggleInstructor(${i.db_id})"
                    class="flex items-center gap-1.5 px-2 py-1 rounded-full border font-bold transition ${on ? 'border-slate-700 text-slate-200' : 'border-slate-800 text-slate-600 line-through'}">
                    <span class="w-2.5 h-2.5 rounded-full" style="background:${on ? calColor(i) : 'transparent'}; border:1px solid ${calColor(i)}"></span>${esc(i.name)}</button>`;
            }).join('') || '<span class="text-slate-500">No hay instructores registrados.</span>';

            const grid = document.getElementById('cal-grid');
            if (!shown.length) {
                grid.innerHTML = '<p class="text-sm text-slate-500 text-center py-16">Selecciona al menos un instructor para ver su calendario.</p>';
                renderCalendarDetail();
                return;
            }

            // Hours shown: from the earliest start to the latest end (working hours and lessons), whole hours
            let from = 24 * 60, to = 0;
            shown.forEach(i => days.forEach(d => {
                const wh = calWorkHours(i, d);
                if (wh && (wh[0] > 0 || wh[1] < 24 * 60)) { from = Math.min(from, wh[0]); to = Math.max(to, wh[1]); }
            }));
            lessons.filter(l => calActive(l) && days.includes(l.date) && shown.some(i => i.db_id === l.instructor_id)).forEach(l => {
                from = Math.min(from, calMin(l.time));
                to = Math.max(to, calMin(l.end_time) || 24 * 60);
            });
            if (from >= to) { from = 7 * 60; to = 19 * 60; }
            from = Math.floor(from / 60) * 60;
            to = Math.min(24 * 60, Math.ceil(to / 60) * 60);
            calRangeFrom = from;
            const height = (to - from) / CAL_SLOT_MIN * CAL_SLOT_PX;
            const hourPx = 60 / CAL_SLOT_MIN * CAL_SLOT_PX;

            const colMin = calView === 'day' ? 170 : 84;
            const cols = shown.length * days.length;
            let html = `<div class="grid" style="grid-template-columns: 64px repeat(${cols}, minmax(${colMin}px, 1fr)); min-width: ${64 + cols * colMin}px">`;

            // Header row 1: instructors
            html += `<div class="sticky left-0 top-0 z-30 bg-slate-900 border-b border-slate-800" style="height:44px"></div>`;
            shown.forEach(i => {
                html += `<div class="sticky top-0 z-20 flex items-center justify-center gap-2 px-2 text-white font-bold text-xs uppercase tracking-wide border-l border-white/20 truncate"
                    style="grid-column: span ${days.length}; height:44px; background:${calColor(i)}" title="${esc(i.shift || '')}">
                    ${i.instructor_photo_url ? `<img src="${esc(i.instructor_photo_url)}" class="w-7 h-7 rounded-full object-cover border border-white/40" alt="">` : '<i class="fa-solid fa-circle-user text-xl opacity-80"></i>'}
                    <span class="truncate">${esc(i.name)}</span></div>`;
            });

            // Header row 2: days
            html += `<div class="sticky left-0 z-30 bg-slate-900 border-b border-slate-800" style="top:44px; height:42px"></div>`;
            shown.forEach(i => days.forEach((d, idx) => {
                const dt = calParse(d);
                const off = !calWorkHours(i, d);
                html += `<div class="sticky z-20 bg-slate-900 border-b border-slate-800 px-2 py-1 leading-tight ${idx === 0 ? 'border-l-2 border-l-slate-600' : 'border-l border-l-slate-800'}" style="top:44px; height:42px">
                    <p class="text-[10px] font-bold uppercase ${off ? 'text-slate-600' : 'text-slate-400'}">${CAL_DAY_NAMES[dt.getDay()]}${off ? ' · libre' : ''}</p>
                    <p class="text-base font-black ${d === today ? 'text-amber-500' : 'text-slate-200'}">${dt.getDate()}</p></div>`;
            }));

            // Time gutter
            html += `<div class="sticky left-0 z-10 bg-slate-900 border-r border-slate-800" style="height:${height}px">`;
            for (let m = from; m < to; m += 60) {
                html += `<span class="absolute right-2 text-[10px] font-semibold text-slate-500" style="top:${(m - from) / CAL_SLOT_MIN * CAL_SLOT_PX + 2}px">${calTime12(m)}</span>`;
                html += `<span class="absolute right-2 text-[9px] text-slate-600" style="top:${(m + 30 - from) / CAL_SLOT_MIN * CAL_SLOT_PX + 2}px">${m + 30 < to ? `${(Math.floor(m / 60) % 12) || 12}:30` : ''}</span>`;
            }
            html += `</div>`;

            // Day columns
            const nowMin = calNowMin();
            const px = min => (min - from) / CAL_SLOT_MIN * CAL_SLOT_PX;
            shown.forEach(i => days.forEach((d, idx) => {
                const wh = calWorkHours(i, d);
                let layers = '';

                // Out of the working schedule
                if (!wh) {
                    layers += `<div class="cal-off absolute inset-x-0 pointer-events-none" style="top:0; height:${height}px"></div>`;
                } else {
                    if (wh[0] > from) layers += `<div class="cal-off absolute inset-x-0 pointer-events-none" style="top:0; height:${px(Math.min(wh[0], to))}px"></div>`;
                    if (wh[1] < to) layers += `<div class="cal-off absolute inset-x-0 pointer-events-none" style="top:${px(Math.max(wh[1], from))}px; bottom:0"></div>`;
                }

                // Time already passed
                if (d < today) {
                    layers += `<div class="absolute inset-0 bg-slate-950/40 pointer-events-none"></div>`;
                } else if (d === today && nowMin > from) {
                    layers += `<div class="absolute inset-x-0 top-0 bg-slate-950/40 pointer-events-none" style="height:${Math.min(height, px(nowMin))}px"></div>`;
                    if (nowMin < to) layers += `<div class="absolute inset-x-0 border-t-2 border-red-500 z-10 pointer-events-none" style="top:${px(nowMin)}px"></div>`;
                }

                // Lessons
                lessons.filter(l => l.instructor_id === i.db_id && l.date === d && calActive(l)).forEach(l => {
                    const start = calMin(l.time), end = calMin(l.end_time) || 24 * 60;
                    const movable = l.status === 'scheduled';
                    const roadTest = isRoadTest(l);
                    const bg = l.status === 'completed' ? '#64748b' : l.status === 'no_show' ? '#dc2626' : roadTest ? '#7c3aed' : calColor(i);
                    const icon = l.status === 'completed' ? 'fa-check' : l.status === 'no_show' ? 'fa-user-xmark' : roadTest ? 'fa-flag-checkered' : 'fa-car';
                    const h = Math.max(CAL_SLOT_PX, px(end) - px(start)) - 2;
                    layers += `<div ${movable ? `draggable="true" ondragstart="calDragStart(event, ${l.db_id})" ondragend="calDragEnd(event)"` : ''}
                        onclick="calSelect(event, ${l.db_id})"
                        class="cal-lesson absolute left-0.5 right-0.5 z-[5] rounded-md px-1.5 py-1 text-[10px] leading-tight text-white overflow-hidden shadow-md ${movable ? 'cursor-grab active:cursor-grabbing' : 'cursor-pointer'} ${calSelectedId === l.db_id ? 'ring-2 ring-amber-400' : ''}"
                        style="top:${px(start) + 1}px; height:${h}px; background:${bg}"
                        title="${esc(`${l.student_name} · ${calTime12(start)} – ${calTime12(end)} · ${roadTest ? 'Road Test' : 'Clase'} (${lessonStatusLabel(l.status)})${movable ? ' · Arrastra para mover' : ''}`)}">
                        <p class="font-bold whitespace-nowrap"><i class="fa-solid ${icon}"></i> ${calTime12(start)}</p>
                        <p class="font-bold uppercase break-words">${esc(l.student_name)}</p>
                        ${h > 44 ? `<p class="opacity-85 truncate">${roadTest ? 'Road Test' : `Clase ${l.duration_minutes} min`}</p>` : ''}
                    </div>`;
                });

                html += `<div class="cal-col relative ${idx === 0 ? 'border-l-2 border-l-slate-600' : 'border-l border-l-slate-800'}"
                    style="height:${height}px; background-size: 100% ${hourPx}px, 100% ${CAL_SLOT_PX}px"
                    data-ins="${i.db_id}" data-date="${d}"
                    ondragover="calDragOver(event)" ondrop="calDrop(event)"
                    onmousemove="calHover(event)" onmouseleave="calHideGhost(this)" onclick="calSlotClick(event)">
                    ${layers}
                    <div class="cal-ghost hidden absolute left-0.5 right-0.5 z-20 rounded-md border-2 border-dashed text-[10px] font-bold px-1.5 py-1 pointer-events-none"></div>
                </div>`;
            }));

            html += `</div>`;
            grid.innerHTML = html;
            renderCalendarDetail();
        }

        // Start minute for a pointer position inside a column (snapped to the grid)
        function calPointerMin(e, col, grab = 0) {
            const y = e.clientY - col.getBoundingClientRect().top - grab;
            return calRangeFrom + Math.round(y / CAL_SLOT_PX) * CAL_SLOT_MIN;
        }

        // Why a lesson can't go to that instructor / date / time (null = it looks free).
        // The server checks every rule again (package, road test gaps, etc.).
        function calSlotProblem(lesson, insId, date, start, minutes) {
            const end = start + minutes;
            const today = todayLocal();
            if (date < today || (date === today && start < calNowMin())) return 'No se puede agendar en una hora que ya pasó.';
            if (end > 24 * 60) return 'La clase debe terminar el mismo día.';

            const ins = instructors.find(i => i.db_id === insId);
            const wh = ins && calWorkHours(ins, date);
            if (!wh) return `${ins ? ins.name : 'El instructor'} no trabaja ese día.`;
            if (start < wh[0] || end > wh[1]) return `Fuera del horario de ${ins.name} (${ins.shift}).`;

            const overlaps = l => l.date === date && calActive(l) && (!lesson || l.db_id !== lesson.db_id)
                && calMin(l.time) < end && (calMin(l.end_time) || 24 * 60) > start;
            if (lessons.some(l => l.instructor_id === insId && overlaps(l))) return 'El instructor ya tiene una clase en ese horario.';
            if (lesson && lessons.some(l => l.student_id === lesson.student_id && overlaps(l))) return 'El estudiante ya tiene una clase en ese horario.';
            return null;
        }

        function calShowGhost(col, start, minutes, ok, text) {
            document.querySelectorAll('.cal-ghost').forEach(g => { if (g.parentElement !== col) g.classList.add('hidden'); });
            const ghost = col.querySelector('.cal-ghost');
            ghost.style.top = `${(start - calRangeFrom) / CAL_SLOT_MIN * CAL_SLOT_PX + 1}px`;
            ghost.style.height = `${minutes / CAL_SLOT_MIN * CAL_SLOT_PX - 2}px`;
            ghost.className = `cal-ghost absolute left-0.5 right-0.5 z-20 rounded-md border-2 border-dashed text-[10px] font-bold px-1.5 py-1 pointer-events-none overflow-hidden
                ${ok ? 'border-emerald-500 bg-emerald-500/15 text-emerald-400' : 'border-red-500 bg-red-500/15 text-red-400'}`;
            ghost.textContent = text;
        }

        function calHideGhost(col) {
            if (!calDrag) col.querySelector('.cal-ghost')?.classList.add('hidden');
        }

        // Hover on an empty slot: shows whether a lesson fits there
        function calHover(e) {
            if (calDrag) return;
            const col = e.currentTarget;
            if (e.target !== col) return calHideGhost(col);
            const start = Math.floor((e.clientY - col.getBoundingClientRect().top) / CAL_SLOT_PX) * CAL_SLOT_MIN + calRangeFrom;
            const problem = calSlotProblem(null, Number(col.dataset.ins), col.dataset.date, start, LESSON_MINUTES);
            if (problem) return calHideGhost(col);
            calShowGhost(col, start, LESSON_MINUTES, true, `+ ${calTime12(start)}`);
        }

        // Click on an empty slot: book a lesson there
        function calSlotClick(e) {
            const col = e.currentTarget;
            if (e.target !== col) return;
            const insId = Number(col.dataset.ins), date = col.dataset.date;
            const start = Math.floor((e.clientY - col.getBoundingClientRect().top) / CAL_SLOT_PX) * CAL_SLOT_MIN + calRangeFrom;
            const problem = calSlotProblem(null, insId, date, start, LESSON_MINUTES);
            if (problem) return showToast(problem, 'warning');

            const ins = instructors.find(i => i.db_id === insId);
            if (!confirm(`¿Agendar una clase con ${ins.name} el ${calDayText(date)} a las ${calTime12(start)}?`)) return;
            switchTab('agenda');
            setLessonType('lesson');
            document.getElementById('lesson-instructor').value = insId;
            document.getElementById('lesson-date').value = date;
            document.getElementById('lesson-time').value = calHhmm(start);
            handleAgendaStudentChange();
            document.getElementById('lesson-student').focus();
            showToast('Selecciona el estudiante para completar la clase.', 'info');
        }

        function calDragStart(e, dbId) {
            const lesson = lessons.find(l => l.db_id === dbId);
            if (!lesson || lesson.status !== 'scheduled') return e.preventDefault();
            const el = e.currentTarget;
            calDrag = { lesson, grab: e.clientY - el.getBoundingClientRect().top, target: null };
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', String(dbId));
            setTimeout(() => el.classList.add('opacity-40'), 0);
        }

        function calDragEnd(e) {
            e.currentTarget.classList.remove('opacity-40');
            calDrag = null;
            document.querySelectorAll('.cal-ghost').forEach(g => g.classList.add('hidden'));
        }

        function calDragOver(e) {
            if (!calDrag) return;
            e.preventDefault();
            const col = e.currentTarget;
            const l = calDrag.lesson;
            const minutes = l.duration_minutes;
            const insId = Number(col.dataset.ins), date = col.dataset.date;
            const start = calPointerMin(e, col, calDrag.grab);
            const problem = calSlotProblem(l, insId, date, start, minutes);
            calDrag.target = { insId, date, start, problem };
            e.dataTransfer.dropEffect = problem ? 'none' : 'move';
            calShowGhost(col, start, minutes, !problem, problem ? problem : `${calTime12(start)} – ${calTime12(start + minutes)}`);
        }

        function calDrop(e) {
            e.preventDefault();
            if (!calDrag || !calDrag.target) return;
            const { lesson: l, target: t } = calDrag;
            calDrag = null;
            document.querySelectorAll('.cal-ghost').forEach(g => g.classList.add('hidden'));

            if (t.problem) return showToast(t.problem, 'warning');
            if (t.insId === l.instructor_id && t.date === l.date && calHhmm(t.start) === l.time) return;

            const ins = instructors.find(i => i.db_id === t.insId);
            const what = isRoadTest(l) ? 'el Road Test' : 'la clase';
            const change = t.insId !== l.instructor_id ? ` con ${ins.name} (antes ${l.instructor_name})` : '';
            if (!confirm(`¿Mover ${what} de ${l.student_name}?\n\nDe: ${calDayText(l.date)} ${calTime12(calMin(l.time))}\nA:  ${calDayText(t.date)} ${calTime12(t.start)}${change}\n\nSe notificará al estudiante y al instructor.`)) return;

            apiRequest(`{{ url('lessons') }}/${l.db_id}`, 'PUT', {
                instructor_id: t.insId,
                date: t.date,
                time: calHhmm(t.start),
                duration_minutes: l.duration_minutes,
                location: l.location,
                notes: l.notes,
            }).then(res => {
                const idx = lessons.findIndex(x => x.db_id === res.lesson.db_id);
                if (idx !== -1) lessons[idx] = res.lesson;
                lessons.sort((a, b) => (a.date + a.time).localeCompare(b.date + b.time));
                const student = students.find(s => s.db_id === res.student.db_id);
                if (student) student.used_minutes = res.student.used_minutes;

                showToast(`${isRoadTest(res.lesson) ? 'Road Test' : 'Clase'} movida: ${res.lesson.student_name}, ${calDayText(res.lesson.date)} ${calTime12(calMin(res.lesson.time))} con ${res.lesson.instructor_name}.`, 'success');
                showLessonNotifications(res.notifications);
                calSelectedId = res.lesson.db_id;
                initAgenda();   // refreshes the agenda and the calendar
            }).catch(err => {
                showToast(err.message, 'warning');
                renderCalendar();
            });
        }

        function calSelect(e, dbId) {
            e.stopPropagation();
            calSelectedId = calSelectedId === dbId ? null : dbId;
            renderCalendar();
        }

        // Bar with the selected lesson's data and actions
        function renderCalendarDetail() {
            const box = document.getElementById('cal-detail');
            const l = calSelectedId ? lessons.find(x => x.db_id === calSelectedId) : null;
            if (!l || !calActive(l)) {
                calSelectedId = null;
                box.classList.add('hidden');
                box.innerHTML = '';
                return;
            }
            const student = students.find(s => s.db_id === l.student_id);
            const roadTest = isRoadTest(l);
            const scheduled = l.status === 'scheduled';
            const btn = 'px-3 py-1.5 rounded-lg text-xs font-bold transition';
            box.classList.remove('hidden');
            box.innerHTML = `
                <div class="px-4 py-3 flex flex-wrap items-center gap-x-6 gap-y-2 bg-slate-950/40 text-sm">
                    <div>
                        <p class="font-bold text-slate-100">${esc(l.student_name)} ${roadTest ? '<span class="ml-1 text-[10px] bg-violet-600 text-white px-1.5 py-0.5 rounded">ROAD TEST</span>' : ''}</p>
                        <p class="text-xs text-slate-400">${calDayText(l.date)} · ${calTime12(calMin(l.time))} – ${calTime12(calMin(l.end_time))} · ${esc(l.instructor_name)}${l.vehicle ? ' · ' + esc(l.vehicle) : ''}</p>
                        ${student?.phone ? `<p class="text-xs text-slate-400"><i class="fa-solid fa-phone"></i> ${esc(student.phone)}</p>` : ''}
                        ${roadTest && l.location ? `<p class="text-xs text-slate-400"><i class="fa-solid fa-location-dot"></i> ${esc(l.location)}</p>` : ''}
                        ${l.notes ? `<p class="text-xs text-slate-500"><i class="fa-solid fa-note-sticky"></i> ${esc(l.notes)}</p>` : ''}
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-800 text-slate-300">${lessonStatusLabel(l.status)}</span>
                    <div class="ml-auto flex flex-wrap gap-1.5">
                        <button type="button" onclick="openStudentProfile(${l.student_id})" class="${btn} bg-slate-800 text-slate-300 hover:bg-slate-700"><i class="fa-solid fa-eye"></i> Estudiante</button>
                        ${scheduled ? `
                            <button type="button" onclick="switchTab('agenda'); startLessonReschedule(${l.db_id})" class="${btn} bg-amber-500/10 text-amber-500 hover:bg-amber-500 hover:text-slate-950"><i class="fa-solid fa-pen"></i> Editar</button>
                            <button type="button" onclick="setLessonStatus(${l.db_id}, 'completed')" class="${btn} bg-green-500/10 text-green-400 hover:bg-green-500 hover:text-slate-950"><i class="fa-solid fa-check"></i> Completada</button>
                            <button type="button" onclick="setLessonStatus(${l.db_id}, 'no_show')" class="${btn} bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950"><i class="fa-solid fa-user-xmark"></i> No asistió</button>
                            <button type="button" onclick="setLessonStatus(${l.db_id}, 'cancelled')" class="${btn} bg-slate-800 text-slate-400 hover:bg-red-600 hover:text-white"><i class="fa-solid fa-ban"></i> Cancelar</button>` : ''}
                        <button type="button" onclick="calSelectedId = null; renderCalendar()" class="${btn} text-slate-500 hover:text-slate-200" title="Cerrar"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>`;
        }

        function calendarVisible() {
            return !document.getElementById('view-calendario').classList.contains('hidden');
        }

        function renderLessonsTable() {
            const th = 'px-4 py-3';
            const actionBtn = (dbId, status, icon, title, cls) =>
                `<button onclick="setLessonStatus(${dbId}, '${status}')" title="${title}" class="${cls} px-2 py-1 rounded text-[10px] font-bold transition hover:text-slate-950"><i class="fa-solid ${icon}"></i></button>`;

            renderDataTable({
                key: 'lessons',
                headId: 'lessons-head',
                bodyId: 'lessons-rows',
                toolbarId: 'lessons-toolbar',
                pagerId: 'lessons-pager',
                thClass: th,
                rowClass: l => isRoadTest(l)
                    ? 'border-b border-violet-500/30 bg-violet-500/10 hover:bg-violet-500/20 border-l-4 border-l-violet-500 transition duration-150'
                    : 'border-b border-slate-800/40 hover:bg-slate-800/20 transition duration-150',
                rows: () => lessons,
                emptyText: 'No hay clases agendadas',
                searchPlaceholder: 'Buscar por estudiante, instructor, vehículo o notas...',
                searchExtra: l => [l.notes, l.vehicle, l.location, isRoadTest(l) ? 'road test' : ''],
                dateField: l => l.date,
                filters: [
                    { id: 'type', label: 'Tipo', value: l => l.type, format: t => t === 'road_test' ? 'Road Test' : 'Clase de manejo' },
                    { id: 'status', label: 'Estado', value: l => l.status, format: lessonStatusLabel },
                    { id: 'instructor', label: 'Instructor', value: l => l.instructor_name },
                ],
                columns: [
                    { label: 'Fecha', value: l => l.date, sortValue: l => l.date + l.time },
                    { label: 'Hora', value: l => `${l.time}–${l.end_time}` },
                    { label: 'Estudiante', value: l => l.student_name },
                    { label: 'Instructor', value: l => l.instructor_name },
                    { label: 'Duración', value: l => l.duration_minutes },
                    { label: 'Estado', value: l => lessonStatusLabel(l.status) },
                    { label: 'Acciones', thClass: `${th} text-center` },
                ],
                row: l => {
                    const st = LESSON_STATUS[l.status] || { label: l.status, cls: 'bg-slate-800 text-slate-400' };
                    const actions = l.status === 'scheduled'
                        ? `<button onclick="startLessonReschedule(${l.db_id})" title="Reprogramar" class="bg-amber-500/10 text-amber-500 hover:bg-amber-500 px-2 py-1 rounded text-[10px] font-bold transition hover:text-slate-950"><i class="fa-solid fa-pen-to-square"></i></button>`
                          + actionBtn(l.db_id, 'completed', 'fa-check', 'Marcar como completada', 'bg-green-500/10 text-green-400 hover:bg-green-500')
                          + actionBtn(l.db_id, 'no_show', 'fa-user-xmark', 'No asistió', 'bg-red-500/10 text-red-400 hover:bg-red-500')
                          + actionBtn(l.db_id, 'cancelled', 'fa-ban', 'Cancelar clase', 'bg-slate-800 text-slate-400 hover:bg-amber-500')
                        : '<span class="text-slate-600 text-xs">—</span>';
                    return `
                        <td class="px-4 py-3 font-mono text-xs text-slate-300 whitespace-nowrap">${esc(l.date)}</td>
                        <td class="px-4 py-3 font-mono text-xs font-bold text-slate-100 whitespace-nowrap">${esc(l.time)}–${esc(l.end_time)}</td>
                        <td class="px-4 py-3 font-semibold text-slate-200">${isRoadTest(l) ? '<span class="inline-flex items-center gap-1 bg-violet-600 text-white text-[10px] font-black px-2 py-0.5 rounded mb-1"><i class="fa-solid fa-flag-checkered"></i> ROAD TEST</span><br>' : ''}${esc(l.student_name)}${isRoadTest(l) && l.location ? `<span class="block text-[11px] font-normal text-violet-300 mt-0.5"><i class="fa-solid fa-location-dot"></i> ${esc(l.location)}</span><span class="flex gap-3 text-[10px] font-normal mt-0.5">${mapLinks(l.location)}</span>` : ''}${l.notes ? `<span class="block text-[10px] font-normal text-slate-500 italic truncate max-w-[14rem]" title="${esc(l.notes)}">${esc(l.notes)}</span>` : ''}</td>
                        <td class="px-4 py-3 text-slate-300">${esc(l.instructor_name)}${l.vehicle ? `<span class="block text-[10px] text-slate-500">${esc(l.vehicle)}</span>` : ''}</td>
                        <td class="px-4 py-3 text-slate-400 whitespace-nowrap">${isRoadTest(l) ? formatMinutes(l.duration_minutes) : `${lessonsText(l.duration_minutes)}<span class="block text-[10px] text-slate-500">${formatMinutes(l.duration_minutes)}</span>`}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap ${st.cls}">${st.label}</span></td>
                        <td class="px-4 py-3 text-center space-x-1 whitespace-nowrap">${actions}</td>`;
                },
                afterRender: () => {
                    const today = todayLocal();
                    const upcoming = lessons.filter(l => l.status === 'scheduled' && l.date >= today).length;
                    document.getElementById('lessons-count-badge').textContent = `${upcoming} próximas`;
                },
            });
        }

        // ================= PAQUETES (CATÁLOGO) =================
        let editingPackageId = null;

        function updatePackageDurationHint() {
            const n = parseInt(document.getElementById('pkg-lessons').value, 10) || 0;
            document.getElementById('pkg-duration-hint').textContent = n > 0
                ? `${lessonsText(n * LESSON_MINUTES)} de ${LESSON_MINUTES} min = ${formatMinutes(n * LESSON_MINUTES)} de manejo en total.`
                : 'Sin clases de manejo (por ejemplo, un curso teórico).';
        }

        function resetPackageForm() {
            editingPackageId = null;
            document.getElementById('package-form').reset();
            document.getElementById('package-form-title').textContent = 'Nuevo Paquete';
            document.getElementById('package-form-icon').className = 'fa-solid fa-box text-amber-500';
            document.getElementById('package-submit-text').textContent = 'Crear Paquete';
            document.getElementById('package-edit-banner').classList.add('hidden');
            document.getElementById('pkg-existing-buyers').classList.add('hidden');
            updatePackageDurationHint();
        }

        function editPackage(id) {
            const pkg = packages.find(p => p.id === id);
            if (!pkg) return;

            editingPackageId = id;
            document.getElementById('pkg-name').value = pkg.name;
            document.getElementById('pkg-description').value = pkg.description || '';
            document.getElementById('pkg-lessons').value = pkg.lessons;
            document.getElementById('pkg-price').value = pkg.price;
            document.getElementById('pkg-active').checked = pkg.is_active;
            updatePackageDurationHint();

            document.getElementById('package-form-title').textContent = 'Editar Paquete';
            document.getElementById('package-form-icon').className = 'fa-solid fa-pen-to-square text-amber-500';
            document.getElementById('package-submit-text').textContent = 'Guardar Cambios';
            document.getElementById('package-edit-banner-text').textContent = `Editando "${pkg.name}".`;
            document.getElementById('package-edit-banner').classList.remove('hidden');

            const buyers = document.getElementById('pkg-existing-buyers');
            if (pkg.students_count > 0) {
                document.getElementById('pkg-existing-buyers-text').textContent =
                    `${pkg.students_count} estudiante(s) ya compraron este paquete y conservarán el precio y las clases que pagaron. Los cambios aplican solo a las nuevas compras.`;
                buyers.classList.remove('hidden');
            } else {
                buyers.classList.add('hidden');
            }

            document.getElementById('package-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Keep the local catalog in sync and refresh every place that lists packages
        function upsertPackage(pkg) {
            const idx = packages.findIndex(p => p.id === pkg.id);
            if (idx === -1) packages.push(pkg); else packages[idx] = pkg;
            packages.sort((a, b) => (b.is_active - a.is_active) || a.name.localeCompare(b.name));

            renderPackagesTable();
            if (!editingStudentId) {
                renderPlanOptions();
                updatePlanCost();
            }
        }

        function handlePackageSubmit(e) {
            e.preventDefault();

            const data = {
                name: document.getElementById('pkg-name').value.trim(),
                description: document.getElementById('pkg-description').value.trim() || null,
                lessons: parseInt(document.getElementById('pkg-lessons').value, 10),
                price: parseFloat(document.getElementById('pkg-price').value),
                is_active: document.getElementById('pkg-active').checked,
            };

            const editing = editingPackageId !== null;
            const btn = document.getElementById('package-submit-btn');
            btn.disabled = true;

            apiRequest(editing ? `{{ url('packages') }}/${editingPackageId}` : '{{ url('packages') }}', editing ? 'PUT' : 'POST', data)
                .then(res => {
                    upsertPackage(res.package);
                    showToast(`Paquete "${res.package.name}" ${editing ? 'actualizado' : 'creado'}.`, 'success');
                    resetPackageForm();
                })
                .catch(err => showToast(err.message, 'warning'))
                .finally(() => { btn.disabled = false; });
        }

        function togglePackageActive(id) {
            const pkg = packages.find(p => p.id === id);
            if (!pkg) return;

            const activate = !pkg.is_active;
            if (!activate && !confirm(`¿Desactivar "${pkg.name}"? Ya no se podrá vender, pero los estudiantes que lo compraron lo conservan.`)) return;

            apiRequest(`{{ url('packages') }}/${id}/active`, 'PUT', { is_active: activate })
                .then(res => {
                    upsertPackage(res.package);
                    showToast(`Paquete "${res.package.name}" ${activate ? 'activado' : 'desactivado'}.`, 'success');
                })
                .catch(err => {
                    showToast(err.message, 'warning');
                    if (activate && pkg.price <= 0) editPackage(id);
                });
        }

        function deletePackage(id) {
            const pkg = packages.find(p => p.id === id);
            if (!pkg || !confirm(`¿Eliminar el paquete "${pkg.name}"? Esta acción no se puede deshacer.`)) return;

            apiRequest(`{{ url('packages') }}/${id}`, 'DELETE')
                .then(() => {
                    packages = packages.filter(p => p.id !== id);
                    if (editingPackageId === id) resetPackageForm();
                    renderPackagesTable();
                    if (!editingStudentId) { renderPlanOptions(); updatePlanCost(); }
                    showToast(`Paquete "${pkg.name}" eliminado.`, 'success');
                })
                .catch(err => showToast(err.message, 'warning'));
        }

        function renderPackagesTable() {
            // Recount buyers from the loaded students (stays right after enrollments/assignments)
            packages.forEach(p => { p.students_count = students.filter(s => s.package_id === p.id).length; });

            const th = 'px-4 py-3';
            renderDataTable({
                key: 'packages',
                headId: 'packages-head',
                bodyId: 'packages-rows',
                toolbarId: 'packages-toolbar',
                pagerId: 'packages-pager',
                thClass: th,
                rowClass: 'border-b border-slate-800/40 hover:bg-slate-800/20 transition duration-150',
                rows: () => packages,
                emptyText: 'No hay paquetes creados',
                searchPlaceholder: 'Buscar paquete...',
                searchExtra: p => [p.description],
                filters: [
                    { id: 'status', label: 'Estado', value: p => p.is_active ? 'Activo' : 'Inactivo' },
                ],
                columns: [
                    { label: 'Paquete', value: p => p.name },
                    { label: 'Clases', value: p => p.lessons, thClass: `${th} text-center` },
                    { label: 'Precio', value: p => p.price, thClass: `${th} text-right` },
                    { label: 'Vendidos', value: p => p.students_count, thClass: `${th} text-center` },
                    { label: 'Estado', value: p => p.is_active ? 'Activo' : 'Inactivo' },
                    { label: 'Acciones', thClass: `${th} text-center` },
                ],
                row: p => `
                    <td class="px-4 py-3">
                        <span class="font-semibold text-slate-100">${esc(p.name)}</span>
                        ${p.description ? `<span class="block text-[11px] text-slate-500">${esc(p.description)}</span>` : ''}
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        ${p.lessons
                            ? `<span class="font-bold text-slate-200">${p.lessons}</span><span class="block text-[10px] text-slate-500">${formatMinutes(p.lessons * LESSON_MINUTES)}</span>`
                            : '<span class="text-xs text-slate-500">Sin manejo</span>'}
                    </td>
                    <td class="px-4 py-3 text-right font-bold font-mono whitespace-nowrap ${p.price > 0 ? 'text-emerald-400' : 'text-red-400'}">
                        ${p.price > 0 ? '$' + p.price.toFixed(2) : 'Sin precio'}
                    </td>
                    <td class="px-4 py-3 text-center text-slate-400">${p.students_count}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap ${p.is_active ? 'bg-green-500/10 text-green-400' : 'bg-slate-800 text-slate-500'}">${p.is_active ? 'Activo' : 'Inactivo'}</span>
                    </td>
                    <td class="px-4 py-3 text-center space-x-1 whitespace-nowrap">
                        <button onclick="editPackage(${p.id})" title="Editar" class="bg-amber-500/10 text-amber-500 hover:bg-amber-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition"><i class="fa-solid fa-pen"></i></button>
                        <button onclick="togglePackageActive(${p.id})" title="${p.is_active ? 'Desactivar' : 'Activar'}"
                            class="${p.is_active ? 'bg-slate-800 text-slate-400 hover:bg-slate-500' : 'bg-green-500/10 text-green-400 hover:bg-green-500'} hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition">
                            <i class="fa-solid ${p.is_active ? 'fa-eye-slash' : 'fa-eye'}"></i>
                        </button>
                        ${p.students_count === 0
                            ? `<button onclick="deletePackage(${p.id})" title="Eliminar" class="bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition"><i class="fa-solid fa-trash-can"></i></button>`
                            : ''}
                    </td>`,
                afterRender: () => {
                    const active = packages.filter(p => p.is_active).length;
                    document.getElementById('packages-count-badge').textContent = `${active} activos`;
                },
            });
        }

        window.onload = function () {
            renderPlanOptions();
            updatePlanCost();
            updateDashboardMetrics();
            populateInstructorDropdown();
            populateVehicleDropdown();
            renderSheetTable();
            renderPaymentsTable();

            // Set up form reset listeners
            document.getElementById('student-form').addEventListener('reset', () => {
                editingStudentId = null;
                document.getElementById('student-form-title').innerText = "Nuevo estudiante";
                document.getElementById('student-form-subtitle').innerText = "Completa la ficha y presiona Guardar.";
                document.getElementById('student-submit-btn-text').innerText = "Registrar Alumno";
                document.getElementById('student-submit-btn-icon').className = "fa-solid fa-save";
                document.getElementById('sf-note-body').value = '';
                setTimeout(() => {
                    renderPlanOptions();
                    updatePlanCost();
                    renderPreferredInstructorOptions(null);
                    renderStudentFormExtras(null);
                    switchStudentFormTab('profile');
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