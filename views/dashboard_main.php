<?php
// Este archivo se incluye desde dashboard.php, donde ya existe $pdo y $_SESSION
$user_id = $_SESSION['user_id'];
$rol = $_SESSION['rol'];
$nombre = htmlspecialchars($_SESSION['nombre']);

// Obtener última conexión
$stmt = $pdo->prepare("SELECT last_login FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$last_login = $stmt->fetchColumn();
$last_login_formatted = $last_login ? date('d/m/Y H:i', strtotime($last_login)) : 'Primera vez';

// Verificar notificaciones (solo para turistas)
$notificaciones = [];
if ($rol === 'turista') {
    $last_check = $_SESSION['last_notification_check'] ?? date('Y-m-d H:i:s', strtotime('-1 hour'));

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas
                           WHERE turista_id = ? AND updated_at > ?
                           AND estado IN ('confirmada', 'cancelada')");
    $stmt->execute([$user_id, $last_check]);
    $total_cambios = $stmt->fetchColumn();

    if ($total_cambios > 0) {
        $notificaciones['total'] = $total_cambios;
        $stmt = $pdo->prepare("SELECT id, estado, updated_at FROM reservas
                               WHERE turista_id = ? AND updated_at > ?
                               AND estado IN ('confirmada', 'cancelada')");
        $stmt->execute([$user_id, $last_check]);
        $notificaciones['detalles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $_SESSION['last_notification_check'] = date('Y-m-d H:i:s');
}

// Obtener saldo pendiente (solo turistas)
$saldo_pendiente = 0;
if ($rol === 'turista') {
    $stmt = $pdo->prepare("SELECT saldo_pendiente FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $saldo_pendiente = (float) $stmt->fetchColumn();
}

// Inicializar estadísticas
$stats = [];
$chartData = ['labels' => [], 'values' => [], 'colors' => []];

if ($rol === 'turista') {
    $stmt = $pdo->prepare("SELECT
        COUNT(*) as total_reservas,
        SUM(estado = 'confirmada') as confirmadas,
        SUM(estado = 'pendiente') as pendientes,
        SUM(estado = 'cancelada') as canceladas
        FROM reservas WHERE turista_id = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    foreach (['total_reservas', 'confirmadas', 'pendientes', 'canceladas'] as $k) {
        $stats[$k] = (int) ($stats[$k] ?? 0);
    }

    $chartData['labels'] = ['Confirmadas', 'Pendientes', 'Canceladas'];
    $chartData['values'] = [$stats['confirmadas'], $stats['pendientes'], $stats['canceladas']];
    $chartData['colors'] = ['#16A34A', '#D97706', '#DC2626'];

} elseif ($rol === 'compania') {
    $stmt = $pdo->prepare("SELECT
        (SELECT COUNT(*) FROM vehiculos WHERE compania_id = ?) as total_vehiculos,
        (SELECT COUNT(*) FROM reservas r JOIN vehiculos v ON r.vehiculo_id = v.id WHERE v.compania_id = ?) as total_reservas,
        (SELECT COUNT(*) FROM reservas r JOIN vehiculos v ON r.vehiculo_id = v.id WHERE v.compania_id = ? AND r.estado = 'confirmada') as confirmadas,
        (SELECT COUNT(*) FROM reservas r JOIN vehiculos v ON r.vehiculo_id = v.id WHERE v.compania_id = ? AND r.estado = 'pendiente') as pendientes,
        (SELECT COUNT(*) FROM reservas r JOIN vehiculos v ON r.vehiculo_id = v.id WHERE v.compania_id = ? AND r.estado = 'cancelada') as canceladas,
        (SELECT COALESCE(SUM(r.monto_total), 0) FROM reservas r JOIN vehiculos v ON r.vehiculo_id = v.id WHERE v.compania_id = ? AND r.estado = 'confirmada') as ingresos_totales");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    foreach (['total_vehiculos', 'total_reservas', 'confirmadas', 'pendientes', 'canceladas'] as $k) {
        $stats[$k] = (int) ($stats[$k] ?? 0);
    }
    $stats['ingresos_totales'] = (float) ($stats['ingresos_totales'] ?? 0);

    $chartData['labels'] = ['Confirmadas', 'Pendientes', 'Canceladas'];
    $chartData['values'] = [$stats['confirmadas'], $stats['pendientes'], $stats['canceladas']];
    $chartData['colors'] = ['#16A34A', '#D97706', '#DC2626'];

} elseif ($rol === 'administrador') {
    $stmt = $pdo->query("SELECT
        (SELECT COUNT(*) FROM usuarios) as total_usuarios,
        (SELECT COUNT(*) FROM vehiculos) as total_vehiculos,
        (SELECT COUNT(*) FROM reservas) as total_reservas,
        (SELECT COUNT(*) FROM reservas WHERE estado = 'confirmada') as confirmadas,
        (SELECT COUNT(*) FROM reservas WHERE estado = 'pendiente') as pendientes,
        (SELECT COUNT(*) FROM reservas WHERE estado = 'cancelada') as canceladas,
        (SELECT COALESCE(SUM(monto_total), 0) FROM reservas WHERE estado = 'confirmada') as ingresos_totales");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    foreach (['total_usuarios', 'total_vehiculos', 'total_reservas', 'confirmadas', 'pendientes', 'canceladas'] as $k) {
        $stats[$k] = (int) ($stats[$k] ?? 0);
    }
    $stats['ingresos_totales'] = (float) ($stats['ingresos_totales'] ?? 0);

    $chartData['labels'] = ['Confirmadas', 'Pendientes', 'Canceladas'];
    $chartData['values'] = [$stats['confirmadas'], $stats['pendientes'], $stats['canceladas']];
    $chartData['colors'] = ['#16A34A', '#D97706', '#DC2626'];

} elseif ($rol === 'soporte') {
    $stmt = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado = 'abierto'");
    $stats['tickets_abiertos'] = (int) $stmt->fetchColumn();
}

$chartDataJson = json_encode($chartData);

$taglines = [
    'turista'       => 'Busca autos, reserva en minutos y paga de forma segura.',
    'compania'      => 'Gestiona tu flota y recibe reservas de turistas.',
    'administrador' => 'Control total del sistema: usuarios, reservas y reportes.',
    'soporte'       => 'Atiende los tickets de soporte de los usuarios.',
];

$heroIcons = [
    'turista'       => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>',
    'compania'      => '<rect width="16" height="20" x="4" y="2" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/>',
    'administrador' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
    'soporte'       => '<path d="M3 11h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5Zm18 0h-3a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-5Z"/><path d="M21 10a9 9 0 0 0-18 0"/>',
];
?>

<div class="dw-hero">
    <div class="dw-hero-text">
        <span class="dw-eyebrow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a8 8 0 0 1-5.66-13.66 8 8 0 1 1 11.32 11.32A8 8 0 0 1 12 22Z"/><path d="m5 15-5 5 5 5"/></svg>
            Panel de <?php echo ucfirst($rol); ?>
        </span>
        <h2>¡Bienvenido, <?php echo $rol === 'compania' ? 'compañía <span>' . $nombre . '</span>' : '<span>' . $nombre . '</span>'; ?>!</h2>
        <p><?php echo $taglines[$rol] ?? ''; ?></p>
        <p class="dw-last">Última conexión: <?php echo $last_login_formatted; ?></p>
    </div>
    <div class="dw-hero-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><?php echo $heroIcons[$rol] ?? ''; ?></svg>
    </div>
</div>

<?php if ($rol === 'turista' && $saldo_pendiente > 0): ?>
    <div class="balance-alert">
        <span class="ba-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
        </span>
        <div>
            <p>Tienes un saldo pendiente de <strong>$<?php echo number_format($saldo_pendiente, 2); ?></strong>. Por favor, paga tus reservas pendientes para poder hacer nuevas reservas.</p>
            <a href="?view=mis_reservas">Ver reservas pendientes &rarr;</a>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($notificaciones['total']) && $notificaciones['total'] > 0): ?>
    <div class="balance-alert">
        <span class="ba-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        </span>
        <div>
            <p><strong>Tienes <?php echo $notificaciones['total']; ?> notificaciones nuevas</strong></p>
            <ul style="list-style: none; padding: 0; margin-top: 0.4rem;">
                <?php foreach ($notificaciones['detalles'] as $noti): ?>
                    <li style="padding: 0.2rem 0; color: var(--rw-gray-700);">
                        <?php if ($noti['estado'] === 'confirmada'): ?>
                            Reserva #<?php echo $noti['id']; ?> ha sido <strong>confirmada</strong>
                        <?php else: ?>
                            Reserva #<?php echo $noti['id']; ?> ha sido <strong>cancelada</strong>
                        <?php endif; ?>
                        <span style="font-size: 0.8rem; color: var(--rw-gray-500);">(<?php echo date('d/m/Y H:i', strtotime($noti['updated_at'])); ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="?view=mis_reservas">Ver mis reservas &rarr;</a>
        </div>
    </div>
<?php endif; ?>

<div class="stats-grid">
    <?php if ($rol === 'turista'): ?>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg></span>
            <div class="stat-number"><?php echo $stats['total_reservas']; ?></div>
            <div class="stat-label">Total reservas</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
            <div class="stat-number"><?php echo $stats['confirmadas']; ?></div>
            <div class="stat-label">Confirmadas</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
            <div class="stat-number"><?php echo $stats['pendientes']; ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></span>
            <div class="stat-number"><?php echo $stats['canceladas']; ?></div>
            <div class="stat-label">Canceladas</div>
        </div>
    <?php elseif ($rol === 'compania'): ?>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg></span>
            <div class="stat-number"><?php echo $stats['total_vehiculos']; ?></div>
            <div class="stat-label">Vehículos en flota</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg></span>
            <div class="stat-number"><?php echo $stats['total_reservas']; ?></div>
            <div class="stat-label">Total reservas</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
            <div class="stat-number"><?php echo $stats['confirmadas']; ?></div>
            <div class="stat-label">Confirmadas</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
            <div class="stat-number">$<?php echo number_format($stats['ingresos_totales'], 2); ?></div>
            <div class="stat-label">Ingresos totales</div>
        </div>
    <?php elseif ($rol === 'administrador'): ?>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
            <div class="stat-number"><?php echo $stats['total_usuarios']; ?></div>
            <div class="stat-label">Usuarios registrados</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg></span>
            <div class="stat-number"><?php echo $stats['total_vehiculos']; ?></div>
            <div class="stat-label">Vehículos totales</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg></span>
            <div class="stat-number"><?php echo $stats['total_reservas']; ?></div>
            <div class="stat-label">Reservas totales</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
            <div class="stat-number">$<?php echo number_format($stats['ingresos_totales'], 2); ?></div>
            <div class="stat-label">Ingresos totales</div>
        </div>
    <?php elseif ($rol === 'soporte'): ?>
        <div class="stat-card">
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg></span>
            <div class="stat-number"><?php echo $stats['tickets_abiertos']; ?></div>
            <div class="stat-label">Tickets abiertos</div>
        </div>
    <?php endif; ?>
</div>

<?php if ($rol !== 'soporte'): ?>
    <div class="chart-panel">
        <h4>Resumen de reservas</h4>
        <div class="chart-wrap">
            <canvas id="reservasChart"></canvas>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var chartEl = document.getElementById('reservasChart');
    if (!chartEl) return;
    var chartData = <?php echo $chartDataJson; ?>;

    if (!chartData.labels || chartData.labels.length === 0 || chartData.values.every(function(v) { return v === 0; })) {
        chartEl.parentElement.innerHTML = '<p style="color:var(--rw-gray-500);text-align:center;padding:1.5rem 0;">No hay datos para mostrar.</p>';
        return;
    }

    var ctx = chartEl.getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartData.labels,
            datasets: [{
                data: chartData.values,
                backgroundColor: chartData.colors,
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } }
            }
        }
    });
});
</script>