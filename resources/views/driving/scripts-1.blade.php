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
                    showToast(__("La imagen supera los 4MB."), "warning");
                    inputElement.value = '';
                    return;
                }
                if (!file.type.startsWith("image/")) {
                    showToast(__("El archivo seleccionado debe ser una imagen."), "warning");
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
            window.currentTab = tabId;   // kept when the language is switched
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
                sectionPre.innerText = __("Gestión de operaciones");
                sectionTitle.innerText = __("Dashboard Principal");
            } else if (tabId === 'inscripcion') {
                sectionPre.innerText = __("Estudiantes");
                sectionTitle.innerText = isEditing ? __("Ficha del Estudiante") : __("Registrar Estudiante");
                if (!isEditing) {
                    editingStudentId = null;
                    document.getElementById('student-form').reset();
                    document.getElementById('student-submit-btn-text').innerText = __("Registrar Alumno");
                    document.getElementById('student-submit-btn-icon').className = "fa-solid fa-save";
                    
                    // Clear upload previews
                    clearUpload(null, 'stu-photo-input', 'stu-photo-preview', 'stu-photo-zone', 'stu-photo-base64');
                    clearUpload(null, 'lic-front-input', 'lic-front-preview', 'lic-front-zone', 'lic-front-base64');
                    clearUpload(null, 'lic-back-input', 'lic-back-preview', 'lic-back-zone', 'lic-back-base64');
                }
            } else if (tabId === 'abonos') {
                sectionPre.innerText = __("Control de Caja");
                sectionTitle.innerText = __("Registro de Abonos");
                
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
                sectionPre.innerText = __("Programación");
                sectionTitle.innerText = __("Agenda de Clases");
                cancelLessonEdit();
            } else if (tabId === 'calendario') {
                sectionPre.innerText = __("Programación");
                sectionTitle.innerText = __("Calendario de Clases");
                renderCalendar();
            } else if (tabId === 'paquetes') {
                sectionPre.innerText = __("Catálogo");
                sectionTitle.innerText = __("Paquetes");
                resetPackageForm();
                renderPackagesTable();
            } else if (tabId === 'registro-instructor') {
                sectionPre.innerText = __("Instructores");
                sectionTitle.innerText = __("Registrar Instructor");
                if (!isEditing) {
                    editingInstructorId = null;
                    document.getElementById('instructor-form').reset();
                    document.getElementById('instructor-form-title').innerText = __("Ficha de Registro de Instructor");
                    document.getElementById('instructor-form-subtitle').innerText = __("Crea nuevos expedientes de instructores que se sincronizarán directamente con la base de datos.");
                    document.getElementById('instructor-submit-btn-text').innerText = __("Registrar Instructor");
                    document.getElementById('instructor-submit-btn-icon').className = "fa-solid fa-save";
                    
                    // Clear upload previews
                    clearUpload(null, 'ins-photo-input', 'ins-photo-preview', 'ins-photo-zone', 'ins-photo-base64');
                    clearUpload(null, 'ins-lic-front-input', 'ins-lic-front-preview', 'ins-lic-front-zone', 'ins-lic-front-base64');
                    clearUpload(null, 'ins-lic-back-input', 'ins-lic-back-preview', 'ins-lic-back-zone', 'ins-lic-back-base64');
                }
            } else if (tabId === 'registro-vehiculo') {
                sectionPre.innerText = __("Vehículos");
                sectionTitle.innerText = __("Registrar Vehículo");
            } else if (tabId === 'encuesta') {
                sectionPre.innerText = __("Post-Venta / Feedback");
                sectionTitle.innerText = __("Encuesta de Servicio");
            } else if (tabId === 'sheets-db') {
                sectionPre.innerText = __("Base de datos en la nube");
                sectionTitle.innerText = __("Visor de Google Sheets");
            } else if (tabId === 'apps-script') {
                sectionPre.innerText = __("Motor de flujos");
                sectionTitle.innerText = __("Google Apps Script Console");
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
            document.getElementById('kpi-fleet').innerText = __(':n Vehículos', { n: vehicles.length });

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
                debtContainer.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-xs text-slate-500">${__('No hay estudiantes con saldos deudores')}</td></tr>`;
            } else {
                debtors.forEach(s => {
                    const tr = document.createElement('tr');
                    tr.className = "border-b border-slate-800/40 hover:bg-slate-800/20 transition duration-150";
                    tr.innerHTML = `
                        <td class="py-3.5 font-semibold text-slate-200"><button type="button" onclick="openStudentProfile(${s.db_id})" class="hover:text-amber-500 hover:underline text-left">${esc(s.name)}</button>${s.contract_number ? `<span class="block text-[10px] font-normal text-slate-500">${__('Contrato :number', { number: esc(s.contract_number) })}</span>` : ''}</td>
                        <td class="py-3.5 text-xs text-slate-400">${esc(s.plan)}</td>
                        <td class="py-3.5 text-right font-bold text-red-500">$${s.debt}</td>
                        <td class="py-3.5 text-center">
                            <button onclick="simulateCobro('${s.id}')" class="bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 px-2.5 py-1 rounded text-xs font-bold transition">${__('Abonar')}</button>
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
                        <p class="text-xs text-slate-400">${__('¡Excelente! No hay calificaciones críticas registradas recientemente.')}</p>
                    </div>
                `;
            } else {
                badReviews.forEach(r => {
                    const card = document.createElement('div');
                    card.className = "bg-red-500/10 border border-red-500/20 p-4 rounded-xl space-y-2 relative";
                    card.innerHTML = `
                        <div class="flex items-center justify-between">
                            <h5 class="text-xs font-bold text-slate-200">${__('Instructor: :name', { name: esc(r.instructor) })}</h5>
                            <span class="text-xs text-red-400 font-bold">${'★'.repeat(r.rating)}${'☆'.repeat(5 - r.rating)}</span>
                        </div>
                        <p class="text-xs text-slate-300 italic">"${esc(r.comment)}"</p>
                        <div class="flex justify-end gap-2 pt-1">
                            <button data-name="${esc(r.instructor)}" onclick="simulateCallInstructor(this.dataset.name)" class="bg-slate-950 hover:bg-slate-900 border border-slate-800 text-[10px] text-slate-300 px-2 py-1 rounded font-bold transition">${__('Contactar Instructor')}</button>
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
            showToast(__('Contactando al instructor :name vía WhatsApp interno para feedback del vehículo...', { name }), "info");
        }

        // Fills the enrollment package select with the active catalog packages.
        // When editing, the student's current package is kept even if it is no longer sold.
        function renderPlanOptions(currentPlan = null, currentCost = 0) {
            const select = document.getElementById('stu-plan');
            if (!select) return;
            select.innerHTML = '';

            const active = packages.filter(p => p.is_active);
            if (currentPlan && !active.some(p => p.name === currentPlan)) {
                // currentCost includes the additional packages: the option shows the enrollment package price only
                const extras = (getEditingStudent()?.charges || []).reduce((sum, c) => sum + c.amount, 0);
                currentCost = Math.max(0, Math.round((currentCost - extras) * 100) / 100);
                const opt = document.createElement('option');
                opt.value = currentPlan;
                opt.dataset.price = currentCost;
                opt.textContent = `${currentPlan} ${__('(paquete actual)')} - $${currentCost}`;
                select.appendChild(opt);
            }

            active.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.name;
                opt.dataset.price = p.price;
                opt.textContent = `${p.name} — ${p.lessons ? lessonsText(p.lessons * LESSON_MINUTES) : __('sin clases de manejo')} - $${p.price}`;
                select.appendChild(opt);
            });

            if (!select.options.length) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = __('No hay paquetes activos (configúralos en "Paquetes")');
                select.appendChild(opt);
            }
        }

        function updatePlanCost() {
            const planSelector = document.getElementById('stu-plan');
            const selectedOption = planSelector.options[planSelector.selectedIndex];
            const price = parseFloat(selectedOption?.getAttribute('data-price')) || 0;
            // The total also includes the student's additional packages (Ledger)
            const extras = (getEditingStudent()?.charges || []).reduce((sum, c) => sum + c.amount, 0);

            document.getElementById('stu-cost').value = Math.round((price + extras) * 100) / 100;
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
                language: document.getElementById('stu-language').value,
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
                        showToast(__('¡Estudiante :name actualizado con éxito en la Base de Datos!', { name: student.name }), "success");
                    } else {
                        students.push(student);
                        showToast(__('¡Estudiante :name registrado con éxito en la Base de Datos!', { name: student.name }), "success");
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
                    showToast(__("Error de validación: ") + (res.message || __("Campos incompletos.")), "warning");
                }
            })
            .catch(err => {
                console.error(err);
                showToast(__("Error de red o de servidor al registrar estudiante."), "warning");
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
                showToast(__('Selecciona al menos un día de trabajo del instructor.'), 'warning');
                return;
            }
            if (work_end <= work_start) {
                showToast(__('La hora de salida del instructor debe ser posterior a la de entrada.'), 'warning');
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
                language: document.getElementById('ins-language').value,
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
                        showToast(__('¡Instructor :name actualizado con éxito en la Base de Datos!', { name: instructor.name }), "success");
                    } else {
                        instructors.push(instructor);
                        showToast(__('¡Instructor :name registrado con éxito en la Base de Datos!', { name: instructor.name }), "success");
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
                        showToast(method === 'PUT' ? __("Los datos se actualizaron en la base de datos MySQL") : __("Los datos se guardaron en la base de datos MySQL y se sincronizaron con Google Sheets"), "info");
                    }, 1000);
                } else {
                    showToast(__("Error de validación: ") + (res.message || __("Campos incompletos.")), "warning");
                }
            })
            .catch(err => {
                console.error(err);
                showToast(__("Error de red o de servidor al registrar instructor."), "warning");
            });
        }

        function deleteInstructor(instructorId) {
            const instructor = instructors.find(i => i.id === instructorId);
            if (instructor) {
                if (!confirm(__('¿Estás seguro de que deseas eliminar permanentemente a :name de la base de datos?', { name: instructor.name }))) return;

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
                        showToast(__('Instructor :name eliminado de la base de datos (Soft Deleted).', { name: instructor.name }), "success");

                        const log = document.getElementById('console-logs');
                        log.innerHTML += `<p class="text-red-500">[DB_TRIGGER] Instructor eliminado en BD ID: ${instructor.db_id} [Instructor ID: ${instructorId}]</p>`;

                        populateInstructorDropdown();
                        updateDashboardMetrics();
                        renderSheetTable();
                    } else {
                        showToast(__("Error al eliminar de la BD: ") + (res.message || ""), "warning");
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast(__("Error al eliminar instructor en el servidor."), "warning");
                });
            }
        }

        function populateVehicleDropdown() {
            const insVehicleDropdown = document.getElementById('ins-vehicle');
            if (insVehicleDropdown) {
                insVehicleDropdown.innerHTML = `<option value="">${__('Selecciona vehículo...')}</option>`;
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
                        showToast(__('¡Vehículo :brand actualizado con éxito en la Base de Datos!', { brand: vehicle.brand }), "success");
                    } else {
                        vehicles.push(vehicle);
                        showToast(__('¡Vehículo :brand registrado con éxito en la Base de Datos!', { brand: vehicle.brand }), "success");
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
                        showToast(method === 'PUT' ? __("Los datos se actualizaron en la base de datos MySQL") : __("Los datos se guardaron en la base de datos MySQL y se sincronizaron con Google Sheets"), "info");
                    }, 1000);
                } else {
                    showToast(__("Error de validación: ") + (res.message || __("Campos incompletos.")), "warning");
                }
            })
            .catch(err => {
                console.error(err);
                showToast(__("Error de red o de servidor al registrar vehículo."), "warning");
            });
        }

        function deleteVehicle(vehicleId) {
            const vehicle = vehicles.find(v => v.id === vehicleId);
            if (vehicle) {
                if (!confirm(__('¿Estás seguro de que deseas eliminar permanentemente el vehículo :brand [:plate] de la base de datos?', { brand: vehicle.brand, plate: vehicle.plate }))) return;

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
                        showToast(__('Vehículo :brand eliminado de la base de datos (Soft Deleted).', { brand: vehicle.brand }), "success");

                        const log = document.getElementById('console-logs');
                        log.innerHTML += `<p class="text-red-500">[DB_TRIGGER] Vehículo eliminado en BD ID: ${vehicle.db_id} [Vehicle ID: ${vehicleId}]</p>`;

                        populateVehicleDropdown();
                        updateDashboardMetrics();
                        renderSheetTable();
                    } else {
                        showToast(__("Error al eliminar de la BD: ") + (res.message || ""), "warning");
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast(__("Error al eliminar vehículo en el servidor."), "warning");
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
                srvDropdown.innerHTML = `<option value="">${__('Selecciona un profesional...')}</option>`;
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
                showToast(__("Por favor selecciona un instructor."), "warning");
                return;
            }

            if (rating === 0 || vehRating === 0) {
                showToast(__("Por favor califica ambas opciones con estrellas antes de enviar."), "warning");
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

                    showToast(__("¡Muchas gracias por evaluar nuestro servicio!"), "success");

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
                            showToast(__("Atención: Alerta roja de satisfacción generada en tu Dashboard."), "warning");
                        }
                    }, 1000);
                } else {
                    showToast(__("Error de validación al enviar encuesta: ") + (res.message || ""), "warning");
                }
            })
            .catch(err => {
                console.error(err);
                showToast(__("Error al enviar la evaluación al servidor."), "warning");
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
                    showToast(res.is_public ? __("Reseña publicada en la web.") : __("Reseña ocultada de la web."), "success");
                } else {
                    showToast(__("Error al cambiar la visibilidad: ") + (res.message || ""), "warning");
                }
            })
            .catch(err => {
                console.error(err);
                showToast(__("Error al cambiar la visibilidad de la reseña."), "warning");
            });
        }

