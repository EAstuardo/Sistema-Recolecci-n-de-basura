<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración | Sistema de Recolección</title>

    <link rel="stylesheet" href="css/estilos_base.css">
    <link rel="stylesheet" href="css/diseño_responsivo.css">

    <link rel="stylesheet" href="css/modo_claro.css">
    <link rel="stylesheet" href="css/modo_oscuro.css">
</head>
<body>
    <div class="configuracion-container" style="padding:32px 24px; border-radius:12px; min-width:320px;">
        <h2>Configuración</h2>
        
        <div class="opcion-tema opcion">
            <span>Modo Oscuro</span>
            <label class="switch">
                <input type="checkbox" id="check-modo" onclick="cambiarModo()">
                <span class="slider round"></span>
            </label>
        </div>

        <div class="opcion-cambiar-pass" style="margin-top:16px;">
            <button onclick="alert('Función para cambiar contraseña')">Cambiar Contraseña</button>
        </div>
        <div class="opcion-notificaciones" style="margin-top:16px;">
            <label style="cursor: pointer;">
                <input type="checkbox" id="notificaciones" checked>
                Recibir notificaciones de alertas
            </label>
        </div>
        <div class="opcion-admin" style="margin-top:16px;">
            <button onclick="window.location.href='/admin/'">Solo Administrador</button>
        </div>
        <div class="opcion-cerrar" style="margin-top:24px;">
            <button onclick="window.history.back()">Cerrar</button>
        </div>
    </div>

        <link rel="stylesheet" href="css/configuracion.css">
        <script src="js/alternarTema.js"></script>
        <style>
        /* Switch moderno para modo oscuro */
        .switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 26px;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #bdbdbd;
            transition: .4s;
            border-radius: 26px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #8bc34a;
        }
        input:checked + .slider:before {
            transform: translateX(22px);
        }
        </style>
</body>
</html>