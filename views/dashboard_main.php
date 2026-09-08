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
    // Obtener la última revisión del usuario
    $last_check = $_SESSION['last_notification_check'] ?? date('Y-m-d H:i:s', strtotime('-1 hour'));
    
    // Buscar reservas que hayan cambiado desde la última revisión
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas 
                           WHERE turista_id = ? AND updated_at > ? 
                           AND estado IN ('confirmada', 'cancelada')");
    $stmt->execute([$user_id, $last_check]);
    $total_cambios = $stmt->fetchColumn();
    
    if ($total_cambios > 0) {
        // Guardar notificaciones en variable para mostrar
        $notificaciones['total'] = $total_cambios;
        // Obtener los detalles de las reservas cambiadas (opcional)
        $stmt = $pdo->prepare("SELECT id, estado, updated_at FROM reservas 
                               WHERE turista_id = ? AND updated_at > ? 
                               AND estado IN ('confirmada', 'cancelada')");
        $stmt->execute([$user_id, $last_check]);
        $notificaciones['detalles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Actualizar la última revisión
    $_SESSION['last_notification_check'] = date('Y-m-d H:i:s');
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
    $stats['total_reservas'] = $stats['total_reservas'] ?? 0;
    $stats['confirmadas'] = $stats['confirmadas'] ?? 0;
    $stats['pendientes'] = $stats['pendientes'] ?? 0;
    $stats['canceladas'] = $stats['canceladas'] ?? 0;

    $chartData['labels'] = ['Confirmadas', 'Pendientes', 'Canceladas'];
    $chartData['values'] = [$stats['confirmadas'], $stats['pendientes'], $stats['canceladas']];
    $chartData['colors'] = ['#10b981', '#f59e0b', '#ef4444'];

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
    $stats['total_vehiculos'] = $stats['total_vehiculos'] ?? 0;
    $stats['total_reservas'] = $stats['total_reservas'] ?? 0;
    $stats['confirmadas'] = $stats['confirmadas'] ?? 0;
    $stats['pendientes'] = $stats['pendientes'] ?? 0;
    $stats['canceladas'] = $stats['canceladas'] ?? 0;
    $stats['ingresos_totales'] = $stats['ingresos_totales'] ?? 0;

    $chartData['labels'] = ['Confirmadas', 'Pendientes', 'Canceladas'];
    $chartData['values'] = [$stats['confirmadas'], $stats['pendientes'], $stats['canceladas']];
    $chartData['colors'] = ['#10b981', '#f59e0b', '#ef4444'];

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
    $stats['total_usuarios'] = $stats['total_usuarios'] ?? 0;
    $stats['total_vehiculos'] = $stats['total_vehiculos'] ?? 0;
    $stats['total_reservas'] = $stats['total_reservas'] ?? 0;
    $stats['confirmadas'] = $stats['confirmadas'] ?? 0;
    $stats['pendientes'] = $stats['pendientes'] ?? 0;
    $stats['canceladas'] = $stats['canceladas'] ?? 0;
    $stats['ingresos_totales'] = $stats['ingresos_totales'] ?? 0;

    $chartData['labels'] = ['Confirmadas', 'Pendientes', 'Canceladas'];
    $chartData['values'] = [$stats['confirmadas'], $stats['pendientes'], $stats['canceladas']];
    $chartData['colors'] = ['#10b981', '#f59e0b', '#ef4444'];

} elseif ($rol === 'soporte') {
    $stmt = $pdo->query("SELECT COUNT(*) as tickets_abiertos FROM tickets WHERE estado = 'abierto'");
    $stats['tickets_abiertos'] = $stmt->fetchColumn() ?? 0;
    // Sin gráfica para soporte
}
// Obtener saldo pendiente (solo turistas)
if ($rol === 'turista') {
    $stmt = $pdo->prepare("SELECT saldo_pendiente FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $saldo_pendiente = $stmt->fetchColumn();
    if ($saldo_pendiente > 0) {
        echo '<div style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;">';
        echo '<p style="font-weight: 600; color: #b91c1c;">⚠️ Tienes un saldo pendiente de <strong>$' . number_format($saldo_pendiente, 2) . '</strong>.</p>';
        echo '<p style="color: #6b7280; font-size: 0.9rem;">Por favor, paga tus reservas pendientes para poder hacer nuevas reservas.</p>';
        echo '<a href="?view=mis_reservas" style="display: inline-block; margin-top: 0.5rem; color: #2563eb; font-weight: 500;">Ver reservas pendientes →</a>';
        echo '</div>';
    }
}
// Convertir datos de gráfica a JSON para pasarlos a JavaScript
$chartDataJson = json_encode($chartData);
?>
<style>
.dashboard-welcome {
    margin-bottom: 2rem;
}
.dashboard-welcome h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1f2937;
}
.dashboard-welcome p {
    color: #6b7280;
    font-size: 1.05rem;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.2rem;
    margin: 1.5rem 0;
}
.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    text-align: center;
    transition: all 0.2s;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.06);
}
.stat-card .stat-number {
    font-size: 2.2rem;
    font-weight: 700;
    color: #2563eb;
    line-height: 1.2;
}
.stat-card .stat-label {
    font-size: 0.9rem;
    color: #6b7280;
    margin-top: 0.3rem;
}
.chart-container {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    border: 1px solid #f0f0f0;
    margin-top: 1.5rem;
    max-width: 600px;
}
.chart-container h4 {
    margin-bottom: 1rem;
    color: #1f2937;
}
</style>

<div class="dashboard-welcome">
    <?php if ($rol === 'turista'): ?>
    <?php
    $stmt = $pdo->prepare("SELECT saldo_pendiente FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $saldo_pendiente = $stmt->fetchColumn();
    if ($saldo_pendiente > 0):
    ?>
        <div style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;">
            <p style="font-weight: 600; color: #b91c1c;">⚠️ Tienes un saldo pendiente de <strong>$<?php echo number_format($saldo_pendiente, 2); ?></strong>.</p>
            <p style="color: #6b7280; font-size: 0.9rem;">Por favor, paga tus reservas pendientes para poder hacer nuevas reservas.</p>
            <a href="?view=mis_reservas" style="display: inline-block; margin-top: 0.5rem; color: #2563eb; font-weight: 500;">Ver reservas pendientes →</a>
        </div>
    <?php endif; ?>
<?php endif; ?>
    <h2>👋 ¡Bienvenido, <?php echo $nombre; ?>!</h2>
    <p>Última conexión: <?php echo $last_login_formatted; ?></p>
    <p style="margin-top:0.3rem; font-size:0.95rem; color:#6b7280;">Este es tu panel de control. Aquí puedes gestionar todo lo relacionado con tus reservas y vehículos.</p>
</div>

<div class="stats-grid">
    <?php if ($rol === 'turista'): ?>
        <div class="stat-card"><div class="stat-number"><?php echo $stats['total_reservas']; ?></div><div class="stat-label">Total reservas</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $stats['confirmadas']; ?></div><div class="stat-label">Confirmadas</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $stats['pendientes']; ?></div><div class="stat-label">Pendientes</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $stats['canceladas']; ?></div><div class="stat-label">Canceladas</div></div>
    <?php elseif ($rol === 'compania'): ?>
        <div class="stat-card"><div class="stat-number"><?php echo $stats['total_vehiculos']; ?></div><div class="stat-label">Vehículos en flota</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $stats['total_reservas']; ?></div><div class="stat-label">Total reservas</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $stats['confirmadas']; ?></div><div class="stat-label">Confirmadas</div></div>
        <div class="stat-card"><div class="stat-number">$<?php echo number_format($stats['ingresos_totales'], 2); ?></div><div class="stat-label">Ingresos totales</div></div>
    <?php elseif ($rol === 'administrador'): ?>
        <div class="stat-card"><div class="stat-number"><?php echo $stats['total_usuarios']; ?></div><div class="stat-label">Usuarios registrados</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $stats['total_vehiculos']; ?></div><div class="stat-label">Vehículos totales</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $stats['total_reservas']; ?></div><div class="stat-label">Reservas totales</div></div>
        <div class="stat-card"><div class="stat-number">$<?php echo number_format($stats['ingresos_totales'], 2); ?></div><div class="stat-label">Ingresos totales</div></div>
    <?php elseif ($rol === 'soporte'): ?>
        <div class="stat-card"><div class="stat-number"><?php echo $stats['tickets_abiertos']; ?></div><div class="stat-label">Tickets abiertos</div></div>
    <?php endif; ?>
</div>

<div class="chart-container">
    <h4>Resumen de reservas</h4>
    <canvas id="reservasChart" width="400" height="200"></canvas>
</div>

<?php if (isset($notificaciones['total']) && $notificaciones['total'] > 0): ?>
<div style="background: #dbeafe; border-left: 4px solid #2563eb; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;">
    <p style="font-weight: 600; color: #1e40af;">Tienes <?php echo $notificaciones['total']; ?> notificaciones nuevas</p>
    <ul style="list-style: none; padding: 0; margin-top: 0.5rem;">
        <?php foreach ($notificaciones['detalles'] as $noti): ?>
            <li style="padding: 0.3rem 0; color: #1f2937;">
                <?php if ($noti['estado'] === 'confirmada'): ?>
                    Reserva #<?php echo $noti['id']; ?> ha sido <strong>confirmada</strong>
                <?php else: ?>
                    Reserva #<?php echo $noti['id']; ?> ha sido <strong>cancelada</strong>
                <?php endif; ?>
                <span style="font-size: 0.8rem; color: #6b7280;">(<?php echo date('d/m/Y H:i', strtotime($noti['updated_at'])); ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="?view=mis_reservas" style="display: inline-block; margin-top: 0.5rem; color: #2563eb; font-weight: 500;">Ver mis reservas →</a>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos pasados desde PHP como JSON
    const chartData = <?php echo $chartDataJson; ?>;
    
    // Si no hay datos o todos son 0, mostrar mensaje
    if (!chartData.labels || chartData.labels.length === 0 || chartData.values.every(v => v === 0)) {
        document.getElementById('reservasChart').parentElement.innerHTML = '<p style="color:#6b7280;text-align:center;">No hay datos para mostrar.</p>';
        return;
    }

    const ctx = document.getElementById('reservasChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartData.labels,
            datasets: [{
                data: chartData.values,
                backgroundColor: chartData.colors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>