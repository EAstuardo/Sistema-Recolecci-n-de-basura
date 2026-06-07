# Sistema de Control de Gasolina — Recolectora S.A.
## Sistema Unificado (dev1Wendy + dev2Cesar + dev3Herielis + dev4Natalia)

---

## Estructura del proyecto

```
sistema_gasolina/
│
├── config/
│   ├── db.php          ← Conexión PDO (editar credenciales aquí)
│   └── auth.php        ← Middleware de sesión y roles
│
├── api/
│   ├── camiones/
│   │   ├── listar.php          GET  → lista todos los camiones
│   │   ├── registrar.php       POST → crea un camión (ADMIN)
│   │   └── detalle.php         GET  ?id=N → detalle de un camión
│   │
│   ├── combustible/
│   │   ├── listar.php          GET  ?id_camion=N → historial cargas
│   │   ├── registrar.php       POST → nueva carga (solo ADMIN)
│   │   ├── consumo.php         GET  ?id_camion=N → cargas del camión
│   │   └── consumo_promedio.php GET ?id_camion=N → estadísticas
│   │
│   ├── colonias/
│   │   ├── listar.php          GET  → colonias activas
│   │   └── registrar.php       POST → nueva colonia (ADMIN)
│   │
│   └── reportes/
│       └── ganancia_diaria.php GET  ?fecha=YYYY-MM-DD → KPIs del día
│
├── sql/
│   └── database.sql    ← Script completo de la base de datos
│
├── login.php           ← Página de inicio de sesión
├── validar_login.php   ← Procesa el login (seguro, prepared statements)
├── logout.php          ← Cierra la sesión
├── admin.php           ← Panel principal (solo ADMIN)
└── colaborador.php     ← Vista para cobradores/operadores
```

---

## Instalación en XAMPP / Laragon

1. **Copiar** esta carpeta a `htdocs/` (XAMPP) o `www/` (Laragon)
2. **Crear la base de datos**:
   - Abrir phpMyAdmin → SQL → pegar contenido de `sql/database.sql` → Ejecutar
3. **Ajustar credenciales** en `config/db.php` si difieren de los valores por defecto
4. **Acceder** a: `http://localhost/sistema_gasolina/login.php`

### Credenciales de prueba
| Email | Contraseña | Rol |
|---|---|---|
| admin@recolectora.com | admin1234 | ADMIN |

---

## Lo que hace cada módulo

| Módulo | Responsable original | Función |
|---|---|---|
| Endpoints API (camiones, combustible) | Wendy | Backend REST con validaciones |
| Login, roles, alerta de consumo | Cesar | Seguridad y cálculo de rendimiento |
| Frontend camiones y combustible, API PDO | Herielis | UI y API con PDO limpio |
| Dashboard financiero (KPIs) | Natalia | Ingresos, gastos, ganancia real |

## Cambios realizados al unir

- **Base de datos única** `recolectora`: se unificaron los esquemas de Wendy (`basura.sql`) y Cesar (`database.sql`) en uno solo, preservando todas las tablas y relaciones.
- **Conexión**: todos los archivos ahora usan PDO desde `config/db.php` (estándar de Herielis, más seguro).
- **Login seguro**: `validar_login.php` usa prepared statements; acepta bcrypt (Cesar) y texto plano legacy (Wendy).
- **Rol en `combustible/registrar.php`**: verifica `$_SESSION['rol'] === 'ADMIN'` antes de insertar (requisito de Cesar).
- **Alerta automática**: al registrar combustible, si rendimiento < 6 km/L o costo > Q2,000 sin km, se guarda `alerta=1` (lógica de Cesar).
- **Cálculo automático**: `costo_total = litros × precio_litro` se calcula en PHP antes de insertar (Cesar).
- **`admin.php`**: panel unificado con tabs: Dashboard (Natalia), Combustible (Cesar + mapa), Camiones (Herielis), Historial.
- **Columna `km_recorridos`** agregada a la tabla `combustible` para soportar el rendimiento calculado.
