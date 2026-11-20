        // ---------- Student "Ledger" tab: account statement (charges, payments and running balance) ----------
        function studentLedgerEntries(student, ownPayments) {
            const entries = [];
            const invoiceNo = `INV-${String(student.db_id).padStart(4, '0')}`;

            // Charge: the package bought (price at purchase time)
            if (student.cost > 0 || student.package_id) {
                entries.push({
                    kind: 'charge', date: student.date, ref: invoiceNo, charge: student.cost, payment: 0,
                    note: __('Invoice :number. :package', { number: invoiceNo, package: student.plan || __('Paquete') }),
                    print: () => `openInvoice(${student.db_id})`,
                });
            }

            // Amount paid at enrollment (it has no payment record of its own)
            const recorded = ownPayments.reduce((sum, p) => sum + p.amount, 0);
            const initial = Math.max(0, Math.round((student.paid - recorded) * 100) / 100);
            if (initial > 0) {
                entries.push({ kind: 'payment', date: student.date, ref: '—', charge: 0, payment: initial, note: __('Pago inicial (inscripción)') });
            }

            ownPayments.slice()
                .sort((a, b) => a.payment_date.localeCompare(b.payment_date) || a.db_id - b.db_id)
                .forEach(p => entries.push({
                    kind: 'payment', date: p.payment_date, ref: p.id, charge: 0, payment: p.amount,
                    method: paymentMethodLabel(p.payment_method), notes: p.notes,
                    print: () => `printReceipt(${p.db_id})`,
                }));

            // Running balance and "payment on previous balance" text
            let balance = 0;
            entries.forEach(e => {
                if (e.kind === 'payment' && !e.note) {
                    e.note = __('Pago sobre saldo anterior :amount', { amount: money(balance) });
                }
                balance = Math.round((balance + e.charge - e.payment) * 100) / 100;
                e.balance = balance;
            });
            return entries;
        }

        function renderStudentLedger(student, ownPayments) {
            const box = document.getElementById('sf-tab-ledger');
            if (!student) {
                box.innerHTML = '';
                return;
            }

            const entries = studentLedgerEntries(student, ownPayments);
            const totalCharge = entries.reduce((s, e) => s + e.charge, 0);
            const totalPayment = entries.reduce((s, e) => s + e.payment, 0);
            const balance = Math.round((totalCharge - totalPayment) * 100) / 100;
            const canPay = student.debt > 0;
            const iconBtn = 'w-7 h-7 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-sky-400 transition';

            box.innerHTML = `
                <div class="flex items-center gap-3 mb-4">
                    <button type="button" onclick="openPaymentModal(${student.db_id})" ${canPay ? '' : 'disabled'}
                        title="${escapeAttr(canPay ? __('Registrar un nuevo pago') : __('El estudiante no tiene saldo pendiente.'))}"
                        class="w-10 h-10 rounded-full bg-sky-600 text-white shadow-lg hover:bg-sky-700 disabled:opacity-40 disabled:cursor-not-allowed transition">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                    <span class="flex-1"></span>
                    <span class="text-xs text-slate-500">${__('Saldo')}:</span>
                    <span class="text-base font-black ${balance > 0 ? 'text-red-500' : 'text-green-500'}">${money(balance)}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[10px] text-slate-500 uppercase tracking-wider border-b border-slate-800">
                            <tr>
                                <th class="py-2 pr-2 w-10"></th>
                                <th class="py-2 pr-3">${__('Fecha')}</th>
                                <th class="py-2 pr-3">${__('Referencia')}</th>
                                <th class="py-2 pr-3 text-right">${__('Cargo')}</th>
                                <th class="py-2 pr-3 text-right">${__('Pago')}</th>
                                <th class="py-2 pr-3 text-right">${__('Saldo')}</th>
                                <th class="py-2">${__('Nota')}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            ${entries.map(e => `
                                <tr class="${e.kind === 'charge' ? 'bg-slate-800/30' : ''}">
                                    <td class="py-1.5 pr-2">${e.print
                                        ? `<button type="button" onclick="${e.print()}" class="${iconBtn}" title="${escapeAttr(e.kind === 'charge' ? __('Invoice') : __('Recibo'))}"><i class="fa-solid fa-print"></i></button>`
                                        : ''}</td>
                                    <td class="py-2 pr-3 font-mono text-slate-400 whitespace-nowrap">${esc(e.date)}</td>
                                    <td class="py-2 pr-3 font-bold ${e.kind === 'charge' ? 'text-slate-200' : 'text-amber-500'} whitespace-nowrap">${esc(e.ref)}</td>
                                    <td class="py-2 pr-3 text-right font-mono ${e.charge ? 'text-slate-100 font-bold' : 'text-slate-600'}">${money(e.charge)}</td>
                                    <td class="py-2 pr-3 text-right font-mono ${e.payment ? 'text-green-500 font-bold' : 'text-slate-600'}">${money(e.payment)}</td>
                                    <td class="py-2 pr-3 text-right font-mono text-slate-400">${money(e.balance)}</td>
                                    <td class="py-2 text-xs text-slate-300">${esc(e.note)}${e.method ? ` <span class="text-slate-500">· ${esc(e.method)}</span>` : ''}${e.notes ? `<span class="block text-[11px] text-slate-500">${esc(e.notes)}</span>` : ''}</td>
                                </tr>`).join('')}
                            ${!entries.length ? `<tr><td colspan="7" class="py-6 text-center text-xs text-slate-500">${__('Sin movimientos.')}</td></tr>` : ''}
                        </tbody>
                        ${entries.length ? `
                        <tfoot class="border-t-2 border-slate-700 font-bold">
                            <tr>
                                <td colspan="3" class="py-2 pr-3 text-[10px] uppercase tracking-wider text-slate-500">${__('Totales')}</td>
                                <td class="py-2 pr-3 text-right font-mono text-slate-100">${money(totalCharge)}</td>
                                <td class="py-2 pr-3 text-right font-mono text-green-500">${money(totalPayment)}</td>
                                <td class="py-2 pr-3 text-right font-mono ${balance > 0 ? 'text-red-500' : 'text-green-500'}">${money(balance)}</td>
                                <td></td>
                            </tr>
                        </tfoot>` : ''}
                    </table>
                </div>`;
        }
