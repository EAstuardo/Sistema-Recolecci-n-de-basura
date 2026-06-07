<?php
/**
 * pages/pagos.php
 * Registro de pagos y gestión de recibos
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
$page_title = 'Registrar Pago';
$page_subtitle = 'Registra pagos mensuales de clientes';

$db = getDB();
$mensaje = null;
$tipo_mensaje = '';
$cliente_seleccionado = null;

// Cliente específico desde URL
$cliente_id = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;
if ($cliente_id > 0) {
    $stmt = $db->prepare("
        SELECT c.*, col.nombre as colonia_nombre, col.tarifa_mensual 
        FROM clientes c 
        JOIN colonias col ON col.id = c.colonia_id 
        WHERE c.id = ?
    ");
    $stmt->execute([$cliente_id]);
    $cliente_seleccionado = $stmt->fetch();
}

// Obtener clientes para el select
$clientes = $db->query("
    SELECT c.id, c.nombre, c.apellido, col.nombre as colonia, col.tarifa_mensual
    FROM clientes c
    JOIN colonias col ON col.id = c.colonia_id
    WHERE c.estatus = 'activo'
    ORDER BY c.nombre ASC
")->fetchAll();

include '../includes/header.php';
?>

<div class="card">
    <div class="card-title">💰 Registrar Nuevo Pago</div>
    
    <form id="pagoForm">
        <div class="form-group">
            <label for="cliente_id">Cliente <span class="required">*</span></label>
            <select id="cliente_id" name="cliente_id" class="form-control" required>
                <option value="">— Selecciona un cliente —</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?php echo $cliente['id']; ?>" 
                        data-tarifa="<?php echo $cliente['tarifa_mensual']; ?>"
                        <?php echo ($cliente_seleccionado && $cliente_seleccionado['id'] == $cliente['id']) ? 'selected' : ''; ?>>
                        <?php echo h($cliente['nombre'] . ' ' . $cliente['apellido'] . ' - ' . $cliente['colonia']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="monto">Monto (Q) <span class="required">*</span></label>
                <input type="number" id="monto" name="monto" class="form-control" required step="0.01" min="0" 
                       placeholder="0.00">
            </div>
            
            <div class="form-group">
                <label for="fecha_pago">Fecha de Pago <span class="required">*</span></label>
                <input type="date" id="fecha_pago" name="fecha_pago" class="form-control" required 
                       value="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="mes_pagado">Mes Pagado (opcional)</label>
                <input type="month" id="mes_pagado" name="mes_pagado" class="form-control" 
                       value="<?php echo date('Y-m'); ?>">
                <small>Dejar vacío para usar la fecha de pago</small>
            </div>
            
            <div class="form-group">
                <label for="metodo_pago">Método de Pago</label>
                <select id="metodo_pago" name="metodo_pago" class="form-control">
                    <option value="EFECTIVO">Efectivo</option>
                    <option value="TRANSFERENCIA">Transferencia</option>
                    <option value="TARJETA">Tarjeta</option>
                    <option value="CHEQUE">Cheque</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="observaciones">Observaciones (opcional)</label>
            <textarea id="observaciones" name="observaciones" class="form-control" rows="2" 
                      placeholder="Notas adicionales..."></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">✓ Registrar Pago</button>
            <button type="reset" class="btn btn-secondary" onclick="this.form.reset(); limpiarTarifa();">Limpiar</button>
        </div>
    </form>
</div>

<div id="ultimoRecibo"></div>

<script>
// Mostrar tarifa automática
const clienteSelect = document.getElementById('cliente_id');
const montoInput = document.getElementById('monto');

clienteSelect.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const tarifa = selected.getAttribute('data-tarifa');
    if (tarifa) {
        montoInput.value = tarifa;
    }
});

// Si ya hay cliente seleccionado, cargar su tarifa
if (clienteSelect.value) {
    const selected = clienteSelect.options[clienteSelect.selectedIndex];
    const tarifa = selected.getAttribute('data-tarifa');
    if (tarifa) montoInput.value = tarifa;
}

// Enviar formulario
document.getElementById('pagoForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Guardando...';
    submitBtn.disabled = true;
    
    const data = {
        cliente_id: parseInt(document.getElementById('cliente_id').value),
        monto: parseFloat(document.getElementById('monto').value),
        fecha_pago: document.getElementById('fecha_pago').value,
        mes_pagado: document.getElementById('mes_pagado').value,
        metodo_pago: document.getElementById('metodo_pago').value,
        observaciones: document.getElementById('observaciones').value
    };
    
    try {
        const response = await fetch('../api/registrar_pago.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            document.getElementById('ultimoRecibo').innerHTML = `
                <div class="card" style="margin-top: 20px; background: var(--primary-light);">
                    <div class="card-title">🧾 Recibo Generado</div>
                    <p><strong>Número de recibo:</strong> ${result.numero_recibo}</p>
                    <p><strong>Fecha:</strong> ${new Date().toLocaleString()}</p>
                    <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir Recibo</button>
                </div>
            `;
            document.getElementById('pagoForm').reset();
            document.getElementById('fecha_pago').value = new Date().toISOString().split('T')[0];
            document.getElementById('mes_pagado').value = new Date().toISOString().slice(0, 7);
        } else {
            showToast(result.error, 'error');
        }
    } catch (err) {
        showToast('Error al registrar pago', 'error');
    } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});

function limpiarTarifa() {
    montoInput.value = '';
}
</script>

<?php include '../includes/footer.php'; ?>