<div class="tarjeta-kpi verde">
    <span class="kpi-label">Gasto en Gasolina Hoy</span>
    <h2 id="gasto_gasolina"></h2>
    <span class="kpi-variacion" id="variacion_gasolina"></span>
</div>
<!-- .otros KPIs, con IDs únicos... -->

<div class="tarjeta-kpi verde">
    <span class="kpi-label">Vehículos en operación</span>
    <h2 id="vehiculos_operacion"></h2>
    <span class="kpi-variacion" id="variacion_vehiculos"></span>
</div>

<div class="tarjeta-kpi verde">
    <span class="kpi-label">Consumo promedio</span>
    <h2 id="consumo_promedio"></h2>
    <span class="kpi-variacion" id="variacion_consumo"></span>
</div>

<div class="tarjeta-kpi verde">
    <span class="kpi-label">Vehículo fuera de rango</span>
    <h2 id="vehiculos_fuera_rango"></h2>
    <span class="kpi-variacion" id="variacion_fuera_rango"></span>
</div>

<script>
fetch('componentes/obtener_kpis.php')
  .then(response => response.json())
  .then(data => {
    document.getElementById('vehiculos_operacion').textContent = data.vehiculos_operacion;
    document.getElementById('variacion_vehiculos').textContent = data.variacion_vehiculos + '% vs ayer';
    document.getElementById('consumo_promedio').textContent = data.consumo_promedio;
    document.getElementById('variacion_consumo').textContent = data.variacion_consumo + '% vs ayer';
    document.getElementById('vehiculos_fuera_rango').textContent = data.vehiculos_fuera_rango;
    document.getElementById('variacion_fuera_rango').textContent = data.variacion_fuera_rango + '% vs ayer';
  });
</script>