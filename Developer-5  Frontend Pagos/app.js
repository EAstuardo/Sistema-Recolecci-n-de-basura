let clientes = [];
let filtroActual = 'todos';
let clienteSeleccionado = null;

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}

function cargarClientes() {
    fetch('com/clientes.php')
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            clientes = data;
            renderTabla();
        })
        .catch(err => mostrarToast('Error al cargar clientes: ' + err.message));
}

function renderTabla() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    const tbody = document.getElementById('clientTableBody');

    const filtrados = clientes.filter(c => {
        const matchEstado = filtroActual === 'todos' || c.estado === filtroActual;
        const matchBusq   = c.nombre_completo.toLowerCase().includes(q) || 
                            (c.colonia && c.colonia.toLowerCase().includes(q));
        return matchEstado && matchBusq;
    });

    if (filtrados.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--muted)">No se encontraron clientes</td></tr>`;
        actualizarStats();
        return;
    }

    tbody.innerHTML = filtrados.map(c => {
        const esPagado = c.estado === 'pagado';
        return `
        <tr>
          <td>
            <div class="client-name">${c.nombre_completo}</div>
            <div class="client-email">${c.telefono || ''}</div>
          </td>
          <td class="hide-sm">${c.colonia || ''}</td>
          <td class="hide-sm">Q ${parseFloat(c.monto).toFixed(2)}</td>
          <td><span class="chip ${c.estado}">${esPagado ? 'Pagado' : 'Pendiente'}</span></td>
          <td>
            <button class="btn-pay" ${esPagado ? 'disabled' : ''} 
                    onclick="openModal(${c.id_cliente})">
              ${esPagado ? 'Pagado' : 'Registrar pago'}
            </button>
          </td>
        </tr>`;
    }).join('');

    actualizarStats();
}

function actualizarStats() {
    const pagados    = clientes.filter(c => c.estado === 'pagado').length;
    const pendientes = clientes.filter(c => c.estado === 'pendiente').length;
    document.getElementById('countPagados').textContent    = pagados;
    document.getElementById('countPendientes').textContent = pendientes;
    document.getElementById('countTotal').textContent      = clientes.length;
}

function setFilter(btn, filtro) {
    filtroActual = filtro;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderTabla();
}

document.getElementById('searchInput').addEventListener('input', renderTabla);

function openModal(id) {
    clienteSeleccionado = clientes.find(c => c.id_cliente == id);
    if (!clienteSeleccionado) return;

    document.getElementById('modalSubtitle').textContent =
        `${clienteSeleccionado.nombre_completo} · ${clienteSeleccionado.colonia}`;

    document.getElementById('montoPago').value  = parseFloat(clienteSeleccionado.monto).toFixed(2);
    document.getElementById('metodoPago').value = '';
    document.getElementById('notasPago').value  = '';
    document.getElementById('mesPagado').value  = new Date().toISOString().slice(0, 7);
    document.getElementById('fechaPago').value  = new Date().toISOString().split('T')[0];

    document.getElementById('modalOverlay').classList.add('open');
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('open');
    clienteSeleccionado = null;
}

function confirmarPago() {
    const monto         = document.getElementById('montoPago').value.trim();
    const fecha_pago    = document.getElementById('fechaPago').value;
    const metodo_pago   = document.getElementById('metodoPago').value;
    const mes_pagado    = document.getElementById('mesPagado').value;
    const observaciones = document.getElementById('notasPago').value.trim();

    if (!monto || !fecha_pago || !metodo_pago || !mes_pagado) {
        mostrarToast('Por favor completa todos los campos obligatorios');
        return;
    }

    const btnConfirm = document.querySelector('.btn-confirm');
    const textoOriginal = btnConfirm.textContent;
    btnConfirm.textContent = 'Guardando…';
    btnConfirm.disabled = true;

    // Guardamos el nombr
    const nombreCliente = clienteSeleccionado ? clienteSeleccionado.nombre_completo : 'el cliente';

    fetch('com/registrar_pago.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_cliente:    clienteSeleccionado.id_cliente,
            id_usuario:    1,
            monto:         parseFloat(monto),
            fecha_pago:    fecha_pago,
            mes_pagado:    mes_pagado,
            metodo_pago:   metodo_pago,
            observaciones: observaciones || null
        })
    })
    .then(res => {
        if (!res.ok) {
            return res.json().then(err => { 
                throw new Error(err.error || `Error del servidor (${res.status})`); 
            });
        }
        return res.json();
    })
    .then(data => {
        if (data.error) {
            mostrarToast('Error: ' + data.error);
            return;
        }

        // Actualización optimizada
        const cliente = clientes.find(c => c.id_cliente === clienteSeleccionado.id_cliente);
        if (cliente) {
            cliente.estado = 'pagado';
        }

        closeModal();                   
        renderTabla();                  
        mostrarToast(`Pago de ${nombreCliente} registrado correctamente`);
    })
    .catch(err => {
        console.error(err);
        mostrarToast('Error: ' + err.message);
    })
    .finally(() => {
        btnConfirm.textContent = textoOriginal;
        btnConfirm.disabled = false;
    });
}

function mostrarToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 4000);
}

// Cerrar modal al hacer clic fuera
document.getElementById('modalOverlay').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeModal();
});

// Cargar al iniciar
cargarClientes();