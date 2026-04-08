// alternarTema.js

function cambiarModo() {
    const body = document.body;
    // Si es dark lo pasa a light, y viceversa
    const nuevoTema = body.getAttribute("data-theme") === "dark" ? "light" : "dark";
    
    aplicarTema(nuevoTema);
}

function aplicarTema(tema) {
    document.body.setAttribute("data-theme", tema);
    localStorage.setItem("tema-recolectora", tema);

    // Sincronizar el checkbox si existe en la página actual (configuración)
    const checkbox = document.getElementById('check-modo');
    if (checkbox) {
        checkbox.checked = (tema === 'dark');
    }
}


// Al cargar cualquier página, aplicar el tema guardado
document.addEventListener("DOMContentLoaded", () => {
    const guardado = localStorage.getItem("tema-recolectora") || "light";
    aplicarTema(guardado);
});

// Listener para detectar cambios en el tema desde otras pestañas o al regresar
window.addEventListener("storage", (event) => {
    if (event.key === "tema-recolectora") {
        aplicarTema(event.newValue || "light");
    }
});