<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - RentWheels</title>
    <link rel="stylesheet" href="css/style-global.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">

    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <span class="logo-text">RentWheels</span>
            </a>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-card">
            <h2>Recuperar contraseña</h2>
            <p class="subtitle">Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-msg">
                    <?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['success'])): ?>
                <div class="success-msg">
                    <?php echo htmlspecialchars(urldecode($_GET['success'])); ?>
                    <?php if (isset($_GET['token'])): ?>
                        <br><br>
                        <div style="background:#f3f4f6; padding:0.8rem; border-radius:8px; word-break:break-all; font-family:monospace;">
                            Token: <strong><?php echo htmlspecialchars($_GET['token']); ?></strong>
                        </div>
                        <div style="margin-top:0.5rem;">
                            <a href="restablecer_contrasena.php?token=<?php echo htmlspecialchars($_GET['token']); ?>" style="color:#2563eb; font-weight:600;">Haz clic aquí para restablecer tu contraseña</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="procesar_recuperacion.php">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" name="email" id="email" placeholder="ejemplo@correo.com" required>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;">Enviar enlace</button>
            </form>

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
            <div class="footer-bottom">
                &copy; <?php echo date('Y'); ?> RentWheels. Todos los derechos reservados.
            </div>
        </div>
    </footer>

</body>
</html>