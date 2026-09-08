<?php
$reserva_id = intval($_GET['reserva_id'] ?? 0);
if ($reserva_id <= 0) {
    echo '<div class="error">ID de reserva no válido.</div>';
    exit;
}

// Verificar propiedad y estado
$stmt = $pdo->prepare("SELECT r.*, v.compania_id, v.marca, v.modelo 
                       FROM reservas r 
                       JOIN vehiculos v ON r.vehiculo_id = v.id 
                       WHERE r.id = ? AND r.turista_id = ?");
$stmt->execute([$reserva_id, $_SESSION['user_id']]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    echo '<div class="error">Reserva no encontrada o no te pertenece.</div>';
    exit;
}

if ($reserva['estado'] !== 'confirmada') {
    echo '<div class="error">La reserva debe estar confirmada para pagar.</div>';
    exit;
}

if ($reserva['pagado'] == 1) {
    echo '<div class="success">Esta reserva ya fue pagada. <a href="?view=mis_reservas">Volver a mis reservas</a></div>';
    exit;
}

// Obtener datos bancarios de la compañía
$stmt = $pdo->prepare("SELECT nombre, cuenta_bancaria, telefono_contacto, nombre_banco, qr_url FROM usuarios WHERE id = ?");
$stmt->execute([$reserva['compania_id']]);
$compania = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$compania) {
    echo '<div class="error">No se encontraron datos de la compañía.</div>';
    exit;
}
?>

<style>
    .pago-container {
        max-width: 700px;
        margin: 0 auto;
        padding: 1rem;
    }

    .pago-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
    }

    .pago-card h3 {
        font-size: 1.5rem;
        color: #1f2937;
        margin-bottom: 1rem;
    }

    .pago-card .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .pago-card .info-row .label {
        font-weight: 500;
        color: #6b7280;
    }

    .pago-card .info-row .value {
        font-weight: 600;
        color: #1f2937;
    }

    .qr-container {
        text-align: center;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 16px;
        margin-top: 1rem;
    }

    .qr-container img {
        max-width: 200px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-confirmar {
        background: #10b981;
        color: white;
        padding: 0.8rem 2rem;
        border-radius: 40px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-confirmar:hover {
        background: #059669;
        transform: translateY(-2px);
    }

    .btn-volver {
        background: #6b7280;
        color: white;
        padding: 0.8rem 2rem;
        border-radius: 40px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
        display: inline-block;
    }

    .btn-volver:hover {
        background: #4b5563;
    }

    .success {
        background: #d1fae5;
        color: #065f46;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        border-left: 4px solid #10b981;
    }

    .error {
        background: #fee2e2;
        color: #b91c1c;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        border-left: 4px solid #ef4444;
    }
</style>

<div class="pago-container">
    <div class="pago-card">
        <h3>Confirmar pago</h3>
        <div class="info-row"><span class="label">Reserva</span><span class="value"><?php echo htmlspecialchars($reserva['marca'] . ' ' . $reserva['modelo']); ?></span></div>
        <div class="info-row"><span class="label">Fechas</span><span class="value"><?php echo htmlspecialchars($reserva['fecha_inicio'] . ' a ' . $reserva['fecha_fin']); ?></span></div>
        <div class="info-row"><span class="label">Monto</span><span class="value">$<?php echo number_format($reserva['monto_total'], 2); ?></span></div>
    </div>

    <div class="pago-card">
        <h3>Datos bancarios</h3>
        <div class="info-row"><span class="label">Compañía</span><span class="value"><?php echo htmlspecialchars($compania['nombre']); ?></span></div>
        <div class="info-row"><span class="label">Banco</span><span class="value"><?php echo htmlspecialchars($compania['nombre_banco'] ?? 'No especificado'); ?></span></div>
        <div class="info-row"><span class="label">Cuenta</span><span class="value"><?php echo htmlspecialchars($compania['cuenta_bancaria'] ?? 'No especificada'); ?></span></div>
        <div class="info-row"><span class="label">Teléfono</span><span class="value"><?php echo htmlspecialchars($compania['telefono_contacto'] ?? 'No especificado'); ?></span></div>
        <?php if (!empty($compania['qr_url'])): ?>
            <div class="qr-container">
                <p>Escanea el QR para pagar</p>
                <img src="<?php echo htmlspecialchars($compania['qr_url']); ?>" alt="QR de pago">
            </div>
        <?php endif; ?>
    </div>

    <div style="display:flex; gap:0.5rem; flex-wrap:wrap; justify-content:center;">
        <button onclick="confirmarPago(<?php echo $reserva_id; ?>)" class="btn-confirmar">Confirmar pago</button>
        <a href="?view=mis_reservas" class="btn-volver"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:6px;"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>Volver a mis reservas</a>
    </div>
</div>

<script>
    function confirmarPago(reserva_id) {
        showConfirm('¿Confirmar el pago de esta reserva?', function() {
            // Llamada AJAX para pagar

            $.post('api/reservas.php', {
                    pagar: 1,
                    reserva_id: reserva_id
                })
                .done(function(res) {
                    if (res.error) {
                        showAlert(res.error, 'error');
                    } else {
                        showAlert(res.mensaje, 'success');
                        setTimeout(function() {
                            window.location.href = '?view=mis_reservas';
                        }, 1500);
                    }
                })
                .fail(function(jqXHR) {
                    let msg = 'Error al procesar el pago';
                    try {
                        const resp = JSON.parse(jqXHR.responseText);
                        if (resp.error) msg = resp.error;
                    } catch (e) {}
                    showAlert(msg, 'error');
                });
        });
    }
</script>