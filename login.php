<?php require_once 'includes/auth.php';
if (estaLogueado()) header('Location: ' . redirigirSegunRol()); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - RentWheels prueba</title>
    <link rel="stylesheet" href="css/style-global.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos específicos para login */
        .auth-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
        }
        .auth-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .auth-card {
            background: white;
            padding: 2.5rem;
            border-radius: 28px;
            box-shadow: 0 20px 40px -12px rgba(0,0,0,0.15);
            max-width: 420px;
            width: 100%;
        }
        .auth-card h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .auth-card .subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 1.5rem;
        }
        .auth-card .form-group {
            margin-bottom: 1.2rem;
        }
        .auth-card label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.3rem;
            color: #374151;
        }
        .auth-card input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s;
        }
        .auth-card input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.2);
            outline: none;
        }
        .auth-card .btn-submit {
            width: 100%;
            padding: 0.8rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 0.5rem;
        }
        .auth-card .btn-submit:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }
        .auth-card .links {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #6b7280;
        }
        .auth-card .links a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }
        .auth-card .links a:hover {
            text-decoration: underline;
        }
        .error-msg {
            background: #fee2e2;
            color: #b91c1c;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border-left: 4px solid #ef4444;
        }
        .success-msg {
            background: #d1fae5;
            color: #065f46;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border-left: 4px solid #10b981;
        }
        .warning-msg {
            background: #fef3c7;
            color: #92400e;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border-left: 4px solid #f59e0b;
        }
    </style>
</head>

<body class="auth-page">

    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <span class="logo-icon">🚗</span>
                <span class="logo-text">RentWheels</span>
            </a>
            <nav class="main-nav">
                <a href="index.php">Inicio</a>
                <a href="faq.php">Ayuda</a>
            </nav>
            <div class="header-actions">
                <a href="register.php" class="btn-primary">Registrarse</a>
            </div>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-card">
            <h2>Bienvenido de nuevo</h2>
            <p class="subtitle">Ingresa tus credenciales para continuar</p>

            <!--  Mensaje de error de login -->
            <?php if (isset($_GET['error'])): ?>
                <div class="error-msg"> Credenciales inválidas. Verifica tu email y contraseña.</div>
            <?php endif; ?>

            <!--  Mensaje de éxito al recuperar contraseña -->
            <?php if (isset($_GET['recuperado']) && $_GET['recuperado'] == 'ok'): ?>
                <div class="success-msg"> Contraseña actualizada correctamente. Ahora puedes iniciar sesión.</div>
            <?php endif; ?>

            <!--  Mensaje de código de verificación (para empresas) -->
            <?php if (isset($_GET['codigo'])): ?>
                <div class="warning-msg">
                     <strong>Código de verificación para tu empresa:</strong>
                    <span style="font-size:1.4rem; font-weight:700; color:#2563eb; letter-spacing:2px;"><?php echo htmlspecialchars($_GET['codigo']); ?></span>
                    <br>Guárdalo, lo necesitarás para activar tu cuenta.
                </div>
            <?php endif; ?>

            <!--  Mensaje de registro exitoso -->
            <?php if (isset($_GET['registro']) && $_GET['registro'] == 'ok'): ?>
                <div class="success-msg"> Registro exitoso. Ahora puedes iniciar sesión.</div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="procesar_login.php">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" name="email" id="email" placeholder="ejemplo@correo.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-submit">Iniciar sesión</button>
            </form>

            <div class="links">
                <a href="recuperar_contrasena.php">¿Olvidaste tu contraseña?</a><br>
                ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
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
                    <a href="#" title="Facebook">📘</a>
                    <a href="#" title="Instagram">📸</a>
                    <a href="#" title="Twitter">🐦</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?php echo date('Y'); ?> RentWheels. Todos los derechos reservados.
        </div>
    </footer>

    <script src="js/validaciones.js"></script>
</body>

</html>