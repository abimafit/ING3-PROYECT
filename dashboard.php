<?php
require_once 'includes/auth.php';
if (!estaLogueado()) {
    header('Location: login.php');
    exit;
}
$view = $_GET['view'] ?? '';
$rol = $_SESSION['rol'];
$stmt = $pdo->prepare("SELECT verificado FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_verificado = (int) ($stmt->fetchColumn() ?? 0);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <script>document.documentElement.classList.add('rw-js');</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>RentWheels - Panel de control</title>
    <link rel="icon" type="image/png" href="img/rw-logo.png">
    <link rel="stylesheet" href="css/style-global.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <a href="index.php" class="logo-area" title="Volver al inicio">
                <span class="logo-icon"><img src="img/rw-logo.png" alt="RentWheels"></span>
                <h1>RentWheels</h1>
            </a>
            <div class="user-area">
                <div class="user-menu" id="userMenu">
                    <button type="button" class="user-menu-btn" id="userMenuBtn">
                        <span class="user-avatar"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['nombre'], 0, 1))); ?></span>
<span class="user-meta">
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?><?php if ($user_verificado): ?><svg class="rw-verified-badge" viewBox="0 0 24 24" width="15" height="15" fill="#1da1f2" aria-label="Cuenta verificada"><path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81C14.67 2.63 13.43 1.75 12 1.75s-2.67.88-3.34 2.19c-1.39-.46-2.9-.2-3.91.81s-1.27 2.52-.81 3.91C2.63 9.33 1.75 10.57 1.75 12s.88 2.67 2.19 3.34c-.46 1.39-.2 2.9.81 3.91s2.52 1.27 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.67-.88 3.34-2.19c1.39.46 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34Zm-11.71 4.2L6.8 12.46l1.41-1.42 2.26 2.26 4.8-5.23 1.47 1.35-6.2 6.78Z"/></svg><?php endif; ?></span>
                                <span class="user-role-badge"><?php echo ucfirst($rol); ?></span>
                            </span>
                        <svg class="user-menu-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="user-menu-dropdown" id="userMenuDropdown">
                        <a href="faq.php" class="user-menu-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                            Centro de ayuda
                        </a>
                        <div class="user-menu-divider"></div>
                        <button type="button" class="user-menu-item logout" onclick="confirmarLogout()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                            Cerrar sesión
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="dashboard-layout">
            <aside class="dashboard-sidebar">
                <nav class="sidebar-nav">
                    <?php if ($rol == 'turista'): ?>
                        <a href="?view=panel" class="nav-link <?php echo ($view == 'panel' || $view == '') ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span> Mi panel
                        </a>
                        <a href="?view=buscar" class="nav-link <?php echo $view == 'buscar' ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></span> Buscar vehículos
                        </a>
                        <a href="?view=mis_reservas" class="nav-link <?php echo $view == 'mis_reservas' ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg></span> Mis reservas
                        </a>
                        <a href="?view=soporte" class="nav-link <?php echo $view == 'soporte' ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5Zm18 0h-3a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-5Z"/><path d="M21 10a9 9 0 0 0-18 0"/></svg></span> Soporte
                        </a>
                    <?php elseif ($rol == 'compania'): ?>
                        <a href="?view=panel" class="nav-link <?php echo ($view == 'panel' || $view == '') ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span> Mi panel
                        </a>
                        <a href="?view=gestion_flota" class="nav-link <?php echo $view == 'gestion_flota' ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg></span> Gestionar flota
                        </a>
                        <a href="?view=reservas_recibidas" class="nav-link <?php echo $view == 'reservas_recibidas' ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg></span> Reservas recibidas
                        </a>
                        <?php if (!$user_verificado): ?>
                            <a href="?view=verificacion" class="nav-link <?php echo $view == 'verificacion' ? 'active' : ''; ?>">
                                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg></span> Verificar cuenta
                            </a>
                        <?php endif; ?>
                    <?php elseif ($rol == 'administrador'): ?>
                        <a href="?view=dashboard" class="nav-link <?php echo ($view == 'dashboard' || $view == '') ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg></span> Dashboard
                        </a>
                        <a href="?view=usuarios" class="nav-link <?php echo $view == 'usuarios' ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span> Usuarios
                        </a>
                        <a href="?view=reservas" class="nav-link <?php echo $view == 'reservas' ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg></span> Reservas
                        </a>
                        <a href="?view=tickets" class="nav-link <?php echo $view == 'tickets' ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg></span> Tickets
                        </a>
                        <a href="?view=reportes" class="nav-link <?php echo $view == 'reportes' ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg></span> Reportes
                        </a>
                    <?php elseif ($rol == 'soporte'): ?>
                        <a href="?view=panel" class="nav-link <?php echo ($view == 'panel' || $view == '') ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span> Mi panel
                        </a>
                        <a href="?view=tickets" class="nav-link <?php echo $view == 'tickets' ? 'active' : ''; ?>">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg></span> Tickets
                        </a>
                    <?php endif; ?>
                </nav>
            </aside>

            <main class="dashboard-content">
                <div class="content-card">
                    <?php
                    switch ($rol) {
                        case 'turista':
                            if ($view == 'panel') include 'views/dashboard_main.php';
                            elseif ($view == 'buscar') include 'views/turista/buscar.php';
                            elseif ($view == 'mis_reservas') include 'views/turista/mis_reservas.php';
                            elseif ($view == 'soporte') include 'views/turista/soporte.php';
                            elseif ($view == 'pago') include 'views/turista/pago.php'; //NUEVO
                            elseif ($view == 'recibo') include 'views/turista/recibo.php';
                            else include 'views/dashboard_main.php';
                            break;
                        case 'compania':
                            if ($view == 'panel') include 'views/dashboard_main.php';
                            elseif ($view == 'gestion_flota') include 'views/compania/gestion_flota.php';
                            elseif ($view == 'reservas_recibidas') include 'views/compania/reservas_recibidas.php';
                            elseif ($view == 'verificacion') include 'views/compania/verificacion.php';
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

    <!-- Sistema compartido de toasts y modales -->
    <script>
        (function() {
            var btn = document.getElementById('userMenuBtn');
            var dd = document.getElementById('userMenuDropdown');
            var menu = document.getElementById('userMenu');
            if (!btn || !dd || !menu) return;

            function closeMenu() {
                btn.classList.remove('open');
                dd.classList.remove('open');
            }

            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                btn.classList.toggle('open');
                dd.classList.toggle('open');
            });

            document.addEventListener('click', function(e) {
                if (!menu.contains(e.target)) closeMenu();
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeMenu();
            });
        })();

        function confirmarLogout() {
            showConfirm('¿Estás seguro de que deseas cerrar sesión?', function() {
                window.location.href = 'logout.php';
            });
        }
    </script>
    <script src="js/modals.js"></script>
</body>

</html>