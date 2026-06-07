<?php
// admin.php — Panel de control unificado (Cesar + Natalia)
require_once __DIR__ . '/config/auth.php';
solo_admin();

require_once __DIR__ . '/config/db.php';

// ── Totales del día (Wendy / Natalia) ─────────────────────────────────────────
$hoy = date('Y-m-d');

$stmtI = $pdo->prepare("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE fecha_pago = ?");
$stmtI->execute([$hoy]);
$ingresos_dia = (float) $stmtI->fetchColumn();

$stmtG = $pdo->prepare("SELECT COALESCE(SUM(costo_total),0) FROM combustible WHERE fecha = ?");
$stmtG->execute([$hoy]);
$gasto_dia = (float) $stmtG->fetchColumn();

$ganancia_dia = $ingresos_dia - $gasto_dia;

$stmtA = $pdo->prepare("SELECT COUNT(*) FROM combustible WHERE fecha = ? AND alerta = 1");
$stmtA->execute([$hoy]);
$alertas_dia = (int) $stmtA->fetchColumn();

// ── Camiones ──────────────────────────────────────────────────────────────────
$camiones  = $pdo->query("SELECT c.*, col.nombre AS colonia FROM camiones c LEFT JOIN colonias col ON col.id_colonia = c.id_colonia ORDER BY c.numero_placa")->fetchAll();
$colonias  = $pdo->query("SELECT * FROM colonias WHERE activo=1 ORDER BY nombre")->fetchAll();
$choferes  = $pdo->query("SELECT * FROM choferes WHERE activo=1 ORDER BY nombre")->fetchAll();

// ── Últimas 10 cargas ─────────────────────────────────────────────────────────
$ultCargas = $pdo->query("
    SELECT cb.*, cam.numero_placa, cam.marca, u.nombre AS usuario
    FROM combustible cb
    LEFT JOIN camiones cam ON cam.id_camion = cb.id_camion
    LEFT JOIN usuarios  u  ON u.id_usuario  = cb.id_usuario
    ORDER BY cb.fecha DESC, cb.created_at DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Panel Admin — Recolectora S.A.</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        :root {
            --green: #145c38; --green-light: #72A603;
            --gold: #e6a817;  --red: #c0392b;
            --bg: #F4F6F5;    --card: #fff;
            --border: #e0e0e0; --text: #222; --muted: #888;
            --radius: 14px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); }

        /* ── Layout shell ── */
        .shell { display: grid; grid-template-columns: 230px 1fr; min-height: 100vh; }

        /* ── Sidebar ── */
        .sidebar { background: var(--green); color: #fff; padding: 24px 16px; display: flex; flex-direction: column; gap: 6px; }
        .sidebar h2 { font-size: 1.1rem; margin-bottom: 18px; }
        .sidebar a { display: block; padding: 10px 12px; color: #fff; text-decoration: none; border-radius: 8px; font-size: .9rem; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,.15); }
        .sidebar-footer { margin-top: auto; font-size: .8rem; opacity: .7; padding-top: 20px; }
        .sidebar-footer a { color: #fff; font-size: .8rem; }

        /* ── Main ── */
        .main { padding: 28px; overflow-y: auto; }

        /* ── Tabs ── */
        .tabs { display: flex; gap: 8px; margin-bottom: 22px; flex-wrap: wrap; }
        .tab-btn {
            padding: 8px 18px; border: none; border-radius: 8px;
            background: var(--card); cursor: pointer; font-size: .9rem;
            border: 1px solid var(--border); transition: all .2s;
        }
        .tab-btn.active, .tab-btn:hover { background: var(--green); color: #fff; border-color: var(--green); }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* ── Cards ── */
        .card { background: var(--card); padding: 24px; border-radius: var(--radius); box-shadow: 0 4px 14px rgba(0,0,0,.07); margin-bottom: 22px; }
        .card h3 { margin-bottom: 16px; font-size: 1rem; color: var(--green); }

        /* ── KPI grid (Natalia) ── */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 22px; }
        .kpi { background: var(--card); padding: 18px 20px; border-radius: var(--radius); box-shadow: 0 4px 14px rgba(0,0,0,.07); }
        .kpi-label { font-size: .75rem; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
        .kpi-value { font-size: 1.6rem; font-weight: 700; }
        .kpi-value.green { color: #1a8a4a; }
        .kpi-value.red   { color: var(--red); }
        .kpi-value.gold  { color: var(--gold); }
        .kpi-sub { font-size: .72rem; color: var(--muted); margin-top: 4px; }

        /* ── Alerta badge ── */
        .badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600; }
        .badge-ok   { background: #d4f5e2; color: #1a8a4a; }
        .badge-warn { background: #ffe7e7; color: var(--red); }

        /* ── Formularios ── */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        label { display: block; font-size: .8rem; color: var(--muted); margin-bottom: 4px; }
        input, select, textarea {
            width: 100%; padding: 9px 12px; border: 1px solid var(--border);
            border-radius: 9px; font-size: .9rem; outline: none; transition: border .2s;
        }
        input:focus, select:focus { border-color: var(--green); }
        .btn {
            padding: 10px 20px; border: none; border-radius: 9px;
            cursor: pointer; font-size: .9rem; font-weight: 600; transition: background .2s;
        }
        .btn-primary { background: var(--green); color: #fff; }
        .btn-primary:hover { background: #0e4029; }
        .btn-secondary { background: var(--green-light); color: #fff; }
        .btn-row { display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap; }

        /* ── Tablas ── */
        table { width: 100%; border-collapse: collapse; font-size: .85rem; }
        th, td { padding: 9px 12px; border-bottom: 1px solid var(--border); text-align: left; }
        th { color: var(--muted); font-weight: 600; font-size: .75rem; text-transform: uppercase; }
        tr:hover td { background: #fafafa; }

        /* ── Mapa ── */
        #map { height: 300px; border-radius: var(--radius); margin-top: 10px; }

        /* ── Resultado calculo ── */
        #resultado { display: none; background: #f0f8f0; border-radius: 9px; padding: 12px 16px; }
        #resultado p { margin: 4px 0; font-size: .9rem; }

        /* ── Alert flash ── */
        .flash { padding: 12px 18px; border-radius: 9px; margin-bottom: 18px; font-size: .9rem; }
        .flash-ok   { background: #d4f5e2; color: #1a8a4a; }
        .flash-err  { background: #ffe7e7; color: var(--red); }

        @media (max-width: 700px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="shell">

<!-- ── SIDEBAR ─────────────────────────────────────────────── -->
<div class="sidebar">
    <h2>🌿 Recolectora S.A.</h2>
    <a href="#" class="active" onclick="showTab('dashboard',this)">📊 Dashboard</a>
    <a href="#" onclick="showTab('combustible',this)">⛽ Registrar Combustible</a>
    <a href="#" onclick="showTab('camiones',this)">🚛 Camiones</a>
    <a href="#" onclick="showTab('historial',this)">📋 Historial</a>
    <div class="sidebar-footer">
        👤 <?= htmlspecialchars($_SESSION['nombre']) ?><br>
        Rol: <?= htmlspecialchars($_SESSION['rol']) ?><br><br>
        <a href="logout.php">Cerrar sesión</a>
    </div>
</div>

<!-- ── MAIN ──────────────────────────────────────────────────── -->
<div class="main">

<!-- Flash message -->
<div id="flash" style="display:none" class="flash"></div>

<!-- ── TABS ── -->
<div class="tabs">
    <button class="tab-btn active" onclick="showTab('dashboard',this)">📊 Dashboard</button>
    <button class="tab-btn" onclick="showTab('combustible',this)">⛽ Combustible</button>
    <button class="tab-btn" onclick="showTab('camiones',this)">🚛 Camiones</button>
    <button class="tab-btn" onclick="showTab('historial',this)">📋 Historial</button>
</div>

<!-- ════════════════════════ DASHBOARD (Natalia) ════════════════════════ -->
<div id="tab-dashboard" class="tab-pane active">

    <!-- KPIs del día -->
    <div class="kpi-grid">
        <div class="kpi">
            <div class="kpi-label">Ingresos del día</div>
            <div class="kpi-value gold">Q <?= number_format($ingresos_dia, 2) ?></div>
            <div class="kpi-sub"><?= $hoy ?></div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Gasto combustible</div>
            <div class="kpi-value <?= $alertas_dia > 0 ? 'red' : 'gold' ?>">Q <?= number_format($gasto_dia, 2) ?></div>
            <div class="kpi-sub">
                <?php if ($alertas_dia > 0): ?>
                    <span class="badge badge-warn">⚠ <?= $alertas_dia ?> alerta<?= $alertas_dia>1?'s':'' ?></span>
                <?php else: ?>
                    <span class="badge badge-ok">✓ Sin alertas</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Ganancia real</div>
            <div class="kpi-value <?= $ganancia_dia >= 0 ? 'green' : 'red' ?>">Q <?= number_format($ganancia_dia, 2) ?></div>
            <div class="kpi-sub"><?= $ganancia_dia >= 0 ? '▲ GANANCIA' : '▼ PÉRDIDA' ?></div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Camiones activos</div>
            <div class="kpi-value green"><?= count(array_filter($camiones, fn($c) => $c['estado']==='ACTIVO')) ?></div>
            <div class="kpi-sub">de <?= count($camiones) ?> registrados</div>
        </div>
    </div>

    <!-- Últimas cargas del día -->
    <div class="card">
        <h3>⛽ Cargas de hoy</h3>
        <table>
            <thead><tr><th>Placa</th><th>Litros</th><th>Costo</th><th>Estado</th></tr></thead>
            <tbody>
            <?php
            $cargasHoy = array_filter($ultCargas, fn($c) => $c['fecha'] === $hoy);
            if (empty($cargasHoy)): ?>
                <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">Sin cargas hoy</td></tr>
            <?php else: foreach ($cargasHoy as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['numero_placa']) ?></td>
                    <td><?= $c['litros'] ?> L</td>
                    <td>Q <?= number_format($c['costo_total'], 2) ?></td>
                    <td><span class="badge <?= $c['alerta'] ? 'badge-warn' : 'badge-ok' ?>"><?= $c['alerta'] ? 'Anormal' : 'Normal' ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ════════════════════════ COMBUSTIBLE (Cesar + Wendy) ════════════════════════ -->
<div id="tab-combustible" class="tab-pane">

    <div class="card">
        <h3>⛽ Registrar carga de combustible</h3>
        <form id="frmCombustible" class="form-grid">
            <div>
                <label>Camión *</label>
                <select name="id_camion" required>
                    <option value="">— Seleccionar —</option>
                    <?php foreach ($camiones as $cam): ?>
                    <option value="<?= $cam['id_camion'] ?>"><?= htmlspecialchars($cam['numero_placa'] . ' — ' . $cam['marca'] . ' ' . $cam['modelo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Fecha *</label>
                <input type="date" name="fecha" value="<?= $hoy ?>" required>
            </div>
            <div>
                <label>Litros cargados *</label>
                <input type="number" name="litros" step="0.01" min="0.01" placeholder="0.00" required oninput="calcularCombustible()">
            </div>
            <div>
                <label>Precio por litro (Q) *</label>
                <input type="number" name="precio_litro" step="0.01" min="0.01" placeholder="0.00" required oninput="calcularCombustible()">
            </div>
            <div>
                <label>Tipo de combustible</label>
                <select name="tipo_combustible">
                    <option value="DIESEL">Diesel</option>
                    <option value="GASOLINA">Gasolina</option>
                </select>
            </div>
            <div>
                <label>Kilometraje odómetro</label>
                <input type="number" name="kilometraje" id="km_odo" min="0" placeholder="0">
            </div>
            <div>
                <label>Inicio del viaje</label>
                <input type="text" id="inicio" name="inicio_viaje" placeholder="Ej. Zona 1, Ciudad de Guatemala">
            </div>
            <div>
                <label>Destino</label>
                <input type="text" id="destino" name="destino_viaje" placeholder="Ej. Zona 5, Ciudad de Guatemala">
            </div>
            <div>
                <label>KM recorridos <small>(o calcular por ruta ↓)</small></label>
                <input type="number" id="km_rec" name="km_recorridos" step="0.1" min="0" placeholder="0" oninput="calcularCombustible()">
            </div>
            <div>
                <label>Estación / Gasolinera</label>
                <input type="text" name="estacion" placeholder="Nombre de la gasolinera">
            </div>
            <div style="grid-column: 1/-1">
                <label>Observaciones</label>
                <input type="text" name="observaciones" placeholder="Notas adicionales…">
            </div>

            <!-- Resultado calculado -->
            <div id="resultado" style="grid-column:1/-1">
                <p>🔢 Rendimiento: <strong><span id="rend">0.00</span> km/L</strong></p>
                <p>💰 Costo total estimado: <strong>Q <span id="total">0.00</span></strong></p>
            </div>
        </form>

        <div class="btn-row">
            <button type="button" class="btn btn-secondary" onclick="calcularRuta()">🗺 Calcular ruta</button>
            <button type="button" class="btn btn-primary" onclick="guardarCombustible()">💾 Guardar carga</button>
        </div>
    </div>

    <!-- Mapa -->
    <div class="card">
        <h3>🗺 Ruta del viaje</h3>
        <div id="map"></div>
    </div>
</div>

<!-- ════════════════════════ CAMIONES (Herielis + Wendy) ════════════════════════ -->
<div id="tab-camiones" class="tab-pane">

    <!-- Formulario nuevo camión -->
    <div class="card">
        <h3>🚛 Registrar nuevo camión</h3>
        <form id="frmCamion" class="form-grid">
            <div>
                <label>Número de placa *</label>
                <input type="text" name="numero_placa" placeholder="ABC-123" required>
            </div>
            <div>
                <label>Marca *</label>
                <input type="text" name="marca" placeholder="Kenworth" required>
            </div>
            <div>
                <label>Modelo *</label>
                <input type="text" name="modelo" placeholder="T370" required>
            </div>
            <div>
                <label>Año *</label>
                <input type="number" name="anio" min="2000" max="2030" placeholder="2024" required>
            </div>
            <div>
                <label>Capacidad (kg)</label>
                <input type="number" name="capacidad_kg" step="0.01" min="0" placeholder="8000">
            </div>
            <div>
                <label>Estado</label>
                <select name="estado">
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                    <option value="MANTENIMIENTO">Mantenimiento</option>
                </select>
            </div>
            <div>
                <label>Colonia *</label>
                <select name="id_colonia" required>
                    <option value="">— Seleccionar —</option>
                    <?php foreach ($colonias as $col): ?>
                    <option value="<?= $col['id_colonia'] ?>"><?= htmlspecialchars($col['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        <div class="btn-row">
            <button class="btn btn-primary" onclick="guardarCamion()">💾 Registrar camión</button>
        </div>
    </div>

    <!-- Lista de camiones -->
    <div class="card">
        <h3>Lista de camiones</h3>
        <table>
            <thead><tr><th>Placa</th><th>Marca/Modelo</th><th>Año</th><th>Colonia</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($camiones as $c): ?>
            <tr>
                <td><strong><?= htmlspecialchars($c['numero_placa']) ?></strong></td>
                <td><?= htmlspecialchars($c['marca'] . ' ' . $c['modelo']) ?></td>
                <td><?= $c['anio'] ?></td>
                <td><?= htmlspecialchars($c['colonia'] ?? '—') ?></td>
                <td><span class="badge <?= $c['estado']==='ACTIVO' ? 'badge-ok' : 'badge-warn' ?>"><?= $c['estado'] ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ════════════════════════ HISTORIAL ════════════════════════ -->
<div id="tab-historial" class="tab-pane">
    <div class="card">
        <h3>📋 Últimas 10 cargas de combustible</h3>
        <table>
            <thead>
                <tr><th>Fecha</th><th>Placa</th><th>Litros</th><th>Precio/L</th><th>Total</th><th>Rendim.</th><th>Alerta</th></tr>
            </thead>
            <tbody>
            <?php foreach ($ultCargas as $c): ?>
            <tr>
                <td><?= $c['fecha'] ?></td>
                <td><?= htmlspecialchars($c['numero_placa']) ?></td>
                <td><?= $c['litros'] ?> L</td>
                <td>Q <?= number_format($c['precio_litro'], 2) ?></td>
                <td>Q <?= number_format($c['costo_total'], 2) ?></td>
                <td><?= $c['rendimiento'] > 0 ? $c['rendimiento'].' km/L' : '—' ?></td>
                <td><span class="badge <?= $c['alerta'] ? 'badge-warn' : 'badge-ok' ?>"><?= $c['alerta'] ? '⚠ Anormal' : '✓ Normal' ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div><!-- /main -->
</div><!-- /shell -->

<script>
// ── Tab switcher ─────────────────────────────────────────────────────────────
function showTab(name, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn, .sidebar a').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    if (btn) btn.classList.add('active');
}

// ── Flash helper ─────────────────────────────────────────────────────────────
function flash(msg, ok) {
    const el = document.getElementById('flash');
    el.className = 'flash ' + (ok ? 'flash-ok' : 'flash-err');
    el.textContent = msg;
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 4000);
}

// ── Calcular combustible en tiempo real ──────────────────────────────────────
function calcularCombustible() {
    const km  = parseFloat(document.getElementById('km_rec').value) || 0;
    const lit = parseFloat(document.querySelector('[name=litros]').value) || 0;
    const pre = parseFloat(document.querySelector('[name=precio_litro]').value) || 0;
    if (lit > 0) {
        document.getElementById('total').textContent = (lit * pre).toFixed(2);
        document.getElementById('rend').textContent  = km > 0 ? (km / lit).toFixed(2) : '—';
        document.getElementById('resultado').style.display = 'block';
    }
}

// ── Guardar combustible via API ───────────────────────────────────────────────
async function guardarCombustible() {
    const frm = document.getElementById('frmCombustible');
    const data = Object.fromEntries(new FormData(frm));
    try {
        const res  = await fetch('api/combustible/registrar.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const json = await res.json();
        if (json.ok) {
            flash('✓ ' + json.mensaje + (json.alerta ? ' — ⚠ ' + json.alerta : ''), !json.alerta);
            frm.reset();
            document.getElementById('resultado').style.display = 'none';
            setTimeout(() => location.reload(), 1500);
        } else {
            flash('✗ ' + json.mensaje, false);
        }
    } catch(e) { flash('Error de conexión', false); }
}

// ── Guardar camión via API ────────────────────────────────────────────────────
async function guardarCamion() {
    const frm = document.getElementById('frmCamion');
    const data = Object.fromEntries(new FormData(frm));
    try {
        const res  = await fetch('api/camiones/registrar.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const json = await res.json();
        if (json.ok) {
            flash('✓ ' + json.mensaje, true);
            frm.reset();
            setTimeout(() => location.reload(), 1200);
        } else {
            flash('✗ ' + json.mensaje, false);
        }
    } catch(e) { flash('Error de conexión', false); }
}

// ── Mapa (Cesar) ──────────────────────────────────────────────────────────────
var map = L.map('map').setView([14.6349, -90.5069], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

let routeLine = null;

async function geocode(lugar) {
    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(lugar)}`);
    return res.json();
}

async function calcularRuta() {
    const inicio  = document.getElementById('inicio').value;
    const destino = document.getElementById('destino').value;
    if (!inicio || !destino) { alert('Ingrese inicio y destino'); return; }

    const [g1, g2] = await Promise.all([geocode(inicio), geocode(destino)]);
    if (!g1.length || !g2.length) { alert('Ubicación no encontrada'); return; }

    const route = await fetch(
        `https://router.project-osrm.org/route/v1/driving/${g1[0].lon},${g1[0].lat};${g2[0].lon},${g2[0].lat}?overview=full&geometries=geojson`
    ).then(r => r.json());

    const dist = (route.routes[0].distance / 1000).toFixed(2);
    document.getElementById('km_rec').value = (dist * 2).toFixed(2); // ida y vuelta
    calcularCombustible();

    if (routeLine) map.removeLayer(routeLine);
    routeLine = L.geoJSON(route.routes[0].geometry, {
        style: { color: '#145c38', weight: 5 }
    }).addTo(map);
    map.fitBounds(routeLine.getBounds());

    showTab('combustible', null);
}
</script>
</body>
</html>
