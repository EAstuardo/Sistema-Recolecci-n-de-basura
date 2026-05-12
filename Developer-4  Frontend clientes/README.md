# AgroGestor GT — Instrucciones de instalación

## Estructura del proyecto
```
gestorcolonias/
├── index.php              ← Dashboard con estadísticas
├── form_colonia.php       ← Registrar y listar colonias
├── form_cliente.php       ← Registrar clientes (teléfono 8 dígitos GT)
├── lista_clientes.php     ← Lista con filtros + exportar CSV
├── exportar_csv.php       ← Descarga CSV con filtros activos
├── install.sql            ← Crea las tablas en tu BD
├── includes/
│   ├── db.php             ← ⚠️ EDITAR con tus datos de 3GoogieHost
│   ├── header.php         ← Sidebar + modo oscuro/claro
│   └── footer.php         ← Cierre HTML
└── assets/
    ├── css/styles.css     ← Estilos (paleta agro + modo oscuro/claro)
    └── js/app.js          ← Toggle modo, toasts, validaciones JS
```

---

## PASO 1 — Crear la base de datos en 3GoogieHost

1. Entra a tu panel → **MySQL Databases**
2. Crea una base de datos (ej: `gestorcolonias`)
3. Crea un usuario y asígnalo con **todos los privilegios**
4. Ve a **phpMyAdmin** → selecciona tu BD
5. Pestaña **SQL** → pega el contenido de `install.sql` → **Ejecutar**

---

## PASO 2 — Configurar la conexión

Abre `includes/db.php` y edita:

```php
define('DB_HOST', 'localhost');          // Dejar como localhost
define('DB_USER', 'tu_usuario');         // Tu usuario MySQL
define('DB_PASS', 'tu_password');        // Tu contraseña MySQL
define('DB_NAME', 'gestorcolonias');     // Nombre de tu base de datos
```

---

## PASO 3 — Subir archivos

- Usa el **File Manager** del panel o **FTP** (FileZilla)
- Sube todo dentro de `public_html/`
- Mantén la estructura de carpetas exacta

---

## PASO 4 — Abrir en el navegador

```
http://tudominio.com/
```

---

## Características incluidas

- ✅ Teléfono de **8 dígitos** (validado en PHP y JS)
- ✅ Colonias cargadas desde la **base de datos** (no hardcodeadas)
- ✅ **Modo oscuro / claro** con botón en el sidebar (guardado en cookie)
- ✅ **22 departamentos de Guatemala** en select
- ✅ Validación completa en PHP (servidor)
- ✅ Filtros: búsqueda + colonia + estatus
- ✅ Exportar CSV con BOM UTF-8 (compatible con Excel)
- ✅ Dashboard con estadísticas y gráfica de barras

## Paleta de colores

| Nombre       | Hex       |
|--------------|-----------|
| Blanco crema | `#F1F2F0` |
| Verde lima   | `#84BF04` |
| Verde oscuro | `#72A603` |
| Dorado       | `#F2B705` |
| Rojo óxido   | `#A62F03` |

## Requisitos del servidor (3GoogieHost los cumple)

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Extensiones: PDO, PDO_MySQL
