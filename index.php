<?php
require_once 'includes/auth.php';
if (estaLogueado()) {
    header('Location: ' . redirigirSegunRol());
    exit;
}
$stmt = $pdo->query("SELECT marca, modelo, precio_por_dia, imagen_url FROM vehiculos WHERE disponible = 1 ORDER BY id DESC LIMIT 5");
$autos = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$autos) $autos = [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentWheels - Alquila tu auto</title>
    <link rel="icon" type="image/png" href="img/rw-logo.png">
    <link rel="stylesheet" href="css/style-global.css">
    <link rel="stylesheet" href="css/style-public.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <!-- HEADER PROFESIONAL -->
    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <span class="logo-icon"><img src="img/rw-logo.png" alt="RentWheels"></span>
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
                <h1>Alquila el auto <span>perfecto</span> para tu viaje</h1>
                <p>Compara precios, reserva en línea y recoge tu vehículo en el destino. Miles de autos disponibles en todo el país.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn-hero">Empieza ahora</a>
                    <a href="faq.php" class="btn-hero-outline">Cómo funciona</a>
                </div>
            </div>
            <div class="hero-gallery" id="heroGallery">
                <div class="gallery-stage" id="galleryStage">
                    <?php if (count($autos) === 0): ?>
                        <div class="gallery-item active" data-idx="0">
                            <img src="https://via.placeholder.com/600x370?text=RentWheels" alt="Próximamente autos disponibles">
                            <span class="gallery-badge">No hay vehículos por ahora</span>
                        </div>
                    <?php else: ?>
                        <?php foreach ($autos as $i => $auto): ?>
                            <div class="gallery-item <?php echo $i === 0 ? 'active' : 'pos' . min($i, 3); ?>" data-idx="<?php echo $i; ?>">
                                <img src="<?php echo htmlspecialchars($auto['imagen_url']); ?>" alt="<?php echo htmlspecialchars($auto['marca'] . ' ' . $auto['modelo']); ?>">
                                <span class="gallery-badge"><?php echo htmlspecialchars($auto['marca'] . ' ' . $auto['modelo']); ?> $<?php echo (int) $auto['precio_por_dia']; ?>/día</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="gallery-tabs" id="galleryTabs">
                    <?php foreach ($autos as $i => $auto): ?>
                        <button type="button" class="gallery-tab <?php echo $i === 0 ? 'active' : ''; ?>" data-idx="<?php echo $i; ?>" aria-label="<?php echo htmlspecialchars($auto['marca'] . ' ' . $auto['modelo']); ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- MISIÓN Y VISIÓN -->
    <section class="about">
        <div class="about-container">
            <div class="about-card">
                <span class="about-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                </span>
                <h3>Misión</h3>
                <p>Conectar a turistas con las mejores opciones de alquiler de autos, garantizando confianza, transparencia y facilidad en cada reserva.</p>
            </div>
            <div class="about-card">
                <span class="about-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                </span>
                <h3>Visión</h3>
                <p>Ser el marketplace de alquiler de autos líder en Latinoamérica, reconocido por su tecnología innovadora y experiencia de usuario excepcional.</p>
            </div>
            <div class="about-card">
                <span class="about-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z"/></svg>
                </span>
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
                <span class="icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><path d="M8 11h6"/></svg>
                </span>
                <h4>Compara fácilmente</h4>
                <p>Encuentra el mejor precio entre múltiples compañías.</p>
            </div>
            <div class="feature-item">
                <span class="icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/><path d="M6 15h4"/></svg>
                </span>
                <h4>Pagos seguros</h4>
                <p>Integración con PayPal y Stripe para transacciones protegidas.</p>
            </div>
            <div class="feature-item">
                <span class="icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2"/><path d="M8 22h8"/><path d="M12 6v4"/></svg>
                </span>
                <h4>Responsive</h4>
                <p>Diseño adaptado a móviles, tablets y computadoras.</p>
            </div>
            <div class="feature-item">
                <span class="icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a8 8 0 0 1-5.66-13.66 8 8 0 1 1 11.32 11.32A8 8 0 0 1 12 22Z"/><path d="M9 12h6"/><path d="M12 9v6"/></svg>
                </span>
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
    <!-- Sistema compartido de toasts y modales -->
    <script>
        (function() {
            const items = Array.from(document.querySelectorAll('#galleryStage .gallery-item'));
            const tabs = Array.from(document.querySelectorAll('#galleryTabs .gallery-tab'));
            const stage = document.getElementById('galleryStage');
            const count = items.length;
            let activeIdx = 0;
            let timer = null;

            function apply(idx) {
                activeIdx = ((idx % count) + count) % count;
                items.forEach(function(item, i) {
                    var pos = (i - activeIdx + count) % count;
                    item.classList.remove('active', 'pos1', 'pos2', 'pos3');
                    if (pos === 0) item.classList.add('active');
                    else if (pos === 1) item.classList.add('pos1');
                    else if (pos === 2) item.classList.add('pos2');
                    else item.classList.add('pos3');
                });
                tabs.forEach(function(t, i) {
                    t.classList.toggle('active', i === activeIdx);
                });
            }

            function next() { apply(activeIdx + 1); }

            function start() {
                stop();
                timer = setInterval(next, 4500);
            }

            function stop() {
                if (timer) { clearInterval(timer); timer = null; }
            }

            tabs.forEach(function(t) {
                t.addEventListener('click', function() {
                    apply(parseInt(t.dataset.idx, 10));
                    start();
                });
            });
            items.forEach(function(item) {
                item.addEventListener('click', function() {
                    apply(parseInt(item.dataset.idx, 10));
                    start();
                });
            });
            if (stage) {
                stage.addEventListener('mouseenter', stop);
                stage.addEventListener('mouseleave', start);
            }

            apply(0);
            start();
        })();
    </script>
    <script src="js/modals.js"></script>
</body>

</html>