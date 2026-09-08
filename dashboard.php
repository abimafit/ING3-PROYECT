<?php
require_once 'includes/auth.php';
if (!estaLogueado()) {
    header('Location: login.php');
    exit;
}
$view = $_GET['view'] ?? '';
$rol = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>RentWheels - Panel de control</title>
    <link rel="stylesheet" href="css/style-global.css">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="logo-area">
                <h1>RentWheels</h1>
                <span class="user-role-badge"><?php echo ucfirst($rol); ?></span>
            </div>
            <div class="user-area">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                <a href="logout.php" class="btn-logout">Cerrar sesión</a>
            </div>
        </header>

        <div class="dashboard-layout">
            <aside class="dashboard-sidebar">
                <nav class="sidebar-nav">
                    <?php if ($rol == 'turista'): ?>
                        <a href="?view=buscar" class="nav-link <?php echo $view == 'buscar' ? 'active' : ''; ?>">
                           Buscar vehículos
                        </a>
                        <a href="?view=mis_reservas" class="nav-link <?php echo $view == 'mis_reservas' ? 'active' : ''; ?>">
                             Mis reservas
                        </a>
                        <a href="?view=soporte" class="nav-link <?php echo $view == 'soporte' ? 'active' : ''; ?>">
                             Soporte
                        </a>
                    <?php elseif ($rol == 'compania'): ?>
                        <a href="?view=gestion_flota" class="nav-link <?php echo $view == 'gestion_flota' ? 'active' : ''; ?>">
                            Gestionar flota
                        </a>
                        <a href="?view=reservas_recibidas" class="nav-link <?php echo $view == 'reservas_recibidas' ? 'active' : ''; ?>">
                             Reservas recibidas
                        </a>
                        <a href="?view=verificacion" class="nav-link <?php echo $view == 'verificacion' ? 'active' : ''; ?>">
                            Verificar cuenta
                        </a>
                    <?php elseif ($rol == 'administrador'): ?>
                        <a href="?view=dashboard" class="nav-link <?php echo ($view == 'dashboard' || $view == '') ? 'active' : ''; ?>">
                             Dashboard
                        </a>
                        <a href="?view=usuarios" class="nav-link <?php echo $view == 'usuarios' ? 'active' : ''; ?>">
                             Usuarios
                        </a>
                        <a href="?view=reservas" class="nav-link <?php echo $view == 'reservas' ? 'active' : ''; ?>">
                            Reservas
                        </a>
                        <a href="?view=tickets" class="nav-link <?php echo $view == 'tickets' ? 'active' : ''; ?>">
                             Tickets
                        </a>
                        <a href="?view=reportes" class="nav-link <?php echo $view == 'reportes' ? 'active' : ''; ?>">
                           Reportes
                        </a>
                    <?php endif; ?>
                </nav>
            </aside>

            <main class="dashboard-content">
                <div class="content-card">
                    <?php
                    switch ($rol) {
                        case 'turista':
                            if ($view == 'buscar') include 'views/turista/buscar.php';
                            elseif ($view == 'mis_reservas') include 'views/turista/mis_reservas.php';
                            elseif ($view == 'soporte') include 'views/turista/soporte.php';
                            elseif ($view == 'pago') include 'views/turista/pago.php'; //NUEVO
                            elseif ($view == 'recibo') include 'views/turista/recibo.php';
                            else include 'views/dashboard_main.php';
                            break;
                        case 'compania':
                            if ($view == 'gestion_flota') include 'views/compania/gestion_flota.php';
                            elseif ($view == 'reservas_recibidas') include 'views/compania/reservas_recibidas.php';
                            elseif ($view == 'verificacion') include 'views/compania/verificacion.php';
                            else include 'views/dashboard_main.php';
                            break;

                            if (!$verificado) {
                                // Mostrar vista de verificación en lugar del contenido normal
                                if ($view == 'verificar') {
                                    include 'views/compania/verificar.php';
                                } else {
                                    include 'views/compania/verificar.php';
                                }
                                break;
                            }

                            // Si está verificada, mostrar contenido normal
                            if ($view == 'gestion_flota') include 'views/compania/gestion_flota.php';
                            elseif ($view == 'reservas_recibidas') include 'views/compania/reservas_recibidas.php';
                            else include 'views/dashboard_main.php';
                            break;
                        case 'administrador':
                            if ($view == 'reportes') include 'views/admin/reportes.php';
                            elseif ($view == 'usuarios') include 'views/admin/usuarios.php';
                            elseif ($view == 'dashboard') include 'views/admin/dashboard_admin.php';
                            elseif ($view == 'tickets') include 'views/admin/tickets.php';
                            elseif ($view == 'reservas') include 'views/admin/reservas.php';
                            else include 'views/admin/dashboard_admin.php';
                            break;
                        case 'soporte':
                            if ($view == 'tickets') include 'views/soporte/tickets.php';
                            else include 'views/dashboard_main.php';
                            break;
                        default:
                            include 'views/dashboard_main.php';
                            break;
                    }
                    ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        if (window.innerWidth <= 768) {
            const links = document.querySelectorAll('.sidebar-nav .nav-link');
            links.forEach(link => {
                link.addEventListener('click', () => {});
            });
        }
    </script>
    <!-- Modal de notificaciones (global) -->
    <div id="appModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; padding:2rem; border-radius:24px; max-width:450px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.3); text-align:center; position:relative; animation:modalFadeIn 0.3s ease;">
            <div id="modalIcon" style="font-size:3rem; margin-bottom:0.5rem;"></div>
            <h3 id="modalTitle" style="margin:0 0 0.5rem 0; color:#1f2937;">Título</h3>
            <p id="modalMessage" style="margin:0 0 1.5rem 0; color:#6b7280; line-height:1.5;">Mensaje</p>
            <div id="modalActions" style="display:flex; gap:0.5rem; justify-content:center; flex-wrap:wrap;">
                <button id="modalBtnConfirm" style="display:none; background:#10b981; color:white; border:none; padding:0.6rem 1.5rem; border-radius:40px; font-weight:600; cursor:pointer; transition:0.2s;">Confirmar</button>
                <button id="modalBtnCancel" style="display:none; background:#ef4444; color:white; border:none; padding:0.6rem 1.5rem; border-radius:40px; font-weight:600; cursor:pointer; transition:0.2s;">Cancelar</button>
                <button id="modalBtnClose" style="background:#2563eb; color:white; border:none; padding:0.6rem 1.5rem; border-radius:40px; font-weight:600; cursor:pointer; transition:0.2s;">Cerrar</button>
            </div>
        </div>
    </div>

    <style>
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>

    <script>
        // Función global para mostrar el modal
        function showModal(options) {
            const modal = document.getElementById('appModal');
            const icon = document.getElementById('modalIcon');
            const title = document.getElementById('modalTitle');
            const message = document.getElementById('modalMessage');
            const btnConfirm = document.getElementById('modalBtnConfirm');
            const btnCancel = document.getElementById('modalBtnCancel');
            const btnClose = document.getElementById('modalBtnClose');

            // Valores por defecto
            const config = {
                icon: options.icon || 'ℹ️',
                title: options.title || 'Aviso',
                message: options.message || '',
                type: options.type || 'info', // info, success, error, warning, confirm
                onConfirm: options.onConfirm || null,
                onCancel: options.onCancel || null,
                onClose: options.onClose || null,
                confirmText: options.confirmText || 'Aceptar',
                cancelText: options.cancelText || 'Cancelar',
            };

            // Configurar iconos según tipo
            const typeMap = {
                success: {
                    icon: '✅',
                    title: 'Éxito'
                },
                error: {
                    icon: '❌',
                    title: 'Error'
                },
                warning: {
                    icon: '⚠️',
                    title: 'Advertencia'
                },
                info: {
                    icon: 'ℹ️',
                    title: 'Información'
                },
                confirm: {
                    icon: '❓',
                    title: 'Confirmar'
                },
            };
            if (typeMap[config.type]) {
                config.icon = config.icon || typeMap[config.type].icon;
                config.title = config.title || typeMap[config.type].title;
            }

            // Aplicar valores
            icon.textContent = config.icon;
            title.textContent = config.title;
            message.textContent = config.message;

            // Configurar botones
            btnClose.style.display = 'inline-block';
            btnConfirm.style.display = 'none';
            btnCancel.style.display = 'none';

            if (config.type === 'confirm') {
                btnConfirm.style.display = 'inline-block';
                btnCancel.style.display = 'inline-block';
                btnConfirm.textContent = config.confirmText;
                btnCancel.textContent = config.cancelText;
                btnClose.style.display = 'none';
            } else {
                btnClose.textContent = 'Cerrar';
            }

            // Mostrar modal
            modal.style.display = 'flex';

            // Eventos
            btnClose.onclick = function() {
                modal.style.display = 'none';
                if (config.onClose) config.onClose();
            };

            btnConfirm.onclick = function() {
                modal.style.display = 'none';
                if (config.onConfirm) config.onConfirm();
            };

            btnCancel.onclick = function() {
                modal.style.display = 'none';
                if (config.onCancel) config.onCancel();
            };
        }

        // Función para mostrar notificaciones rápidas (tipo alert)
        function showAlert(message, type = 'info', title = '') {
            showModal({
                message,
                type,
                title
            });
        }

        // Función para confirmar acciones (reemplaza confirm)
        function showConfirm(message, onConfirm, onCancel) {
            showModal({
                message,
                type: 'confirm',
                onConfirm: onConfirm || function() {},
                onCancel: onCancel || function() {},
                confirmText: 'Sí',
                cancelText: 'No'
            });
        }

        // Función para mostrar mensajes de error desde el backend (usar en los .fail de AJAX)
        function handleAjaxError(xhr) {
            let msg = 'Error de conexión con el servidor.';
            try {
                const resp = JSON.parse(xhr.responseText);
                if (resp.error) msg = resp.error;
                else if (resp.message) msg = resp.message;
            } catch (e) {}
            showAlert(msg, 'error');
        }
    </script>
</body>

</html>