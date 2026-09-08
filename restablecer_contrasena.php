<?php
require_once 'includes/config.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: recuperar_contrasena.php?error=Token inválido');
    exit;
}

// Verificar token
$stmt = $pdo->prepare("SELECT id, email, reset_token_expiry FROM usuarios WHERE reset_token = ?");
$stmt->execute([$token]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: recuperar_contrasena.php?error=Token inválido o expirado');
    exit;
}

if (strtotime($usuario['reset_token_expiry']) < time()) {
    header('Location: recuperar_contrasena.php?error=El enlace ha expirado. Solicita uno nuevo.');
    exit;
}

// Si hay mensaje de error desde procesar_restablecer.php
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña - RentWheels</title>
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
            <h2>Restablecer contraseña</h2>
            <p class="subtitle">Ingresa tu nueva contraseña para el usuario <strong><?php echo htmlspecialchars($usuario['email']); ?></strong></p>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars(urldecode($error)); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-msg"><?php echo htmlspecialchars(urldecode($success)); ?></div>
                <div style="text-align:center; margin-top:1rem;">
                    <a href="login.php" class="btn-primary">Ir al inicio de sesión</a>
                </div>
            <?php else: ?>
                <form method="POST" action="procesar_restablecer.php">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <label for="password">Nueva contraseña</label>
                        <input type="password" name="password" id="password" placeholder="Mínimo 4 caracteres" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar contraseña</label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Repite la contraseña" required>
                    </div>
                    <button type="submit" class="btn-primary" style="width:100%;">Restablecer contraseña</button>
                </form>
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
            <div class="footer-bottom">
                &copy; <?php echo date('Y'); ?> RentWheels. Todos los derechos reservados.
            </div>
        </div>
    </footer>

</body>
</html>