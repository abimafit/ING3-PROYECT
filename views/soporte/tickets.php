<h3>Tickets de soporte</h3>
<div id="tickets"></div>

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
    $.get('api/soporte.php?tickets=1')
        .done(function(data) {
            console.log('Respuesta de la API (soporte):', data);
            
            if (!Array.isArray(data)) {
                const msg = data.error || 'Error desconocido';
                $('#tickets').html('<div class="error"> ' + escapeHtml(msg) + '</div>');
                return;
            }
            
            if (data.length === 0) {
                $('#tickets').html('<p style="color:#6b7280;">No hay tickets.</p>');
                return;
            }
            
            let html = '';
            data.forEach(t => {
                const asunto = escapeHtml(t.asunto);
                const mensaje = escapeHtml(t.mensaje);
                const estado = escapeHtml(t.estado);
                const respuesta = t.respuesta ? escapeHtml(t.respuesta) : '';
                const fechaCreacion = t.fecha_creacion ? escapeHtml(t.fecha_creacion) : '';
                const fechaRespuesta = t.fecha_respuesta ? escapeHtml(t.fecha_respuesta) : '';
                const turistaNombre = t.turista_nombre ? escapeHtml(t.turista_nombre) : 'Usuario #' + t.turista_id;
                
                let estadoColor = '#f59e0b';
                if (estado === 'en proceso') estadoColor = '#3b82f6';
                else if (estado === 'cerrado') estadoColor = '#6b7280';
                else if (estado === 'abierto') estadoColor = '#10b981';
                
                html += `
                    <div style="background: white; border-radius: 16px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #f0f0f0;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                            <h4 style="margin: 0; color: #1f2937;">${asunto}</h4>
                            <span style="background: ${estadoColor}; color: white; padding: 0.2rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">${estado.toUpperCase()}</span>
                        </div>
                        <p style="color: #6b7280; font-size: 0.85rem; margin: 0.3rem 0;"> ${turistaNombre} |  ${fechaCreacion}</p>
                        <p style="margin: 0.5rem 0; color: #1f2937;">${mensaje}</p>
                        ${respuesta ? `
                            <div style="background: #f0f9ff; border-left: 4px solid #3b82f6; padding: 0.8rem; border-radius: 8px; margin-top: 0.8rem;">
                                <p style="font-weight: 500; color: #1e40af; margin: 0 0 0.3rem 0;"> Respuesta:</p>
                                <p style="margin: 0; color: #1f2937;">${respuesta}</p>
                                ${fechaRespuesta ? `<p style="font-size: 0.8rem; color: #6b7280; margin: 0.3rem 0 0 0;"> ${fechaRespuesta}</p>` : ''}
                            </div>
                        ` : ''}
                        <div style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            ${estado !== 'cerrado' ? `
                                <button onclick="responderTicket(${t.id})" style="background:#3b82f6; color:white; border:none; padding:0.4rem 1rem; border-radius:20px; cursor:pointer;"> Responder</button>
                                <button onclick="cerrarTicket(${t.id})" style="background:#ef4444; color:white; border:none; padding:0.4rem 1rem; border-radius:20px; cursor:pointer;"> Cerrar</button>
                            ` : ''}
                            <!-- Botón Eliminar para todos los tickets (solo soporte/admin) -->
                            <button onclick="eliminarTicket(${t.id})" style="background:#6b7280; color:white; border:none; padding:0.4rem 1rem; border-radius:20px; cursor:pointer;"> Eliminar</button>
                        </div>
                        <div id="respuesta-${t.id}" style="display:none; margin-top: 1rem;">
                            <textarea id="respuesta-text-${t.id}" style="width:100%; padding:0.5rem; border-radius:10px; border:1px solid #d1d5db; min-height:60px;" placeholder="Escribe tu respuesta..."></textarea>
                            <button onclick="enviarRespuesta(${t.id})" style="background:#10b981; color:white; border:none; padding:0.4rem 1rem; border-radius:20px; cursor:pointer; margin-top:0.3rem;"> Enviar respuesta</button>
                        </div>
                    </div>
                `;
            });
            $('#tickets').html(html);
        })
        .fail(function(jqXHR) {
            console.error('Error en la petición:', jqXHR);
            let msg = 'Error de conexión con el servidor.';
            try {
                const resp = JSON.parse(jqXHR.responseText);
                if (resp.error) msg = resp.error;
            } catch(e) {}
            $('#tickets').html('<div class="error">' + escapeHtml(msg) + '</div>');
        });
}

function responderTicket(id) {
    const div = document.getElementById('respuesta-' + id);
    if (div.style.display === 'none') {
        div.style.display = 'block';
        document.getElementById('respuesta-text-' + id).focus();
    } else {
        div.style.display = 'none';
    }
}

function enviarRespuesta(id) {
    const texto = document.getElementById('respuesta-text-' + id).value.trim();
    if (!texto) {
        alert('Escribe una respuesta antes de enviar.');
        return;
    }
    $.post('api/soporte.php', { 
        responder: 1, 
        ticket_id: id, 
        respuesta: texto 
    }, function(res) {
        if (res.error) {
            alert('❌ ' + res.error);
        } else {
            alert('✅ ' + res.mensaje);
            cargarTickets();
        }
    }, 'json').fail(function() {
        alert('Error al enviar la respuesta');
    });
}

function cerrarTicket(id) {
    if (!confirm('¿Cerrar este ticket?')) return;
    $.post('api/soporte.php', { cerrar: id }, function(res) {
        if (res.error) alert('❌ ' + res.error);
        else {
            alert('✅ ' + res.mensaje);
            cargarTickets();
        }
    }, 'json').fail(function() {
        alert('Error al cerrar el ticket');
    });
}

//  Nueva función para eliminar ticket
function eliminarTicket(id) {
    if (!confirm('¿Eliminar este ticket permanentemente? Esta acción no se puede deshacer.')) return;
    $.ajax({
        url: 'api/soporte.php',
        method: 'DELETE',
        data: { ticket_id: id },
        success: function(res) {
            if (res.error) alert('❌ ' + res.error);
            else {
                alert('✅ ' + res.mensaje);
                cargarTickets();
            }
        },
        error: function(xhr) {
            const resp = xhr.responseJSON;
            alert(resp ? resp.error : 'Error al eliminar');
        }
    });
}

cargarTickets();
</script>