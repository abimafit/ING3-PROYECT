<?php
require_once 'includes/config.php';
$toasts = [];
if (isset($_GET['error'])) {
    $toasts[] = ['t' => 'error', 'm' => $_GET['error']];
}
if (isset($_GET['success'])) {
    $toasts[] = ['t' => 'success', 'm' => $_GET['success']];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - RentWheels</title>
    <link rel="icon" type="image/png" href="img/rw-logo.png">
    <link rel="stylesheet" href="css/style-global.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">

    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <span class="logo-icon"><img src="img/rw-logo.png" alt="RentWheels"></span>
                <span class="logo-text">RentWheels</span>
            </a>
            <nav class="main-nav">
                <a href="index.php">Inicio</a>
                <a href="login.php">Iniciar sesión</a>
            </nav>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-card">
            <h2>Recuperar contraseña</h2>
            <p class="subtitle">Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>

            <form method="POST" action="procesar_recuperacion.php">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" name="email" id="email" placeholder="ejemplo@correo.com" required>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;">Enviar enlace</button>
            </form>

            <?php if (isset($_GET['success']) && isset($_GET['token'])): ?>
                <div style="margin-top:1.2rem; background:#f6f6f6; border:1px solid var(--rw-gray-200); border-radius:12px; padding:0.9rem 1rem; word-break:break-all;">
                    <p style="font-size:0.82rem; color:var(--rw-gray-500); margin-bottom:0.4rem;">Enlace para esta prueba (sin correo real):</p>
                    <a href="restablecer_contrasena.php?token=<?php echo htmlspecialchars($_GET['token']); ?>" style="color:var(--rw-red); font-weight:600;">Clic aquí para restablecer tu contraseña</a>
                </div>
            <?php endif; ?>

            <div class="links" style="margin-top:1.5rem; text-align:center;">
                <a href="login.php">← Volver al inicio de sesión</a>
            </div>
        </div>
    </main>

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

    <script src="js/modals.js"></script>
    <script>
        (function() {
            var toasts = <?php echo json_encode($toasts, JSON_UNESCAPED_UNICODE); ?>;
            toasts.forEach(function(t) { showAlert(t.m, t.t); });
        })();
    </script>
</body>
</html>