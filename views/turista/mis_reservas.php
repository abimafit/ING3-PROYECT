<div class="view-header">
    <h3>Mis reservas</h3>
</div>

<div class="tabs-container" style="display:flex; gap:0.25rem; border-bottom:2px solid #e5e7eb; margin-bottom:1.5rem;">
    <button id="tabActivas" class="tab-btn active" onclick="cargarReservas(false)"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:6px;"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>Reservas activas</button>
    <button id="tabHistorial" class="tab-btn" onclick="cargarReservas(true)"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:6px;"><path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/></svg>Historial</button>
</div>

<div id="reservasContainer">
    <div id="reservas"></div>
</div>

<script>
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function cambiarPestana(historial) {
    const tabActivas = document.getElementById('tabActivas');
    const tabHistorial = document.getElementById('tabHistorial');
    tabActivas.classList.toggle('active', !historial);
    tabHistorial.classList.toggle('active', historial);
}

function cargarReservas(historial) {
    cambiarPestana(historial);
    const url = 'api/reservas.php?mis_reservas=1' + (historial ? '&historial=1' : '');
    $('#reservas').html('<div class="loading">Cargando reservas...</div>');
    $.get(url, function(data) {
        if (data.error) {
            $('#reservas').html('<div class="error">' + escapeHtml(data.error) + '</div>');
            return;
        }
        if (data.length === 0) {
            $('#reservas').html(
                '<div class="empty-state">' +
                    '<span class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h3l2.5-3h7l2.5 3h3a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1Z"/><path d="M3 13h18"/></svg></span>' +
                    '<h4>' + (historial ? 'No hay reservas en el historial' : 'No tienes reservas activas') + '</h4>' +
                    (historial ? '' : '<p>Busca un vehículo y haz tu primera reserva.</p>') +
                '</div>'
            );
            return;
        }
        let html = '<div class="table-responsive"><table class="table">';
        html += '<thead><tr><th>Vehículo</th><th>Fechas</th><th>Monto</th><th>Estado</th>';
        if (!historial) html += '<th>Acción</th>';
        html += '</tr></thead><tbody>';
        data.forEach(r => {
            const marca = escapeHtml(r.marca);
            const modelo = escapeHtml(r.modelo);
            const fechaInicio = escapeHtml(r.fecha_inicio);
            const fechaFin = escapeHtml(r.fecha_fin);
            const estado = escapeHtml(r.estado);

            const pagado = parseInt(r.pagado) === 1;

            let montoHtml = '$' + r.monto_total;
            if (r.recargo_aplicado > 0) {
                montoHtml += '<br><span style="color:var(--rw-danger); font-size:0.8rem;">+25% recargo ($' + r.recargo_aplicado + ')<br><strong>Total: $' + r.monto_total_con_recargo + '</strong></span>';
            }

            let badge = '<span class="badge">' + estado + '</span>';
            if (estado === 'pendiente') badge = '<span class="badge badge-warning">Pendiente de confirmación</span>';
            else if (estado === 'confirmada' && pagado) badge = '<span class="badge badge-success">Pagado</span>';
            else if (estado === 'confirmada') badge = '<span class="badge badge-warning">Pendiente de pago</span>';
            else if (estado === 'cancelada') badge = '<span class="badge badge-danger">Cancelada</span>';

            html += `<tr>
                         <td><strong>${marca} ${modelo}</strong></td>
                         <td>${fechaInicio} a ${fechaFin}</td>
                         <td>${montoHtml}</td>
                         <td>${badge}</td>`;

            if (!historial) {
                html += '<td>';
                if (estado === 'pendiente') {
                    html += `<button onclick="pagar(${r.id})" class="btn-small btn-warning">Pagar</button> `;
                    html += `<button onclick="cancelarReserva(${r.id})" class="btn-small btn-warning" style="background:var(--rw-red);border-color:var(--rw-red);">Cancelar</button>`;
                } else if (estado === 'confirmada') {
                    if (pagado) {
                        html += `<button onclick="verRecibo(${r.id})" class="btn-small btn-primary">Ver recibo</button>`;
                    } else {
                        html += `<button onclick="pagar(${r.id})" class="btn-small btn-warning">Pagar</button>`;
                    }
                } else if (estado === 'cancelada') {
                    html += `<button onclick="eliminarReserva(${r.id})" class="btn-small" style="background:var(--rw-gray-500);border-color:var(--rw-gray-500);">Eliminar</button>`;
                }
                html += '</td>';
            }
            html += `</tr>`;
        });
        html += '</tbody></table></div>';
        $('#reservas').html(html);
    }).fail(function() {
        $('#reservas').html('<div class="error">Error al cargar reservas</div>');
    });
}

function pagar(reserva_id) {
    window.location.href = '?view=pago&reserva_id=' + reserva_id;
}

function cancelarReserva(reserva_id) {
    showConfirm('¿Cancelar esta reserva?', function() {
        $.ajax({
            url: 'api/reservas.php',
            method: 'PUT',
            data: { actualizar_estado: 1, reserva_id, estado: 'cancelada' },
            success: function(res) {
                showAlert(res.mensaje, 'success');
                cargarReservas(false);
            },
            error: function(xhr) {
                const resp = xhr.responseJSON;
                showAlert(resp ? resp.error : 'Error al cancelar', 'error');
            }
        });
    });
}

function eliminarReserva(reserva_id) {
    showConfirm('¿Eliminar esta reserva cancelada?', function() {
        $.ajax({
            url: 'api/reservas.php',
            method: 'DELETE',
            data: { reserva_id },
            success: function(res) {
                showAlert(res.mensaje, 'success');
                cargarReservas(false);
            },
            error: function(xhr) {
                const resp = xhr.responseJSON;
                showAlert(resp ? resp.error : 'Error al eliminar', 'error');
            }
        });
    });
}

function verRecibo(reserva_id) {
    window.location.href = '?view=recibo&reserva_id=' + reserva_id;
}

cargarReservas(false);
</script>