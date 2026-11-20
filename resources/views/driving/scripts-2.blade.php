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
                return `<th class="${cls} cursor-pointer select-none hover:text-slate-100" onclick="sortDataTable('${cfg.key}', ${i})" title="${escapeAttr(__('Ordenar por :label', { label: c.label }))}">`
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
                const msg = hasCriteria ? __('No hay registros que coincidan con la búsqueda o los filtros.') : (cfg.emptyText || __('No hay registros.'));
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
                return `<select class="${DATA_TABLE_INPUT}" onchange="setDataTableFilter('${cfg.key}', '${f.id}', this.value)" title="${escapeAttr(__('Filtrar por :label', { label: f.label }))}">
                        <option value="">${__(':label: Todos', { label: f.label })}</option>
                        ${options.map(o => `<option value="${escapeAttr(o)}" ${o === current ? 'selected' : ''}>${escapeAttr(f.format ? f.format(o) : o)}</option>`).join('')}
                    </select>`;
            }).join('');

            const dateHtml = cfg.dateField ? `
                    <label class="flex items-center gap-1 text-[11px] text-slate-400">${__('Desde')}
                        <input type="date" class="${DATA_TABLE_INPUT}" value="${state.dateFrom}" onchange="setDataTableDate('${cfg.key}', 'dateFrom', this.value)">
                    </label>
                    <label class="flex items-center gap-1 text-[11px] text-slate-400">${__('Hasta')}
                        <input type="date" class="${DATA_TABLE_INPUT}" value="${state.dateTo}" onchange="setDataTableDate('${cfg.key}', 'dateTo', this.value)">
                    </label>` : '';

            el.innerHTML = `
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative flex-1 min-w-[200px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                        <input type="search" class="${DATA_TABLE_INPUT} w-full pl-8" placeholder="${escapeAttr(cfg.searchPlaceholder || __('Buscar...'))}"
                            value="${escapeAttr(state.search)}" oninput="setDataTableSearch('${cfg.key}', this.value)">
                    </div>
                    ${filtersHtml}
                    ${dateHtml}
                    <select class="${DATA_TABLE_INPUT}" onchange="setDataTablePageSize('${cfg.key}', this.value)" title="${__('Registros por página')}">
                        ${DATA_TABLE_PAGE_SIZES.map(n => `<option value="${n}" ${n === state.pageSize ? 'selected' : ''}>${n === 0 ? __('Todos') : __(':n por página', { n })}</option>`).join('')}
                    </select>
                    <button type="button" onclick="resetDataTable('${cfg.key}')" title="${__('Limpiar búsqueda, filtros y orden')}"
                        class="px-3 py-2 rounded-lg text-xs font-bold border border-slate-800 text-slate-400 hover:bg-slate-800 hover:text-slate-100 transition">
                        <i class="fa-solid fa-filter-circle-xmark"></i> ${__('Limpiar')}
                    </button>
                </div>`;
        }

        function renderDataTablePager(cfg, state, total, start, count, pages) {
            const el = document.getElementById(cfg.pagerId);
            if (!el) return;

            const info = total === 0 ? __('0 registros') : __('Mostrando :from–:to de :total registros', { from: start + 1, to: start + count, total });
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
            return method === 'Cash' ? __('Efectivo') : method === 'Credit Card' ? __('Tarjeta de Crédito') : method;
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
                emptyText: __('No hay estudiantes registrados.'),
                searchPlaceholder: __('Buscar por nombre, contrato, teléfono, email o permiso...'),
                searchExtra: s => [s.email, s.middle_name, s.city, s.license ? s.license.license_number : ''],
                filters: [
                    { id: 'plan', label: __('Plan'), value: s => s.plan },
                    { id: 'status', label: __('Estatus'), value: s => s.status, format: o => __(o) },
                    { id: 'transmission', label: __('Transmisión'), value: s => s.transmission, format: o => __(o) },
                ],
                columns: [
                    { label: __('A - ID'), value: s => s.id, sortValue: s => s.db_id },
                    { label: __('B - Contrato'), value: s => s.contract_number },
                    { label: __('C - Fecha Reg'), value: s => s.date },
                    { label: __('D - Estudiante'), value: s => s.name },
                    { label: __('E - Teléfono'), value: s => s.phone },
                    { label: __('F - Plan'), value: s => s.plan },
                    { label: __('G - Costo'), value: s => s.cost },
                    { label: __('H - Abonado'), value: s => s.paid },
                    { label: __('I - Deuda'), value: s => s.debt },
                    { label: __('J - Transmisión'), value: s => s.transmission },
                    { label: __('K - Estatus'), value: s => s.status },
                    { label: __('Acción'), thClass: 'px-4 py-3' },
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
                        <td class="${SHEET_TD} text-slate-400">${esc(__(s.transmission))}</td>
                        <td class="${SHEET_TD}">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${s.status === 'Al Día' ? 'bg-green-500/10 text-green-400' : 'bg-amber-500/10 text-amber-500'}">${esc(__(s.status))}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center space-x-1 whitespace-nowrap">
                            <button onclick="openStudentProfile(${s.db_id})" class="bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="${__('Ver ficha completa')}">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button onclick="openInvoice(${s.db_id})" class="bg-sky-500/10 text-sky-400 hover:bg-sky-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="${__('Ver Invoice')}">
                                <i class="fa-solid fa-file-invoice"></i>
                            </button>
                            <button onclick="editStudent('${s.id}')" class="bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="${__('Editar')}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button onclick="deleteStudent('${s.id}')" class="bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="${__('Eliminar')}">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>`,
            };

            if (tab === 'instructors') return {
                ...base,
                rows: () => instructors,
                emptyText: __('No hay instructores registrados.'),
                searchPlaceholder: __('Buscar por nombre, licencia, teléfono, auto o placa...'),
                searchExtra: ins => [ins.email],
                filters: [
                    { id: 'status', label: __('Estatus'), value: ins => ins.status, format: o => __(o) },
                    { id: 'shift', label: __('Turno'), value: ins => ins.shift },
                    { id: 'vehicle', label: __('Auto'), value: ins => ins.vehicle },
                ],
                columns: [
                    { label: __('A - ID'), value: ins => ins.id, sortValue: ins => ins.db_id },
                    { label: __('B - Nombre Instructor'), value: ins => ins.name },
                    { label: __('C - Licencia Certificado'), value: ins => ins.license_text || ins.license },
                    { label: __('D - Teléfono'), value: ins => ins.phone },
                    { label: __('E - Auto Asignado'), value: ins => ins.vehicle },
                    { label: __('F - Placa'), value: ins => ins.plate },
                    { label: __('G - Turno Horario'), value: ins => ins.shift },
                    { label: __('H - Estatus'), value: ins => ins.status },
                    { label: __('Acción'), thClass: 'px-4 py-3' },
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
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${ins.status === 'Disponible' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-500'}">${esc(__(ins.status))}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center space-x-1 whitespace-nowrap">
                            <button onclick="instructorPortalLink(${ins.db_id}, 'copy')" class="bg-sky-500/10 text-sky-400 hover:bg-sky-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="${__("Copiar enlace 'Mis clases'")}">
                                <i class="fa-solid fa-link"></i>
                            </button>
                            <button onclick="instructorPortalLink(${ins.db_id}, 'send')" class="bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="${__("Enviar enlace 'Mis clases' por correo/SMS")}">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                            <button onclick="instructorPortalLink(${ins.db_id}, 'regenerate')" class="bg-slate-500/10 text-slate-400 hover:bg-slate-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="${__('Generar enlace nuevo (el anterior deja de funcionar)')}">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                            <button onclick="editInstructor('${ins.id}')" class="bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="${__('Editar')}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button onclick="deleteInstructor('${ins.id}')" class="bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="${__('Eliminar')}">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>`,
            };

            if (tab === 'vehicles') return {
                ...base,
                rows: () => vehicles,
                emptyText: __('No hay vehículos registrados.'),
                searchPlaceholder: __('Buscar por marca, modelo o placa...'),
                filters: [
                    { id: 'transmission', label: __('Transmisión'), value: v => v.transmission, format: o => __(o) },
                    { id: 'status', label: __('Estatus'), value: v => v.status, format: o => __(o) },
                ],
                columns: [
                    { label: __('A - ID'), value: v => v.id, sortValue: v => v.db_id },
                    { label: __('B - Marca / Modelo'), value: v => v.brand },
                    { label: __('C - Placa Patente'), value: v => v.plate },
                    { label: __('D - Transmisión'), value: v => v.transmission },
                    { label: __('E - Estatus Operación'), value: v => v.status },
                    { label: __('Acción'), thClass: 'px-4 py-3' },
                ],
                row: v => `
                        <td class="${SHEET_TD} text-amber-500 font-bold">${v.id}</td>
                        <td class="${SHEET_TD} text-slate-100 font-semibold">${esc(v.brand)}</td>
                        <td class="${SHEET_TD} text-slate-400 font-bold">${esc(v.plate)}</td>
                        <td class="${SHEET_TD} text-slate-400">${esc(__(v.transmission))}</td>
                        <td class="${SHEET_TD}">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${v.status === 'Disponible' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-500'}">${esc(__(v.status))}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center space-x-1">
                            <button onclick="editVehicle('${v.id}')" class="bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="${__('Editar')}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button onclick="deleteVehicle('${v.id}')" class="bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition" title="${__('Eliminar')}">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>`,
            };

            if (tab === 'reviews') return {
                ...base,
                rows: () => reviews,
                emptyText: __('No hay encuestas recibidas.'),
                searchPlaceholder: __('Buscar por instructor, comentario o autor...'),
                filters: [
                    { id: 'instructor', label: __('Instructor'), value: r => r.instructor },
                    { id: 'rating', label: __('Trato'), value: r => r.rating, format: o => `${o} ★` },
                    { id: 'vehicleRating', label: __('Auto'), value: r => r.vehicleRating, format: o => `${o} ★` },
                    { id: 'public', label: __('Web'), value: r => r.is_public ? 'Pública' : 'Oculta', format: o => __(o) },
                ],
                columns: [
                    { label: __('A - ID'), value: r => r.id, sortValue: r => r.db_id },
                    { label: __('B - Instructor Evaluado'), value: r => r.instructor },
                    { label: __('C - Calificación Trato'), value: r => r.rating },
                    { label: __('D - Calificación Auto'), value: r => r.vehicleRating },
                    { label: __('E - Comentarios Alumno'), value: r => r.comment },
                    { label: __('F - Autor'), value: r => r.author_name },
                    { label: __('G - Publicada en Web'), value: r => r.is_public ? 'Pública' : 'Oculta', thClass: 'px-4 py-3 text-center' },
                ],
                row: r => `
                        <td class="${SHEET_TD} text-amber-500 font-bold">${r.id}</td>
                        <td class="${SHEET_TD} text-slate-100 font-semibold">${esc(r.instructor)}</td>
                        <td class="${SHEET_TD} text-amber-400 font-bold">${'★'.repeat(r.rating)} (${r.rating}/5)</td>
                        <td class="${SHEET_TD} text-amber-400 font-bold">${'★'.repeat(r.vehicleRating)} (${r.vehicleRating}/5)</td>
                        <td class="${SHEET_TD} text-slate-300 italic truncate max-w-xs">${esc(r.comment)}</td>
                        <td class="${SHEET_TD} text-slate-300">${r.author_name ? esc(r.author_name) : `<span class="text-slate-500">${__('Anónimo')}</span>`}</td>
                        <td class="px-4 py-2.5 text-center">
                            <button onclick="toggleReviewVisibility(${r.db_id})" title="${r.is_public ? __('Ocultar de la web') : __('Publicar en la web')}"
                                class="${r.is_public ? 'bg-green-500/10 text-green-400 hover:bg-green-500' : 'bg-slate-800 text-slate-400 hover:bg-amber-500'} hover:text-slate-950 px-2 py-1 rounded text-[10px] font-bold transition">
                                <i class="fa-solid ${r.is_public ? 'fa-eye' : 'fa-eye-slash'}"></i> ${r.is_public ? __('Pública') : __('Oculta')}
                            </button>
                        </td>`,
            };

            // payments
            return {
                ...base,
                rows: () => payments,
                emptyText: __('No hay abonos registrados.'),
                searchPlaceholder: __('Buscar por ID, estudiante, método o notas...'),
                dateField: p => p.payment_date,
                filters: [
                    { id: 'method', label: __('Método'), value: p => p.payment_method, format: paymentMethodLabel },
                ],
                columns: [
                    { label: __('A - ID'), value: p => p.id, sortValue: p => p.db_id },
                    { label: __('B - Estudiante'), value: p => p.student_name },
                    { label: __('C - Fecha Pago'), value: p => p.payment_date },
                    { label: __('D - Método Pago'), value: p => paymentMethodLabel(p.payment_method) },
                    { label: __('E - Monto'), value: p => p.amount },
                    { label: __('F - Deuda Restante'), value: p => p.remaining_debt },
                    { label: __('G - Notas'), value: p => p.notes, thClass: 'px-4 py-3' },
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
                if (!confirm(__('¿Estás seguro de que deseas eliminar permanentemente a :name de la base de datos?', { name: student.name }))) return;

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
                        showToast(__('Estudiante :name eliminado de la base de datos (Soft Deleted).', { name: student.name }), "success");

                        const log = document.getElementById('console-logs');
                        log.innerHTML += `<p class="text-red-500">[DB_TRIGGER] Estudiante eliminado en BD ID: ${student.db_id} [Student ID: ${studentId}]</p>`;

                        updateDashboardMetrics();
                        renderSheetTable();
                    } else {
                        showToast(__('Error al eliminar de la BD: :message', { message: res.message || '' }), "warning");
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast(__('Error al eliminar estudiante en el servidor.'), "warning");
                });
            }
        }

        function triggerInvoiceSimulation() {
            showToast(__("Ejecutando Google Apps Script: 'generarFacturaPDF()'..."), "info");

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
                showToast(__("Plantilla duplicada y Factura en PDF guardada en Google Drive."), "success");
            }, 1500);
        }

        function triggerWelcomeSimulation() {
            showToast(__("Ejecutando Google Apps Script: 'enviarMensajeTeoria()'..."), "info");

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
                showToast(__("WhatsApp enviado automáticamente con acceso a Google Classroom."), "success");
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
            document.getElementById('stu-language').value = student.language || @json(config('app.default_language'));
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
            document.getElementById('student-form-title').innerText = `${student.id}${student.contract_number ? ' · ' + __('Contrato :number', { number: student.contract_number }) : ''}`;
            document.getElementById('student-form-subtitle').innerText = `${__('Inscrito el :date', { date: student.date })}${student.plan ? ' · ' + student.plan : ''}`;
            document.getElementById('student-submit-btn-text').innerText = __('Guardar');
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
            document.getElementById('ins-language').value = instructor.language || @json(config('app.default_language'));

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
            document.getElementById('instructor-form-title').innerText = __('Editar Expediente de Instructor');
            document.getElementById('instructor-form-subtitle').innerText = __('Modificando datos del instructor :name [ID: :id] en la base de datos.', { name: instructor.name, id: instructor.id });
            document.getElementById('instructor-submit-btn-text').innerText = __('Guardar Cambios');
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
            document.getElementById('vehicle-form-title').innerText = __('Editar Vehículo');
            document.getElementById('vehicle-form-subtitle').innerText = __('Modificando datos del vehículo :brand [Placa: :plate] en la base de datos.', { brand: vehicle.brand, plate: vehicle.plate });
            document.getElementById('vehicle-submit-btn-text').innerText = __('Guardar Cambios');
            document.getElementById('vehicle-submit-btn-icon').className = "fa-solid fa-pen-to-square";

            // Switch to form tab
            switchTab('registro-vehiculo');
        }

        function populatePaymentStudentsDropdown() {
            const dropdown = document.getElementById('pay-student-id');
            if (dropdown) {
                dropdown.innerHTML = `<option value="">${__('Selecciona un estudiante...')}</option>`;
                // Sort students alphabetically by name for better UX
                const sortedStudents = [...students].sort((a, b) => a.name.localeCompare(b.name));
                sortedStudents.forEach(s => {
                    if (s.debt > 0) {
                        const opt = document.createElement('option');
                        opt.value = s.db_id;
                        opt.innerText = `${s.name} (${s.id}) - ${__('Deuda')}: $${s.debt}`;
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
                    amountInput.placeholder = __('Máx: :amount', { amount: student.debt });
                }
                
                if (helpBlock) helpBlock.classList.remove('hidden');
            } else {
                if (helpBlock) helpBlock.classList.add('hidden');
            }
        }

