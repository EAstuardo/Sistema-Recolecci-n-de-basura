// Coordenadas de ejemplo para vehículos (puedes obtenerlas de tu backend PHP)
const vehiculos = [
    { id: 1, nombre: "Unidad 01", lat: 19.4326, lng: -99.1332 },
    { id: 2, nombre: "Unidad 02", lat: 19.4270, lng: -99.1400 },
    { id: 3, nombre: "Unidad 03", lat: 19.4400, lng: -99.1200 }
];

// Inicializa el mapa
const mapa = L.map('mapa').setView([19.4326, -99.1332], 12); // Centrado en CDMX

// Capa base de OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(mapa);

// Agrega los marcadores de los vehículos
vehiculos.forEach(v => {
    L.marker([v.lat, v.lng])
        .addTo(mapa)
        .bindPopup(`<b>${v.nombre}</b>`);
});