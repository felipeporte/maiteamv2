<?php
declare(strict_types=1);
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestion de Gala</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Yeseva+One&family=Manrope:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <style>
    :root {
      --encanto-green: #1f7a5a;
      --encanto-teal: #1f8e9e;
      --encanto-sun: #f6b443;
      --encanto-rose: #d85a7f;
      --encanto-cream: #fff7ea;
      --encanto-ink: #2b1f2a;
      --encanto-shadow: rgba(31, 122, 90, 0.15);
      --movie-bg-image: url("http://wallpaper.forfun.com/fetch/cf/cf61eba0bf668a30be2b7c93a80b5cf6.jpeg");
    }
    body {
      font-family: "Manrope", sans-serif;
      color: var(--encanto-ink);
      background:
        linear-gradient(180deg, rgba(255, 250, 240, 0.92) 0%, rgba(247, 239, 225, 0.05) 100%),
        radial-gradient(circle at 10% 10%, rgba(246, 180, 67, 0.25), transparent 45%),
        radial-gradient(circle at 85% 0%, rgba(216, 90, 127, 0.25), transparent 40%),
        radial-gradient(circle at 70% 90%, rgba(31, 142, 158, 0.22), transparent 45%),
        var(--movie-bg-image);
      background-size: cover, auto, auto, auto, cover;
      background-position: center, center, center, center, center;
      background-repeat: no-repeat;
      background-attachment: fixed;
    }
    h1, h2, h3 {
      font-family: "Yeseva One", serif;
      letter-spacing: 0.5px;
    }
    header {
      background: linear-gradient(90deg, rgba(31, 122, 90, 0.08), rgba(246, 180, 67, 0.12));
      border-bottom: 2px solid rgba(31, 122, 90, 0.12);
    }
    .nav-pills .nav-link {
      border: 1px solid rgba(31, 122, 90, 0.18);
      color: var(--encanto-green);
    }
    .nav-pills .nav-link.active {
      background: linear-gradient(120deg, var(--encanto-green), var(--encanto-teal));
      border-color: transparent;
    }
    .card {
      border: 1px solid rgba(31, 122, 90, 0.18);
      box-shadow: 0 10px 24px var(--encanto-shadow);
    }
    .btn-primary {
      background: linear-gradient(120deg, var(--encanto-rose), var(--encanto-sun));
      border: none;
    }
    .btn-primary:hover {
      opacity: 0.92;
    }
    .btn-dark {
      background: linear-gradient(120deg, var(--encanto-green), var(--encanto-teal));
      border: none;
    }
    .bg-body-tertiary {
      background: rgba(246, 180, 67, 0.12) !important;
      border: 1px dashed rgba(216, 90, 127, 0.4);
    }
    .table thead th {
      color: rgba(43, 31, 42, 0.7);
    }
    .form-control:focus,
    .form-select:focus {
      border-color: var(--encanto-teal);
      box-shadow: 0 0 0 0.2rem rgba(31, 142, 158, 0.2);
    }
    .tab-panel {
      display: none;
    }
    .tab-panel.active { display: block; }
  </style>
</head>
<body>
  <header class="py-4">
    <div class="container text-center">
      <h1 class="text-uppercase fw-bold mb-1">Gestion de Gala</h1>
      <p class="text-muted mb-3">Control de gastos y pagos de apoderados</p>
      <ul class="nav nav-pills justify-content-center gap-2">
        <li class="nav-item">
          <button class="nav-link active" data-tab="expenses" type="button">Gastos</button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-tab="payments" type="button">Pagos apoderados</button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-tab="summary" type="button">Resumen</button>
        </li>
      </ul>
    </div>
  </header>

  <main class="container pb-4">
    <section id="expenses" class="tab-panel active">
      <div class="row g-4">
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h2 class="h5 mb-3">Registrar gasto</h2>
              <form id="expense-form" class="vstack gap-2" enctype="multipart/form-data">
                <label class="form-label" for="expense-date">Fecha</label>
                <input class="form-control" type="date" id="expense-date" required>
                <label class="form-label" for="expense-concept">Concepto</label>
                <input class="form-control" type="text" id="expense-concept" placeholder="Ej: Decoracion" required>
                <label class="form-label" for="expense-amount">Monto</label>
                <input class="form-control" type="number" id="expense-amount" min="1" step="0.01" placeholder="Ej: 25000" required>
                <label class="form-label" for="expense-receipt">Comprobante (opcional)</label>
                <input class="form-control" type="file" id="expense-receipt" accept="image/*">
                <div class="form-check mt-1">
                  <input class="form-check-input" type="checkbox" id="expense-reimbursable">
                  <label class="form-check-label" for="expense-reimbursable">Reembolsable</label>
                </div>
                <button class="btn btn-primary mt-2" type="submit">Agregar gasto</button>
              </form>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h2 class="h5 mb-1">Listado de gastos</h2>
              <div class="text-muted small mb-2">Datos guardados en JSON local.</div>
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Concepto</th>
                      <th>Monto</th>
                      <th>Comprobante</th>
                      <th>Reembolso</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody id="expense-rows"></tbody>
                </table>
              </div>
              <div class="fw-bold text-end">Total: $<span id="expense-total">0</span></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="payments" class="tab-panel">
      <div class="row g-4">
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-body">
              <h2 class="h5 mb-3">Rendicion de entradas</h2>
              <div class="bg-body-tertiary rounded p-3 mb-4">
                <div class="fw-semibold">Valores:</div>
                <div>Cuota fija: $10.000</div>
                <div>Valor entrada: $4.000</div>
              </div>

              <h3 class="h6 mb-3">Registro de pago</h3>
              <form id="payment-form" class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="payment-apoderado">Nombre del apoderado</label>
                  <input class="form-control" type="text" id="payment-apoderado" placeholder="Ej: Maria Gonzalez" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="payment-athlete">Nombre del deportista</label>
                  <input class="form-control" type="text" id="payment-athlete" placeholder="Ej: Sofia Perez">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="payment-tickets">Cantidad de entradas</label>
                  <input class="form-control" type="number" id="payment-tickets" min="0" step="1" value="0" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="payment-method">Forma de pago</label>
                  <select class="form-select" id="payment-method">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="payment-include-fixed">Incluye cuota fija</label>
                  <select class="form-select" id="payment-include-fixed">
                    <option value="yes" selected>Si ($10.000)</option>
                    <option value="no">No</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="payment-date">Fecha</label>
                  <input class="form-control" type="date" id="payment-date" required>
                </div>
                <div class="col-12">
                  <label class="form-label" for="payment-notes">Observaciones</label>
                  <textarea class="form-control" id="payment-notes" rows="3" placeholder="Ej: pago realizado via transferencia"></textarea>
                </div>
                <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
                  <div class="fw-semibold">Total a pagar: $<span id="payment-total-preview">0</span></div>
                  <button class="btn btn-dark px-4" type="submit">Guardar registro</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-body">
              <h3 class="h6 mb-2">Registros ingresados</h3>
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Apoderado</th>
                      <th>Deportista</th>
                      <th>Entradas</th>
                      <th>Cuota</th>
                      <th>Total</th>
                      <th>Pago</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody id="payment-rows"></tbody>
                </table>
              </div>
              <div class="fw-bold text-end">Total: $<span id="payment-total">0</span></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="summary" class="tab-panel">
      <div class="row g-4">
        <div class="col-12 col-lg-8 mx-auto">
          <div class="card shadow-sm">
            <div class="card-body">
              <h2 class="h5 mb-3">Resumen general</h2>
              <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                  <span>Total ingresos</span>
                  <span class="fw-semibold">$<span id="summary-income">0</span></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span>Total gastos</span>
                  <span class="fw-semibold">$<span id="summary-expenses">0</span></span>
                </div>
                <hr class="my-1">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-semibold">Saldo (ingresos - gastos)</span>
                  <span class="fw-bold fs-5">$<span id="summary-balance">0</span></span>
                </div>
              </div>
              <div class="text-muted small mt-3">Se actualiza automaticamente al registrar o eliminar datos.</div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>
    const apiUrl = 'api.php';
    const FIXED_FEE = 10000;
    const TICKET_PRICE = 4000;

    const tabs = document.querySelectorAll('.nav-link[data-tab]');
    const panels = document.querySelectorAll('.tab-panel');
    tabs.forEach(btn => {
      btn.addEventListener('click', () => {
        tabs.forEach(b => b.classList.remove('active'));
        panels.forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
      });
    });

    function formatCurrency(value) {
      return Number(value).toLocaleString('es-CL');
    }

    let lastExpenseTotal = 0;
    let lastPaymentTotal = 0;

    function updateSummary() {
      document.getElementById('summary-income').textContent = formatCurrency(lastPaymentTotal);
      document.getElementById('summary-expenses').textContent = formatCurrency(lastExpenseTotal);
      const balance = lastPaymentTotal - lastExpenseTotal;
      document.getElementById('summary-balance').textContent = formatCurrency(balance);
    }

    async function loadExpenses() {
      const res = await fetch(`${apiUrl}?action=get_expenses`);
      const data = await res.json();
      const rows = document.getElementById('expense-rows');
      rows.innerHTML = '';
      data.items.forEach(item => {
        const tr = document.createElement('tr');
        const receiptCell = item.receipt_url
          ? `<a class="btn btn-sm btn-outline-secondary" href="${item.receipt_url}" target="_blank" rel="noopener">Ver</a>`
          : '<span class="text-muted">—</span>';
        const reimbursable = Boolean(item.reimbursable);
        const reimbursed = Boolean(item.reimbursed);
        const statusLabel = reimbursable ? (reimbursed ? 'Reembolsado' : 'Pendiente') : 'No aplica';
        const statusClass = reimbursable ? (reimbursed ? 'text-success' : 'text-warning') : 'text-muted';
        const toggleBtn = reimbursable
          ? `<button class="btn btn-sm btn-outline-success me-2" data-action="toggle-reimbursed" data-id="${item.id}">${reimbursed ? 'Marcar pendiente' : 'Marcar reembolsado'}</button>`
          : '';
        tr.innerHTML = `
          <td>${item.date}</td>
          <td>${item.concept}</td>
          <td>$${formatCurrency(item.amount)}</td>
          <td>${receiptCell}</td>
          <td class="${statusClass}">${statusLabel}</td>
          <td>
            ${toggleBtn}
            <button class="btn btn-sm btn-outline-danger" data-action="delete-expense" data-id="${item.id}">Eliminar</button>
          </td>
        `;
        const deleteBtn = tr.querySelector('[data-action="delete-expense"]');
        deleteBtn.addEventListener('click', () => deleteExpense(item.id));
        const toggle = tr.querySelector('[data-action="toggle-reimbursed"]');
        if (toggle) {
          toggle.addEventListener('click', () => toggleExpenseReimbursed(item.id));
        }
        rows.appendChild(tr);
      });
      lastExpenseTotal = Number(data.total || 0);
      document.getElementById('expense-total').textContent = formatCurrency(lastExpenseTotal);
      updateSummary();
    }

    async function loadPayments() {
      const res = await fetch(`${apiUrl}?action=get_payments`);
      const data = await res.json();
      const rows = document.getElementById('payment-rows');
      rows.innerHTML = '';
      data.items.forEach(item => {
        const tr = document.createElement('tr');
        const fixedFeeApplied = Number(item.fixed_fee_applied ?? (item.include_fixed ? item.fixed_fee : 0) ?? 0);
        tr.innerHTML = `
          <td>${item.date}</td>
          <td>${item.apoderado}</td>
          <td>${item.athlete || '-'}</td>
          <td>${item.tickets}</td>
          <td>$${formatCurrency(fixedFeeApplied)}</td>
          <td>$${formatCurrency(item.total)}</td>
          <td>${item.payment_method || '-'}</td>
          <td><button class="btn btn-sm btn-outline-danger" data-id="${item.id}">Eliminar</button></td>
        `;
        tr.querySelector('button').addEventListener('click', () => deletePayment(item.id));
        rows.appendChild(tr);
      });
      lastPaymentTotal = Number(data.total || 0);
      document.getElementById('payment-total').textContent = formatCurrency(lastPaymentTotal);
      updateSummary();
    }

    async function addExpense(formData) {
      await fetch(`${apiUrl}?action=add_expense`, {
        method: 'POST',
        body: formData
      });
      await loadExpenses();
    }

    async function deleteExpense(id) {
      await fetch(`${apiUrl}?action=delete_expense`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      await loadExpenses();
    }

    async function toggleExpenseReimbursed(id) {
      await fetch(`${apiUrl}?action=toggle_expense_reimbursed`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      await loadExpenses();
    }

    async function addPayment(payload) {
      await fetch(`${apiUrl}?action=add_payment`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      await loadPayments();
    }

    async function deletePayment(id) {
      await fetch(`${apiUrl}?action=delete_payment`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      await loadPayments();
    }

    document.getElementById('expense-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData();
      formData.append('date', document.getElementById('expense-date').value);
      formData.append('concept', document.getElementById('expense-concept').value.trim());
      formData.append('amount', document.getElementById('expense-amount').value);
      formData.append('reimbursable', document.getElementById('expense-reimbursable').checked ? '1' : '0');
      const receiptInput = document.getElementById('expense-receipt');
      if (receiptInput.files[0]) {
        formData.append('receipt', receiptInput.files[0]);
      }
      await addExpense(formData);
      e.target.reset();
    });

    document.getElementById('payment-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const payload = {
        date: document.getElementById('payment-date').value,
        apoderado: document.getElementById('payment-apoderado').value.trim(),
        athlete: document.getElementById('payment-athlete').value.trim(),
        payment_method: document.getElementById('payment-method').value,
        include_fixed: document.getElementById('payment-include-fixed').value === 'yes',
        notes: document.getElementById('payment-notes').value.trim(),
        tickets: Number(document.getElementById('payment-tickets').value)
      };
      await addPayment(payload);
      e.target.reset();
      document.getElementById('payment-include-fixed').value = 'yes';
      updatePaymentTotalPreview();
    });

    function updatePaymentTotalPreview() {
      const tickets = Number(document.getElementById('payment-tickets').value || 0);
      const includeFixed = document.getElementById('payment-include-fixed').value === 'yes';
      const total = (includeFixed ? FIXED_FEE : 0) + (tickets * TICKET_PRICE);
      document.getElementById('payment-total-preview').textContent = formatCurrency(total);
    }

    document.getElementById('payment-tickets').addEventListener('input', updatePaymentTotalPreview);
    document.getElementById('payment-include-fixed').addEventListener('change', updatePaymentTotalPreview);

    loadExpenses();
    loadPayments();
    updatePaymentTotalPreview();
  </script>
</body>
</html>
