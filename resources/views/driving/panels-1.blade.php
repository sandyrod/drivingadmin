{{-- Dashboard panels: KPIs and student form (included by driving.blade.php) --}}
    <!-- ================= PANELS: DASHBOARD ================= -->
    <div id="view-dashboard" class="space-y-8">
        <!-- KPI CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl relative overflow-hidden group hover:border-amber-500/40 transition">
                <div class="absolute -right-3 -bottom-3 text-slate-800/40 text-7xl group-hover:scale-110 transition duration-300">
                    <i class="fa-solid fa-users"></i>
                </div>
                <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">{{ __('Estudiantes Activos') }}</p>
                <h3 class="text-3xl font-extrabold mt-2 text-slate-100" id="kpi-active">0</h3>
                <p class="text-xs text-amber-500 mt-1"><i class="fa-solid fa-circle-check"></i> {{ __('En formación actual') }}</p>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl relative overflow-hidden group hover:border-red-500/40 transition">
                <div class="absolute -right-3 -bottom-3 text-slate-800/40 text-7xl group-hover:scale-110 transition duration-300">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">{{ __('Pendiente por Cobrar') }}</p>
                <h3 class="text-3xl font-extrabold mt-2 text-red-500" id="kpi-debts">$0</h3>
                <p class="text-xs text-slate-400 mt-1"><i class="fa-solid fa-triangle-exclamation text-amber-500"></i> {{ __('Alumnos con deudas') }}</p>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl relative overflow-hidden group hover:border-amber-500/40 transition">
                <div class="absolute -right-3 -bottom-3 text-slate-800/40 text-7xl group-hover:scale-110 transition duration-300">
                    <i class="fa-solid fa-ranking-star"></i>
                </div>
                <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">{{ __('Satisfacción General') }}</p>
                <h3 class="text-3xl font-extrabold mt-2 text-slate-100" id="kpi-rating">4.8 / 5</h3>
                <p class="text-xs text-amber-500 mt-1">
                    <span id="kpi-stars">⭐⭐⭐⭐⭐</span>
                </p>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl relative overflow-hidden group hover:border-amber-500/40 transition">
                <div class="absolute -right-3 -bottom-3 text-slate-800/40 text-7xl group-hover:scale-110 transition duration-300">
                    <i class="fa-solid fa-car-side"></i>
                </div>
                <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">{{ __('Flota de Práctica') }}</p>
                <h3 class="text-3xl font-extrabold mt-2 text-slate-100" id="kpi-fleet">0</h3>
                <p class="text-xs text-slate-400 mt-1"><i class="fa-solid fa-check text-green-500"></i> {{ __('Unidades activas hoy') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- REPORT: ACTIVE STUDENTS & DEBTS -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-base font-bold text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-amber-500"></i>
                        {{ __('Reporte: Estudiantes con Saldos Pendientes') }}
                    </h4>
                    <span class="text-xs bg-red-500/10 text-red-400 px-3 py-1 rounded-full font-medium">{{ __('Saldos vencidos') }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-400 uppercase tracking-wider border-b border-slate-800">
                            <tr>
                                <th class="pb-3">{{ __('Estudiante') }}</th>
                                <th class="pb-3">{{ __('Plan') }}</th>
                                <th class="pb-3 text-right">{{ __('Saldo Deuda') }}</th>
                                <th class="pb-3 text-center">{{ __('Acción') }}</th>
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
                        {{ __('Alertas de Satisfacción (1-2 Estrellas)') }}
                    </h4>
                    <span class="text-xs bg-amber-500/10 text-amber-500 px-3 py-1 rounded-full font-medium">{{ __('Calificaciones Críticas') }}</span>
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
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500" id="student-form-title">{{ __('Nuevo estudiante') }}</p>
                    <h2 class="text-2xl font-black text-slate-100 uppercase leading-tight" id="sf-name">{{ __('Nuevo estudiante') }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5" id="student-form-subtitle">{{ __('Completa la ficha y presiona Guardar.') }}</p>
                </div>
                <label class="sf-flag" title="{{ __('Marcar al estudiante como problemático') }}">
                    <input type="checkbox" id="stu-problematic" class="hidden" onchange="renderProblematicFlag()">
                    <i class="fa-regular fa-face-frown"></i> <span>{{ __('Problemático') }}</span>
                </label>
                <span id="sf-balance-badge" class="hidden px-3 py-1 rounded-full text-sm font-black"></span>
            </div>

            <!-- Action bar -->
            <div class="flex flex-wrap items-center gap-2 bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 mb-4">
                <button type="submit" id="student-submit-btn" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-4 py-2 rounded-lg text-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-save" id="student-submit-btn-icon"></i> <span id="student-submit-btn-text">{{ __('Registrar Alumno') }}</span>
                </button>
                <button type="button" class="sf-action sf-needs-student" onclick="studentFormAction('agenda')"><i class="fa-solid fa-calendar-plus"></i> {{ __('Agendar clase') }}</button>
                <button type="button" class="sf-action sf-needs-student" onclick="studentFormAction('road_test')"><i class="fa-solid fa-road"></i> Road Test</button>
                <button type="button" class="sf-action sf-needs-student" onclick="studentFormAction('pay')"><i class="fa-solid fa-dollar-sign"></i> {{ __('Registrar pago') }}</button>
                <button type="button" class="sf-action sf-needs-student" onclick="studentFormAction('invoice')"><i class="fa-solid fa-file-invoice"></i> {{ __('Factura') }}</button>
                <button type="button" class="sf-action sf-needs-student" onclick="studentFormAction('email')"><i class="fa-solid fa-envelope"></i> {{ __('Correo') }}</button>
                <button type="button" class="sf-action sf-needs-student" onclick="studentFormAction('text')"><i class="fa-solid fa-comment-sms"></i> {{ __('SMS') }}</button>
                <span class="flex-1"></span>
                <button type="button" class="sf-action" onclick="switchTab('inscripcion')" title="{{ __('Limpiar y registrar otro estudiante') }}"><i class="fa-solid fa-user-plus"></i> {{ __('Nuevo') }}</button>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <!-- Tabs -->
                <div class="flex gap-1 px-4 pt-2 border-b border-slate-800 overflow-x-auto">
                    <button type="button" data-sf-tab="profile" onclick="switchStudentFormTab('profile')" class="sf-tab">{{ __('Perfil') }}</button>
                    <button type="button" data-sf-tab="lessons" onclick="switchStudentFormTab('lessons')" class="sf-tab sf-needs-student">{{ __('Clases') }} <span id="sf-lessons-count"></span></button>
                    <button type="button" data-sf-tab="payments" onclick="switchStudentFormTab('payments')" class="sf-tab sf-needs-student">{{ __('Recibos') }} <span id="sf-payments-count"></span></button>
                    <button type="button" data-sf-tab="notes" onclick="switchStudentFormTab('notes')" class="sf-tab sf-needs-student">{{ __('Notas') }} <span id="sf-notes-count"></span></button>
                </div>

                <!-- ===== PROFILE ===== -->
                <div id="sf-tab-profile" class="p-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-start">
                    <!-- Column 1: permit + name + address -->
                    <div class="sf-card">
                        <div class="sf-field">
                            <span class="sf-label">{{ __('Número de permiso') }}</span>
                            <div class="flex items-center gap-2">
                                <input type="text" id="lic-number" class="sf-input" maxlength="50" placeholder="123-456-789">
                                <button type="button" onclick="copyToClipboard(document.getElementById('lic-number').value)" class="text-slate-500 hover:text-amber-500" title="{{ __('Copiar') }}"><i class="fa-regular fa-copy"></i></button>
                            </div>
                        </div>
                        <label class="sf-field"><span class="sf-label">{{ __('Vencimiento del permiso') }}</span>
                            <input type="date" id="lic-expiration" class="sf-input" onchange="renderExpiryWarnings()">
                            <span id="sf-permit-expired" class="hidden sf-warn">{{ __('Vencido') }}</span></label>
                        <label class="sf-field"><span class="sf-label">{{ __('Clase de permiso') }}</span>
                            <select id="lic-class" class="sf-input">
                                <option value="">—</option>
                                <option value="Class D">{{ __('Clase D (Pasajeros)') }}</option>
                                <option value="Class DJ">{{ __('Clase DJ (Conductor junior)') }}</option>
                                <option value="Class E">{{ __('Clase E (Taxi/Limusina de alquiler)') }}</option>
                                <option value="Class M">{{ __('Clase M (Motocicleta)') }}</option>
                                <option value="Commercial">{{ __('Comercial (CDL)') }}</option>
                            </select></label>
                        <label class="sf-field"><span class="sf-label">{{ __('Certificado # (5 horas / MV-278)') }}</span>
                            <input type="text" id="stu-certificate-number" class="sf-input" maxlength="50"></label>
                        <label class="sf-field"><span class="sf-label">{{ __('Vencimiento del certificado') }}</span>
                            <input type="date" id="stu-certificate-expiry" class="sf-input" onchange="renderExpiryWarnings()">
                            <span id="sf-certificate-expired" class="hidden sf-warn">{{ __('Vencido') }}</span></label>
                        <label class="sf-field"><span class="sf-label">{{ __('Apellido') }} <b>*</b></span>
                            <input type="text" id="stu-last-name" required maxlength="100" class="sf-input sf-upper" oninput="renderStudentFormName()"></label>
                        <label class="sf-field"><span class="sf-label">{{ __('Nombre (de pila)') }} <b>*</b></span>
                            <input type="text" id="stu-first-name" required maxlength="100" class="sf-input sf-upper" oninput="renderStudentFormName()"></label>
                        <label class="sf-field"><span class="sf-label">{{ __('Segundo nombre') }}</span>
                            <input type="text" id="stu-middle-name" maxlength="100" class="sf-input sf-upper" oninput="renderStudentFormName()"></label>
                        <label class="sf-field"><span class="sf-label">{{ __('Sufijo') }}</span>
                            <input type="text" id="stu-suffix" maxlength="10" class="sf-input sf-upper" placeholder="Jr, Sr, III"></label>
                        <label class="sf-field"><span class="sf-label">{{ __('Dirección') }} <b>*</b></span>
                            <input type="text" id="stu-address" required maxlength="255" class="sf-input sf-upper"></label>
                        <label class="sf-field"><span class="sf-label">{{ __('Apto. #') }}</span>
                            <input type="text" id="stu-apt" maxlength="20" class="sf-input sf-upper"></label>
                        <label class="sf-field"><span class="sf-label">{{ __('Ciudad') }} <b>*</b></span>
                            <input type="text" id="stu-city" required maxlength="100" class="sf-input sf-upper"></label>
                        <label class="sf-field"><span class="sf-label">{{ __('Estado (dirección)') }} <b>*</b></span>
                            <input type="text" id="stu-state" required maxlength="100" value="NY" class="sf-input sf-upper"></label>
                        <label class="sf-field"><span class="sf-label">{{ __('Código postal') }} <b>*</b></span>
                            <input type="text" id="stu-zip-code" required maxlength="20" class="sf-input"></label>
                    </div>

                    <!-- Column 2: contact + personal -->
                    <div class="space-y-4">
                        <div class="sf-card">
                            <div class="sf-field">
                                <span class="sf-label">{{ __('Celular') }} <b>*</b></span>
                                <div class="flex items-center gap-2">
                                    <input type="text" id="stu-phone" required maxlength="30" class="sf-input" placeholder="347-555-0199">
                                    <a id="sf-call-link" href="#" class="text-slate-500 hover:text-amber-500" title="{{ __('Llamar') }}"><i class="fa-solid fa-phone"></i></a>
                                </div>
                            </div>
                            <label class="sf-field"><span class="sf-label">{{ __('Otro teléfono') }}</span>
                                <input type="text" id="stu-other-phone" maxlength="30" class="sf-input"></label>
                            <label class="sf-field"><span class="sf-label">{{ __('Descripción otro teléfono') }}</span>
                                <input type="text" id="stu-other-phone-desc" maxlength="50" class="sf-input" placeholder="{{ __('Casa, trabajo, mamá...') }}"></label>
                            <label class="sf-field"><span class="sf-label">{{ __('Correo principal') }} <b>*</b></span>
                                <input type="email" id="stu-email" required maxlength="255" class="sf-input"></label>
                            <label class="sf-field"><span class="sf-label">{{ __('Correo secundario') }}</span>
                                <input type="email" id="stu-secondary-email" maxlength="255" class="sf-input"></label>
                            <label class="sf-field"><span class="sf-label">{{ __('Nombre del padre/madre') }}</span>
                                <input type="text" id="stu-parent-name" maxlength="150" class="sf-input sf-upper"></label>
                            <label class="sf-field"><span class="sf-label">{{ __('Teléfono del padre/madre') }}</span>
                                <input type="text" id="stu-parent-phone" maxlength="30" class="sf-input"></label>
                            <label class="sf-field"><span class="sf-label">{{ __('Escuela secundaria') }}</span>
                                <input type="text" id="stu-high-school" maxlength="150" class="sf-input sf-upper"></label>
                        </div>

                        <div class="sf-card">
                            <label class="sf-field"><span class="sf-label">{{ __('Fecha de nacimiento') }} <span id="sf-age"></span> <b>*</b></span>
                                <input type="date" id="stu-birth-date" required class="sf-input" onchange="renderStudentAge()"></label>
                            <label class="sf-field"><span class="sf-label">{{ __('Género') }} <b>*</b></span>
                                <select id="stu-gender" required class="sf-input">
                                    <option value="M">{{ __('Masculino') }}</option>
                                    <option value="F">{{ __('Femenino') }}</option>
                                    <option value="O">{{ __('Otro') }}</option>
                                </select></label>
                            <label class="sf-field"><span class="sf-label">{{ __('Restricción') }}</span>
                                <input type="text" id="stu-restrictions" maxlength="100" class="sf-input sf-upper" placeholder="{{ __('Ej. lentes') }}"></label>
                            <label class="sf-field"><span class="sf-label">{{ __('Endosos') }}</span>
                                <input type="text" id="stu-endorsements" maxlength="100" class="sf-input sf-upper"></label>
                            <label class="sf-field"><span class="sf-label">{{ __('Color de ojos') }}</span>
                                <select id="stu-eye-color" class="sf-input">
                                    <option value="">—</option>
                                    <option value="BLK">{{ __('Negro (BLK)') }}</option>
                                    <option value="BLU">{{ __('Azul (BLU)') }}</option>
                                    <option value="BRN">{{ __('Marrón (BRN)') }}</option>
                                    <option value="GRN">{{ __('Verde (GRN)') }}</option>
                                    <option value="HAZ">{{ __('Avellana (HAZ)') }}</option>
                                    <option value="GRY">{{ __('Gris (GRY)') }}</option>
                                </select></label>
                            <label class="sf-field"><span class="sf-label">{{ __('Estatura') }}</span>
                                <input type="text" id="stu-height" maxlength="10" class="sf-input" placeholder="5'08&quot;"></label>
                            <label class="sf-field sf-check">
                                <input type="checkbox" id="stu-organ-donor" value="1" class="accent-amber-500">
                                <span class="text-xs font-semibold text-slate-300">{{ __('Donante de órganos') }}</span></label>
                            <label class="sf-field"><span class="sf-label">{{ __('Contrato #') }}</span>
                                <input type="text" id="stu-contract-number" maxlength="30" class="sf-input"></label>
                            <div class="sf-field sf-pb">
                                <span class="sf-label">{{ __('Transmisión') }} <b>*</b></span>
                                <div class="flex gap-4 pt-1 text-xs font-semibold text-slate-300">
                                    <label class="flex items-center gap-1.5 cursor-pointer"><input type="radio" name="stu-transmission" value="Automático" checked class="accent-amber-500"> {{ __('Automático') }}</label>
                                    <label class="flex items-center gap-1.5 cursor-pointer"><input type="radio" name="stu-transmission" value="Mecánico" class="accent-amber-500"> {{ __('Mecánico') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: road test, pickup, package, instructor -->
                    <div class="space-y-4">
                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-road text-violet-400"></i> Road Test</span>
                                <button type="button" class="sf-icon-btn sf-needs-student" onclick="studentFormAction('road_test')" title="{{ __('Agendar Road Test') }}"><i class="fa-solid fa-plus"></i></button></h4>
                            <div id="sf-road-test" class="px-4 py-3 text-xs text-slate-500"></div>
                        </div>

                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-location-dot text-amber-500"></i> {{ __('Lugar de recogida preferido') }}</span></h4>
                            <label class="sf-field"><span class="sf-label">{{ __('Dirección donde se recoge al estudiante') }}</span>
                                <input type="text" id="stu-pickup-location" maxlength="255" class="sf-input" oninput="renderPickupLinks()" placeholder="{{ __('Igual a la dirección si se deja vacío') }}"></label>
                            <div id="sf-pickup-links" class="flex gap-4 px-4 py-2 text-xs"></div>
                        </div>

                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-box-open text-amber-500"></i> {{ __('Paquete') }}</span></h4>
                            <label class="sf-field"><span class="sf-label">{{ __('Paquete') }} <b>*</b></span>
                                <select id="stu-plan" onchange="updatePlanCost()" required class="sf-input">
                                    <!-- Options generated by renderPlanOptions() from the package catalog -->
                                </select></label>
                            <div class="grid grid-cols-3 gap-2 px-4">
                                <label class="sf-field sf-tight"><span class="sf-label">{{ __('Total ($)') }}</span>
                                    <input type="number" id="stu-cost" readonly class="sf-input text-slate-400 cursor-not-allowed" value="0"></label>
                                <label class="sf-field sf-tight"><span class="sf-label"><span id="sf-paid-label">{{ __('Pago inicial') }}</span> <b>*</b></span>
                                    <input type="number" id="stu-paid" oninput="calculateInscripcionDebt()" required min="0" step="0.01" class="sf-input" placeholder="0"></label>
                                <label class="sf-field sf-tight"><span class="sf-label">{{ __('Saldo') }}</span>
                                    <input type="number" id="stu-debt" readonly class="sf-input text-red-500 cursor-not-allowed" value="0"></label>
                            </div>
                            <div id="stu-lessons-summary" class="hidden px-4 py-3"></div>
                        </div>

                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-chalkboard-user text-amber-500"></i> {{ __('Instructor preferido') }}</span></h4>
                            <label class="sf-field sf-pb"><span class="sf-label">{{ __('Se propone al agendar clases') }}</span>
                                <select id="stu-preferred-instructor" class="sf-input"></select></label>
                            <label class="sf-field sf-pb"><span class="sf-label">{{ __('Idioma preferido (correos y SMS)') }}</span>
                                <select id="stu-language" class="sf-input">@foreach(config('app.languages') as $code => $name)<option value="{{ $code }}" @selected($code === config('app.default_language'))>{{ $name }}</option>@endforeach</select></label>
                        </div>
                    </div>

                    <!-- Column 4: permit photos, student photo, last note, appointments -->
                    <div class="space-y-4">
                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-id-card text-amber-500"></i> {{ __('Permiso de aprendiz') }}</span></h4>
                            <div class="p-3 space-y-3">
                                <div class="sf-upload group" id="lic-front-zone" onclick="document.getElementById('lic-front-input').click()">
                                    <input type="file" id="lic-front-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'lic-front-preview', 'lic-front-zone', 'lic-front-base64')">
                                    <input type="hidden" id="lic-front-base64" value="">
                                    <div class="upload-placeholder flex flex-col items-center gap-1">
                                        <i class="fa-solid fa-id-card text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                                        <span class="text-xs text-slate-400 font-medium">{{ __('Subir permiso (frente)') }}</span>
                                        <span class="text-[10px] text-slate-600">{{ __('JPG, PNG o WEBP (máx. 4MB)') }}</span>
                                    </div>
                                    <img id="lic-front-preview" src="" class="hidden w-full max-h-56 rounded-lg object-contain" alt="{{ __('Permiso (frente)') }}">
                                    <button type="button" onclick="clearUpload(event, 'lic-front-input', 'lic-front-preview', 'lic-front-zone', 'lic-front-base64')"
                                        class="sf-upload-remove hidden" id="lic-front-remove" title="{{ __('Quitar') }}"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                                <div class="sf-upload group" id="lic-back-zone" onclick="document.getElementById('lic-back-input').click()">
                                    <input type="file" id="lic-back-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'lic-back-preview', 'lic-back-zone', 'lic-back-base64')">
                                    <input type="hidden" id="lic-back-base64" value="">
                                    <div class="upload-placeholder flex flex-col items-center gap-1">
                                        <i class="fa-solid fa-id-card text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                                        <span class="text-xs text-slate-400 font-medium">{{ __('Subir permiso (reverso)') }}</span>
                                    </div>
                                    <img id="lic-back-preview" src="" class="hidden w-full max-h-56 rounded-lg object-contain" alt="{{ __('Permiso (reverso)') }}">
                                    <button type="button" onclick="clearUpload(event, 'lic-back-input', 'lic-back-preview', 'lic-back-zone', 'lic-back-base64')"
                                        class="sf-upload-remove hidden" id="lic-back-remove" title="{{ __('Quitar') }}"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-user text-amber-500"></i> {{ __('Foto del estudiante') }}</span></h4>
                            <div class="p-3">
                                <div class="sf-upload group" id="stu-photo-zone" onclick="document.getElementById('stu-photo-input').click()">
                                    <input type="file" id="stu-photo-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'stu-photo-preview', 'stu-photo-zone', 'stu-photo-base64')">
                                    <input type="hidden" id="stu-photo-base64" value="">
                                    <div class="upload-placeholder flex flex-col items-center gap-1">
                                        <i class="fa-solid fa-camera text-2xl text-slate-500 group-hover:text-amber-500 transition"></i>
                                        <span class="text-xs text-slate-400 font-medium">{{ __('Subir foto') }}</span>
                                    </div>
                                    <img id="stu-photo-preview" src="" class="hidden max-h-40 rounded-lg object-contain" alt="{{ __('Foto del estudiante') }}">
                                    <button type="button" onclick="clearUpload(event, 'stu-photo-input', 'stu-photo-preview', 'stu-photo-zone', 'stu-photo-base64')"
                                        class="sf-upload-remove hidden" id="stu-photo-remove" title="{{ __('Quitar') }}"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-note-sticky text-amber-500"></i> {{ __('Última nota') }}</span>
                                <button type="button" class="sf-icon-btn sf-needs-student" onclick="switchStudentFormTab('notes')" title="{{ __('Ver / agregar notas') }}"><i class="fa-solid fa-plus"></i></button></h4>
                            <div id="sf-last-note" class="px-4 py-3 text-xs text-slate-500"></div>
                        </div>

                        <div class="sf-card">
                            <h4 class="sf-card-title"><span><i class="fa-solid fa-calendar-check text-amber-500"></i> {{ __('Citas') }} <span id="sf-appointments-badge"></span></span>
                                <button type="button" class="sf-icon-btn sf-needs-student" onclick="switchStudentFormTab('lessons')" title="{{ __('Ver todas') }}"><i class="fa-solid fa-list"></i></button></h4>
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
                            placeholder="{{ __('Escribe una nota interna (ej. llamé para reagendar el Road Test, no contestó)...') }}"></textarea>
                        <div class="flex justify-end mt-2">
                            <button type="button" onclick="addStudentNote()" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-4 py-2 rounded-lg text-xs transition">
                                <i class="fa-solid fa-plus"></i> {{ __('Agregar nota') }}
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

