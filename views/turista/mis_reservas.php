<h3>Mis reservas</h3>

<!-- Pestañas -->
<div style="display: flex; gap: 1rem; border-bottom: 2px solid #e5e7eb; margin-bottom: 1.5rem;">
    <button id="tabActivas" class="tab-btn active" onclick="cargarReservas(false)" style="background: none; border: none; padding: 0.5rem 1rem; font-weight: 600; color: #2563eb; border-bottom: 3px solid #2563eb; cursor: pointer; transition: all 0.2s;">📅 Reservas activas</button>
    <button id="tabHistorial" class="tab-btn" onclick="cargarReservas(true)" style="background: none; border: none; padding: 0.5rem 1rem; font-weight: 600; color: #6b7280; border-bottom: 3px solid transparent; cursor: pointer; transition: all 0.2s;">📜 Historial</button>
</div>

<div id="reservasContainer">
    <div id="reservas">
        <p style="color:#6b7280;">Cargando reservas...</p>
    </div>
</div>

<script>
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function cambiarPestana(historial) {
    const tabActivas = document.getElementById('tabActivas');
    const tabHistorial = document.getElementById('tabHistorial');
    if (historial) {
        tabActivas.className = 'tab-btn';
        tabActivas.style.color = '#6b7280';
        tabActivas.style.borderBottom = '3px solid transparent';
        tabHistorial.className = 'tab-btn active';
        tabHistorial.style.color = '#2563eb';
        tabHistorial.style.borderBottom = '3px solid #2563eb';
    } else {
        tabActivas.className = 'tab-btn active';
        tabActivas.style.color = '#2563eb';
        tabActivas.style.borderBottom = '3px solid #2563eb';
        tabHistorial.className = 'tab-btn';
        tabHistorial.style.color = '#6b7280';
        tabHistorial.style.borderBottom = '3px solid transparent';
    }
}

function cargarReservas(historial) {
    cambiarPestana(historial);
    const url = 'api/reservas.php?mis_reservas=1' + (historial ? '&historial=1' : '');
    $('#reservas').html('<p style="color:#6b7280;">Cargando reservas...</p>');
    $.get(url, function(data) {
        if (data.error) {
            $('#reservas').html('<div class="error">' + escapeHtml(data.error) + '</div>');
            return;
        }
        if (data.length === 0) {
            $('#reservas').html('<p style="color:#6b7280;">' + (historial ? 'No hay reservas en el historial.' : 'No tienes reservas activas.') + '</p>');
            return;
        }
        let html = '<table class="table">';
        html += '<thead><tr><th>Vehículo</th><th>Fechas</th><th>Monto</th><th>Estado</th>';
        if (!historial) html += '<th>Acción</th>';
        html += '</tr></thead><tbody>';
        data.forEach(r => {
            const marca = escapeHtml(r.marca);
            const modelo = escapeHtml(r.modelo);
            const fechaInicio = escapeHtml(r.fecha_inicio);
            const fechaFin = escapeHtml(r.fecha_fin);
            const estado = escapeHtml(r.estado);
            
            // Verificar si la reserva está pagada (campo `pagado` en BD)
            const pagado = parseInt(r.pagado) === 1;
            
            // Mostrar monto con recargo si aplica
            let montoHtml = '$' + r.monto_total;
            if (r.recargo_aplicado > 0) {
                montoHtml += '<br><span style="color:#dc2626; font-size:0.8rem;">+25% recargo ($' + r.recargo_aplicado + ')<br><strong>Total: $' + r.monto_total_con_recargo + '</strong></span><br><span style="color:#dc2626; font-size:0.7rem;">⚠️ Reserva vencida</span>';
            }
            
            html += `<tr>
                         <td>${marca} ${modelo}</td>
                         <td>${fechaInicio} a ${fechaFin}</td>
                         <td>${montoHtml}</td>
                         <td>${estado}</td>`;
                         
            if (!historial) {
                html += '<td>';
                if (estado === 'pendiente') {
                    html += `<button onclick="pagar(${r.id})" class="btn-small" style="background:#2563eb;">Pagar</button> `;
                    html += `<button onclick="cancelarReserva(${r.id})" class="btn-small" style="background:#f59e0b;">Cancelar</button>`;
                } else if (estado === 'confirmada') {
                    if (pagado) {
                        html += `<span class="badge" style="background:#10b981;color:white;">Pagado</span> `;
                        html += `<button onclick="verRecibo(${r.id})" class="btn-small" style="background:#2563eb;">Ver recibo</button>`;
                    } else {
                        html += `<span class="badge" style="background:#f59e0b;color:white;">Pendiente de pago</span> `;
                        html += `<button onclick="pagar(${r.id})" class="btn-small" style="background:#2563eb;">Pagar</button>`;
                    }
                } else if (estado === 'cancelada') {
                    html += `<span class="badge" style="background:#ef4444;color:white;">Cancelada</span> `;
                    html += `<button onclick="eliminarReserva(${r.id})" class="btn-small" style="background:#6b7280;">Eliminar</button>`;
                }
                html += '</td>';
            }
            html += `</tr>`;
        });
        html += '</tbody></table>';
        $('#reservas').html(html);
    }).fail(function() {
        $('#reservas').html('<div class="error">Error al cargar reservas</div>');
    });
}

// Ir a la página de pago
function pagar(reserva_id) {
    window.location.href = '?view=pago&reserva_id=' + reserva_id;
}

// Cancelar reserva (solo pendiente)
function cancelarReserva(reserva_id) {
showConfirm('¿Cancelar esta reserva?', function() {
    // Llamada AJAX para cancelar

    $.ajax({
        url: 'api/reservas.php',
        method: 'PUT',
        data: { actualizar_estado: 1, reserva_id, estado: 'cancelada' },
        success: function(res) {
            alert(res.mensaje);
            cargarReservas(false);
        },
        error: function(xhr) {
            const resp = xhr.responseJSON;
            alert(resp ? resp.error : 'Error al cancelar');
        }
    });
    });
}

// Eliminar reserva cancelada
function eliminarReserva(reserva_id) {
    if (!confirm('¿Eliminar esta reserva cancelada?')) return;
    $.ajax({
        url: 'api/reservas.php',
        method: 'DELETE',
        data: { reserva_id },
        success: function(res) {
            alert(res.mensaje);
            cargarReservas(false);
        },
        error: function(xhr) {
            const resp = xhr.responseJSON;
            alert(resp ? resp.error : 'Error al eliminar');
        }
    });
}

// Ver recibo de pago (redirige a la vista de recibo)
function verRecibo(reserva_id) {
    window.location.href = '?view=recibo&reserva_id=' + reserva_id;
}

// Cargar por defecto: reservas activas
cargarReservas(false);
</script>

<style>
.table { width: 100%; border-collapse: collapse; margin-top: 15px; }
.table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.table th { background: #007bff; color: white; }
.btn-small { padding: 0.3rem 0.8rem; border: none; border-radius: 20px; font-size: 0.8rem; color: white; cursor: pointer; margin: 0.2rem; transition: all 0.2s; }
.btn-small:hover { opacity: 0.8; transform: translateY(-1px); }
.badge { padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.8rem; display: inline-block; margin-right: 0.3rem; }
.tab-btn { font-family: 'Inter', sans-serif; font-size: 1rem; background: none; border: none; cursor: pointer; transition: all 0.2s; }
.tab-btn:hover { opacity: 0.7; }
</style>