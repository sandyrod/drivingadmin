        // ---------- Ledger "+" → assign a package (enrollment package or additional package with its invoice) ----------
        let pkmStudentId = null;

        function pkmStudent() {
            return pkmStudentId ? students.find(s => s.db_id === pkmStudentId) : null;
        }

        function openChargeInvoice(chargeDbId) {
            window.open(`{{ url('charges') }}/${chargeDbId}/invoice`, '_blank');
        }

        function openPackageModal(studentDbId) {
            const student = students.find(s => s.db_id === Number(studentDbId));
            if (!student) return;

            const active = packages.filter(p => p.is_active);
            if (!active.length) return showToast(__('No hay paquetes activos (configúralos en "Paquetes")'), 'warning');

            pkmStudentId = student.db_id;
            document.getElementById('pkm-form').reset();
            document.getElementById('pkm-print').checked = true;
            document.getElementById('pkm-student').textContent = `${student.id} · ${student.name}`;
            document.getElementById('pkm-package').innerHTML = active.map(p =>
                `<option value="${p.id}">${esc(p.name)} — ${p.lessons ? lessonsText(p.lessons * LESSON_MINUTES) : __('sin clases de manejo')} - ${money(p.price)}</option>`
            ).join('');
            const date = document.getElementById('pkm-date');
            date.value = todayLocal();
            date.max = todayLocal();
            // Without an enrollment package, the chosen one becomes it (no separate charge, date or notes)
            document.getElementById('pkm-extra-fields').classList.toggle('hidden', !student.package_id);
            renderPackageSummary();

            document.getElementById('package-modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closePackageModal() {
            pkmStudentId = null;
            document.getElementById('package-modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && pkmStudentId) closePackageModal();
        });

        // What the package does to the student's account
        function renderPackageSummary() {
            const student = pkmStudent();
            const pkg = packages.find(p => p.id == document.getElementById('pkm-package').value);
            const box = document.getElementById('pkm-summary');
            if (!student || !pkg) {
                box.innerHTML = '';
                return;
            }
            const additional = !!student.package_id;
            const cost = additional ? student.cost + pkg.price : pkg.price;
            const debt = Math.max(0, Math.round((cost - student.paid) * 100) / 100);
            const row = (label, value, cls = 'text-slate-200') =>
                `<div class="flex justify-between gap-3"><span class="text-slate-500">${label}</span><span class="font-bold ${cls}">${value}</span></div>`;

            box.innerHTML = `
                <p class="text-slate-300 mb-1.5">${additional
                    ? __('Se agrega como paquete adicional, con su propia factura.')
                    : __('El estudiante no tiene paquete: este será su paquete de inscripción.')}</p>
                ${row(__('Cargo'), money(pkg.price))}
                ${pkg.lessons ? row(__('Clases'), '+' + lessonsText(pkg.lessons * LESSON_MINUTES), 'text-amber-500') : ''}
                ${row(__('Saldo actual'), money(student.debt), student.debt > 0 ? 'text-red-500' : 'text-green-500')}
                ${row(__('Saldo después'), money(debt), debt > 0 ? 'text-red-500' : 'text-green-500')}`;
        }

        function savePackageModal(e) {
            e.preventDefault();
            const student = pkmStudent();
            const pkg = packages.find(p => p.id == document.getElementById('pkm-package').value);
            if (!student || !pkg) return;

            const print = document.getElementById('pkm-print').checked;
            const pay = document.getElementById('pkm-pay').checked;
            const saveBtn = document.getElementById('pkm-save');
            saveBtn.disabled = true;

            apiRequest(`{{ url('students') }}/${student.db_id}/charges`, 'POST', {
                package_id: pkg.id,
                charge_date: document.getElementById('pkm-date').value || todayLocal(),
                notes: document.getElementById('pkm-notes').value.trim() || null,
            }).then(res => {
                const updated = res.student;
                const idx = students.findIndex(s => s.db_id === updated.db_id);
                if (idx !== -1) students[idx] = updated;

                showToast(res.charge
                    ? __('Paquete agregado. Factura :number', { number: res.charge.number })
                    : __('Paquete asignado a :name.', { name: updated.name }), 'success');
                closePackageModal();

                // Student page: keep the profile's package fields in sync so saving the profile doesn't undo it
                const editing = getEditingStudent();
                if (editing) {
                    if (editing.db_id === updated.db_id) {
                        renderPlanOptions(updated.plan, updated.cost || 0);
                        document.getElementById('stu-plan').value = updated.plan || '';
                        document.getElementById('stu-cost').value = updated.cost || 0;
                        document.getElementById('stu-paid').value = updated.paid || 0;
                        document.getElementById('stu-debt').value = updated.debt || 0;
                    }
                    renderStudentFormExtras(editing);
                }
                initAgenda();
                populatePaymentStudentsDropdown();
                updateDashboardMetrics();
                renderSheetTable();

                if (print) res.charge ? openChargeInvoice(res.charge.db_id) : openInvoice(updated.db_id);
                if (pay && updated.debt > 0) openPaymentModal(updated.db_id);
            }).catch(err => showToast(err.message, 'warning'))
              .finally(() => { saveBtn.disabled = false; });
        }
