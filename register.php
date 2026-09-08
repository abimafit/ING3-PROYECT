<?php require_once 'includes/auth.php';
require_once 'includes/ciudades_panama.php';
$ciudades = obtenerNombresCiudades();
if (estaLogueado()) header('Location: ' . redirigirSegunRol());
$toasts = [];
if (isset($_GET['error'])) {
    $toasts[] = ['t' => 'error', 'm' => $_GET['error']];
}
if (isset($_GET['registro']) && $_GET['registro'] == 'ok') {
    $toasts[] = ['t' => 'success', 'm' => 'Registro exitoso. Ahora puedes iniciar sesión.'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - RentWheels</title>
    <link rel="icon" type="image/png" href="img/rw-logo.png">
    <link rel="stylesheet" href="css/style-global.css">
    <link rel="stylesheet" href="css/style-public.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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
    </style>
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
                        <div class="placeholder" id="qrPlaceholder"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-5px;margin-right:6px;opacity:0.8;"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>No se ha seleccionado ninguna imagen</div>
                        <img id="qrPreview" src="#" alt="Vista previa del QR">
                    </div>
                </div>

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

            const nombreRegex = /^[A-Za-zÁÉÍÓÚáéíóúñÑüÜ\s\-\'\.]+$/;
            if (!nombreRegex.test(nombre)) {
                showAlert('El nombre solo puede contener letras, espacios, apóstrofes, guiones o puntos.', 'warning');
                document.getElementById('nombre').focus();
                return false;
            }
            if (nombre.length < 2 || nombre.length > 100) {
                showAlert('El nombre debe tener entre 2 y 100 caracteres.', 'warning');
                document.getElementById('nombre').focus();
                return false;
            }

            if (!email || !email.includes('@')) {
                showAlert('Ingresa un correo electrónico válido.', 'warning');
                document.getElementById('email').focus();
                return false;
            }

            if (pass.length < 4) {
                showAlert('La contraseña debe tener al menos 4 caracteres.', 'warning');
                document.getElementById('password').focus();
                return false;
            }

            if (pass !== confirm) {
                showAlert('Las contraseñas no coinciden.', 'warning');
                document.getElementById('confirm_password').focus();
                return false;
            }

            if (rol === 'compania' && ciudad === '') {
                showAlert('La ciudad es obligatoria para las compañías.', 'warning');
                document.getElementById('ciudad').focus();
                return false;
            }

            return true;
        }
    </script>

    <script src="js/modals.js"></script>
    <script>
        (function() {
            var toasts = <?php echo json_encode($toasts, JSON_UNESCAPED_UNICODE); ?>;
            toasts.forEach(function(t) { showAlert(t.m, t.t); });
        })();
    </script>
</body>

</html>