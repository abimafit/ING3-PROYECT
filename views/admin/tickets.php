<h3> Todos los tickets de soporte</h3>

<div id="ticketsContainer">
    <p style="color:#6b7280;">Cargando tickets...</p>
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

function cargarTickets() {
    $.get('api/admin.php?tickets=1')
        .done(function(data) {
            if (!Array.isArray(data)) {
                $('#ticketsContainer').html('<div class="error">Error al cargar tickets</div>');
                return;
            }
            if (data.length === 0) {
                $('#ticketsContainer').html('<p style="color:#6b7280;">No hay tickets en el sistema.</p>');
                return;
            }
            let html = '';
            data.forEach(t => {
                const estado = escapeHtml(t.estado);
                const estadoColor = estado === 'cerrado' ? '#6b7280' : (estado === 'en proceso' ? '#f59e0b' : '#10b981');
                const respuesta = t.respuesta ? escapeHtml(t.respuesta) : '';
                html += `
                    <div style="background:white; border-radius:16px; padding:1.2rem; margin-bottom:1rem; border-left:4px solid ${estadoColor}; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.5rem;">
                            <strong style="font-size:1.1rem;">${escapeHtml(t.asunto)}</strong>
                            <span style="background:${estadoColor}; color:white; padding:0.2rem 0.8rem; border-radius:20px; font-size:0.75rem; font-weight:600; text-transform:uppercase;">${estado}</span>
                        </div>
                        <p style="color:#6b7280; font-size:0.85rem;">👤 ${escapeHtml(t.turista_nombre)} |  ${escapeHtml(t.fecha_creacion)}</p>
                        <p style="margin:0.5rem 0;">${escapeHtml(t.mensaje)}</p>
                        ${respuesta ? `<div style="background:#f0fdf4; border-radius:10px; padding:0.8rem; margin-top:0.5rem; border-left:3px solid #22c55e;">
                            <p style="font-weight:500; color:#166534;"> Respuesta:</p>
                            <p>${respuesta}</p>
                            <p style="font-size:0.75rem; color:#6b7280;">${escapeHtml(t.fecha_respuesta)}</p>
                        </div>` : ''}
                        <div style="margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
                            ${estado !== 'cerrado' ? `
                                <button onclick="responderTicket(${t.id})" style="background:#3b82f6; color:white; border:none; padding:0.3rem 1rem; border-radius:20px; cursor:pointer;"> Responder</button>
                                <button onclick="cerrarTicket(${t.id})" style="background:#ef4444; color:white; border:none; padding:0.3rem 1rem; border-radius:20px; cursor:pointer;"> Cerrar</button>
                            ` : ''}
                            <button onclick="eliminarTicket(${t.id})" style="background:#6b7280; color:white; border:none; padding:0.3rem 1rem; border-radius:20px; cursor:pointer;"> Eliminar</button>
                        </div>
                        <div id="respuesta-${t.id}" style="display:none; margin-top:1rem;">
                            <textarea id="respuesta-text-${t.id}" style="width:100%; padding:0.5rem; border-radius:10px; border:1px solid #d1d5db; min-height:60px;" placeholder="Escribe tu respuesta..."></textarea>
                            <button onclick="enviarRespuesta(${t.id})" style="background:#10b981; color:white; border:none; padding:0.3rem 1rem; border-radius:20px; cursor:pointer; margin-top:0.3rem;"> Enviar</button>
                        </div>
                    </div>
                `;
            });
            $('#ticketsContainer').html(html);
        })
        .fail(function() {
            $('#ticketsContainer').html('<div class="error">Error al cargar tickets</div>');
        });
}

function responderTicket(id) {
    const div = document.getElementById('respuesta-' + id);
    div.style.display = div.style.display === 'none' ? 'block' : 'none';
}

function enviarRespuesta(id) {
    const texto = document.getElementById('respuesta-text-' + id).value.trim();
    if (!texto) { alert('Escribe una respuesta'); return; }
    $.post('api/soporte.php', { responder: 1, ticket_id: id, respuesta: texto })
        .done(function(res) {
            if (res.error) alert('❌ ' + res.error);
            else { alert('✅ ' + res.mensaje); cargarTickets(); }
        })
        .fail(function() { alert('Error al enviar'); });
}

function cerrarTicket(id) {
    if (!confirm('¿Cerrar este ticket?')) return;
    $.post('api/soporte.php', { cerrar: id })
        .done(function(res) {
            if (res.error) alert('❌ ' + res.error);
            else { alert('✅ ' + res.mensaje); cargarTickets(); }
        })
        .fail(function() { alert('Error al cerrar'); });
}

function eliminarTicket(id) {
    if (!confirm('¿Eliminar este ticket permanentemente?')) return;
    $.ajax({
        url: 'api/soporte.php',
        method: 'DELETE',
        data: { ticket_id: id },
        success: function(res) {
            if (res.error) alert('❌ ' + res.error);
            else { alert('✅ ' + res.mensaje); cargarTickets(); }
        },
        error: function() { alert('Error al eliminar'); }
    });
}

cargarTickets();
</script>