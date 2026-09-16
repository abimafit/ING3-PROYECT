<?php
require_once 'includes/auth.php';
$user_verificado = 0;
if (estaLogueado()) {
    $stmtV = $pdo->prepare("SELECT verificado FROM usuarios WHERE id = ?");
    $stmtV->execute([$_SESSION['user_id']]);
    $user_verificado = (int) ($stmtV->fetchColumn() ?? 0);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <script>document.documentElement.classList.add('rw-js');</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Ayuda - RentWheels</title>
    <link rel="icon" type="image/png" href="img/rw-logo.png">
    <link rel="stylesheet" href="css/style-global.css">
    <link rel="stylesheet" href="css/style-public.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <!-- HEADER -->
    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo" title="Ir al inicio">
                <span class="logo-icon"><img src="img/rw-logo.png" alt="RentWheels"></span>
                <span class="logo-text">RentWheels</span>
            </a>
            <nav class="main-nav">
                <a href="index.php">Inicio</a>
                <a href="faq.php" class="active">Ayuda</a>
            </nav>
            <div class="header-actions">
                <?php if (estaLogueado()): ?>
                    <div class="user-menu user-menu--light" id="userMenu">
                        <button type="button" class="user-menu-btn" id="userMenuBtn">
                            <span class="user-avatar"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['nombre'], 0, 1))); ?></span>
                            <span class="user-meta">
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?><?php if ($user_verificado): ?><svg class="rw-verified-badge" viewBox="0 0 24 24" width="15" height="15" fill="#1da1f2" aria-label="Cuenta verificada"><path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81C14.67 2.63 13.43 1.75 12 1.75s-2.67.88-3.34 2.19c-1.39-.46-2.9-.2-3.91.81s-1.27 2.52-.81 3.91C2.63 9.33 1.75 10.57 1.75 12s.88 2.67 2.19 3.34c-.46 1.39-.2 2.9.81 3.91s2.52 1.27 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.67-.88 3.34-2.19c1.39.46 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34Zm-11.71 4.2L6.8 12.46l1.41-1.42 2.26 2.26 4.8-5.23 1.47 1.35-6.2 6.78Z"/></svg><?php endif; ?></span>
                                <span class="user-role-badge"><?php echo ucfirst($_SESSION['rol']); ?></span>
                            </span>
                            <svg class="user-menu-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div class="user-menu-dropdown" id="userMenuDropdown">
                            <a href="dashboard.php" class="user-menu-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                Mi panel
                            </a>
                            <div class="user-menu-divider"></div>
                            <button type="button" class="user-menu-item logout" onclick="confirmarLogout()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                                Cerrar sesión
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn-outline">Iniciar sesión</a>
                    <a href="register.php" class="btn-primary">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- HERO -->
    <section class="faq-hero">
        <h1>Centro de <span>Ayuda</span></h1>
        <p>Todo lo que necesitas saber para usar RentWheels como un experto.</p>
    </section>

    <!-- Buscador -->
    <div class="faq-search">
        <div class="search-wrap">
            <span class="search-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </span>
            <input type="text" id="searchInput" placeholder="Buscar en la ayuda..." onkeyup="filtrarPreguntas()">
        </div>
    </div>

    <!-- Tabs -->
    <div class="faq-tabs" id="faqTabs">
        <button class="faq-tab active" data-tab="general">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            General
        </button>
        <button class="faq-tab" data-tab="turista">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Turista
        </button>
        <button class="faq-tab" data-tab="compania">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/></svg>
            Compañía
        </button>
        <button class="faq-tab" data-tab="admin">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
            Administrador
        </button>
        <button class="faq-tab" data-tab="soporte">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5Zm18 0h-3a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-5Z"/><path d="M21 10a9 9 0 0 0-18 0"/></svg>
            Soporte
        </button>
    </div>

    <!-- CONTENIDO DE SECCIONES -->

    <!-- ===== SECCIÓN GENERAL ===== -->
    <div id="seccion-general" class="faq-section active">
        <div class="faq-item open">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Qué es RentWheels?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>RentWheels es un marketplace de alquiler de vehículos que conecta a turistas con compañías de renta de autos en Panamá. Aquí encontrarás guías paso a paso para cada rol.</p>
                    <div class="tip"><strong>Consejo:</strong> Usa las pestañas de arriba para navegar según tu perfil.</div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo me registro?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Haz clic en <strong>Registrarse</strong> en la esquina superior derecha. Completa tu nombre, correo, contraseña y selecciona tu rol (turista o compañía). Si eres compañía, deberás ingresar datos bancarios y subir un código QR.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Olvidé mi contraseña?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>En la página de inicio de sesión, haz clic en <strong>¿Olvidaste tu contraseña?</strong> y sigue las instrucciones. Recibirás un enlace para restablecerla (próximamente). Por ahora, contacta al administrador.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo contacto con soporte?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Dentro de tu panel, ve a la sección <strong>Soporte</strong> y crea un ticket. El equipo de soporte te responderá a la brevedad.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Qué navegador debo usar?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Recomendamos Chrome, Firefox, Edge o Safari en su versión más reciente.</p>
                </div>
            </div>
        </div>

        <div class="faq-empty" id="empty-general">No se encontraron resultados en General.</div>
    </div>

    <!-- ===== SECCIÓN TURISTA ===== -->
    <div id="seccion-turista" class="faq-section">
        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo me registro como turista?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ol>
                        <li>Ve a <strong>Registrarse</strong>.</li>
                        <li>Completa tus datos (nombre, correo, contraseña).</li>
                        <li>Selecciona <strong>Turista extranjero</strong>.</li>
                        <li>Haz clic en <strong>Registrarse</strong>.</li>
                        <li>Revisa tu correo (simulado) y confirma tu cuenta.</li>
                    </ol>
                    <div class="tip">Ya puedes iniciar sesión.</div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo busco vehículos?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ol>
                        <li>Inicia sesión y ve al panel de <strong>Turista</strong>.</li>
                        <li>Haz clic en <strong>Buscar vehículos</strong>.</li>
                        <li>Selecciona la <strong>ciudad</strong>, <strong>fecha de inicio</strong> y <strong>fecha de fin</strong>.</li>
                        <li>Haz clic en <strong>Buscar</strong>.</li>
                    </ol>
                    <p>Verás una lista de vehículos disponibles con precio y compañía.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo reservo y pago un vehículo?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ol>
                        <li>En la lista de resultados, haz clic en <strong>Reservar</strong>.</li>
                        <li>Confirma las fechas y el monto.</li>
                        <li>La reserva quedará en estado <strong>pendiente</strong> hasta que la compañía la confirme.</li>
                        <li>Cuando la compañía confirme, ve a <strong>Mis reservas</strong> y haz clic en <strong>Pagar</strong>.</li>
                        <li>Verás los datos bancarios de la compañía y el código QR.</li>
                        <li>Haz clic en <strong>Confirmar pago</strong>.</li>
                    </ol>
                    <div class="success-box">¡Reserva pagada! Aparecerá en tu historial.</div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo gestiono mis reservas?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ul>
                        <li><strong>Cancelar:</strong> Si la reserva está pendiente, puedes cancelarla desde <strong>Mis reservas</strong>.</li>
                        <li><strong>Eliminar:</strong> Si la reserva está cancelada, puedes eliminarla permanentemente.</li>
                        <li><strong>Historial:</strong> En la pestaña <strong>Historial</strong> verás todas tus reservas pagadas y canceladas.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo recibo soporte como turista?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Si tienes problemas, ve a <strong>Soporte</strong> y crea un ticket. Describe tu problema y el equipo te responderá.</p>
                </div>
            </div>
        </div>

        <div class="faq-empty" id="empty-turista">No se encontraron resultados en Turista.</div>
    </div>

    <!-- ===== SECCIÓN COMPAÑÍA ===== -->
    <div id="seccion-compania" class="faq-section">
        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo me registro como compañía?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ol>
                        <li>Ve a <strong>Registrarse</strong>.</li>
                        <li>Completa tus datos (nombre, correo, contraseña).</li>
                        <li>Selecciona <strong>Compañía de renta</strong>.</li>
                        <li>Ingresa tu <strong>ciudad</strong> (selecciona del menú).</li>
                        <li>Completa los datos bancarios (cuenta, teléfono, banco).</li>
                        <li>Sube una imagen con tu <strong>código QR</strong> (opcional).</li>
                        <li>Haz clic en <strong>Registrarse</strong>.</li>
                    </ol>
                    <div class="note">Recibirás un <strong>código de verificación de 6 dígitos</strong>. Guárdalo, lo necesitarás para activar tu cuenta.</div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo verifico mi cuenta de compañía?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ol>
                        <li>Inicia sesión con tu correo y contraseña.</li>
                        <li>Ve a tu panel de <strong>Compañía</strong>.</li>
                        <li>En la sección de verificación, ingresa el código de 6 dígitos.</li>
                        <li>Haz clic en <strong>Verificar</strong>.</li>
                    </ol>
                    <div class="success-box">¡Cuenta activada! Ahora puedes publicar vehículos.</div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo gestiono mi flota de vehículos?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ol>
                        <li>En el panel, ve a <strong>Gestionar flota</strong>.</li>
                        <li>Para agregar un vehículo: completa los campos (marca, modelo, año, precio, imagen).</li>
                        <li>Haz clic en <strong>Agregar vehículo</strong>.</li>
                        <li>Para eliminar: haz clic en el botón <strong>Eliminar</strong> del vehículo.</li>
                    </ol>
                    <div class="tip">Solo puedes eliminar vehículos sin reservas activas.</div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo gestiono las reservas recibidas?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ol>
                        <li>Ve a <strong>Reservas recibidas</strong>.</li>
                        <li>Verás todas las solicitudes de reserva.</li>
                        <li>Haz clic en <strong>Confirmar</strong> o <strong>Cancelar</strong> según corresponda.</li>
                    </ol>
                    <p>Las reservas confirmadas aparecerán en el listado de la flota con estado "Reservado".</p>
                </div>
            </div>
        </div>

        <div class="faq-empty" id="empty-compania">No se encontraron resultados en Compañía.</div>
    </div>

    <!-- ===== SECCIÓN ADMINISTRADOR ===== -->
    <div id="seccion-admin" class="faq-section">
        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo gestiono los usuarios?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ul>
                        <li><strong>Ver todos los usuarios:</strong> Ve a <strong>Usuarios</strong>.</li>
                        <li><strong>Banear/Desbanear:</strong> Usa el botón correspondiente en cada fila.</li>
                        <li><strong>Verificar empresas:</strong> Las empresas pendientes aparecen con estado "Pendiente". Haz clic en <strong>Verificar manual</strong> para activarlas.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo gestiono las reservas?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ul>
                        <li>Ve a <strong>Reservas</strong> para ver todas las reservas del sistema.</li>
                        <li>Puedes <strong>confirmar</strong>, <strong>cancelar</strong> o <strong>marcar como pagado</strong> cualquier reserva.</li>
                        <li>Usa los filtros para buscar por estado o vehículo.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo gestiono los tickets de soporte?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ul>
                        <li>Ve a <strong>Tickets</strong> para ver todos los tickets de soporte.</li>
                        <li>Puedes <strong>responder</strong>, <strong>cerrar</strong> o <strong>eliminar</strong> tickets.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Dónde veo los reportes y estadísticas?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>En el <strong>Dashboard</strong> verás tarjetas con estadísticas clave: usuarios, vehículos, reservas, ingresos y tickets. También gráficas de reservas por estado y usuarios por rol.</p>
                </div>
            </div>
        </div>

        <div class="faq-empty" id="empty-admin">No se encontraron resultados en Administrador.</div>
    </div>

    <!-- ===== SECCIÓN SOPORTE ===== -->
    <div id="seccion-soporte" class="faq-section">
        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo veo los tickets asignados?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Ve a <strong>Tickets</strong> en tu panel. Verás todos los tickets abiertos, en proceso y cerrados.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo respondo un ticket?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ol>
                        <li>Haz clic en <strong>Responder</strong> en el ticket.</li>
                        <li>Escribe tu respuesta en el campo de texto.</li>
                        <li>Haz clic en <strong>Enviar respuesta</strong>.</li>
                    </ol>
                    <p>El usuario recibirá la respuesta en su panel.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo cierro o elimino tickets?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ul>
                        <li><strong>Cerrar:</strong> Cuando el problema esté resuelto, haz clic en <strong>Cerrar</strong>.</li>
                        <li><strong>Eliminar:</strong> Si el ticket es spam o duplicado, puedes <strong>Eliminarlo</strong> permanentemente.</li>
                    </ul>
                    <div class="note">La eliminación es irreversible.</div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span class="q-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </span>
                <span class="faq-qtext">¿Cómo contactar al administrador?</span>
                <span class="faq-chevron">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Si necesitas ayuda con un ticket complejo, contacta al administrador del sistema.</p>
                </div>
            </div>
        </div>

        <div class="faq-empty" id="empty-soporte">No se encontraron resultados en Soporte.</div>
    </div>

    <!-- FOOTER -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-column">
                <h4>RentWheels</h4>
                <p>El marketplace de alquiler de autos para turistas extranjeros.</p>
            </div>
            <div class="footer-column">
                <h4>Enlaces</h4>
                <a href="index.php">Inicio</a>
                <a href="faq.php">Preguntas frecuentes</a>
                <a href="login.php">Iniciar sesión</a>
                <a href="register.php">Registrarse</a>
            </div>
            <div class="footer-column">
                <h4>Contacto</h4>
                <a href="mailto:info@rentwheels.com">info@rentwheels.com</a>
                <a href="#">+507 123-4567</a>
            </div>
            <div class="footer-column">
                <h4>Síguenos</h4>
                <div class="social-links">
                    <a href="#" title="Facebook"><svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M14 13.5h2.5l1-4H14v-2c0-1.03 0-2 2-2h1.5V2.14c-.33-.05-1.4-.14-2.55-.14C11.74 2 9.97 3.66 9.97 6.7v2.8H7v4h2.97V22h4.02v-8.5Z"/></svg></a>
                    <a href="#" title="Instagram"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37Z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg></a>
                    <a href="#" title="X (Twitter)"><svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?php echo date('Y'); ?> RentWheels. Todos los derechos reservados.
        </div>
    </footer>

    <!-- SCRIPT: TABS, ACORDEÓN Y BUSCADOR -->
    <script>
        // Cambiar pestañas
        function cambiarTab(tab) {
            document.querySelectorAll('.faq-tab').forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');

            document.querySelectorAll('.faq-section').forEach(function(sec) { sec.classList.remove('active'); });
            document.getElementById('seccion-' + tab.dataset.tab).classList.add('active');

            var input = document.getElementById('searchInput');
            if (input.value.trim()) filtrarPreguntas();
        }

        document.querySelectorAll('.faq-tab').forEach(function(tab) {
            tab.addEventListener('click', function() { cambiarTab(this); });
        });

        // Acordeón
        function abrirItem(item) {
            var answer = item.querySelector('.faq-answer');
            item.classList.add('open');
            answer.style.maxHeight = answer.scrollHeight + 'px';
        }

        function cerrarItem(item) {
            var answer = item.querySelector('.faq-answer');
            item.classList.remove('open');
            answer.style.maxHeight = '0px';
        }

        document.querySelectorAll('.faq-question').forEach(function(q) {
            q.addEventListener('click', function() {
                var item = this.closest('.faq-item');
                var section = item.closest('.faq-section');
                var wasOpen = item.classList.contains('open');
                section.querySelectorAll('.faq-item.open').forEach(function(other) {
                    if (other !== item) cerrarItem(other);
                });
                if (wasOpen) cerrarItem(item);
                else abrirItem(item);
            });
        });

        // Abrir los ítems abiertos por defecto tras el render inicial
        window.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.faq-item.open').forEach(function(item) {
                abrirItem(item);
            });
        });

        // Buscador (filtra ítems dentro de la sección activa)
        function filtrarPreguntas() {
            var input = document.getElementById('searchInput');
            var filter = input.value.toLowerCase().trim();
            var activeSection = document.querySelector('.faq-section.active');
            if (!activeSection) return;

            var items = activeSection.querySelectorAll('.faq-item');
            var visible = 0;
            items.forEach(function(item) {
                var text = item.textContent.toLowerCase();
                var match = text.includes(filter);
                item.style.display = match ? 'block' : 'none';
                if (match) {
                    item.classList.remove('rw-faq-in');
                    void item.offsetWidth;
                    item.classList.add('rw-faq-in');
                    visible++;
                } else {
                    item.classList.remove('rw-faq-in');
                }
            });

            var emptyEl = activeSection.querySelector('.faq-empty');
            if (emptyEl) emptyEl.style.display = visible === 0 ? 'block' : 'none';
        }
    </script>

    <!-- Menú de sesión (perfil) -->
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