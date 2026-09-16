<h3>Reservas de mis vehículos</h3>
<div id="reservasContainer">
    <div id="reservasList"></div>
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

function cargarReservas() {
    $('#reservasList').html('<div class="loading" style="text-align:center;padding:1rem;">Cargando reservas...</div>');
    
    $.get('api/reservas.php?recibidas=1', function(data) {
        if (data.error) {
            $('#reservasList').html('<div class="error">' + escapeHtml(data.error) + '</div>');
            return;
        }
        if (data.length === 0) {
            $('#reservasList').html('<p style="color:#6b7280;text-align:center;padding:1rem;">No hay reservas para tus vehículos.</p>');
            return;
        }

        // Filtrar solo reservas pendientes (las procesadas se ocultan)
        const pendientes = data.filter(r => r.estado === 'pendiente');
        if (pendientes.length === 0) {
            $('#reservasList').html('<p style="color:#6b7280;text-align:center;padding:1rem;"> Todas las reservas han sido procesadas.</p>');
            return;
        }

        let html = `
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; background:white; border-radius:16px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <thead>
                        <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                            <th style="padding:1rem; text-align:left; font-weight:600; color:#1f2937;">Turista</th>
                            <th style="padding:1rem; text-align:left; font-weight:600; color:#1f2937;">Vehículo</th>
                            <th style="padding:1rem; text-align:left; font-weight:600; color:#1f2937;">Fechas</th>
                            <th style="padding:1rem; text-align:left; font-weight:600; color:#1f2937;">Monto</th>
                            <th style="padding:1rem; text-align:center; font-weight:600; color:#1f2937;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        pendientes.forEach(r => {
            const turista = escapeHtml(r.turista_nombre);
            const vehiculo = escapeHtml(r.marca + ' ' + r.modelo);
            const fechaInicio = escapeHtml(r.fecha_inicio);
            const fechaFin = escapeHtml(r.fecha_fin);
            const monto = r.monto_total;

            html += `
                <tr style="border-bottom:1px solid #f3f4f6;" id="reserva-${r.id}">
                    <td style="padding:1rem;">${turista}</td>
                    <td style="padding:1rem;">
                        <div style="display:flex; align-items:center; gap:0.7rem;">
                            <div style="position:relative; width:58px; height:42px; flex:0 0 58px; border-radius:10px; overflow:hidden; background:#f3f4f6;">
                                <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#9ca3af;"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg></div>
                                <img src="${r.imagen_url ? escapeHtml(r.imagen_url) : ''}" alt="${vehiculo}" loading="lazy" style="position:relative; width:100%; height:100%; object-fit:cover; display:${r.imagen_url ? 'block' : 'none'};" onerror="this.onerror=null; this.style.display='none';">
                            </div>
                            <strong>${vehiculo}</strong>
                        </div>
                    </td>
                    <td style="padding:1rem;">${fechaInicio} a ${fechaFin}</td>
                    <td style="padding:1rem;">$${monto}</td>
                    <td style="padding:0.5rem; text-align:center;">
                        <div class="rw-acciones rw-acciones--center">
                            <button onclick="procesarReserva(${r.id}, 'confirmada')" title="Confirmar reserva" style="background:#10b981;"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg></button>
                            <button onclick="procesarReserva(${r.id}, 'cancelada')" title="Cancelar reserva" style="background:#ef4444;"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;
        $('#reservasList').html(html);
    }).fail(function() {
        $('#reservasList').html('<div class="error">Error al cargar reservas. Intenta de nuevo.</div>');
    });
}

function procesarReserva(reserva_id, estado) {
    showConfirm(`¿Estás seguro de ${estado === 'confirmada' ? 'confirmar' : 'cancelar'} esta reserva?`, function() {

        $.ajax({
            url: 'api/reservas.php',
            method: 'PUT',
            data: { actualizar_estado: 1, reserva_id, estado },
            success: function(res) {
                showAlert(res.mensaje, 'success');

                $('#reserva-' + reserva_id).fadeOut(300, function() {
                    $(this).remove();
                    if ($('#reservasList tbody tr').length === 0) {
                        $('#reservasList').html('<p style="color:#6b7280;text-align:center;padding:1rem;"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="var(--rw-success)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:6px;"><path d="M20 6 9 17l-5-5"/></svg>Todas las reservas han sido procesadas.</p>');
                    }
                });
            },
            error: function(xhr) {
                const resp = xhr.responseJSON;
                showAlert(resp && resp.error ? resp.error : 'Error al procesar la reserva', 'error');
            }
        });
    });
}

// Cargar al inicio
cargarReservas();
</script>

<style>
.loading {
    text-align: center;
    padding: 1rem;
    color: #6b7280;
}
.error {
    background: #fee2e2;
    color: #b91c1c;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    border-left: 4px solid #ef4444;
}
</style>