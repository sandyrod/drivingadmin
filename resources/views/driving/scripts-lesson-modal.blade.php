        // ---------- Edit a lesson from the student's "Lessons" tab ----------
        // Modal calendar: every day is split in one column per instructor, so a click on a free slot
        // picks the new date, time AND instructor. Uses the calendar helpers (calParse, calMin, calWorkHours,
        // calColor...) from scripts-4.
        const LEM_SLOT_MIN = 15;     // start times every 15 minutes
        const LEM_SLOT_PX = 22;      // height of one step

        // { lessonId, draft, minutes, view: 'day'|'week', date, pick: { insId, date, start } | null }
        // draft = a lesson not saved yet (booking a lesson left in the package): lessonId is 0
        let lem = null;
        let lemSavedView = 'day';   // last view used (Day / Week)
        try { if (localStorage.getItem('nyds-lem-view') === 'week') lemSavedView = 'week'; } catch (e) {}

        function lemLesson() {
            if (!lem) return null;
            return lem.draft || lessons.find(l => l.db_id === lem.lessonId);
        }

        // Book one of the lessons left in the student's package (Lessons tab → "Por agendar")
        function openLessonBooker(studentDbId) {
            const student = students.find(s => s.db_id === Number(studentDbId));
            if (!student) return;
            const remaining = studentRemainingMinutes(student);
            const minutes = LESSON_DURATIONS.find(v => v === LESSON_MINUTES && v <= remaining)
                || LESSON_DURATIONS.filter(v => v <= remaining).pop();
            if (!minutes) return showToast(__('El estudiante ya usó todas las clases de su paquete.'), 'warning');

            const today = todayLocal();
            lem = {
                lessonId: 0,
                draft: {
                    db_id: 0, student_id: student.db_id, student_name: student.name, type: 'lesson', status: 'scheduled',
                    duration_minutes: minutes, instructor_id: student.preferred_instructor_id || 0,
                    date: today, time: '', end_time: '', notes: '', location: '',
                },
                minutes,
                view: lemSavedView,
                date: today,
                pick: null,
            };
            document.getElementById('lesson-modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            renderLessonEditor();
            lemScrollToFocus();
        }

        function lemWeekStart(ymd) {
            return calAddDays(ymd, -calParse(ymd).getDay());   // weeks start on Sunday, like the calendar
        }

        function lemDays() {
            if (lem.view === 'day') return [lem.date];
            const start = lemWeekStart(lem.date);
            return [...Array(7)].map((_, i) => calAddDays(start, i));
        }

        // Instructors offered: not suspended, plus the lesson's current one
        function lemInstructors() {
            const l = lemLesson();
            return instructors.filter(i => i.status !== 'Suspendido' || (l && i.db_id === l.instructor_id))
                .sort((a, b) => a.db_id - b.db_id);
        }

        function openLessonEditor(dbId) {
            const l = lessons.find(x => x.db_id === dbId);
            if (!l) return;
            const today = todayLocal();
            lem = {
                lessonId: dbId,
                minutes: l.duration_minutes,
                view: lemSavedView,
                date: l.date < today ? today : l.date,
                pick: null,
            };
            document.getElementById('lesson-modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            renderLessonEditor();
            lemScrollToFocus();
        }

        function closeLessonEditor() {
            lem = null;
            document.getElementById('lesson-modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && lem) closeLessonEditor();
        });

        // Show the lesson / the working hours without the user having to scroll
        function lemScrollToFocus() {
            const scroller = document.getElementById('lem-scroll');
            const target = document.querySelector('#lem-grid [data-lem-focus]');
            if (!scroller || !target) return;
            const offset = target.getBoundingClientRect().top - scroller.getBoundingClientRect().top + scroller.scrollTop;
            scroller.scrollTop = Math.max(0, offset - 130);   // leave room for the sticky headers
        }

        // Durations offered: road tests any; lessons only what fits in the package (this lesson's time is given back)
        function lemDurations(l) {
            if (isRoadTest(l)) return ROAD_TEST_DURATIONS.map(v => ({ v, ok: true }));
            const student = students.find(s => s.db_id === l.student_id);
            const own = l.db_id ? l.duration_minutes : 0;   // a new lesson has no time to give back
            const fits = (student ? studentRemainingMinutes(student) : 0) + own;
            return LESSON_DURATIONS.map(v => ({ v, ok: v <= fits || v === own }));
        }

        // Active lessons of that day, without the one being edited
        function lemDayLessons(l, date) {
            return lessons.filter(x => x.date === date && calActive(x) && x.db_id !== l.db_id);
        }

        // Why the lesson can't start at that minute with that instructor (null = free). Same rules as the server.
        function lemProblem(insId, date, start) {
            const l = lemLesson();
            const end = start + lem.minutes;
            const today = todayLocal();
            if (date < today || (date === today && start < calNowMin())) return __('No se puede agendar en una hora que ya pasó.');
            if (end > 24 * 60) return __('La clase debe terminar el mismo día.');

            const ins = instructors.find(i => i.db_id === insId);
            const wh = ins && calWorkHours(ins, date);
            if (!wh) return ins ? __(':name no trabaja ese día.', { name: ins.name }) : __('El instructor no trabaja ese día.');
            if (start < wh[0] || end > wh[1]) return __('Fuera del horario de :name (:shift).', { name: ins.name, shift: ins.shift });

            const others = lemDayLessons(l, date).filter(x => x.instructor_id === insId || x.student_id === l.student_id);
            const overlaps = (x, from, to) => calMin(x.time) < to && (calMin(x.end_time) || 24 * 60) > from;
            if (others.some(x => x.instructor_id === insId && overlaps(x, start, end))) return __('El instructor ya tiene una clase en ese horario.');
            if (others.some(x => x.student_id === l.student_id && overlaps(x, start, end))) return __('El estudiante ya tiene una clase en ese horario.');

            // Free time around road tests
            const gapText = formatMinutes(ROAD_TEST_GAP);
            const tooClose = others.filter(x => (isRoadTest(l) || isRoadTest(x)) && overlaps(x, start - ROAD_TEST_GAP, end + ROAD_TEST_GAP));
            if (tooClose.some(x => x.instructor_id === insId)) return __('El instructor necesita al menos :gap libre entre un Road Test y otra clase.', { gap: gapText });
            if (tooClose.some(x => x.student_id === l.student_id)) return __('El estudiante necesita al menos :gap libre entre un Road Test y otra clase.', { gap: gapText });
            return null;
        }

        // A column (instructor + day) is blocked when the instructor doesn't work, it already passed or nothing fits
        function lemColumnState(ins, date) {
            const wh = calWorkHours(ins, date);
            if (date < todayLocal()) return { blocked: true, label: __('Pasado') };
            if (!wh) return { blocked: true, label: __('No trabaja') };
            for (let m = wh[0]; m + lem.minutes <= wh[1]; m += LEM_SLOT_MIN) {
                if (!lemProblem(ins.db_id, date, m)) return { blocked: false, label: '' };
            }
            return { blocked: true, label: __('Sin horas libres') };
        }

        function lemSetDuration(value) {
            lem.minutes = Number(value);
            if (lem.pick && lemProblem(lem.pick.insId, lem.pick.date, lem.pick.start)) lem.pick = null;
            renderLessonEditor();
        }

        function lemSetView(view) {
            lem.view = view;
            try { localStorage.setItem('nyds-lem-view', view); } catch (e) {}
            lemSavedView = view;
            renderLessonEditor();
            lemScrollToFocus();
        }

        function lemMove(step) {
            const today = todayLocal();
            let next = calAddDays(lem.date, step * (lem.view === 'day' ? 1 : 7));
            if (lem.view === 'day' && next < today) return;
            if (lem.view === 'week' && lemWeekStart(next) < lemWeekStart(today)) return;
            if (next < today) next = today;
            lem.date = next;
            renderLessonEditor();
        }

        function lemGoTo(ymd) {
            if (!ymd) return;
            lem.date = ymd < todayLocal() ? todayLocal() : ymd;
            renderLessonEditor();
        }

        function lemPointerStart(e, col) {
            const y = e.clientY - col.getBoundingClientRect().top;
            return Number(col.dataset.from) + Math.floor(y / LEM_SLOT_PX) * LEM_SLOT_MIN;
        }

        function lemHover(e) {
            const col = e.currentTarget;
            const ghost = col.querySelector('.lem-ghost');
            if (col.dataset.blocked === '1') return ghost.classList.add('hidden');
            const start = lemPointerStart(e, col);
            const problem = lemProblem(Number(col.dataset.ins), col.dataset.date, start);
            const from = Number(col.dataset.from);
            ghost.style.top = `${(start - from) / LEM_SLOT_MIN * LEM_SLOT_PX + 1}px`;
            ghost.style.height = `${lem.minutes / LEM_SLOT_MIN * LEM_SLOT_PX - 2}px`;
            ghost.className = `lem-ghost absolute left-0.5 right-0.5 z-20 rounded-md border-2 border-dashed text-[10px] font-bold px-1.5 py-1 pointer-events-none overflow-hidden
                ${problem ? 'border-red-500 bg-red-500/15 text-red-400' : 'border-emerald-500 bg-emerald-500/15 text-emerald-400'}`;
            ghost.textContent = problem || `${calTime12(start)} – ${calTime12(start + lem.minutes)}`;
        }

        function lemLeave(e) {
            e.currentTarget.querySelector('.lem-ghost').classList.add('hidden');
        }

        function lemClick(e) {
            const col = e.currentTarget;
            const insId = Number(col.dataset.ins), date = col.dataset.date;
            if (col.dataset.blocked === '1') {
                const ins = instructors.find(i => i.db_id === insId);
                return showToast(`${ins ? ins.name + ': ' : ''}${lemColumnState(ins, date).label}`, 'warning');
            }
            const start = lemPointerStart(e, col);
            const problem = lemProblem(insId, date, start);
            if (problem) return showToast(problem, 'warning');
            lem.pick = { insId, date, start };
            renderLessonEditor();
        }

        function lemPickText(l) {
            if (!lem.pick) return l.db_id
                ? __('Se mantiene el día, la hora y el instructor actuales.')
                : __('Haz clic en un espacio libre para elegir día, hora e instructor.');
            const ins = instructors.find(i => i.db_id === lem.pick.insId);
            return `<i class="fa-solid fa-calendar-check"></i> ${l.db_id ? __('Nuevo horario') : __('Horario')}: ${calDayText(lem.pick.date)} · ${calTime12(lem.pick.start)} – ${calTime12(lem.pick.start + lem.minutes)} · ${esc(ins ? ins.name : '')}${l.db_id && ins && ins.db_id !== l.instructor_id ? ` <span class="text-amber-400">(${__('cambia de instructor')})</span>` : ''}`;
        }

        function renderLessonEditor() {
            const l = lemLesson();
            if (!lem || !l) return closeLessonEditor();

            const roadTest = isRoadTest(l);
            const scheduled = l.status === 'scheduled';
            const statusBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${(LESSON_STATUS[l.status] || {}).cls || ''}">${esc(lessonStatusLabel(l.status))}</span>`;

            const isNew = !l.db_id;
            if (isNew) {
                const student = students.find(s => s.db_id === l.student_id);
                document.getElementById('lem-title').innerHTML = `
                    <i class="fa-solid fa-calendar-plus text-emerald-500"></i>
                    <span class="truncate">${__('Agendar Clase de Manejo')} · ${esc(l.student_name)}</span>`;
                document.getElementById('lem-subtitle').textContent = __('Clases por agendar del paquete: :left', {
                    left: lessonsText(student ? studentRemainingMinutes(student) : 0),
                });
            } else {
                document.getElementById('lem-title').innerHTML = `
                    <i class="fa-solid ${roadTest ? 'fa-flag-checkered text-violet-400' : 'fa-pen-to-square text-amber-500'}"></i>
                    <span class="truncate">${roadTest ? __('Reprogramar Road Test') : __('Reprogramar Clase')} · ${esc(l.student_name)}</span> ${statusBadge}`;
                document.getElementById('lem-subtitle').textContent = __('Actualmente: :day :from – :to con :instructor', {
                    day: calDayText(l.date), from: calTime12(calMin(l.time)), to: calTime12(calMin(l.end_time) || 24 * 60), instructor: l.instructor_name,
                }) + (l.vehicle ? ` · ${l.vehicle}` : '');
            }

            const body = document.getElementById('lem-body');
            const footer = document.getElementById('lem-footer');
            const btn = 'px-3 py-2 rounded-lg text-xs font-bold transition';

            // Completed, no-show or cancelled lessons can't be moved anymore
            if (!scheduled) {
                body.innerHTML = `
                    <div class="p-5 space-y-3 text-sm">
                        <div class="rounded-xl border border-slate-800 bg-slate-950 p-4 text-slate-300">
                            <i class="fa-solid fa-circle-info text-sky-400"></i>
                            ${__('Esta clase está :status. Solo se pueden reprogramar clases que estén programadas.', { status: lessonStatusLabel(l.status).toLowerCase() })}
                        </div>
                        ${l.location ? `<p class="text-slate-400"><i class="fa-solid fa-location-dot"></i> ${esc(l.location)}</p>` : ''}
                        ${l.notes ? `<p class="text-slate-400"><i class="fa-solid fa-note-sticky"></i> ${esc(l.notes)}</p>` : ''}
                    </div>`;
                footer.innerHTML = `<button type="button" onclick="closeLessonEditor()" class="${btn} ml-auto bg-slate-800 text-slate-200 hover:bg-slate-700">${__('Cerrar')}</button>`;
                return;
            }

            // Keep what the user typed while the calendar re-renders
            const typedNotes = document.getElementById('lem-notes')?.dataset.lesson == l.db_id ? document.getElementById('lem-notes').value : (l.notes || '');
            const typedLocation = document.getElementById('lem-location')?.dataset.lesson == l.db_id ? document.getElementById('lem-location').value : (l.location || '');

            const durOptions = lemDurations(l).map(({ v, ok }) => {
                const label = roadTest ? formatMinutes(v)
                    : (v === LESSON_MINUTES ? __('1 clase (:min min)', { min: v }) : __(':n clases seguidas (:duration)', { n: v / LESSON_MINUTES, duration: formatMinutes(v) }));
                return `<option value="${v}" ${v === lem.minutes ? 'selected' : ''} ${ok ? '' : 'disabled'}>${label}</option>`;
            }).join('');
            const field = 'w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-amber-500';
            const label = 'block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1';

            body.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 px-5 pt-4">
                    <label><span class="${label}">${__('Duración')}</span>
                        <select class="${field}" onchange="lemSetDuration(this.value)">${durOptions}</select></label>
                    ${roadTest ? `<label><span class="${label}">${__('Lugar del examen')}</span>
                        <input id="lem-location" data-lesson="${l.db_id}" type="text" class="${field}" value="${escapeAttr(typedLocation)}"></label>` : ''}
                    <label class="${roadTest ? 'md:col-span-2' : 'md:col-span-3'}"><span class="${label}">${__('Notas')}</span>
                        <input id="lem-notes" data-lesson="${l.db_id}" type="text" maxlength="1000" class="${field}" value="${escapeAttr(typedNotes)}"></label>
                </div>
                <div class="flex flex-wrap items-center gap-3 px-5 py-3">
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="lemMove(-1)" class="w-8 h-8 rounded-lg hover:bg-slate-800 text-slate-300" title="${__('Anterior')}"><i class="fa-solid fa-chevron-left"></i></button>
                        <button type="button" onclick="lemMove(1)" class="w-8 h-8 rounded-lg hover:bg-slate-800 text-slate-300" title="${__('Siguiente')}"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                    <label class="relative flex items-center gap-2 font-bold text-slate-100 cursor-pointer">
                        <span id="lem-label"></span>
                        <i class="fa-solid fa-caret-down text-slate-400 text-xs"></i>
                        <input type="date" min="${todayLocal()}" value="${lem.date}" onchange="lemGoTo(this.value)" class="absolute inset-0 opacity-0 cursor-pointer" aria-label="${escapeAttr(__('Ir a la fecha'))}">
                    </label>
                    <div class="ml-auto flex items-center gap-2">
                        <button type="button" onclick="lemGoTo(todayLocal())" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-300 hover:bg-slate-800 uppercase">${__('Hoy')}</button>
                        <div class="flex rounded-lg border border-slate-800 overflow-hidden text-xs font-bold uppercase">
                            ${['day', 'week'].map(v => `<button type="button" onclick="lemSetView('${v}')" class="px-3 py-1.5 ${lem.view === v ? 'bg-amber-500 text-slate-950' : 'text-slate-400 hover:bg-slate-800'}">${v === 'day' ? __('Día') : __('Semana')}</button>`).join('')}
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 px-5 pb-2 text-[11px] text-slate-400">
                    <span><i class="fa-solid fa-hand-pointer text-amber-500"></i> ${isNew
                        ? __('Haz clic en un espacio libre para elegir día, hora e instructor.')
                        : __('Haz clic en un espacio libre de cualquier instructor para cambiar el día, la hora o el instructor.')}</span>
                    <span class="ml-auto flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded border border-slate-700 bg-slate-900"></span> ${__('Disponible')}</span>
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded border border-slate-700 lem-off"></span> ${__('Bloqueado / fuera de horario')}</span>
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-amber-500/25 border border-amber-500/50"></span> ${__('Ocupado (estudiante)')}</span>
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded" style="background:#7c3aed"></span> Road Test</span>
                </div>
                <div id="lem-scroll" class="mx-5 mb-4 border border-slate-800 rounded-xl overflow-auto" style="max-height: 58vh">
                    <div id="lem-grid">${lemGridHtml(l)}</div>
                </div>`;

            const days = lemDays();
            const first = calParse(days[0]), last = calParse(days[days.length - 1]);
            document.getElementById('lem-label').textContent = lem.view === 'day'
                ? `${CAL_DAY_NAMES[first.getDay()]} ${first.getDate()} ${CAL_MONTHS[first.getMonth()]} ${first.getFullYear()}`
                : `${first.getDate()} ${first.getMonth() !== last.getMonth() ? CAL_MONTHS[first.getMonth()] + ' ' : ''}– ${last.getDate()} ${CAL_MONTHS[last.getMonth()]} ${last.getFullYear()}`;

            if (isNew) {
                footer.innerHTML = `
                    <p class="ml-auto text-xs ${lem.pick ? 'text-emerald-400 font-bold' : 'text-slate-500'}">${lemPickText(l)}</p>
                    <button type="button" onclick="closeLessonEditor()" class="${btn} bg-slate-800 text-slate-200 hover:bg-slate-700">${__('Cerrar')}</button>
                    <button type="button" id="lem-save" onclick="saveLessonEditor()" ${lem.pick ? '' : 'disabled'} class="${btn} bg-emerald-500 text-slate-950 hover:bg-emerald-600 disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-calendar-check"></i> ${__('Agendar clase')}</button>`;
                return;
            }

            const changed = lem.pick || lem.minutes !== l.duration_minutes;
            footer.innerHTML = `
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="lemStatus('completed')" class="${btn} bg-green-500/10 text-green-400 hover:bg-green-500 hover:text-slate-950"><i class="fa-solid fa-check"></i> ${__('Completada')}</button>
                    <button type="button" onclick="lemStatus('no_show')" class="${btn} bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950"><i class="fa-solid fa-user-xmark"></i> ${__('No asistió')}</button>
                    <button type="button" onclick="lemStatus('cancelled')" class="${btn} bg-slate-800 text-slate-400 hover:bg-red-600 hover:text-white"><i class="fa-solid fa-ban"></i> ${__('Cancelar clase')}</button>
                </div>
                <p class="ml-auto text-xs ${lem.pick ? 'text-emerald-400 font-bold' : 'text-slate-500'}">${lemPickText(l)}</p>
                <button type="button" onclick="closeLessonEditor()" class="${btn} bg-slate-800 text-slate-200 hover:bg-slate-700">${__('Cerrar')}</button>
                <button type="button" id="lem-save" onclick="saveLessonEditor()" class="${btn} bg-amber-500 text-slate-950 hover:bg-amber-600">
                    <i class="fa-solid fa-floppy-disk"></i> ${changed ? __('Guardar Cambios') : __('Guardar notas')}</button>`;
        }

        // Grid: day headers, then one column per instructor inside every day
        function lemGridHtml(l) {
            const days = lemDays();
            const inss = lemInstructors();
            if (!inss.length) return `<p class="text-sm text-slate-500 text-center py-16">${__('No hay instructores registrados.')}</p>`;
            const today = todayLocal();

            // Hours shown: working hours and lessons of the visible days, whole hours
            let from = 24 * 60, to = 0;
            days.forEach(d => {
                inss.forEach(i => {
                    const wh = calWorkHours(i, d);
                    if (wh && (wh[0] > 0 || wh[1] < 24 * 60)) { from = Math.min(from, wh[0]); to = Math.max(to, wh[1]); }
                });
                lessons.filter(x => x.date === d && calActive(x)).forEach(x => {
                    from = Math.min(from, calMin(x.time)); to = Math.max(to, calMin(x.end_time) || 24 * 60);
                });
            });
            if (from >= to) { from = 7 * 60; to = 20 * 60; }
            from = Math.floor(from / 60) * 60;
            to = Math.min(24 * 60, Math.ceil(to / 60) * 60);
            const height = (to - from) / LEM_SLOT_MIN * LEM_SLOT_PX;
            const hourPx = 60 / LEM_SLOT_MIN * LEM_SLOT_PX;
            const px = min => (min - from) / LEM_SLOT_MIN * LEM_SLOT_PX;
            const nowMin = calNowMin();

            const colMin = lem.view === 'day' ? 170 : 96;
            const cols = days.length * inss.length;
            let html = `<div class="grid" style="grid-template-columns: 64px repeat(${cols}, minmax(${colMin}px, 1fr)); min-width: ${64 + cols * colMin}px">`;

            // Header row 1: days (each one spans its instructors)
            html += `<div class="sticky left-0 top-0 z-30 bg-slate-900 border-b border-slate-800" style="height:40px"></div>`;
            days.forEach(d => {
                const dt = calParse(d);
                html += `<div class="sticky top-0 z-20 bg-slate-900 border-b border-l-2 border-slate-800 border-l-slate-600 px-2 flex items-center gap-2" style="grid-column: span ${inss.length}; height:40px">
                    <span class="text-[10px] font-bold uppercase text-slate-400">${CAL_DAY_NAMES[dt.getDay()]}</span>
                    <span class="text-lg font-black ${d === today ? 'text-amber-500' : 'text-slate-200'}">${dt.getDate()}</span>
                    <span class="text-[10px] text-slate-500">${CAL_MONTHS[dt.getMonth()]}</span></div>`;
            });

            // Header row 2: instructors (color + photo), with the column state
            html += `<div class="sticky left-0 z-30 bg-slate-900 border-b border-slate-800" style="top:40px; height:52px"></div>`;
            const states = {};
            days.forEach(d => inss.forEach((i, idx) => {
                const st = lemColumnState(i, d);
                states[`${d}|${i.db_id}`] = st;
                html += `<div class="sticky z-20 flex flex-col items-center justify-center px-1 text-white text-[10px] font-bold uppercase leading-tight overflow-hidden ${idx === 0 ? 'border-l-2 border-l-slate-600' : 'border-l border-white/30'}"
                    style="top:40px; height:52px; background:${calColor(i)}; ${st.blocked ? 'opacity:.55' : ''}" title="${escapeAttr(`${i.name} · ${i.shift || ''}`)}">
                    ${i.instructor_photo_url ? `<img src="${escapeAttr(i.instructor_photo_url)}" class="w-6 h-6 rounded-full object-cover border border-white/50" alt="">` : '<i class="fa-solid fa-circle-user text-lg opacity-90"></i>'}
                    <span class="truncate max-w-full mt-0.5">${esc(i.name)}</span>
                    ${st.blocked ? `<span class="text-[9px] font-black opacity-90 truncate max-w-full">${esc(st.label)}</span>` : ''}</div>`;
            }));

            // Time gutter
            html += `<div class="sticky left-0 z-10 bg-slate-900 border-r border-slate-800" style="height:${height}px">`;
            for (let m = from; m < to; m += 60) {
                html += `<span class="absolute right-2 text-[10px] font-semibold text-slate-500" style="top:${px(m) + 2}px">${calTime12(m)}</span>`;
                if (m + 30 < to) html += `<span class="absolute right-2 text-[9px] text-slate-600" style="top:${px(m + 30) + 2}px">${(Math.floor(m / 60) % 12) || 12}:30</span>`;
            }
            html += `</div>`;

            let focusSet = false;
            days.forEach(d => {
                const dayLessons = lemDayLessons(l, d);
                // Times the student is busy with any instructor: blocked in every column of the day
                const studentBusy = dayLessons.filter(x => x.student_id === l.student_id);

                inss.forEach((i, idx) => {
                    const wh = calWorkHours(i, d);
                    const st = states[`${d}|${i.db_id}`];
                    let layers = '';

                    // Off schedule / column blocked
                    if (!wh || st.blocked) {
                        layers += `<div class="lem-off absolute inset-0 pointer-events-none"></div>`;
                    } else {
                        if (wh[0] > from) layers += `<div class="lem-off absolute inset-x-0 top-0 pointer-events-none" style="height:${px(Math.min(wh[0], to))}px"></div>`;
                        if (wh[1] < to) layers += `<div class="lem-off absolute inset-x-0 bottom-0 pointer-events-none" style="top:${px(Math.max(wh[1], from))}px"></div>`;
                    }
                    // Time already passed
                    if (d === today && nowMin > from) {
                        layers += `<div class="absolute inset-x-0 top-0 bg-slate-950/50 pointer-events-none" style="height:${Math.min(height, px(nowMin))}px"></div>`;
                        if (nowMin < to) layers += `<div class="absolute inset-x-0 border-t-2 border-red-500 z-10 pointer-events-none" style="top:${px(nowMin)}px"></div>`;
                    }
                    // Student busy with another instructor
                    studentBusy.filter(x => x.instructor_id !== i.db_id).forEach(x => {
                        const s = calMin(x.time), e = calMin(x.end_time) || 24 * 60;
                        layers += `<div class="absolute inset-x-0 z-[4] bg-amber-500/15 border-y border-amber-500/40 pointer-events-none" style="top:${px(s)}px; height:${px(e) - px(s)}px"
                            title="${escapeAttr(__('El estudiante ya tiene una clase en ese horario.'))}"></div>`;
                    });

                    // Lessons of this instructor that day
                    dayLessons.filter(x => x.instructor_id === i.db_id).forEach(x => {
                        const s = calMin(x.time), e = calMin(x.end_time) || 24 * 60;
                        const h = Math.max(LEM_SLOT_PX, px(e) - px(s)) - 2;
                        const sameStudent = x.student_id === l.student_id;
                        const bg = x.status === 'completed' ? '#64748b' : x.status === 'no_show' ? '#dc2626' : isRoadTest(x) ? '#7c3aed' : (sameStudent ? '#d97706' : calColor(i));
                        const phone = students.find(s2 => s2.db_id === x.student_id)?.phone;
                        layers += `<div class="absolute left-0.5 right-0.5 z-[5] rounded-md px-1.5 py-0.5 text-[10px] leading-tight text-white text-center overflow-hidden shadow pointer-events-none"
                            style="top:${px(s) + 1}px; height:${h}px; background:${bg}">
                            <p class="font-bold"><i class="fa-solid fa-lock text-[8px] opacity-80"></i> ${calTime12(s)}</p>
                            <p class="font-bold uppercase truncate">${esc(x.student_name)}</p>
                            ${h > 40 ? `<p class="opacity-90 truncate">${isRoadTest(x) ? 'Road Test' : __('Clase :min min', { min: x.duration_minutes })}</p>` : ''}
                            ${h > 54 && phone ? `<p class="opacity-90 truncate">${esc(phone)}</p>` : ''}
                        </div>`;
                    });

                    // Current position of the edited lesson
                    if (l.db_id && d === l.date && i.db_id === l.instructor_id) {
                        const s = calMin(l.time), e = calMin(l.end_time) || 24 * 60;
                        layers += `<div ${!lem.pick ? 'data-lem-focus' : ''} class="absolute left-0.5 right-0.5 z-[6] rounded-md border-2 border-dashed border-slate-400 bg-slate-500/10 px-1.5 py-0.5 text-[10px] font-bold text-slate-300 pointer-events-none overflow-hidden"
                            style="top:${px(s) + 1}px; height:${Math.max(LEM_SLOT_PX, px(e) - px(s)) - 2}px">${__('Actual')} · ${calTime12(s)}</div>`;
                        if (!lem.pick) focusSet = true;
                    }
                    // New time picked
                    if (lem.pick && lem.pick.date === d && lem.pick.insId === i.db_id) {
                        const s = lem.pick.start;
                        layers += `<div data-lem-focus class="absolute left-0.5 right-0.5 z-[7] rounded-md bg-emerald-600 text-white px-1.5 py-0.5 text-[10px] font-bold shadow-lg pointer-events-none overflow-hidden"
                            style="top:${px(s) + 1}px; height:${lem.minutes / LEM_SLOT_MIN * LEM_SLOT_PX - 2}px"><i class="fa-solid fa-check"></i> ${calTime12(s)} – ${calTime12(s + lem.minutes)}</div>`;
                        focusSet = true;
                    }
                    // Scroll target when the lesson isn't visible: start of the first working column
                    if (!focusSet && wh && !st.blocked) {
                        layers += `<span data-lem-focus class="absolute" style="top:${px(Math.max(wh[0], from))}px"></span>`;
                        focusSet = true;
                    }

                    html += `<div class="lem-col relative ${idx === 0 ? 'border-l-2 border-l-slate-600' : 'border-l border-l-slate-800'} ${st.blocked ? 'cursor-not-allowed' : 'cursor-pointer'}"
                        style="height:${height}px; background-size: 100% ${hourPx}px, 100% ${LEM_SLOT_PX}px"
                        data-ins="${i.db_id}" data-date="${d}" data-from="${from}" data-blocked="${st.blocked ? 1 : 0}"
                        onmousemove="lemHover(event)" onmouseleave="lemLeave(event)" onclick="lemClick(event)">
                        ${layers}
                        <div class="lem-ghost hidden absolute"></div>
                    </div>`;
                });
            });

            return html + `</div>`;
        }

        function lemRefreshPages(res) {
            if (res?.student) {
                const student = students.find(s => s.db_id === res.student.db_id);
                if (student) student.used_minutes = res.student.used_minutes;
            }
            initAgenda();   // agenda table + calendar
            const editing = getEditingStudent();
            if (editing) renderStudentFormExtras(editing);
        }

        function saveLessonEditor() {
            const l = lemLesson();
            if (!l) return;
            if (!l.db_id) return saveNewLesson(l);
            const insId = lem.pick ? lem.pick.insId : l.instructor_id;
            const date = lem.pick ? lem.pick.date : l.date;
            const start = lem.pick ? lem.pick.start : calMin(l.time);

            // Keeping the time: it still has to be valid with the new duration
            const problem = lemProblem(insId, date, start);
            if (problem) {
                return showToast(lem.pick ? problem : `${problem} ${__('Elige un nuevo horario en el calendario.')}`, 'warning');
            }

            const saveBtn = document.getElementById('lem-save');
            saveBtn.disabled = true;
            apiRequest(`{{ url('lessons') }}/${l.db_id}`, 'PUT', {
                instructor_id: insId,
                date,
                time: calHhmm(start),
                duration_minutes: lem.minutes,
                location: isRoadTest(l) ? document.getElementById('lem-location').value.trim() : null,
                notes: document.getElementById('lem-notes').value.trim() || null,
            }).then(res => {
                const idx = lessons.findIndex(x => x.db_id === res.lesson.db_id);
                if (idx !== -1) lessons[idx] = res.lesson;
                lessons.sort((a, b) => (a.date + a.time).localeCompare(b.date + b.time));

                const vars = { student: res.lesson.student_name, instructor: res.lesson.instructor_name, date: res.lesson.date, time: res.lesson.time };
                showToast(isRoadTest(res.lesson)
                    ? __('Road Test reprogramado(a): :student con :instructor, :date :time.', vars)
                    : __('Clase reprogramado(a): :student con :instructor, :date :time.', vars), 'success');
                showLessonNotifications(res.notifications);
                closeLessonEditor();
                lemRefreshPages(res);
            }).catch(err => {
                showToast(err.message, 'warning');
                saveBtn.disabled = false;
            });
        }

        // Book a lesson left in the package at the picked slot
        function saveNewLesson(l) {
            if (!lem.pick) return showToast(__('Haz clic en un espacio libre para elegir día, hora e instructor.'), 'warning');
            const problem = lemProblem(lem.pick.insId, lem.pick.date, lem.pick.start);
            if (problem) return showToast(problem, 'warning');

            const saveBtn = document.getElementById('lem-save');
            saveBtn.disabled = true;
            apiRequest('{{ url('lessons') }}', 'POST', {
                student_id: l.student_id,
                type: 'lesson',
                instructor_id: lem.pick.insId,
                date: lem.pick.date,
                time: calHhmm(lem.pick.start),
                duration_minutes: lem.minutes,
                notes: document.getElementById('lem-notes').value.trim() || null,
            }).then(res => {
                lessons.push(res.lesson);
                lessons.sort((a, b) => (a.date + a.time).localeCompare(b.date + b.time));

                const vars = { student: res.lesson.student_name, instructor: res.lesson.instructor_name, date: res.lesson.date, time: res.lesson.time };
                showToast(__('Clase agendado(a): :student con :instructor, :date :time.', vars), 'success');
                showLessonNotifications(res.notifications);
                closeLessonEditor();
                lemRefreshPages(res);
            }).catch(err => {
                showToast(err.message, 'warning');
                saveBtn.disabled = false;
            });
        }

        // Completed / no-show / cancelled from the modal (same confirmation and request as the agenda)
        function lemStatus(status) {
            const l = lemLesson();
            if (!l) return;
            const done = setLessonStatus(l.db_id, status);
            if (!done) return;   // not confirmed
            done.then(() => {
                const editing = getEditingStudent();
                if (editing) renderStudentFormExtras(editing);
                if (lem) renderLessonEditor();
            });
        }
