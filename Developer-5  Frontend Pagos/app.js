let clientes = [
  { id:1, nombre:'María García',    email:'mgarcia@mail.com',  colonia:'Col. Las Flores',  monto:150, estado:'pendiente' },
  { id:2, nombre:'Juan López',      email:'jlopez@mail.com',   colonia:'Col. El Progreso', monto:200, estado:'pagado'    },
  { id:3, nombre:'Ana Martínez',    email:'amartinez@mail.com',colonia:'Col. San José',    monto:175, estado:'pendiente' },
  { id:4, nombre:'Carlos Pérez',    email:'cperez@mail.com',   colonia:'Col. Las Flores',  monto:150, estado:'pagado'    },
  { id:5, nombre:'Luisa Ramírez',   email:'lramirez@mail.com', colonia:'Col. El Progreso', monto:200, estado:'pendiente' },
  { id:6, nombre:'Pedro Herrera',   email:'pherrera@mail.com', colonia:'Col. Centro',      monto:125, estado:'pagado'    },
  { id:7, nombre:'Sandra Méndez',   email:'smendez@mail.com',  colonia:'Col. San José',    monto:175, estado:'pendiente' },
  { id:8, nombre:'Roberto Vásquez', email:'rvasquez@mail.com', colonia:'Col. Centro',      monto:125, estado:'pagado'    },
];

let filtroActual = 'todos';
let clienteSeleccionado = null;

function renderTabla() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  const tbody = document.getElementById('clientTableBody');

  const filtrados = clientes.filter(c => {
    const matchEstado = filtroActual === 'todos' || c.estado === filtroActual;
    const matchBusq   = c.nombre.toLowerCase().includes(q) || c.colonia.toLowerCase().includes(q);
    return matchEstado && matchBusq;
  });

  tbody.innerHTML = filtrados.map((c) => {
    const esPagado = c.estado === 'pagado';
    return `
      <tr>
        <td>
          <div class="client-info">
            <div>
              <div class="client-name">${c.nombre}</div>
              <div class="client-email">${c.email}</div>
            </div>
          </div>
        </td>
        <td>${c.colonia}</td>
        <td>Q ${c.monto.toFixed(2)}</td>
        <td><span class="chip ${c.estado}">${esPagado ? 'Pagado' : 'Pendiente'}</span></td>
        <td>
          <button class="btn-pay" ${esPagado ? 'disabled' : ''} onclick="openModal(${c.id})">
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
  clienteSeleccionado = clientes.find(c => c.id === id);
  document.getElementById('modalSubtitle').textContent =
    `${clienteSeleccionado.nombre} · ${clienteSeleccionado.colonia}`;
  document.getElementById('montoPago').value  = clienteSeleccionado.monto;
  document.getElementById('metodoPago').value = '';
  document.getElementById('notasPago').value  = '';
  document.getElementById('fechaPago').value  = new Date().toISOString().split('T')[0];
  document.getElementById('modalOverlay').classList.add('open');
}

function closeModal() {
  document.getElementById('modalOverlay').classList.remove('open');
  clienteSeleccionado = null;
}

function confirmarPago() {
  const monto  = document.getElementById('montoPago').value;
  const fecha  = document.getElementById('fechaPago').value;
  const metodo = document.getElementById('metodoPago').value;

  if (!monto || !fecha || !metodo) {
    alert('Por favor completa todos los campos obligatorios.');
    return;
  }

  clientes = clientes.map(c =>
    c.id === clienteSeleccionado.id ? { ...c, estado: 'pagado' } : c
  );

  closeModal();
  renderTabla();
  mostrarToast('Pago de ' + clienteSeleccionado.nombre + ' registrado');
}

function mostrarToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

document.getElementById('modalOverlay').addEventListener('click', e => {
  if (e.target === e.currentTarget) closeModal();
});

function toggleTheme() {
  const isLight = document.body.classList.toggle('light');
  const track = document.getElementById('switchTrack');
  const thumb = document.getElementById('switchThumb');
  const label = document.getElementById('themeLabel');
  track.classList.toggle('on', isLight);
  thumb.textContent = '';
  label.textContent = isLight ? 'Claro' : 'Oscuro';
}

renderTabla();