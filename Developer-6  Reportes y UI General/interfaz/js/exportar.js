function exportarExcel() {
    const tabla = document.getElementById("tablaReportes");
    let contenido = "";

    for (let i = 0; i < tabla.rows.length; i++) {
        let fila = tabla.rows[i];

        // 🚫 Saltar fila "No hay datos"
        if (fila.cells.length === 1 && fila.cells[0].colSpan > 1) {
            continue;
        }

        let filaTexto = [];
        for (let celda of fila.cells) {
            filaTexto.push(celda.innerText);
        }

        contenido += filaTexto.join("\t") + "\n";
    }

    // 🚫 Si no hay datos reales
    if (contenido.trim() === "") {
        alert("No hay datos para exportar");
        return;
    }

    const blob = new Blob([contenido], { type: 'application/vnd.ms-excel' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'reporte.xls';
    link.click();
}


function exportarPDF() {
    const tabla = document.getElementById("tablaReportes");
    let contenido = "REPORTE\n\n";

    let hayDatos = false;

    for (let i = 0; i < tabla.rows.length; i++) {
        let fila = tabla.rows[i];

        // 🚫 Saltar fila "No hay datos"
        if (fila.cells.length === 1 && fila.cells[0].colSpan > 1) {
            continue;
        }

        let filaTexto = [];
        for (let celda of fila.cells) {
            filaTexto.push(celda.innerText);
        }

        contenido += filaTexto.join(" | ") + "\n";
        hayDatos = true;
    }

    // 🚫 Si no hay datos reales
    if (!hayDatos) {
        alert("No hay datos para exportar");
        return;
    }

    const blob = new Blob([contenido], { type: 'application/pdf' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'reporte.pdf';
    link.click();
}