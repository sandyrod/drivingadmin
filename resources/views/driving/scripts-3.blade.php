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
                emptyText: __('No hay movimientos de abonos registrados'),
                searchPlaceholder: __('Buscar por ID, estudiante, método o notas...'),
                searchExtra: p => [p.notes],
                dateField: p => p.payment_date,
                filters: [
                    { id: 'method', label: __('Forma de Pago'), value: p => p.payment_method, format: paymentMethodLabel },
                ],
                columns: [
                    { label: __('ID Abono'), value: p => p.id, sortValue: p => p.db_id },
                    { label: __('Estudiante'), value: p => p.student_name },
                    { label: __('Fecha Pago'), value: p => p.payment_date },
                    { label: __('Forma de Pago'), value: p => paymentMethodLabel(p.payment_method) },
                    { label: __('Monto'), value: p => p.amount, thClass: `${th} text-right` },
                    { label: __('Deuda Restante'), value: p => p.remaining_debt, thClass: `${th} text-right` },
                    { label: __('Acciones'), thClass: 'px-4 py-3 text-center' },
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
                                title="${__('Recibo de este pago')}">
                                <i class="fa-solid fa-print"></i>
                                <span>${__('Recibo')}</span>
                            </button>
                            <button onclick="openInvoice(${p.student_id})"
                                class="bg-sky-500/10 text-sky-400 hover:bg-sky-500 hover:text-slate-950 p-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5"
                                title="${__('Invoice del alumno (paquete, pagos y balance)')}">
                                <i class="fa-solid fa-file-invoice"></i>
                                <span>${__('Invoice')}</span>
                            </button>
                        </div>
                    </td>`,
                afterRender: () => {
                    const badge = document.getElementById('movements-count-badge');
                    if (badge) badge.textContent = __(':n abonos', { n: payments.length });
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
                    <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400">${__('Clases de Manejo')}</h4>
                    <button type="button" onclick="openAgendaForStudent(${student.db_id})" class="text-xs font-bold text-amber-500 hover:underline">
                        <i class="fa-solid fa-calendar-plus"></i> ${__('Agendar clase')}
                    </button>
                </div>
                ${st.total ? `
                <div class="grid grid-cols-2 gap-2">
                    ${tile(__('Compradas'), count(st.total), 'text-slate-100', __('de :min min', { min: LESSON_MINUTES }))}
                    ${tile(__('Tomadas'), count(st.taken), 'text-green-500', st.noShow ? __('+ :n no asistió', { n: count(st.noShow) }) : '')}
                    ${tile(__('Agendadas'), count(st.scheduled), 'text-amber-500', __('por tomar'))}
                    ${tile(__('Le quedan'), count(st.left), st.left ? 'text-sky-400' : 'text-red-500', __('sin agendar'))}
                </div>` : `<p class="text-xs text-slate-500">${student.package_id ? __('El paquete del estudiante no incluye clases de manejo.') : __('El estudiante no tiene un paquete asignado.')}</p>`}
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
            if (!student) return showToast(__('No se encontró el estudiante.'), 'warning');
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
                else showToast(__('El estudiante no tiene saldo pendiente.'), 'info');
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
            navigator.clipboard?.writeText(text).then(() => showToast(__('Copiado: :text', { text }), 'success'));
        }

        function renderStudentFormName() {
            const name = ['stu-last-name', 'stu-first-name', 'stu-middle-name']
                .map(id => document.getElementById(id).value.trim()).filter(Boolean).join(' ');
            document.getElementById('sf-name').textContent = name || __('Nuevo estudiante');
        }

        function renderStudentAge() {
            const age = ageFrom(document.getElementById('stu-birth-date').value);
            document.getElementById('sf-age').textContent = age !== null && age >= 0 ? __('(:age años)', { age }) : '';
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
            select.innerHTML = `<option value="">${__('Ninguno')}</option>` + [...instructors]
                .sort((a, b) => a.name.localeCompare(b.name))
                .map(i => `<option value="${i.db_id}">${esc(i.name)}${i.status === 'Suspendido' ? ' ' + __('(suspendido)') : ''}</option>`).join('');
            select.value = selected ? String(selected) : '';
        }

        // Everything on the student page that is not a form input: header, cards and the other tabs
        function renderStudentFormExtras(student) {
            const has = !!student;
            document.querySelectorAll('#view-inscripcion .sf-needs-student').forEach(b => { b.disabled = !has; });

            const badge = document.getElementById('sf-balance-badge');
            if (has) {
                badge.textContent = student.debt > 0 ? money(student.debt) : __('Al día');
                badge.className = 'px-3 py-1 rounded-full text-sm font-black ' + (student.debt > 0 ? 'bg-red-600 text-white' : 'bg-green-600 text-white');
            } else {
                badge.className = 'hidden';
            }

            document.getElementById('sf-paid-label').textContent = has ? __('Pagado') : __('Pago inicial');

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
                ? __('Guarda el estudiante para poder agendar su Road Test.')
                : roadTest ? `
                    <p class="sf-label">${__('Hora de la cita')}</p>
                    <div class="flex items-center justify-between gap-2"><p class="text-sm font-semibold text-slate-100 font-mono">${esc(roadTest.date)} ${esc(roadTest.time)}</p>${statusBadge(roadTest)}</div>
                    <p class="sf-label mt-2">${__('Lugar del examen')}</p>
                    <p class="text-sm font-semibold text-slate-100">${esc(roadTest.location || '—')}</p>
                    ${roadTest.location ? `<div class="flex gap-4 mt-1">${mapLinks(roadTest.location)}</div>` : ''}
                    <p class="sf-label mt-2">${__('Instructor')}</p>
                    <p class="text-sm font-semibold text-slate-100">${esc(roadTest.instructor_name)}</p>`
                : __('Sin Road Test agendado.');

            // Appointments card: upcoming lessons
            const upcoming = ownLessons.filter(l => l.status === 'scheduled' && l.date >= today).sort(byStart);
            document.getElementById('sf-appointments-badge').innerHTML = upcoming.length
                ? `<span class="bg-green-600 text-white text-[10px] font-black rounded-full px-2 py-0.5">${upcoming.length}</span>` : '';
            document.getElementById('sf-appointments').innerHTML = !has ? __('Sin clases todavía.')
                : upcoming.length ? `<ul class="space-y-1.5">${upcoming.slice(0, 5).map(l => `
                    <li class="flex items-center justify-between gap-2 ${isRoadTest(l) ? 'text-violet-500 font-semibold' : 'text-slate-300'}">
                        <span class="font-mono">${esc(l.date)} ${esc(l.time)}</span>
                        <span class="truncate">${isRoadTest(l) ? 'ROAD TEST' : esc(l.instructor_name)}</span>
                    </li>`).join('')}</ul>${upcoming.length > 5 ? `<p class="mt-1 text-slate-500">${__('y :n más...', { n: upcoming.length - 5 })}</p>` : ''}`
                : __('Sin clases próximas.');

            // Last note card
            const last = notes[0];
            document.getElementById('sf-last-note').innerHTML = !has ? __('Guarda el estudiante para agregar notas.')
                : last ? `<p class="text-[11px] text-slate-500">${esc(last.created_at)}${last.author ? ' · ' + esc(last.author) : ''}</p>
                          <p class="text-sm text-slate-200 uppercase whitespace-pre-line mt-0.5">${esc(last.body)}</p>`
                : __('Sin notas.');

            renderStudentNotes(notes);

            // Lessons tab
            document.getElementById('sf-tab-lessons').innerHTML = ownLessons.length ? `
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[10px] text-slate-500 uppercase tracking-wider border-b border-slate-800">
                            <tr><th class="py-2 pr-3">${__('Fecha')}</th><th class="py-2 pr-3">${__('Hora')}</th><th class="py-2 pr-3">${__('Tipo')}</th><th class="py-2 pr-3">${__('Instructor')}</th><th class="py-2 pr-3">${__('Vehículo')}</th><th class="py-2 pr-3">${__('Estado')}</th><th class="py-2">${__('Notas / Lugar')}</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            ${ownLessons.map(l => `
                                <tr onclick="openLessonEditor(${l.db_id})" title="${escapeAttr(l.status === 'scheduled' ? __('Clic para editar o reprogramar') : __('Clic para ver la clase'))}"
                                    class="cursor-pointer transition ${isRoadTest(l) ? 'bg-violet-500/10 hover:bg-violet-500/20' : 'hover:bg-slate-800/50'}">
                                    <td class="py-2 pr-3 font-mono text-slate-200 whitespace-nowrap">${esc(l.date)}</td>
                                    <td class="py-2 pr-3 font-mono text-slate-400 whitespace-nowrap">${esc(l.time)}–${esc(l.end_time)}</td>
                                    <td class="py-2 pr-3 whitespace-nowrap">${isRoadTest(l)
                                        ? '<span class="bg-violet-600 text-white text-[10px] font-black px-2 py-0.5 rounded">ROAD TEST</span>'
                                        : `<span class="text-xs text-slate-400">${esc(lessonsText(l.duration_minutes))}</span>`}</td>
                                    <td class="py-2 pr-3 text-slate-300">${esc(l.instructor_name)}</td>
                                    <td class="py-2 pr-3 text-xs text-slate-500">${esc(l.vehicle || '—')}</td>
                                    <td class="py-2 pr-3">${statusBadge(l)}</td>
                                    <td class="py-2 text-xs text-slate-400">
                                        ${l.location ? `<span class="text-violet-300">${esc(l.location)}</span><span class="flex gap-3 mt-0.5" onclick="event.stopPropagation()">${mapLinks(l.location)}</span>` : ''}
                                        ${l.notes ? `<span class="block">${esc(l.notes)}</span>` : ''}
                                    </td>
                                </tr>`).join('')}
                        </tbody>
                    </table>
                </div>` : `<p class="text-sm text-slate-500 text-center py-8">${__('El estudiante no tiene clases agendadas.')}</p>`;

            // Receipts tab
            if (!has) {
                document.getElementById('sf-tab-payments').innerHTML = '';
                return;
            }
            const initial = Math.max(0, Math.round((student.paid - ownPayments.reduce((sum, p) => sum + p.amount, 0)) * 100) / 100);
            document.getElementById('sf-tab-payments').innerHTML = `
                <div class="flex flex-wrap items-end gap-3 mb-5">
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 min-w-[120px]"><p class="text-[10px] uppercase font-bold text-slate-500">${__('Total')}</p><p class="text-lg font-black text-slate-100">${money(student.cost)}</p></div>
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 min-w-[120px]"><p class="text-[10px] uppercase font-bold text-slate-500">${__('Pagos')}</p><p class="text-lg font-black text-green-500">${money(student.paid)}</p></div>
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 min-w-[120px]"><p class="text-[10px] uppercase font-bold text-slate-500">${__('Saldo')}</p><p class="text-lg font-black ${student.debt > 0 ? 'text-red-500' : 'text-green-500'}">${money(student.debt)}</p></div>
                    <span class="flex-1"></span>
                    <button type="button" onclick="studentFormAction('invoice')" class="sf-action"><i class="fa-solid fa-file-invoice"></i> ${__('Invoice')}</button>
                    <button type="button" onclick="studentFormAction('pay')" class="bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 px-3 py-2 rounded-lg text-xs font-bold transition"><i class="fa-solid fa-dollar-sign"></i> ${__('Registrar pago')}</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[10px] text-slate-500 uppercase tracking-wider border-b border-slate-800">
                            <tr><th class="py-2 pr-3">${__('Fecha')}</th><th class="py-2 pr-3">${__('Recibo')}</th><th class="py-2 pr-3">${__('Método')}</th><th class="py-2 pr-3 text-right">${__('Pago')}</th><th class="py-2 pr-3 text-right">${__('Saldo')}</th><th class="py-2"></th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            ${initial > 0 ? `
                                <tr><td class="py-2 pr-3 font-mono text-slate-400">${esc(student.date)}</td><td class="py-2 pr-3 text-slate-400" colspan="2">${__('Pago inicial (inscripción)')}</td>
                                    <td class="py-2 pr-3 text-right font-bold text-green-500">${money(initial)}</td><td></td><td></td></tr>` : ''}
                            ${ownPayments.map(p => `
                                <tr>
                                    <td class="py-2 pr-3 font-mono text-slate-400">${esc(p.payment_date)}</td>
                                    <td class="py-2 pr-3 font-bold text-amber-500">${esc(p.id)}</td>
                                    <td class="py-2 pr-3 text-slate-300">${esc(paymentMethodLabel(p.payment_method))}${p.notes ? `<span class="block text-[11px] text-slate-500">${esc(p.notes)}</span>` : ''}</td>
                                    <td class="py-2 pr-3 text-right font-bold text-green-500">${money(p.amount)}</td>
                                    <td class="py-2 pr-3 text-right text-slate-400">${money(p.remaining_debt)}</td>
                                    <td class="py-2 text-right"><button type="button" onclick="printReceipt(${p.db_id})" class="text-xs font-bold text-sky-400 hover:underline"><i class="fa-solid fa-receipt"></i> ${__('Recibo')}</button></td>
                                </tr>`).join('')}
                            ${!initial && !ownPayments.length ? `<tr><td colspan="6" class="py-6 text-center text-xs text-slate-500">${__('Sin pagos registrados.')}</td></tr>` : ''}
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
                        <button type="button" onclick="deleteStudentNote(${n.db_id})" class="hover:text-red-400" title="${__('Eliminar nota')}"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                    <p class="text-sm text-slate-200 whitespace-pre-line mt-1">${esc(n.body)}</p>
                </div>`).join('') : `<p class="text-xs text-slate-500">${__('Sin notas.')}</p>`;
        }

        function saveStudentNotes(student, res) {
            student.notes = res.notes;
            renderStudentFormExtras(student);
        }

        function addStudentNote() {
            const student = getEditingStudent();
            const box = document.getElementById('sf-note-body');
            const body = box.value.trim();
            if (!student || !body) return showToast(__('Escribe la nota primero.'), 'warning');

            apiRequest(`{{ url('students') }}/${student.db_id}/notes`, 'POST', { body })
                .then(res => {
                    box.value = '';
                    saveStudentNotes(student, res);
                    showToast(__('Nota agregada.'), 'success');
                })
                .catch(err => showToast(err.message, 'warning'));
        }

        function deleteStudentNote(noteId) {
            const student = getEditingStudent();
            if (!student || !confirm(__('¿Eliminar esta nota?'))) return;

            apiRequest(`{{ url('students') }}/${student.db_id}/notes/${noteId}`, 'DELETE')
                .then(res => saveStudentNotes(student, res))
                .catch(err => showToast(err.message, 'warning'));
        }

        // Instructor's personal "Mis clases" link (marks lessons as completed, no login)
        function instructorPortalLink(instructorDbId, mode) {
            const ins = instructors.find(i => i.db_id === instructorDbId);
            if (!ins) return;
            if (mode === 'send' && !confirm(__('¿Enviar a :name su enlace "Mis clases" por correo/SMS?', { name: ins.name }))) return;
            if (mode === 'regenerate' && !confirm(__('¿Generar un enlace nuevo para :name? El enlace anterior dejará de funcionar y habrá que enviarle el nuevo.', { name: ins.name }))) return;

            apiRequest(`{{ url('instructors') }}/${instructorDbId}/portal-link`, 'POST', {
                send: mode !== 'copy',
                regenerate: mode === 'regenerate',
            }).then(res => {
                if (mode === 'copy' || mode === 'regenerate') {
                    // The clipboard API only works over https; otherwise show the link to copy by hand
                    const manual = () => prompt(__('Copia el enlace "Mis clases":'), res.url);
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(res.url)
                            .then(() => showToast(__('Enlace "Mis clases" copiado. Pégalo en WhatsApp o SMS al instructor.'), 'success'))
                            .catch(manual);
                    } else {
                        manual();
                    }
                }
                if (res.sent.length) showToast(__('Enlace enviado a :name por :channels.', { name: ins.name, channels: res.sent.join(__(' y ')) }), 'success');
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
                showToast(__("Por favor selecciona un estudiante."), "warning");
                return;
            }

            const student = students.find(s => s.db_id == student_id);
            if (!student) return;

            if (amount <= 0) {
                showToast(__("El monto del abono debe ser mayor a cero."), "warning");
                return;
            }

            if (amount > student.debt) {
                showToast(__('El monto no puede superar la deuda del estudiante ($:debt USD).', { debt: student.debt }), "warning");
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

                    showToast(__('¡Abono registrado con éxito! Folio: :id', { id: newPayment.id }), "success");

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
                    showToast(__("Error de validación: ") + (res.message || __("Campos incompletos.")), "warning");
                }
            })
            .catch(err => {
                console.error(err);
                showToast(__("Error de red o de servidor al registrar abono."), "warning");
            });
        }

        // ================= AGENDA DE CLASES =================
        const LESSON_STATUS = {
            scheduled: { label: __('Programada'), cls: 'bg-amber-500/10 text-amber-500' },
            completed: { label: __('Completada'), cls: 'bg-green-500/10 text-green-400' },
            no_show:   { label: __('No asistió'), cls: 'bg-red-500/10 text-red-400' },
            cancelled: { label: __('Cancelada'), cls: 'bg-slate-800 text-slate-500' },
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
                    : (v === LESSON_MINUTES ? __('1 clase (:min min)', { min: v }) : __(':n clases seguidas (:duration)', { n: v / LESSON_MINUTES, duration: formatMinutes(v) }));
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
            return n === 1 ? __('1 clase') : __(':n clases', { n });
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
                    throw new Error(firstError || body.message || __('Error de red o de servidor.'));
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
            stuSelect.innerHTML = `<option value="">${__('Selecciona un estudiante...')}</option>`;
            [...students].sort((a, b) => a.name.localeCompare(b.name)).forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.db_id;
                opt.textContent = s.package_id
                    ? `${s.name} (${s.id}) — ${__(':lessons disponibles', { lessons: lessonsText(studentRemainingMinutes(s)) })}`
                    : `${s.name} (${s.id}) — ${__('sin paquete')}`;
                stuSelect.appendChild(opt);
            });
            stuSelect.value = selectedStudent;

            // Instructors (not suspended)
            const insSelect = document.getElementById('lesson-instructor');
            const selectedInstructor = insSelect.value;
            insSelect.innerHTML = `<option value="">${__('Selecciona un instructor...')}</option>`;
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
                            <i class="fa-solid fa-flag-checkered text-violet-400"></i> ${__('Road Test de :name', { name: esc(student.name) })}
                        </p>
                        <p class="text-xs text-slate-400">${__('No descuenta clases del paquete. Road Tests registrados:')} <span class="font-bold text-violet-400">${tests.length}</span></p>
                    </div>`;
                return;
            }

            // No package: let the user assign one
            if (!student.package_id) {
                const activePackages = packages.filter(p => p.is_active);
                const options = activePackages.map(p =>
                    `<option value="${p.id}">${esc(p.name)} — ${p.lessons ? lessonsText(p.lessons * LESSON_MINUTES) : __('sin manejo')} — $${p.price}</option>`).join('');
                if (!activePackages.length) {
                    panel.innerHTML = `
                        <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4 space-y-2">
                            <p class="text-sm font-semibold text-slate-100 flex items-center gap-2">
                                <i class="fa-solid fa-box-open text-amber-500"></i> Este estudiante no tiene paquete
                            </p>
                            <p class="text-xs text-slate-400">${__('No hay paquetes activos para asignar. Ve a')} <button type="button" onclick="switchTab('paquetes')" class="font-bold text-amber-500 hover:underline">${__('Paquetes')}</button> ${__('y activa al menos uno.')}</p>
                        </div>`;
                    return;
                }
                panel.innerHTML = `
                    <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4 space-y-3">
                        <p class="text-sm font-semibold text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-box-open text-amber-500"></i> Este estudiante no tiene paquete
                        </p>
                        <p class="text-xs text-slate-400">${student.plan ? __('Registro actual: ":plan".', { plan: esc(student.plan) }) + ' ' : ''}${__('Asígnale un paquete para poder agendar sus clases. El precio del paquete se sumará a su deuda, conservando lo que ya abonó ($:paid).', { paid: student.paid })}</p>
                        <select id="lesson-assign-package"
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:border-amber-500 transition text-sm">
                            ${options}
                        </select>
                        <button type="button" onclick="assignPackageToStudent()" id="lesson-assign-btn"
                            class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2 rounded-lg text-sm transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-box"></i> ${__('Asignar paquete')}
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
                footer = `<p class="text-xs text-red-400 font-semibold"><i class="fa-solid fa-circle-info"></i> ${__('Este paquete no incluye clases de manejo.')}</p>`;
            } else if (remaining <= 0) {
                footer = `<p class="text-xs text-red-400 font-semibold"><i class="fa-solid fa-circle-info"></i> ${__('El estudiante ya usó todas las clases de su paquete.')}</p>`;
            } else {
                footer = `<p class="text-xs text-slate-400">${__('Disponibles:')} <span class="font-bold text-green-500">${lessonsText(remaining)}</span> ${__('de :min min', { min: LESSON_MINUTES })}</p>`;
            }

            panel.innerHTML = `
                <div class="rounded-xl border border-slate-800 bg-slate-950 p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">${__('Paquete')}</p>
                            <p class="text-sm font-semibold text-slate-100">${esc(student.plan)}</p>
                        </div>
                        <span class="text-xs font-bold text-amber-500 whitespace-nowrap">${lessonsText(totalMin)}</span>
                    </div>
                    ${totalMin ? `
                    <div>
                        <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                            <div class="h-full bg-amber-500 rounded-full" style="width: ${pct}%"></div>
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5">${__('Usadas o agendadas: :used de :total', { used: lessonsText(used), total: lessonsText(totalMin) })}</p>
                    </div>` : ''}
                    ${footer}
                </div>`;
        }

        function assignPackageToStudent() {
            const student = getAgendaStudent();
            const packageId = document.getElementById('lesson-assign-package')?.value;
            if (!student || !packageId) return;

            const pkg = packages.find(p => p.id == packageId);
            if (!confirm(__('¿Asignar ":package" a :name?', { package: pkg.name, name: student.name }) + '\n' + __('Costo del plan: $:price', { price: pkg.price }))) return;

            const btn = document.getElementById('lesson-assign-btn');
            btn.disabled = true;

            apiRequest(`{{ url('students') }}/${student.db_id}/package`, 'PUT', { package_id: Number(packageId) })
                .then(res => {
                    const idx = students.findIndex(s => s.db_id === res.student.db_id);
                    if (idx !== -1) students[idx] = res.student;
                    showToast(__('Paquete asignado a :name.', { name: res.student.name }), 'success');
                    initAgenda();
                    updateDashboardMetrics();
                    renderSheetTable();
                })
                .catch(err => {
                    showToast(err.message, 'warning');
                    btn.disabled = false;
                });
        }

