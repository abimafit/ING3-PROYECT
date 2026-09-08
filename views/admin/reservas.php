<h3>📋 Todas las reservas del sistema</h3>

<!-- Filtros (opcional) -->
<div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
    <select id="filtroEstado" style="padding:0.5rem; border-radius:10px; border:1px solid #d1d5db;">
        <option value="">Todos los estados</option>
        <option value="pendiente">Pendiente</option>
        <option value="confirmada">Confirmada</option>
        <option value="cancelada">Cancelada</option>
    </select>
    <input type="text" id="filtroBuscar" placeholder="Buscar por vehículo o usuario..." style="padding:0.5rem; border-radius:10px; border:1px solid #d1d5db; flex:1;">
    <button onclick="cargarReservas()" style="background:#2563eb; color:white; border:none; padding:0.5rem 1.5rem; border-radius:40px; cursor:pointer;">Filtrar</button>
</div>

<div id="reservasContainer">
    <p style="color:#6b7280;">Cargando reservas...</p>
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

function cargarReservas() {
    const estado = $('#filtroEstado').val();
    const buscar = $('#filtroBuscar').val().toLowerCase();
    $.get('api/admin.php?reservas=1')
        .done(function(data) {
            if (!Array.isArray(data)) {
                $('#reservasContainer').html('<div class="error">Error al cargar reservas</div>');
                return;
            }
            // Aplicar filtros
            let filtradas = data;
            if (estado) filtradas = filtradas.filter(r => r.estado === estado);
            if (buscar) {
                filtradas = filtradas.filter(r => 
                    (r.marca + ' ' + r.modelo).toLowerCase().includes(buscar) ||
                    (r.turista_nombre || '').toLowerCase().includes(buscar) ||
                    (r.compania_nombre || '').toLowerCase().includes(buscar)
                );
            }
            if (filtradas.length === 0) {
                $('#reservasContainer').html('<p style="color:#6b7280;">No hay reservas que coincidan con los filtros.</p>');
                return;
            }
            let html = '<table class="admin-table">';
            html += '<thead><tr><th>ID</th><th>Turista</th><th>Compañía</th><th>Vehículo</th><th>Fechas</th><th>Monto</th><th>Estado</th><th>Pagado</th><th>Acciones</th></tr></thead><tbody>';
            filtradas.forEach(r => {
                const estadoClass = r.estado === 'confirmada' ? 'badge-success' : (r.estado === 'pendiente' ? 'badge-warning' : 'badge-danger');
                const pagadoClass = r.pagado == 1 ? 'badge-success' : 'badge-warning';
                const pagadoText = r.pagado == 1 ? 'Sí' : 'No';
                html += `<tr>
                    <td>${r.id}</td>
                    <td>${escapeHtml(r.turista_nombre || 'N/A')}</td>
                    <td>${escapeHtml(r.compania_nombre || 'N/A')}</td>
                    <td>${escapeHtml(r.marca)} ${escapeHtml(r.modelo)}</td>
                    <td>${escapeHtml(r.fecha_inicio)} → ${escapeHtml(r.fecha_fin)}</td>
                    <td>$${Number(r.monto_total).toFixed(2)}</td>
                    <td><span class="badge ${estadoClass}">${escapeHtml(r.estado)}</span></td>
                    <td><span class="badge ${pagadoClass}">${pagadoText}</span></td>
                    <td>
                        ${r.estado !== 'confirmada' ? `<button onclick="actualizarReserva(${r.id}, 'estado', 'confirmada')" style="background:#10b981; color:white; border:none; padding:0.2rem 0.6rem; border-radius:12px; cursor:pointer; font-size:0.7rem;">Confirmar</button> ` : ''}
                        ${r.estado !== 'cancelada' ? `<button onclick="actualizarReserva(${r.id}, 'estado', 'cancelada')" style="background:#ef4444; color:white; border:none; padding:0.2rem 0.6rem; border-radius:12px; cursor:pointer; font-size:0.7rem;">Cancelar</button> ` : ''}
                        ${r.pagado == 0 ? `<button onclick="actualizarReserva(${r.id}, 'pagado', 1)" style="background:#3b82f6; color:white; border:none; padding:0.2rem 0.6rem; border-radius:12px; cursor:pointer; font-size:0.7rem;">Marcar pagado</button>` : ''}
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            $('#reservasContainer').html(html);
        })
        .fail(function() {
            $('#reservasContainer').html('<div class="error">Error al cargar reservas</div>');
        });
}

function actualizarReserva(reserva_id, campo, valor) {
    if (!confirm(`¿Actualizar reserva #${reserva_id}?`)) return;
    $.post('api/admin.php', { actualizar_reserva: 1, reserva_id, campo, valor })
        .done(function(res) {
            if (res.error) alert('❌ ' + res.error);
            else { alert('✅ ' + res.mensaje); cargarReservas(); }
        })
        .fail(function() { alert('Error al actualizar'); });
}

// Cargar al inicio
cargarReservas();
</script>