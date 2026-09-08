<?php
require_once 'includes/auth.php';
// No redirige, es pública
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Ayuda - RentWheels</title>
    <link rel="stylesheet" href="css/style-global.css">
    <link rel="stylesheet" href="css/style-public.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos adicionales para el centro de ayuda */
        .faq-hero {
            background: linear-gradient(135deg, #eff6ff, #ffffff);
            padding: 3rem 2rem;
            text-align: center;
        }

        .faq-hero h1 {
            font-size: 2.5rem;
            color: #1f2937;
        }

        .faq-hero p {
            color: #6b7280;
            font-size: 1.1rem;
        }

        .faq-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            margin: 2rem 0 1.5rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.5rem;
        }

        .faq-tab {
            background: none;
            border: none;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            border-radius: 30px;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .faq-tab:hover {
            background: #f3f4f6;
        }

        .faq-tab.active {
            background: #2563eb;
            color: white;
        }

        .faq-section {
            display: none;
            max-width: 900px;
            margin: 0 auto;
            padding: 1rem 0;
        }

        .faq-section.active {
            display: block;
        }

        .faq-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0f0f0;
        }

        .faq-card h3 {
            font-size: 1.3rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .faq-card h4 {
            font-size: 1.05rem;
            color: #2563eb;
            margin: 1rem 0 0.3rem;
        }

        .faq-card p,
        .faq-card li {
            color: #4b5563;
            line-height: 1.6;
        }

        .faq-card ul,
        .faq-card ol {
            padding-left: 1.5rem;
            margin: 0.5rem 0;
        }

        .faq-card .note {
            background: #fef3c7;
            padding: 0.8rem 1rem;
            border-radius: 10px;
            border-left: 4px solid #f59e0b;
            margin: 0.8rem 0;
        }

        .faq-card .tip {
            background: #dbeafe;
            padding: 0.8rem 1rem;
            border-radius: 10px;
            border-left: 4px solid #3b82f6;
            margin: 0.8rem 0;
        }

        .faq-card .success-box {
            background: #d1fae5;
            padding: 0.8rem 1rem;
            border-radius: 10px;
            border-left: 4px solid #10b981;
            margin: 0.8rem 0;
        }

        .faq-search {
            max-width: 500px;
            margin: 0 auto 1.5rem;
        }

        .faq-search input {
            width: 100%;
            padding: 0.7rem 1rem;
            border-radius: 40px;
            border: 1px solid #d1d5db;
            font-size: 1rem;
            outline: none;
            transition: 0.2s;
        }

        .faq-search input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        .badge-role {
            display: inline-block;
            background: #e5e7eb;
            color: #374151;
            padding: 0.15rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 0.5rem;
        }

        .badge-role.turista {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-role.compania {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-role.admin {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-role.soporte {
            background: #d1fae5;
            color: #065f46;
        }

        @media (max-width: 768px) {
            .faq-tab {
                padding: 0.4rem 1rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <span class="logo-text">RentWheels</span>
            </a>
            <nav class="main-nav">
                <a href="index.php">Inicio</a>
                <a href="faq.php" class="active">Ayuda</a>
            </nav>
            <div class="header-actions">
                <?php if (estaLogueado()): ?>
                    <a href="dashboard.php" class="btn-primary">Panel</a>
                <?php else: ?>
                    <a href="login.php" class="btn-outline">Iniciar sesión</a>
                    <a href="register.php" class="btn-primary">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- HERO -->
    <section class="faq-hero">
        <h1>Centro de Ayuda</h1>
        <p>Todo lo que necesitas saber para usar RentWheels como un experto.</p>
    </section>

    <!-- Buscador -->
    <div class="faq-search">
        <input type="text" id="searchInput" placeholder="🔍 Buscar en la ayuda..." onkeyup="filtrarPreguntas()">
    </div>

    <!-- Tabs -->
    <div class="faq-tabs" id="faqTabs">
        <button class="faq-tab active" data-tab="general"> General</button>
        <button class="faq-tab" data-tab="turista">Turista</button>
        <button class="faq-tab" data-tab="compania"> Compañía</button>
        <button class="faq-tab" data-tab="admin"> Administrador</button>
        <button class="faq-tab" data-tab="soporte"> Soporte</button>
    </div>

    <!-- CONTENIDO DE SECCIONES -->

    <!-- ===== SECCIÓN GENERAL ===== -->
    <div id="seccion-general" class="faq-section active">
        <div class="faq-card">
            <h3> Bienvenido a RentWheels</h3>
            <p>RentWheels es un marketplace de alquiler de vehículos que conecta a turistas con compañías de renta de autos en Panamá. Aquí encontrarás guías paso a paso para cada rol.</p>
            <div class="tip">
                 <strong>Consejo:</strong> Usa las pestañas de arriba para navegar según tu perfil.
            </div>
        </div>

        <div class="faq-card">
            <h3> Preguntas frecuentes (Generales)</h3>
            <h4>¿Cómo me registro?</h4>
            <p>Haz clic en "Registrarse" en la esquina superior derecha. Completa tu nombre, correo, contraseña y selecciona tu rol (turista o compañía). Si eres compañía, deberás ingresar datos bancarios y subir un código QR.</p>

            <h4>¿Olvidé mi contraseña?</h4>
            <p>En la página de inicio de sesión, haz clic en "¿Olvidaste tu contraseña?" y sigue las instrucciones. Recibirás un enlace para restablecerla (próximamente). Por ahora, contacta al administrador.</p>

            <h4>¿Cómo contacto con soporte?</h4>
            <p>Dentro de tu panel, ve a la sección "Soporte" y crea un ticket. El equipo de soporte te responderá a la brevedad.</p>

            <h4>¿Qué navegador debo usar?</h4>
            <p>Recomendamos Chrome, Firefox, Edge o Safari en su versión más reciente.</p>
        </div>
    </div>

    <!-- ===== SECCIÓN TURISTA ===== -->
    <div id="seccion-turista" class="faq-section">
        <div class="faq-card">
            <h3>Guía para turistas</h3>
            <p>Aquí encontrarás todo lo que necesitas para buscar, reservar y pagar un vehículo.</p>
        </div>

        <div class="faq-card">
            <h4>1. Registrarse como turista</h4>
            <ol>
                <li>Ve a <strong>Registrarse</strong>.</li>
                <li>Completa tus datos (nombre, correo, contraseña).</li>
                <li>Selecciona <strong>Turista extranjero</strong>.</li>
                <li>Haz clic en <strong>Registrarse</strong>.</li>
                <li>Revisa tu correo (simulado) y confirma tu cuenta.</li>
            </ol>
            <div class="tip">Ya puedes iniciar sesión.</div>
        </div>

        <div class="faq-card">
            <h4>2. Buscar vehículos</h4>
            <ol>
                <li>Inicia sesión y ve al panel de <strong>Turista</strong>.</li>
                <li>Haz clic en <strong>Buscar vehículos</strong>.</li>
                <li>Selecciona la <strong>ciudad</strong>, <strong>fecha de inicio</strong> y <strong>fecha de fin</strong>.</li>
                <li>Haz clic en <strong>Buscar</strong>.</li>
            </ol>
            <p>Verás una lista de vehículos disponibles con precio y compañía.</p>
        </div>

        <div class="faq-card">
            <h4>3. Reservar y pagar</h4>
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

        <div class="faq-card">
            <h4>4. Gestionar reservas</h4>
            <ul>
                <li><strong>Cancelar:</strong> Si la reserva está pendiente, puedes cancelarla desde <strong>Mis reservas</strong>.</li>
                <li><strong>Eliminar:</strong> Si la reserva está cancelada, puedes eliminarla permanentemente.</li>
                <li><strong>Historial:</strong> En la pestaña <strong>Historial</strong> verás todas tus reservas pagadas y canceladas.</li>
            </ul>
        </div>

        <div class="faq-card">
            <h4>5. Soporte</h4>
            <p>Si tienes problemas, ve a <strong>Soporte</strong> y crea un ticket. Describe tu problema y el equipo te responderá.</p>
        </div>
    </div>

    <!-- ===== SECCIÓN COMPAÑÍA ===== -->
    <div id="seccion-compania" class="faq-section">
        <div class="faq-card">
            <h3>Guía para compañías de renta</h3>
            <p>Gestiona tu flota y reservas de manera eficiente.</p>
        </div>

        <div class="faq-card">
            <h4>1. Registrarse como compañía</h4>
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

        <div class="faq-card">
            <h4>2. Verificar tu cuenta</h4>
            <ol>
                <li>Inicia sesión con tu correo y contraseña.</li>
                <li>Ve a tu panel de <strong>Compañía</strong>.</li>
                <li>En la sección de verificación, ingresa el código de 6 dígitos.</li>
                <li>Haz clic en <strong>Verificar</strong>.</li>
            </ol>
            <div class="success-box">¡Cuenta activada! Ahora puedes publicar vehículos.</div>
        </div>

        <div class="faq-card">
            <h4>3. Gestionar flota</h4>
            <ol>
                <li>En el panel, ve a <strong>Gestionar flota</strong>.</li>
                <li>Para agregar un vehículo: completa los campos (marca, modelo, año, precio, imagen).</li>
                <li>Haz clic en <strong>Agregar vehículo</strong>.</li>
                <li>Para eliminar: haz clic en el botón <strong>Eliminar</strong> del vehículo.</li>
            </ol>
            <div class="tip">Solo puedes eliminar vehículos sin reservas activas.</div>
        </div>

        <div class="faq-card">
            <h4>4. Gestionar reservas recibidas</h4>
            <ol>
                <li>Ve a <strong>Reservas recibidas</strong>.</li>
                <li>Verás todas las solicitudes de reserva.</li>
                <li>Haz clic en <strong>Confirmar</strong> o <strong>Cancelar</strong> según corresponda.</li>
            </ol>
            <p>Las reservas confirmadas aparecerán en el listado de la flota con estado "Reservado".</p>
        </div>
    </div>

    <!-- ===== SECCIÓN ADMINISTRADOR ===== -->
    <div id="seccion-admin" class="faq-section">
        <div class="faq-card">
            <h3>Guía para administradores</h3>
            <p>Control total del sistema: usuarios, reservas, tickets y reportes.</p>
        </div>

        <div class="faq-card">
            <h4>1. Gestionar usuarios</h4>
            <ul>
                <li><strong>Ver todos los usuarios:</strong> Ve a <strong>Usuarios</strong>.</li>
                <li><strong>Banear/Desbanear:</strong> Usa el botón correspondiente en cada fila.</li>
                <li><strong>Verificar empresas:</strong> Las empresas pendientes aparecen con estado "Pendiente". Haz clic en <strong>Verificar manual</strong> para activarlas.</li>
            </ul>
        </div>

        <div class="faq-card">
            <h4>2. Gestionar reservas</h4>
            <ul>
                <li>Ve a <strong>Reservas</strong> para ver todas las reservas del sistema.</li>
                <li>Puedes <strong>confirmar</strong>, <strong>cancelar</strong> o <strong>marcar como pagado</strong> cualquier reserva.</li>
                <li>Usa los filtros para buscar por estado o vehículo.</li>
            </ul>
        </div>

        <div class="faq-card">
            <h4>3. Gestionar tickets</h4>
            <ul>
                <li>Ve a <strong>Tickets</strong> para ver todos los tickets de soporte.</li>
                <li>Puedes <strong>responder</strong>, <strong>cerrar</strong> o <strong>eliminar</strong> tickets.</li>
            </ul>
        </div>

        <div class="faq-card">
            <h4>4. Reportes y estadísticas</h4>
            <p>En el <strong>Dashboard</strong> verás tarjetas con estadísticas clave: usuarios, vehículos, reservas, ingresos y tickets. También gráficas de reservas por estado y usuarios por rol.</p>
        </div>
    </div>

    <!-- ===== SECCIÓN SOPORTE ===== -->
    <div id="seccion-soporte" class="faq-section">
        <div class="faq-card">
            <h3>Guía para soporte técnico</h3>
            <p>Gestiona los tickets de los usuarios de manera eficiente.</p>
        </div>

        <div class="faq-card">
            <h4>1. Ver tickets</h4>
            <p>Ve a <strong>Tickets</strong> en tu panel. Verás todos los tickets abiertos, en proceso y cerrados.</p>
        </div>

        <div class="faq-card">
            <h4>2. Responder un ticket</h4>
            <ol>
                <li>Haz clic en <strong>Responder</strong> en el ticket.</li>
                <li>Escribe tu respuesta en el campo de texto.</li>
                <li>Haz clic en <strong>Enviar respuesta</strong>.</li>
            </ol>
            <p>El usuario recibirá la respuesta en su panel.</p>
        </div>

        <div class="faq-card">
            <h4>3. Cerrar y eliminar tickets</h4>
            <ul>
                <li><strong>Cerrar:</strong> Cuando el problema esté resuelto, haz clic en <strong>Cerrar</strong>.</li>
                <li><strong>Eliminar:</strong> Si el ticket es spam o duplicado, puedes <strong>Eliminarlo</strong> permanentemente.</li>
            </ul>
            <div class="note">La eliminación es irreversible.</div>
        </div>

        <div class="faq-card">
            <h4>4. Contacto con el administrador</h4>
            <p>Si necesitas ayuda con un ticket complejo, contacta al administrador del sistema.</p>
        </div>
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

    <!-- SCRIPT: TABS Y BUSCADOR -->
    <script>
        // Cambiar pestañas
        document.querySelectorAll('.faq-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Quitar active de todos los tabs
                document.querySelectorAll('.faq-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Ocultar todas las secciones
                document.querySelectorAll('.faq-section').forEach(sec => sec.classList.remove('active'));

                // Mostrar la sección correspondiente
                const tabName = this.dataset.tab;
                const sectionId = 'seccion-' + tabName;
                document.getElementById(sectionId).classList.add('active');
            });
        });

        // Buscador simple (filtra tarjetas dentro de la sección activa)
        function filtrarPreguntas() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const activeSection = document.querySelector('.faq-section.active');
            if (!activeSection) return;

            const cards = activeSection.querySelectorAll('.faq-card');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(filter) ? 'block' : 'none';
            });
        }
    </script>

</body>

</html>