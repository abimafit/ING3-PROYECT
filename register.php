<?php require_once 'includes/auth.php';
require_once 'includes/ciudades_panama.php';
$ciudades = obtenerNombresCiudades();
if (estaLogueado()) header('Location: ' . redirigirSegunRol()); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - RentWheels</title>
    <link rel="stylesheet" href="css/style-global.css">
    <link rel="stylesheet" href="css/style-public.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
            max-width: 480px;
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

        .auth-card input,
        .auth-card select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s;
            background: white;
        }

        .auth-card input:focus,
        .auth-card select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
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
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
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

        .input-hint {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.2rem;
        }

        #ciudad-group {
            display: none;
        }

        #ciudad-group.show {
            display: block;
        }

        #datos-bancarios {
            display: none;
        }

        #datos-bancarios.show {
            display: block;
        }

        /* ✅ Estilos para previsualización del QR */
        .qr-preview-container {
            margin-top: 0.5rem;
            text-align: center;
        }

        .qr-preview-container img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            padding: 0.5rem;
            background: white;
            display: none;
        }

        .qr-preview-container img.show {
            display: inline-block;
        }

        .qr-preview-container .placeholder {
            color: #6b7280;
            font-size: 0.85rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 12px;
            border: 2px dashed #d1d5db;
        }
    </style>
</head>

<body class="auth-page">

    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <span class="logo-text">RentWheels</span>
            </a>
            <nav class="main-nav">
                <a href="index.php">Inicio</a>
                <a href="faq.php">Ayuda</a>
            </nav>
            <div class="header-actions">
                <a href="login.php" class="btn-outline">Iniciar sesión</a>
            </div>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-card">
            <h2>Crea tu cuenta</h2>
            <p class="subtitle">Únete a RentWheels y comienza a reservar</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-msg"> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['registro']) && $_GET['registro'] == 'ok'): ?>
                <div class="success-msg"> Registro exitoso. Ya puedes <a href="login.php">iniciar sesión</a>.</div>
            <?php endif; ?>

            <form id="registerForm" method="POST" action="procesar_registro.php" enctype="multipart/form-data" onsubmit="return validarRegistro()">
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" name="nombre" id="nombre" placeholder="Ej: María José Pérez" required>
                    <div class="input-hint">Solo letras, espacios, apóstrofes, guiones y puntos.</div>
                </div>

                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" name="email" id="email" placeholder="ejemplo@correo.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" name="password" id="password" placeholder="Mínimo 4 caracteres" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Repite la contraseña" required>
                </div>

                <div class="form-group">
                    <label for="rol">Tipo de cuenta</label>
                    <select name="rol" id="rol" required>
                        <option value="turista">Turista extranjero</option>
                        <option value="compania">Compañía de renta</option>
                    </select>
                </div>

                <!--  SOLO UN CAMPO DE CIUDAD (SELECT) -->
                <div class="form-group" id="ciudad-group">
                    <label for="ciudad">Ciudad (solo para compañías)</label>
                    <select name="ciudad" id="ciudad" class="form-control">
                        <option value="">Selecciona una ciudad</option>
                        <?php foreach ($ciudades as $ciudad): ?>
                            <option value="<?php echo htmlspecialchars($ciudad); ?>"><?php echo htmlspecialchars($ciudad); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="datos-bancarios" style="display:none;">
                    <label for="cuenta_bancaria">Cuenta bancaria</label>
                    <input type="text" name="cuenta_bancaria" id="cuenta_bancaria" placeholder="Número de cuenta">

                    <label for="telefono_contacto">Teléfono de contacto</label>
                    <input type="text" name="telefono_contacto" id="telefono_contacto" placeholder="Teléfono">

                    <label for="nombre_banco">Nombre del banco</label>
                    <input type="text" name="nombre_banco" id="nombre_banco" placeholder="Ej: Banco General">

                    <label for="qr_image">Código QR (imagen)</label>
                    <input type="file" name="qr_image" id="qr_image" accept="image/*">
                    <div class="input-hint">Sube una imagen PNG, JPG o GIF (máx. 2MB)</div>

                    <div class="qr-preview-container">
                        <div class="placeholder" id="qrPlaceholder">📷 No se ha seleccionado ninguna imagen</div>
                        <img id="qrPreview" src="#" alt="Vista previa del QR">
                    </div>
                </div>

                <div id="formError" style="display:none; color:#b91c1c; background:#fee2e2; padding:0.5rem; border-radius:8px; margin-bottom:1rem;"></div>
                <button type="submit" class="btn-submit">Registrarse</button>
            </form>

            <div class="links">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
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

    <script>
        const rolSelect = document.getElementById('rol');
        const ciudadGroup = document.getElementById('ciudad-group');
        const ciudadSelect = document.getElementById('ciudad');
        const bancariosGroup = document.getElementById('datos-bancarios');

        function toggleCamposCompania() {
            if (rolSelect.value === 'compania') {
                ciudadGroup.classList.add('show');
                ciudadSelect.setAttribute('required', 'required');
                bancariosGroup.style.display = 'block';
            } else {
                ciudadGroup.classList.remove('show');
                ciudadSelect.removeAttribute('required');
                bancariosGroup.style.display = 'none';
            }
        }

        rolSelect.addEventListener('change', toggleCamposCompania);
        toggleCamposCompania();

        // Previsualización del QR
        const qrInput = document.getElementById('qr_image');
        const qrPreview = document.getElementById('qrPreview');
        const qrPlaceholder = document.getElementById('qrPlaceholder');

        qrInput.addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    qrPreview.src = event.target.result;
                    qrPreview.classList.add('show');
                    qrPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                qrPreview.classList.remove('show');
                qrPreview.src = '#';
                qrPlaceholder.style.display = 'block';
            }
        });

        function validarRegistro() {
            const nombre = document.getElementById('nombre').value.trim();
            const email = document.getElementById('email').value.trim();
            const pass = document.getElementById('password').value.trim();
            const confirm = document.getElementById('confirm_password').value.trim();
            const rol = document.getElementById('rol').value;
            const ciudad = document.getElementById('ciudad').value.trim();

            document.getElementById('formError').style.display = 'none';

            const nombreRegex = /^[A-Za-zÁÉÍÓÚáéíóúñÑüÜ\s\-\'\.]+$/;
            if (!nombreRegex.test(nombre)) {
                alert(' El nombre solo puede contener letras, espacios, apóstrofes, guiones o puntos.');
                document.getElementById('nombre').focus();
                return false;
            }
            if (nombre.length < 2 || nombre.length > 100) {
                alert(' El nombre debe tener entre 2 y 100 caracteres.');
                document.getElementById('nombre').focus();
                return false;
            }

            if (!email || !email.includes('@')) {
                alert(' Ingresa un correo electrónico válido.');
                document.getElementById('email').focus();
                return false;
            }

            if (pass.length < 4) {
                alert(' La contraseña debe tener al menos 4 caracteres.');
                document.getElementById('password').focus();
                return false;
            }

            if (pass !== confirm) {
                alert(' Las contraseñas no coinciden.');
                document.getElementById('confirm_password').focus();
                return false;
            }

            if (rol === 'compania' && ciudad === '') {
                alert(' La ciudad es obligatoria para las compañías.');
                document.getElementById('ciudad').focus();
                return false;
            }

            return true;
        }
    </script>

</body>

</html>