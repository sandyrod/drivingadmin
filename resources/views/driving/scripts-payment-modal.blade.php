        // ---------- New payment from the student's "Receipts" tab (modal) ----------
        let pmStudentId = null;

        function pmStudent() {
            return pmStudentId ? students.find(s => s.db_id === pmStudentId) : null;
        }

        function openPaymentModal(studentDbId) {
            const student = students.find(s => s.db_id === Number(studentDbId));
            if (!student) return;
            if (!(student.debt > 0)) return showToast(__('El estudiante no tiene saldo pendiente.'), 'info');

            pmStudentId = student.db_id;
            document.getElementById('pm-form').reset();
            document.getElementById('pm-print').checked = true;
            document.getElementById('pm-student').textContent = `${student.id} · ${student.name}`;
            document.getElementById('pm-debt').textContent = money(student.debt);
            const amount = document.getElementById('pm-amount');
            amount.max = student.debt;
            const date = document.getElementById('pm-date');
            date.value = todayLocal();
            date.max = todayLocal();
            renderPaymentAfter();

            document.getElementById('payment-modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            setTimeout(() => amount.focus(), 50);
        }

        function closePaymentModal() {
            pmStudentId = null;
            document.getElementById('payment-modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && pmStudentId) closePaymentModal();
        });

        function payFullDebt() {
            const student = pmStudent();
            if (!student) return;
            document.getElementById('pm-amount').value = student.debt.toFixed(2);
            renderPaymentAfter();
        }

        // Balance left after the payment
        function renderPaymentAfter() {
            const student = pmStudent();
            const help = document.getElementById('pm-after');
            const amount = parseFloat(document.getElementById('pm-amount').value);
            if (!student || !(amount > 0)) {
                help.textContent = __('Máximo :amount.', { amount: money(student ? student.debt : 0) });
                help.className = 'text-[11px] text-slate-500 mt-1';
                return;
            }
            const left = Math.round((student.debt - amount) * 100) / 100;
            help.textContent = left < 0
                ? __('El monto no puede superar el saldo pendiente.')
                : (left === 0 ? __('Queda al día.') : __('Saldo después del pago: :amount', { amount: money(left) }));
            help.className = `text-[11px] mt-1 ${left < 0 ? 'text-red-400 font-bold' : (left === 0 ? 'text-emerald-400 font-bold' : 'text-slate-400')}`;
        }
        document.getElementById('pm-amount').addEventListener('input', renderPaymentAfter);

        function savePaymentModal(e) {
            e.preventDefault();
            const student = pmStudent();
            if (!student) return;

            const amount = Math.round(parseFloat(document.getElementById('pm-amount').value) * 100) / 100;
            if (!(amount > 0)) return showToast(__('El monto del abono debe ser mayor a cero.'), 'warning');
            if (amount > student.debt) return showToast(__('El monto no puede superar la deuda del estudiante ($:debt USD).', { debt: student.debt }), 'warning');

            const print = document.getElementById('pm-print').checked;
            const saveBtn = document.getElementById('pm-save');
            saveBtn.disabled = true;

            apiRequest('{{ url('payments') }}', 'POST', {
                student_id: student.db_id,
                amount,
                payment_method: document.getElementById('pm-method').value,
                payment_date: document.getElementById('pm-date').value,
                notes: document.getElementById('pm-notes').value.trim() || null,
            }).then(res => {
                payments.unshift(res.payment);
                const s = students.find(x => x.db_id === res.student.db_id);
                if (s) {
                    s.paid = res.student.paid;
                    s.debt = res.student.debt;
                    s.status = res.student.status;
                }

                showToast(__('¡Abono registrado con éxito! Folio: :id', { id: res.payment.id }), 'success');
                closePaymentModal();

                // Student page (header balance + receipts) and the other screens
                const editing = getEditingStudent();
                if (editing) {
                    // The profile form sends "paid": keep it in sync so saving the profile doesn't undo the payment
                    if (editing.db_id === res.student.db_id) {
                        document.getElementById('stu-paid').value = editing.paid;
                        document.getElementById('stu-debt').value = editing.debt;
                    }
                    renderStudentFormExtras(editing);
                }
                populatePaymentStudentsDropdown();
                updateDashboardMetrics();
                renderSheetTable();
                renderPaymentsTable();

                if (print) printReceipt(res.payment.db_id);
            }).catch(err => showToast(err.message, 'warning'))
              .finally(() => { saveBtn.disabled = false; });
        }
