<?php
require_once 'includes/auth.php';
if (estaLogueado()) {
    header('Location: ' . redirigirSegunRol());
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Alquila tu auto</title>
    <link rel="stylesheet" href="css/style-global.css">
    <link rel="stylesheet" href="css/style-public.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
</head>

<body>

    <!-- HEADER PROFESIONAL -->
    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <span class="logo-text">RentWheels</span>
            </a>
            <nav class="main-nav">
                <a href="index.php" class="active">Inicio</a>
                <a href="faq.php">Ayuda</a>
            </nav>
            <div class="header-actions">
                <a href="login.php" class="btn-outline">Iniciar sesión</a>
                <a href="register.php" class="btn-primary">Registrarse</a>
            </div>
        </div>
    </header>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-text">
                <h1>Alquila el auto perfecto para tu viaje</h1>
                <p>Compara precios, reserva en línea y recoge tu vehículo en el destino. Miles de autos disponibles en todo el país.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn-hero">Empieza ahora</a>
                    <a href="faq.php" class="btn-hero-outline">Cómo funciona</a>
                </div>
            </div>
            <div class="hero-image">
                <!-- Imagen de ejemplo (puedes cambiarla) -->
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTSoVeV_fcIuFiHxtiyYQQYGomK5fk_ykmLNHGuyJirMSm_LqidbBGpR-Q&s=10" alt="Autos en carretera">
            </div>
        </div>
    </section>

    <!-- MISIÓN Y VISIÓN -->
    <section class="about">
        <div class="about-container">
            <div class="about-card">
                <h3>Misión</h3>
                <p>Conectar a turistas con las mejores opciones de alquiler de autos, garantizando confianza, transparencia y facilidad en cada reserva.</p>
            </div>
            <div class="about-card">
                <h3>Visión</h3>
                <p>Ser el marketplace de alquiler de autos líder en Latinoamérica, reconocido por su tecnología innovadora y experiencia de usuario excepcional.</p>
            </div>
            <div class="about-card">
                <h3>Valores</h3>
                <p>Confianza, seguridad, innovación y servicio al cliente. Trabajamos para que cada viaje sea inolvidable.</p>
            </div>
        </div>
    </section>

    <!-- CARACTERÍSTICAS -->
    <section class="features">
        <h2>¿Por qué elegir RentWheels?</h2>
        <div class="features-grid">
            <div class="feature-item">
                <h4>Compara fácilmente</h4>
                <p>Encuentra el mejor precio entre múltiples compañías.</p>
            </div>
            <div class="feature-item">
                <h4>Pagos seguros</h4>
                <p>Integración con PayPal y Stripe para transacciones protegidas.</p>
            </div>
            <div class="feature-item">
                <h4>Responsive</h4>
                <p>Diseño adaptado a móviles, tablets y computadoras.</p>
            </div>
            <div class="feature-item">
                <h4>Confianza</h4>
                <p>Compañías verificadas y opiniones reales de usuarios.</p>
            </div>
        </div>
    </section>

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