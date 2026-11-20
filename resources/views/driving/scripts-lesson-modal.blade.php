        // ---------- Edit a lesson from the student's "Lessons" tab (modal with the instructor's week) ----------
        // Uses the calendar helpers (calParse, calMin, calWorkHours...) from scripts-4.
        const LEM_SLOT_MIN = 15;     // start times every 15 minutes
        const LEM_SLOT_PX = 20;      // height of one step

        let lem = null;   // { lessonId, insId, minutes, weekStart, pick: { date, start } | null }

        function lemLesson() {
            return lem ? lessons.find(l => l.db_id === lem.lessonId) : null;
        }

        function lemWeekStart(ymd) {
            return calAddDays(ymd, -calParse(ymd).getDay());   // weeks start on Sunday, like the calendar
        }

        function openLessonEditor(dbId) {
            const l = lessons.find(x => x.db_id === dbId);
            if (!l) return;
            const today = todayLocal();
            lem = {
                lessonId: dbId,
                insId: l.instructor_id,
                minutes: l.duration_minutes,
                weekStart: lemWeekStart(l.date < today ? today : l.date),
                pick: null,
            };
            document.getElementById('lesson-modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            renderLessonEditor();
            // Show the working hours / the lesson without the user having to scroll
            const scroller = document.getElementById('lem-scroll');
            const target = document.querySelector('#lem-grid [data-lem-focus]');
            if (scroller && target) scroller.scrollTop = Math.max(0, target.offsetTop - 80);
        }

        function closeLessonEditor() {
            lem = null;
            document.getElementById('lesson-modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && lem) closeLessonEditor();
        });

        // Durations offered: road tests any; lessons only what fits in the package (this lesson's time is given back)
        function lemDurations(l) {
            if (isRoadTest(l)) return ROAD_TEST_DURATIONS.map(v => ({ v, ok: true }));
            const student = students.find(s => s.db_id === l.student_id);
            const fits = (student ? studentRemainingMinutes(student) : 0) + l.duration_minutes;
            return LESSON_DURATIONS.map(v => ({ v, ok: v <= fits || v === l.duration_minutes }));
        }

        // Lessons that block the instructor / the student (the edited lesson doesn't count)
        function lemOthers(l, date) {
            return lessons.filter(x => x.date === date && calActive(x) && x.db_id !== l.db_id
                && (x.instructor_id === lem.insId || x.student_id === l.student_id));
        }

        // Why the lesson can't start at that minute (null = free). Same rules as the server.
        function lemProblem(date, start) {
            const l = lemLesson();
            const minutes = lem.minutes;
            const end = start + minutes;
            const today = todayLocal();
            if (date < today || (date === today && start < calNowMin())) return __('No se puede agendar en una hora que ya pasó.');
            if (end > 24 * 60) return __('La clase debe terminar el mismo día.');

            const ins = instructors.find(i => i.db_id === lem.insId);
            const wh = ins && calWorkHours(ins, date);
            if (!wh) return ins ? __(':name no trabaja ese día.', { name: ins.name }) : __('El instructor no trabaja ese día.');
            if (start < wh[0] || end > wh[1]) return __('Fuera del horario de :name (:shift).', { name: ins.name, shift: ins.shift });

            const others = lemOthers(l, date);
            const overlaps = (x, from, to) => calMin(x.time) < to && (calMin(x.end_time) || 24 * 60) > from;
            if (others.some(x => x.instructor_id === lem.insId && overlaps(x, start, end))) return __('El instructor ya tiene una clase en ese horario.');
            if (others.some(x => x.student_id === l.student_id && overlaps(x, start, end))) return __('El estudiante ya tiene una clase en ese horario.');

            // Free time around road tests
            const gapText = formatMinutes(ROAD_TEST_GAP);
            const tooClose = others.filter(x => (isRoadTest(l) || isRoadTest(x)) && overlaps(x, start - ROAD_TEST_GAP, end + ROAD_TEST_GAP));
            if (tooClose.some(x => x.instructor_id === lem.insId)) return __('El instructor necesita al menos :gap libre entre un Road Test y otra clase.', { gap: gapText });
            if (tooClose.some(x => x.student_id === l.student_id)) return __('El estudiante necesita al menos :gap libre entre un Road Test y otra clase.', { gap: gapText });
            return null;
        }

        // A day is blocked when the instructor doesn't work, it already passed or there is no free start time
        function lemDayState(date) {
            const ins = instructors.find(i => i.db_id === lem.insId);
            const wh = ins && calWorkHours(ins, date);
            if (date < todayLocal()) return { blocked: true, label: __('Pasado') };
            if (!wh) return { blocked: true, label: __('No trabaja') };
            for (let m = wh[0]; m + lem.minutes <= wh[1]; m += LEM_SLOT_MIN) {
                if (!lemProblem(date, m)) return { blocked: false, label: '' };
            }
            return { blocked: true, label: __('Sin horas libres') };
        }

        function lemSetInstructor(value) {
            lem.insId = Number(value);
            if (lem.pick && lemProblem(lem.pick.date, lem.pick.start)) lem.pick = null;
            renderLessonEditor();
        }

        function lemSetDuration(value) {
            lem.minutes = Number(value);
            if (lem.pick && lemProblem(lem.pick.date, lem.pick.start)) lem.pick = null;
            renderLessonEditor();
        }

        function lemMoveWeek(step) {
            const next = calAddDays(lem.weekStart, step * 7);
            if (next < lemWeekStart(todayLocal())) return;
            lem.weekStart = next;
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
            const problem = lemProblem(col.dataset.date, start);
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
            const date = col.dataset.date;
            if (col.dataset.blocked === '1') return showToast(lemDayState(date).label, 'warning');
            const start = lemPointerStart(e, col);
            const problem = lemProblem(date, start);
            if (problem) return showToast(problem, 'warning');
            lem.pick = { date, start };
            renderLessonEditor();
        }

        function renderLessonEditor() {
            const l = lemLesson();
            if (!lem || !l) return closeLessonEditor();

            const roadTest = isRoadTest(l);
            const scheduled = l.status === 'scheduled';
            const statusBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${(LESSON_STATUS[l.status] || {}).cls || ''}">${esc(lessonStatusLabel(l.status))}</span>`;

            document.getElementById('lem-title').innerHTML = `
                <i class="fa-solid ${roadTest ? 'fa-flag-checkered text-violet-400' : 'fa-pen-to-square text-amber-500'}"></i>
                <span class="truncate">${roadTest ? __('Reprogramar Road Test') : __('Reprogramar Clase')} · ${esc(l.student_name)}</span> ${statusBadge}`;
            document.getElementById('lem-subtitle').textContent = __('Actualmente: :day :from – :to con :instructor', {
                day: calDayText(l.date), from: calTime12(calMin(l.time)), to: calTime12(calMin(l.end_time) || 24 * 60), instructor: l.instructor_name,
            }) + (l.vehicle ? ` · ${l.vehicle}` : '');

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

            // Instructors: not suspended (plus the current one)
            const insOptions = instructors.filter(i => i.status !== 'Suspendido' || i.db_id === lem.insId)
                .map(i => `<option value="${i.db_id}" ${i.db_id === lem.insId ? 'selected' : ''}>${esc(i.name)} — ${esc(i.shift || '')}</option>`).join('');
            const durOptions = lemDurations(l).map(({ v, ok }) => {
                const label = roadTest ? formatMinutes(v)
                    : (v === LESSON_MINUTES ? __('1 clase (:min min)', { min: v }) : __(':n clases seguidas (:duration)', { n: v / LESSON_MINUTES, duration: formatMinutes(v) }));
                return `<option value="${v}" ${v === lem.minutes ? 'selected' : ''} ${ok ? '' : 'disabled'}>${label}</option>`;
            }).join('');
            const field = 'w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-amber-500';
            const label = 'block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1';

            // Week grid
            const days = [...Array(7)].map((_, i) => calAddDays(lem.weekStart, i));
            const ins = instructors.find(i => i.db_id === lem.insId);
            const today = todayLocal();
            let from = 24 * 60, to = 0;
            days.forEach(d => {
                const wh = ins && calWorkHours(ins, d);
                if (wh && (wh[0] > 0 || wh[1] < 24 * 60)) { from = Math.min(from, wh[0]); to = Math.max(to, wh[1]); }
                lemOthers(l, d).forEach(x => { from = Math.min(from, calMin(x.time)); to = Math.max(to, calMin(x.end_time) || 24 * 60); });
            });
            if (days.includes(l.date)) { from = Math.min(from, calMin(l.time)); to = Math.max(to, calMin(l.end_time) || 24 * 60); }
            if (from >= to) { from = 7 * 60; to = 20 * 60; }
            from = Math.floor(from / 60) * 60;
            to = Math.min(24 * 60, Math.ceil(to / 60) * 60);
            const height = (to - from) / LEM_SLOT_MIN * LEM_SLOT_PX;
            const hourPx = 60 / LEM_SLOT_MIN * LEM_SLOT_PX;
            const px = min => (min - from) / LEM_SLOT_MIN * LEM_SLOT_PX;
            const nowMin = calNowMin();

            const first = calParse(days[0]), last = calParse(days[6]);
            const weekLabel = `${first.getDate()} ${first.getMonth() !== last.getMonth() ? CAL_MONTHS[first.getMonth()] + ' ' : ''}– ${last.getDate()} ${CAL_MONTHS[last.getMonth()]} ${last.getFullYear()}`;
            const canGoBack = lem.weekStart > lemWeekStart(today);

            let grid = `<div class="grid" style="grid-template-columns: 60px repeat(7, minmax(92px, 1fr)); min-width: ${60 + 7 * 92}px">`;
            grid += `<div class="sticky left-0 top-0 z-30 bg-slate-900 border-b border-slate-800"></div>`;
            const states = days.map(lemDayState);
            days.forEach((d, idx) => {
                const dt = calParse(d), st = states[idx];
                grid += `<div class="sticky top-0 z-20 bg-slate-900 border-b border-l border-slate-800 px-2 py-1.5 leading-tight ${st.blocked ? 'opacity-60' : ''}">
                    <p class="text-[10px] font-bold uppercase ${st.blocked ? 'text-slate-600' : 'text-slate-400'}">${CAL_DAY_NAMES[dt.getDay()]}</p>
                    <p class="text-base font-black ${d === today ? 'text-amber-500' : (st.blocked ? 'text-slate-600' : 'text-slate-200')}">${dt.getDate()}</p>
                    <p class="text-[9px] font-bold uppercase ${st.blocked ? 'text-red-400' : 'text-emerald-500'}">${st.blocked ? esc(st.label) : __('Disponible')}</p></div>`;
            });

            grid += `<div class="sticky left-0 z-10 bg-slate-900 border-r border-slate-800" style="height:${height}px">`;
            for (let m = from; m < to; m += 60) {
                grid += `<span class="absolute right-2 text-[10px] font-semibold text-slate-500" style="top:${px(m) + 2}px">${calTime12(m)}</span>`;
            }
            grid += `</div>`;

            days.forEach((d, idx) => {
                const wh = ins && calWorkHours(ins, d);
                const st = states[idx];
                let layers = '';

                // Off schedule / day blocked
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

                // Busy: instructor's lessons (red) and the student's lessons with other instructors (amber)
                lemOthers(l, d).forEach(x => {
                    const s = calMin(x.time), e = calMin(x.end_time) || 24 * 60;
                    const mine = x.instructor_id === lem.insId;
                    const who = mine ? (x.student_id === l.student_id ? __('Este estudiante') : x.student_name) : `${__('Estudiante')} · ${x.instructor_name}`;
                    layers += `<div class="absolute left-0.5 right-0.5 z-[5] rounded-md px-1.5 py-0.5 text-[10px] leading-tight overflow-hidden pointer-events-none border
                        ${mine ? 'bg-red-500/20 border-red-500/50 text-red-300' : 'bg-amber-500/15 border-amber-500/50 text-amber-300'}"
                        style="top:${px(s) + 1}px; height:${Math.max(LEM_SLOT_PX, px(e) - px(s)) - 2}px">
                        <p class="font-bold"><i class="fa-solid fa-lock"></i> ${calTime12(s)}${isRoadTest(x) ? ' · RT' : ''}</p>
                        <p class="truncate">${esc(who)}</p></div>`;
                });

                // Current position of the lesson
                if (d === l.date && l.instructor_id === lem.insId) {
                    const s = calMin(l.time), e = calMin(l.end_time) || 24 * 60;
                    layers += `<div data-lem-focus class="absolute left-0.5 right-0.5 z-[6] rounded-md border-2 border-dashed border-slate-400 bg-slate-500/10 px-1.5 py-0.5 text-[10px] font-bold text-slate-300 pointer-events-none overflow-hidden"
                        style="top:${px(s) + 1}px; height:${Math.max(LEM_SLOT_PX, px(e) - px(s)) - 2}px">${__('Actual')} · ${calTime12(s)}</div>`;
                }
                // New time picked
                if (lem.pick && lem.pick.date === d) {
                    const s = lem.pick.start;
                    layers += `<div data-lem-focus class="absolute left-0.5 right-0.5 z-[7] rounded-md bg-emerald-600 text-white px-1.5 py-0.5 text-[10px] font-bold shadow-lg pointer-events-none overflow-hidden"
                        style="top:${px(s) + 1}px; height:${lem.minutes / LEM_SLOT_MIN * LEM_SLOT_PX - 2}px"><i class="fa-solid fa-check"></i> ${calTime12(s)} – ${calTime12(s + lem.minutes)}</div>`;
                }
                if (!st.blocked && wh && !layers.includes('data-lem-focus')) {
                    // Scroll target: start of the working day
                    layers += `<span ${idx === days.findIndex((_, i) => !states[i].blocked) ? 'data-lem-focus' : ''} class="absolute" style="top:${px(Math.max(wh[0], from))}px"></span>`;
                }

                grid += `<div class="lem-col relative border-l border-slate-800 ${st.blocked ? 'cursor-not-allowed' : 'cursor-pointer'}"
                    style="height:${height}px; background-size: 100% ${hourPx}px, 100% ${LEM_SLOT_PX}px"
                    data-date="${d}" data-from="${from}" data-blocked="${st.blocked ? 1 : 0}"
                    onmousemove="lemHover(event)" onmouseleave="lemLeave(event)" onclick="lemClick(event)">
                    ${layers}
                    <div class="lem-ghost hidden absolute"></div>
                </div>`;
            });
            grid += `</div>`;

            body.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 px-5 pt-4">
                    <label class="md:col-span-2"><span class="${label}">${__('Instructor')}</span>
                        <select class="${field}" onchange="lemSetInstructor(this.value)">${insOptions}</select></label>
                    <label><span class="${label}">${__('Duración')}</span>
                        <select class="${field}" onchange="lemSetDuration(this.value)">${durOptions}</select></label>
                    ${roadTest ? `<label><span class="${label}">${__('Lugar del examen')}</span>
                        <input id="lem-location" type="text" class="${field}" value="${escapeAttr(l.location || '')}"></label>` : '<div></div>'}
                    <label class="md:col-span-4"><span class="${label}">${__('Notas')}</span>
                        <input id="lem-notes" type="text" maxlength="1000" class="${field}" value="${escapeAttr(l.notes || '')}"></label>
                </div>
                <div class="flex flex-wrap items-center gap-3 px-5 py-3">
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="lemMoveWeek(-1)" ${canGoBack ? '' : 'disabled'} class="w-8 h-8 rounded-lg hover:bg-slate-800 text-slate-300 disabled:opacity-30 disabled:cursor-not-allowed" title="${__('Anterior')}"><i class="fa-solid fa-chevron-left"></i></button>
                        <button type="button" onclick="lemMoveWeek(1)" class="w-8 h-8 rounded-lg hover:bg-slate-800 text-slate-300" title="${__('Siguiente')}"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                    <span class="font-bold text-slate-100">${weekLabel}</span>
                    <div class="ml-auto flex flex-wrap items-center gap-3 text-[11px] text-slate-400">
                        <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded border border-slate-700 bg-slate-900"></span> ${__('Disponible')}</span>
                        <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded border border-slate-700 lem-off"></span> ${__('Bloqueado / fuera de horario')}</span>
                        <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded border border-red-500/50 bg-red-500/20"></span> ${__('Ocupado (instructor)')}</span>
                        <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded border border-amber-500/50 bg-amber-500/15"></span> ${__('Ocupado (estudiante)')}</span>
                    </div>
                </div>
                <p class="px-5 pb-2 text-[11px] text-slate-500"><i class="fa-solid fa-hand-pointer text-amber-500"></i> ${__('Haz clic en un espacio libre para elegir el nuevo horario.')}</p>
                <div id="lem-scroll" class="mx-5 mb-4 border border-slate-800 rounded-xl overflow-auto" style="max-height: 55vh">
                    <div id="lem-grid">${grid}</div>
                </div>`;

            const changed = lem.pick || lem.insId !== l.instructor_id || lem.minutes !== l.duration_minutes;
            footer.innerHTML = `
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="lemStatus('completed')" class="${btn} bg-green-500/10 text-green-400 hover:bg-green-500 hover:text-slate-950"><i class="fa-solid fa-check"></i> ${__('Completada')}</button>
                    <button type="button" onclick="lemStatus('no_show')" class="${btn} bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950"><i class="fa-solid fa-user-xmark"></i> ${__('No asistió')}</button>
                    <button type="button" onclick="lemStatus('cancelled')" class="${btn} bg-slate-800 text-slate-400 hover:bg-red-600 hover:text-white"><i class="fa-solid fa-ban"></i> ${__('Cancelar clase')}</button>
                </div>
                <p class="ml-auto text-xs ${lem.pick ? 'text-emerald-400 font-bold' : 'text-slate-500'}">
                    ${lem.pick ? `<i class="fa-solid fa-calendar-check"></i> ${__('Nuevo horario')}: ${calDayText(lem.pick.date)} · ${calTime12(lem.pick.start)} – ${calTime12(lem.pick.start + lem.minutes)}` : __('Se mantiene el día y la hora actuales.')}
                </p>
                <button type="button" onclick="closeLessonEditor()" class="${btn} bg-slate-800 text-slate-200 hover:bg-slate-700">${__('Cerrar')}</button>
                <button type="button" id="lem-save" onclick="saveLessonEditor()" class="${btn} bg-amber-500 text-slate-950 hover:bg-amber-600">
                    <i class="fa-solid fa-floppy-disk"></i> ${changed ? __('Guardar Cambios') : __('Guardar notas')}</button>`;
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
            const date = lem.pick ? lem.pick.date : l.date;
            const start = lem.pick ? lem.pick.start : calMin(l.time);

            // Keeping the time: it still has to be valid with the new instructor / duration
            const problem = lemProblem(date, start);
            if (problem) {
                return showToast(lem.pick ? problem : `${problem} ${__('Elige un nuevo horario en el calendario.')}`, 'warning');
            }

            const saveBtn = document.getElementById('lem-save');
            saveBtn.disabled = true;
            apiRequest(`{{ url('lessons') }}/${l.db_id}`, 'PUT', {
                instructor_id: lem.insId,
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
