        // Shows what the instructor and the student already have booked on the chosen day
        function renderAgendaBusySlots() {
            const box = document.getElementById('lesson-busy-slots');
            const instructorId = document.getElementById('lesson-instructor').value;
            const studentId = document.getElementById('lesson-student').value;
            const date = document.getElementById('lesson-date').value;

            const help = document.getElementById('lesson-instructor-help');
            const instructor = instructors.find(i => i.db_id == instructorId);
            if (instructor) {
                help.textContent = `${__('Horario: :shift · Vehículo: :vehicle', { shift: instructor.shift, vehicle: instructor.vehicle })}${instructor.plate && instructor.plate !== '-' ? ` [${instructor.plate}]` : ''}`;
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
                        ? __('Instructor y estudiante')
                        : (l.instructor_id == instructorId ? `${__('Instructor')} · ${esc(l.student_name)}` : `${__('Estudiante')} · ${esc(l.instructor_name)}`);
                    const rt = isRoadTest(l);
                    return `<li class="flex items-center justify-between gap-2 ${rt ? 'rounded-md bg-violet-500/15 px-2 py-1 -mx-2' : ''}"><span class="font-mono font-bold ${rt ? 'text-violet-300' : 'text-slate-200'}">${l.time}–${l.end_time}${rt ? ' <span class="font-sans text-[10px] font-black">ROAD TEST</span>' : ''}</span><span class="text-slate-500 truncate">${who}</span></li>`;
                }).join('')
                : `<li class="text-slate-500">${__('Sin clases ese día. Todo el horario está libre.')}</li>`;

            box.innerHTML = `
                <div class="rounded-xl border border-slate-800 bg-slate-950 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2"><i class="fa-regular fa-clock"></i> ${__('Horarios ocupados ese día')}</p>
                    <ul class="space-y-1 text-xs">${items}</ul>
                    ${dayLessons.some(isRoadTest) || getLessonType() === 'road_test' ? `<p class="text-[10px] text-violet-400 mt-2"><i class="fa-solid fa-circle-info"></i> ${__('Debe quedar al menos :gap libre entre un Road Test y otra clase.', { gap: formatMinutes(ROAD_TEST_GAP) })}</p>` : ''}
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
                showToast(__('No se puede agendar una clase en una fecha u hora que ya pasó.'), 'warning');
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

                    const toastVars = { student: res.lesson.student_name, instructor: res.lesson.instructor_name, date: res.lesson.date, time: res.lesson.time };
                    showToast(isRoadTest(res.lesson)
                        ? (rescheduling ? __('Road Test reprogramado(a): :student con :instructor, :date :time.', toastVars) : __('Road Test agendado(a): :student con :instructor, :date :time.', toastVars))
                        : (rescheduling ? __('Clase reprogramado(a): :student con :instructor, :date :time.', toastVars) : __('Clase agendado(a): :student con :instructor, :date :time.', toastVars)), 'success');
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
                ? (rescheduling ? __('Reprogramar Road Test') : __('Agendar Road Test'))
                : (rescheduling ? __('Reprogramar Clase') : __('Agendar Clase de Manejo'));
            document.getElementById('lesson-form-subtitle').textContent = roadTest
                ? __('Examen de carretera: instructor, fecha, hora y sitio del examen.')
                : (rescheduling
                    ? __('Cambia instructor, fecha, hora o duración de la clase.')
                    : __('Asigna instructor, fecha y hora según las clases del paquete del estudiante.'));
            document.getElementById('lesson-form-icon').className = `fa-solid ${rescheduling ? 'fa-pen-to-square' : (roadTest ? 'fa-flag-checkered' : 'fa-calendar-plus')} ${roadTest ? 'text-violet-400' : 'text-amber-500'}`;
            document.getElementById('lesson-submit-text').textContent = rescheduling ? __('Guardar Cambios') : (roadTest ? __('Agendar Road Test') : __('Agendar Clase'));
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
                __('Reprogramando :id de :student: actualmente :date :time–:end con :instructor.', { id: lesson.id, student: lesson.student_name, date: lesson.date, time: lesson.time, end: lesson.end_time, instructor: lesson.instructor_name });
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
            const qv = { name: lesson.student_name, date: lesson.date, time: lesson.time };
            const question = isRoadTest(lesson) ? {
                completed: __('¿Marcar como realizado el Road Test de :name (:date :time)?', qv),
                no_show: __('¿Marcar que :name no se presentó al Road Test?', qv),
                cancelled: __('¿Cancelar el Road Test de :name (:date :time)?', qv),
            }[status] : {
                completed: __('¿Marcar como completada la clase de :name (:date :time)?', qv),
                no_show: __('¿Marcar que :name no asistió? Las horas de la clase se descontarán del paquete.', qv),
                cancelled: __('¿Cancelar la clase de :name (:date :time)? Las horas volverán a quedar disponibles.', qv),
            }[status];
            if (!confirm(question)) return;

            return apiRequest(`{{ url('lessons') }}/${dbId}/status`, 'PUT', { status })
                .then(res => {
                    const idx = lessons.findIndex(l => l.db_id === dbId);
                    if (idx !== -1) lessons[idx] = res.lesson;
                    const student = students.find(s => s.db_id === res.student.db_id);
                    if (student) student.used_minutes = res.student.used_minutes;

                    showToast(__('Clase :id: :status.', { id: res.lesson.id, status: lessonStatusLabel(res.lesson.status) }), 'success');
                    showLessonNotifications(res.notifications);
                    if (editingLessonId === dbId) cancelLessonEdit(); else initAgenda();
                })
                .catch(err => showToast(err.message, 'warning'));
        }

        // Tells who received the calendar invitation / SMS and who could not be notified
        function showLessonNotifications(n) {
            if (!n) return;
            if (n.sent && n.sent.length) {
                showToast(__('Notificado: :list.', { list: n.sent.join(', ') }), 'info');
            }
            if (n.failed && n.failed.length) {
                showToast(__('No se pudo notificar a: :list. Revisa sus datos de contacto o la configuración de correo.', { list: n.failed.join(', ') }), 'warning');
            }
        }

        // ---------- Calendar: lessons of every instructor by week / day, drag & drop to reschedule ----------
        const CAL_SLOT_MIN = 15;     // grid step (minutes)
        const CAL_SLOT_PX = 22;      // height of one step
        const CAL_COLORS = ['#2563eb', '#16a34a', '#0891b2', '#ea580c', '#db2777', '#4f46e5', '#65a30d', '#ca8a04', '#0d9488', '#be123c'];
        const CAL_DAY_NAMES = [__('Dom'), __('Lun'), __('Mar'), __('Mié'), __('Jue'), __('Vie'), __('Sáb')];
        const CAL_MONTHS = [__('ene'), __('feb'), __('mar'), __('abr'), __('may'), __('jun'), __('jul'), __('ago'), __('sep'), __('oct'), __('nov'), __('dic')];

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
            }).join('') || `<span class="text-slate-500">${__('No hay instructores registrados.')}</span>`;

            const grid = document.getElementById('cal-grid');
            if (!shown.length) {
                grid.innerHTML = `<p class="text-sm text-slate-500 text-center py-16">${__('Selecciona al menos un instructor para ver su calendario.')}</p>`;
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
                    <p class="text-[10px] font-bold uppercase ${off ? 'text-slate-600' : 'text-slate-400'}">${CAL_DAY_NAMES[dt.getDay()]}${off ? ` · ${__('libre')}` : ''}</p>
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
                        title="${esc(`${l.student_name} · ${calTime12(start)} – ${calTime12(end)} · ${roadTest ? 'Road Test' : __('Clase')} (${lessonStatusLabel(l.status)})${movable ? ` · ${__('Arrastra para mover')}` : ''}`)}">
                        <p class="font-bold whitespace-nowrap"><i class="fa-solid ${icon}"></i> ${calTime12(start)}</p>
                        <p class="font-bold uppercase break-words">${esc(l.student_name)}</p>
                        ${h > 44 ? `<p class="opacity-85 truncate">${roadTest ? 'Road Test' : __('Clase :min min', { min: l.duration_minutes })}</p>` : ''}
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
            if (date < today || (date === today && start < calNowMin())) return __('No se puede agendar en una hora que ya pasó.');
            if (end > 24 * 60) return __('La clase debe terminar el mismo día.');

            const ins = instructors.find(i => i.db_id === insId);
            const wh = ins && calWorkHours(ins, date);
            if (!wh) return ins ? __(':name no trabaja ese día.', { name: ins.name }) : __('El instructor no trabaja ese día.');
            if (start < wh[0] || end > wh[1]) return __('Fuera del horario de :name (:shift).', { name: ins.name, shift: ins.shift });

            const overlaps = l => l.date === date && calActive(l) && (!lesson || l.db_id !== lesson.db_id)
                && calMin(l.time) < end && (calMin(l.end_time) || 24 * 60) > start;
            if (lessons.some(l => l.instructor_id === insId && overlaps(l))) return __('El instructor ya tiene una clase en ese horario.');
            if (lesson && lessons.some(l => l.student_id === lesson.student_id && overlaps(l))) return __('El estudiante ya tiene una clase en ese horario.');
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
            if (!confirm(__('¿Agendar una clase con :name el :day a las :time?', { name: ins.name, day: calDayText(date), time: calTime12(start) }))) return;
            switchTab('agenda');
            setLessonType('lesson');
            document.getElementById('lesson-instructor').value = insId;
            document.getElementById('lesson-date').value = date;
            document.getElementById('lesson-time').value = calHhmm(start);
            handleAgendaStudentChange();
            document.getElementById('lesson-student').focus();
            showToast(__('Selecciona el estudiante para completar la clase.'), 'info');
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
            const change = t.insId !== l.instructor_id ? ` ${__('con :name (antes :previous)', { name: ins.name, previous: l.instructor_name })}` : '';
            const moveVars = { name: l.student_name, from: `${calDayText(l.date)} ${calTime12(calMin(l.time))}`, to: `${calDayText(t.date)} ${calTime12(t.start)}${change}` };
            if (!confirm(isRoadTest(l)
                ? __('¿Mover el Road Test de :name?\n\nDe: :from\nA:  :to\n\nSe notificará al estudiante y al instructor.', moveVars)
                : __('¿Mover la clase de :name?\n\nDe: :from\nA:  :to\n\nSe notificará al estudiante y al instructor.', moveVars))) return;

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

                const movedVars = { name: res.lesson.student_name, day: calDayText(res.lesson.date), time: calTime12(calMin(res.lesson.time)), instructor: res.lesson.instructor_name };
                showToast(isRoadTest(res.lesson)
                    ? __('Road Test movida: :name, :day :time con :instructor.', movedVars)
                    : __('Clase movida: :name, :day :time con :instructor.', movedVars), 'success');
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
                        <button type="button" onclick="openStudentProfile(${l.student_id})" class="${btn} bg-slate-800 text-slate-300 hover:bg-slate-700"><i class="fa-solid fa-eye"></i> ${__('Estudiante')}</button>
                        ${scheduled ? `
                            <button type="button" onclick="switchTab('agenda'); startLessonReschedule(${l.db_id})" class="${btn} bg-amber-500/10 text-amber-500 hover:bg-amber-500 hover:text-slate-950"><i class="fa-solid fa-pen"></i> ${__('Editar')}</button>
                            <button type="button" onclick="setLessonStatus(${l.db_id}, 'completed')" class="${btn} bg-green-500/10 text-green-400 hover:bg-green-500 hover:text-slate-950"><i class="fa-solid fa-check"></i> ${__('Completada')}</button>
                            <button type="button" onclick="setLessonStatus(${l.db_id}, 'no_show')" class="${btn} bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950"><i class="fa-solid fa-user-xmark"></i> ${__('No asistió')}</button>
                            <button type="button" onclick="setLessonStatus(${l.db_id}, 'cancelled')" class="${btn} bg-slate-800 text-slate-400 hover:bg-red-600 hover:text-white"><i class="fa-solid fa-ban"></i> ${__('Cancelar')}</button>` : ''}
                        <button type="button" onclick="calSelectedId = null; renderCalendar()" class="${btn} text-slate-500 hover:text-slate-200" title="${__('Cerrar')}"><i class="fa-solid fa-xmark"></i></button>
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
                emptyText: __('No hay clases agendadas'),
                searchPlaceholder: __('Buscar por estudiante, instructor, vehículo o notas...'),
                searchExtra: l => [l.notes, l.vehicle, l.location, isRoadTest(l) ? 'road test' : ''],
                dateField: l => l.date,
                filters: [
                    { id: 'type', label: __('Tipo'), value: l => l.type, format: t => t === 'road_test' ? 'Road Test' : __('Clase de manejo') },
                    { id: 'status', label: __('Estado'), value: l => l.status, format: lessonStatusLabel },
                    { id: 'instructor', label: __('Instructor'), value: l => l.instructor_name },
                ],
                columns: [
                    { label: __('Fecha'), value: l => l.date, sortValue: l => l.date + l.time },
                    { label: __('Hora'), value: l => `${l.time}–${l.end_time}` },
                    { label: __('Estudiante'), value: l => l.student_name },
                    { label: __('Instructor'), value: l => l.instructor_name },
                    { label: __('Duración'), value: l => l.duration_minutes },
                    { label: __('Estado'), value: l => lessonStatusLabel(l.status) },
                    { label: __('Acciones'), thClass: `${th} text-center` },
                ],
                row: l => {
                    const st = LESSON_STATUS[l.status] || { label: l.status, cls: 'bg-slate-800 text-slate-400' };
                    const actions = l.status === 'scheduled'
                        ? `<button onclick="startLessonReschedule(${l.db_id})" title="${__('Reprogramar')}" class="bg-amber-500/10 text-amber-500 hover:bg-amber-500 px-2 py-1 rounded text-[10px] font-bold transition hover:text-slate-950"><i class="fa-solid fa-pen-to-square"></i></button>`
                          + actionBtn(l.db_id, 'completed', 'fa-check', __('Marcar como completada'), 'bg-green-500/10 text-green-400 hover:bg-green-500')
                          + actionBtn(l.db_id, 'no_show', 'fa-user-xmark', __('No asistió'), 'bg-red-500/10 text-red-400 hover:bg-red-500')
                          + actionBtn(l.db_id, 'cancelled', 'fa-ban', __('Cancelar clase'), 'bg-slate-800 text-slate-400 hover:bg-amber-500')
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
                    document.getElementById('lessons-count-badge').textContent = __(':n próximas', { n: upcoming });
                },
            });
        }

        // ================= PAQUETES (CATÁLOGO) =================
        let editingPackageId = null;

        function updatePackageDurationHint() {
            const n = parseInt(document.getElementById('pkg-lessons').value, 10) || 0;
            document.getElementById('pkg-duration-hint').textContent = n > 0
                ? __(':lessons de :min min = :total de manejo en total.', { lessons: lessonsText(n * LESSON_MINUTES), min: LESSON_MINUTES, total: formatMinutes(n * LESSON_MINUTES) })
                : __('Sin clases de manejo (por ejemplo, un curso teórico).');
        }

        function resetPackageForm() {
            editingPackageId = null;
            document.getElementById('package-form').reset();
            document.getElementById('package-form-title').textContent = __('Nuevo Paquete');
            document.getElementById('package-form-icon').className = 'fa-solid fa-box text-amber-500';
            document.getElementById('package-submit-text').textContent = __('Crear Paquete');
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

            document.getElementById('package-form-title').textContent = __('Editar Paquete');
            document.getElementById('package-form-icon').className = 'fa-solid fa-pen-to-square text-amber-500';
            document.getElementById('package-submit-text').textContent = __('Guardar Cambios');
            document.getElementById('package-edit-banner-text').textContent = __('Editando ":name".', { name: pkg.name });
            document.getElementById('package-edit-banner').classList.remove('hidden');

            const buyers = document.getElementById('pkg-existing-buyers');
            if (pkg.students_count > 0) {
                document.getElementById('pkg-existing-buyers-text').textContent =
                    __(':n estudiante(s) ya compraron este paquete y conservarán el precio y las clases que pagaron. Los cambios aplican solo a las nuevas compras.', { n: pkg.students_count });
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
                    showToast(editing ? __('Paquete ":name" actualizado.', { name: res.package.name }) : __('Paquete ":name" creado.', { name: res.package.name }), 'success');
                    resetPackageForm();
                })
                .catch(err => showToast(err.message, 'warning'))
                .finally(() => { btn.disabled = false; });
        }

        function togglePackageActive(id) {
            const pkg = packages.find(p => p.id === id);
            if (!pkg) return;

            const activate = !pkg.is_active;
            if (!activate && !confirm(__('¿Desactivar ":name"? Ya no se podrá vender, pero los estudiantes que lo compraron lo conservan.', { name: pkg.name }))) return;

            apiRequest(`{{ url('packages') }}/${id}/active`, 'PUT', { is_active: activate })
                .then(res => {
                    upsertPackage(res.package);
                    showToast(activate ? __('Paquete ":name" activado.', { name: res.package.name }) : __('Paquete ":name" desactivado.', { name: res.package.name }), 'success');
                })
                .catch(err => {
                    showToast(err.message, 'warning');
                    if (activate && pkg.price <= 0) editPackage(id);
                });
        }

        function deletePackage(id) {
            const pkg = packages.find(p => p.id === id);
            if (!pkg || !confirm(__('¿Eliminar el paquete ":name"? Esta acción no se puede deshacer.', { name: pkg.name }))) return;

            apiRequest(`{{ url('packages') }}/${id}`, 'DELETE')
                .then(() => {
                    packages = packages.filter(p => p.id !== id);
                    if (editingPackageId === id) resetPackageForm();
                    renderPackagesTable();
                    if (!editingStudentId) { renderPlanOptions(); updatePlanCost(); }
                    showToast(__('Paquete ":name" eliminado.', { name: pkg.name }), 'success');
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
                emptyText: __('No hay paquetes creados'),
                searchPlaceholder: __('Buscar paquete...'),
                searchExtra: p => [p.description],
                filters: [
                    { id: 'status', label: __('Estado'), value: p => p.is_active ? 'Activo' : 'Inactivo', format: v => __(v) },
                ],
                columns: [
                    { label: __('Paquete'), value: p => p.name },
                    { label: __('Clases'), value: p => p.lessons, thClass: `${th} text-center` },
                    { label: __('Precio'), value: p => p.price, thClass: `${th} text-right` },
                    { label: __('Vendidos'), value: p => p.students_count, thClass: `${th} text-center` },
                    { label: __('Estado'), value: p => p.is_active ? 'Activo' : 'Inactivo' },
                    { label: __('Acciones'), thClass: `${th} text-center` },
                ],
                row: p => `
                    <td class="px-4 py-3">
                        <span class="font-semibold text-slate-100">${esc(p.name)}</span>
                        ${p.description ? `<span class="block text-[11px] text-slate-500">${esc(p.description)}</span>` : ''}
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        ${p.lessons
                            ? `<span class="font-bold text-slate-200">${p.lessons}</span><span class="block text-[10px] text-slate-500">${formatMinutes(p.lessons * LESSON_MINUTES)}</span>`
                            : `<span class="text-xs text-slate-500">${__('Sin manejo')}</span>`}
                    </td>
                    <td class="px-4 py-3 text-right font-bold font-mono whitespace-nowrap ${p.price > 0 ? 'text-emerald-400' : 'text-red-400'}">
                        ${p.price > 0 ? '$' + p.price.toFixed(2) : __('Sin precio')}
                    </td>
                    <td class="px-4 py-3 text-center text-slate-400">${p.students_count}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap ${p.is_active ? 'bg-green-500/10 text-green-400' : 'bg-slate-800 text-slate-500'}">${p.is_active ? __('Activo') : __('Inactivo')}</span>
                    </td>
                    <td class="px-4 py-3 text-center space-x-1 whitespace-nowrap">
                        <button onclick="editPackage(${p.id})" title="${__('Editar')}" class="bg-amber-500/10 text-amber-500 hover:bg-amber-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition"><i class="fa-solid fa-pen"></i></button>
                        <button onclick="togglePackageActive(${p.id})" title="${p.is_active ? __('Desactivar') : __('Activar')}"
                            class="${p.is_active ? 'bg-slate-800 text-slate-400 hover:bg-slate-500' : 'bg-green-500/10 text-green-400 hover:bg-green-500'} hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition">
                            <i class="fa-solid ${p.is_active ? 'fa-eye-slash' : 'fa-eye'}"></i>
                        </button>
                        ${p.students_count === 0
                            ? `<button onclick="deletePackage(${p.id})" title="${__('Eliminar')}" class="bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition"><i class="fa-solid fa-trash-can"></i></button>`
                            : ''}
                    </td>`,
                afterRender: () => {
                    const active = packages.filter(p => p.is_active).length;
                    document.getElementById('packages-count-badge').textContent = __(':n activos', { n: active });
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
                document.getElementById('student-form-title').innerText = __('Nuevo estudiante');
                document.getElementById('student-form-subtitle').innerText = __('Completa la ficha y presiona Guardar.');
                document.getElementById('student-submit-btn-text').innerText = __('Registrar Alumno');
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
                document.getElementById('instructor-form-title').innerText = __('Ficha de Registro de Instructor');
                document.getElementById('instructor-form-subtitle').innerText = __('Crea nuevos expedientes de instructores que se sincronizarán directamente con la base de datos.');
                document.getElementById('instructor-submit-btn-text').innerText = __('Registrar Instructor');
                document.getElementById('instructor-submit-btn-icon').className = "fa-solid fa-save";
            });

            // Coming back from the ES / EN switch: reopen the tab that was open
            try {
                const tab = sessionStorage.getItem('nyds-tab');
                sessionStorage.removeItem('nyds-tab');
                if (tab && tab !== 'dashboard' && document.getElementById(`view-${tab}`)) switchTab(tab);
            } catch (e) {}

            document.getElementById('vehicle-form').addEventListener('reset', () => {
                editingVehicleId = null;
                document.getElementById('vehicle-form-title').innerText = __('Ficha de Registro de Vehículo');
                document.getElementById('vehicle-form-subtitle').innerText = __('Crea nuevos expedientes de vehículos que se sincronizarán directamente con la base de datos.');
                document.getElementById('vehicle-submit-btn-text').innerText = __('Registrar Vehículo');
                document.getElementById('vehicle-submit-btn-icon').className = "fa-solid fa-save";
            });
        };
