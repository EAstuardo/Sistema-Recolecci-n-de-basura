<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes</title>
    <link rel="stylesheet" href="../../css/sReport.css">
    <link rel="stylesheet" href="../../css/diseño_responsivo.css">
    <link rel="stylesheet" href="../../css/estilos_base.css">
    <link rel="stylesheet" href="../../css/modo_oscuro.css">
    <link rel="stylesheet" href="../../css/modo_claro.css">
</head>
<body>

<div class="container">

    <h1>📊 Reportes</h1>

    <!-- � FILTROS -->
    <div class="filtros">
        <label>Tipo de reporte:</label>
        <select>
            <option>Rutas</option>
            <option>Combustible</option>
            <option>Vehículos</option>
        </select>

        <label>Fecha inicio:</label>
        <input type="date">

        <label>Fecha fin:</label>
        <input type="date">

        <label>Vehículo:</label>
        <select>
            <option>Todos</option>
        </select>

        <label>Conductor:</label>
        <select>
            <option>Todos</option>
        </select>

        <button onclick="generarReporte()">Generar</button>
    </div>

    <!-- 🔹 BOTONES -->
    <div class="acciones">
        <button onclick="exportarPDF()">📄 Exportar PDF</button>
        <button onclick="exportarExcel()">📊 Exportar Excel</button>
    </div>

    <!-- 🔹 TABLA -->
    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Vehículo</th>
                    <th>Conductor</th>
                    <th>Toneladas</th>
                    <th>Consumo</th>
                </tr>
            </thead>

            <tbody id="tablaDatos">
                <tr>
                    <td colspan="5" style="text-align:center; color: #777;">
                        No hay datos para mostrar
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script src="../assets/js/app.js"></script>

</body>
</html>