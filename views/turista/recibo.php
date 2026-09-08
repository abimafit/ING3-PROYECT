<?php
$reserva_id = intval($_GET['reserva_id'] ?? 0);
if ($reserva_id <= 0) {
    echo '<div class="error">ID de reserva no válido.</div>';
    exit;
}

// Verificar propiedad
$stmt = $pdo->prepare("SELECT r.*, v.marca, v.modelo, u.nombre as compania_nombre 
                       FROM reservas r 
                       JOIN vehiculos v ON r.vehiculo_id = v.id 
                       JOIN usuarios u ON v.compania_id = u.id 
                       WHERE r.id = ? AND r.turista_id = ? AND r.pagado = 1");
$stmt->execute([$reserva_id, $_SESSION['user_id']]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    echo '<div class="error">Recibo no disponible.</div>';
    exit;
}
?>
<style>
.recibo-container { max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.recibo-header { text-align: center; border-bottom: 2px solid var(--rw-red); padding-bottom: 1rem; margin-bottom: 1rem; }
.recibo-header h1 { color: var(--rw-red); margin: 0; }
.recibo-header p { color: #6b7280; }
.recibo-detalle { margin: 1rem 0; }
.recibo-detalle .fila { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0; }
.recibo-detalle .fila .label { font-weight: 500; color: #6b7280; }
.recibo-detalle .fila .value { font-weight: 600; color: #1f2937; }
.recibo-total { text-align: right; font-size: 1.5rem; font-weight: 700; color: #10b981; margin-top: 1rem; }
.btn-imprimir { background: var(--rw-red); color: white; border: none; padding: 0.8rem 2rem; border-radius: 40px; font-weight: 600; cursor: pointer; margin-top: 1rem; transition: all 0.3s ease; }
.btn-imprimir:hover { background: var(--rw-red-dark); transform: translateY(-1px); }
.btn-volver { background: #6b7280; color: white; border: none; padding: 0.8rem 2rem; border-radius: 40px; font-weight: 600; cursor: pointer; margin-top: 1rem; text-decoration: none; display: inline-block; }
</style>

<div class="recibo-container">
    <div class="recibo-header">
        <h1>RentWheels</h1>
        <p>Recibo de pago #<?php echo $reserva['id']; ?></p>
        <p>Fecha: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
    <div class="recibo-detalle">
        <div class="fila"><span class="label">Compañía</span><span class="value"><?php echo htmlspecialchars($reserva['compania_nombre']); ?></span></div>
        <div class="fila"><span class="label">Vehículo</span><span class="value"><?php echo htmlspecialchars($reserva['marca'] . ' ' . $reserva['modelo']); ?></span></div>
        <div class="fila"><span class="label">Fechas</span><span class="value"><?php echo htmlspecialchars($reserva['fecha_inicio'] . ' a ' . $reserva['fecha_fin']); ?></span></div>
        <div class="fila"><span class="label">Monto total</span><span class="value">$<?php echo number_format($reserva['monto_total'], 2); ?></span></div>
        <?php if ($reserva['recargo_aplicado'] > 0): ?>
        <div class="fila"><span class="label">Recargo (25%)</span><span class="value">$<?php echo number_format($reserva['recargo_aplicado'], 2); ?></span></div>
        <div class="fila"><span class="label">Total pagado</span><span class="value">$<?php echo number_format($reserva['monto_total_con_recargo'], 2); ?></span></div>
        <?php endif; ?>
    </div>
    <div class="recibo-total">Pagado</div>
    <div style="text-align: center;">
        <button onclick="window.print()" class="btn-imprimir">Imprimir</button>
        <a href="?view=mis_reservas" class="btn-volver"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:6px;"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>Volver</a>
    </div>
</div>