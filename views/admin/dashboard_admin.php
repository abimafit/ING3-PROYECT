<?php
// Obtener estadísticas generales del sistema
$stats = [];

// Total de usuarios por rol
$stmt = $pdo->query("SELECT rol, COUNT(*) as total FROM usuarios GROUP BY rol");
$stats['usuarios_por_rol'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stats['total_usuarios'] = array_sum(array_column($stats['usuarios_por_rol'], 'total'));

// Total de vehículos
$stmt = $pdo->query("SELECT COUNT(*) FROM vehiculos");
$stats['total_vehiculos'] = $stmt->fetchColumn();

// Reservas por estado
$stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM reservas GROUP BY estado");
$stats['reservas_por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stats['total_reservas'] = array_sum(array_column($stats['reservas_por_estado'], 'total'));

// Ingresos totales (reservas confirmadas)
$stmt = $pdo->query("SELECT COALESCE(SUM(monto_total), 0) FROM reservas WHERE estado = 'confirmada'");
$stats['ingresos_totales'] = $stmt->fetchColumn();

// Tickets abiertos y cerrados
$stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM tickets GROUP BY estado");
$stats['tickets_por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stats['total_tickets'] = array_sum(array_column($stats['tickets_por_estado'], 'total'));

// Últimas 5 reservas
$stmt = $pdo->query("SELECT r.*, v.marca, v.modelo, u.nombre as turista 
                     FROM reservas r 
                     JOIN vehiculos v ON r.vehiculo_id = v.id 
                     JOIN usuarios u ON r.turista_id = u.id 
                     ORDER BY r.fecha_reserva DESC LIMIT 5");
$stats['ultimas_reservas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Últimos 5 tickets
$stmt = $pdo->query("SELECT t.*, u.nombre as turista 
                     FROM tickets t 
                     JOIN usuarios u ON t.turista_id = u.id 
                     ORDER BY t.fecha_creacion DESC LIMIT 5");
$stats['ultimos_tickets'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para la gráfica de reservas
$chartLabels = [];
$chartValues = [];
$chartColors = ['#10b981', '#f59e0b', '#ef4444'];
foreach ($stats['reservas_por_estado'] as $item) {
    $chartLabels[] = ucfirst($item['estado']);
    $chartValues[] = $item['total'];
}
$chartDataJson = json_encode([
    'labels' => $chartLabels,
    'values' => $chartValues,
    'colors' => $chartColors
]);
?>

<style>
.admin-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.2rem;
    margin: 1.5rem 0;
}
.admin-stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    text-align: center;
    transition: all 0.2s;
}
.admin-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.06);
}
.admin-stat-card .stat-number {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--rw-red);
    line-height: 1.2;
}
.admin-stat-card .stat-label {
    font-size: 0.9rem;
    color: #6b7280;
    margin-top: 0.3rem;
}
.admin-stat-card .stat-icon {
    font-size: 2rem;
    display: block;
    margin-bottom: 0.5rem;
}
.admin-section-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #1f2937;
    margin: 2rem 0 1rem 0;
}
.admin-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.admin-table th {
    background: #f9fafb;
    padding: 0.8rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #1f2937;
    border-bottom: 1px solid #e5e7eb;
}
.admin-table td {
    padding: 0.8rem 1rem;
    border-bottom: 1px solid #f3f4f6;
}
.admin-table tr:hover td {
    background: #fafafa;
}
.admin-table .badge {
    background: #e5e7eb;
    color: #374151;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
}
.admin-table .badge-success { background: #d1fae5; color: #065f46; }
.admin-table .badge-warning { background: #fef3c7; color: #92400e; }
.admin-table .badge-danger { background: #fee2e2; color: #991b1b; }
.admin-table .badge-info { background: var(--rw-red-tint); color: var(--rw-red-dark); }
</style>

<div class="admin-dashboard">
    <h2 style="font-size:1.8rem; font-weight:700; color:#1f2937;"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--rw-red)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-5px;margin-right:10px;"><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg>Panel de administración</h2>
    <p style="color:#6b7280; margin-bottom:1.5rem;">Resumen general del sistema RentWheels</p>

    <!-- Tarjetas de estadísticas -->
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="stat-number"><?php echo $stats['total_usuarios']; ?></div>
            <div class="stat-label">Usuarios registrados</div>
        </div>
        <div class="admin-stat-card">
            <div class="stat-number"><?php echo $stats['total_vehiculos']; ?></div>
            <div class="stat-label">Vehículos en el sistema</div>
        </div>
        <div class="admin-stat-card">
            <div class="stat-number"><?php echo $stats['total_reservas']; ?></div>
            <div class="stat-label">Reservas totales</div>
        </div>
        <div class="admin-stat-card">
            <div class="stat-number">$<?php echo number_format($stats['ingresos_totales'], 2); ?></div>
            <div class="stat-label">Ingresos totales</div>
        </div>
        <div class="admin-stat-card">
            <div class="stat-number"><?php echo $stats['total_tickets']; ?></div>
            <div class="stat-label">Tickets de soporte</div>
        </div>
    </div>

    <!-- Gráfica de reservas y usuarios por rol -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin: 1.5rem 0;">
        <div style="background: white; padding: 1.5rem; border-radius: 16px; border: 1px solid #f0f0f0;">
            <h4 style="margin-bottom:1rem;"> Reservas por estado</h4>
            <canvas id="reservasChart" width="300" height="200"></canvas>
        </div>
        <div style="background: white; padding: 1.5rem; border-radius: 16px; border: 1px solid #f0f0f0;">
            <h4 style="margin-bottom:1rem;"> Usuarios por rol</h4>
            <canvas id="rolesChart" width="300" height="200"></canvas>
        </div>
    </div>

    <!-- Últimas reservas -->
    <h4 class="admin-section-title"> Últimas reservas</h4>
    <?php if (count($stats['ultimas_reservas']) > 0): ?>
    <table class="admin-table">
        <thead>
            <tr><th>Turista</th><th>Vehículo</th><th>Fechas</th><th>Monto</th><th>Estado</th></tr>
        </thead>
        <tbody>
            <?php foreach ($stats['ultimas_reservas'] as $r): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['turista']); ?></td>
                <td><?php echo htmlspecialchars($r['marca'] . ' ' . $r['modelo']); ?></td>
                <td><?php echo htmlspecialchars($r['fecha_inicio'] . ' → ' . $r['fecha_fin']); ?></td>
                <td>$<?php echo number_format($r['monto_total'], 2); ?></td>
                <td>
                    <span class="badge 
                        <?php echo $r['estado'] === 'confirmada' ? 'badge-success' : ($r['estado'] === 'pendiente' ? 'badge-warning' : 'badge-danger'); ?>">
                        <?php echo htmlspecialchars($r['estado']); ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p style="color:#6b7280;">No hay reservas registradas.</p>
    <?php endif; ?>

    <!-- Últimos tickets -->
    <h4 class="admin-section-title"> Tickets recientes</h4>
    <?php if (count($stats['ultimos_tickets']) > 0): ?>
    <table class="admin-table">
        <thead>
            <tr><th>Turista</th><th>Asunto</th><th>Estado</th><th>Fecha</th></tr>
        </thead>
        <tbody>
            <?php foreach ($stats['ultimos_tickets'] as $t): ?>
            <tr>
                <td><?php echo htmlspecialchars($t['turista']); ?></td>
                <td><?php echo htmlspecialchars($t['asunto']); ?></td>
                <td>
                    <span class="badge 
                        <?php echo $t['estado'] === 'cerrado' ? 'badge-danger' : ($t['estado'] === 'en proceso' ? 'badge-warning' : 'badge-success'); ?>">
                        <?php echo htmlspecialchars($t['estado']); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($t['fecha_creacion']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p style="color:#6b7280;">No hay tickets registrados.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfica de reservas
    const reservasData = <?php echo json_encode($stats['reservas_por_estado']); ?>;
    if (reservasData.length > 0) {
        const labels = reservasData.map(item => item.estado.charAt(0).toUpperCase() + item.estado.slice(1));
        const values = reservasData.map(item => item.total);
        const colors = ['#10b981', '#f59e0b', '#ef4444'];
        
        new Chart(document.getElementById('reservasChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors.slice(0, values.length),
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
    } else {
        document.getElementById('reservasChart').parentElement.innerHTML = '<p style="color:#6b7280;">No hay datos de reservas.</p>';
    }

    // Gráfica de roles
    const rolesData = <?php echo json_encode($stats['usuarios_por_rol']); ?>;
    if (rolesData.length > 0) {
        const labels = rolesData.map(item => item.rol.charAt(0).toUpperCase() + item.rol.slice(1));
        const values = rolesData.map(item => item.total);
        const colors = ['#C61A30', '#141414', '#f59e0b', '#8b5cf6', '#16a34a'];
        
        new Chart(document.getElementById('rolesChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Usuarios',
                    data: values,
                    backgroundColor: colors.slice(0, values.length),
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    } else {
        document.getElementById('rolesChart').parentElement.innerHTML = '<p style="color:#6b7280;">No hay datos de usuarios.</p>';
    }
});
</script>