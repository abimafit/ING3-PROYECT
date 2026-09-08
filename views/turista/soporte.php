<h3>Soporte técnico</h3>

<!-- Formulario para enviar ticket -->
<div style="background: #f8fafc; padding: 1.5rem; border-radius: 16px; margin-bottom: 2rem;">
    <h4>Enviar nuevo ticket</h4>
    <form id="ticketForm">
        <div style="margin-bottom: 1rem;">
            <label for="asunto" style="display: block; font-weight: 500; margin-bottom: 0.3rem;">Asunto</label>
            <input type="text" name="asunto" id="asunto" placeholder="Breve descripción" required style="width:100%; padding:0.7rem; border-radius:10px; border:1px solid #d1d5db;">
        </div>
        <div style="margin-bottom: 1rem;">
            <label for="mensaje" style="display: block; font-weight: 500; margin-bottom: 0.3rem;">Mensaje</label>
            <textarea name="mensaje" id="mensaje" rows="4" placeholder="Describe tu problema en detalle" required style="width:100%; padding:0.7rem; border-radius:10px; border:1px solid #d1d5db;"></textarea>
        </div>
        <button type="submit" style="background: #2563eb; color: white; border: none; padding: 0.7rem 2rem; border-radius: 40px; font-weight: 600; cursor: pointer;">Enviar ticket</button>
    </form>
    <div id="respuesta" style="margin-top: 0.5rem;"></div>
</div>

<!-- Lista de tickets del usuario -->
<h4>Mis tickets</h4>
<div id="listaTickets">
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

// Cargar tickets del turista
function cargarTickets() {
    $.get('api/soporte.php?tickets=1')
        .done(function(data) {
            console.log('Respuesta de la API:', data); // 👈 Para depurar
            
            // Verificar si la respuesta es un array
            if (!Array.isArray(data)) {
                // Si no es array, probablemente es un error
                const msg = data.error || 'Error desconocido';
                $('#listaTickets').html('<div class="error">' + escapeHtml(msg) + '</div>');
                return;
            }
            
            if (data.length === 0) {
                $('#listaTickets').html('<p style="color:#6b7280;">No has enviado tickets aún.</p>');
                return;
            }
            
            let html = '';
            data.forEach(t => {
                const estado = escapeHtml(t.estado);
                const estadoColor = estado === 'cerrado' ? '#ef4444' : (estado === 'en proceso' ? '#f59e0b' : '#10b981');
                html += `
                    <div style="background: white; border-radius: 16px; padding: 1.2rem; margin-bottom: 1rem; border-left: 4px solid ${estadoColor}; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                            <strong style="font-size: 1.1rem;">${escapeHtml(t.asunto)}</strong>
                            <span style="background: ${estadoColor}; color: white; padding: 0.2rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">${estado}</span>
                        </div>
                        <p style="margin: 0.5rem 0; color: #374151;">${escapeHtml(t.mensaje)}</p>
                        <p style="font-size: 0.8rem; color: #6b7280;">Enviado: ${escapeHtml(t.fecha_creacion)}</p>
                        ${t.respuesta ? `
                            <div style="background: #f0fdf4; border-radius: 10px; padding: 0.8rem; margin-top: 0.5rem; border-left: 3px solid #22c55e;">
                                <p style="font-weight: 500; color: #166534; margin-bottom: 0.2rem;">Respuesta del soporte:</p>
                                <p style="margin: 0; color: #1f2937;">${escapeHtml(t.respuesta)}</p>
                                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.2rem;">${escapeHtml(t.fecha_respuesta)}</p>
                            </div>
                        ` : ''}
                    </div>
                `;
            });
            $('#listaTickets').html(html);
        })
        .fail(function(jqXHR) {
            console.error('Error en la petición:', jqXHR);
            let msg = 'Error de conexión con el servidor.';
            try {
                const resp = JSON.parse(jqXHR.responseText);
                if (resp.error) msg = resp.error;
            } catch(e) {}
            $('#listaTickets').html('<div class="error">' + escapeHtml(msg) + '</div>');
        });
}

// Enviar nuevo ticket
$('#ticketForm').submit(function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    $.post('api/soporte.php', formData)
        .done(function(res) {
            if (res.error) {
                $('#respuesta').html('<div class="error">' + escapeHtml(res.error) + '</div>');
            } else {
                $('#respuesta').html('<div class="success">' + escapeHtml(res.mensaje) + '</div>');
                $('#ticketForm')[0].reset();
                cargarTickets(); // Recargar lista
                setTimeout(function() { $('#respuesta').empty(); }, 4000);
            }
        })
        .fail(function() {
            $('#respuesta').html('<div class="error"> Error de conexión con el servidor.</div>');
        });
});

cargarTickets();
</script>

<style>
.success { background: #d1fae5; color: #065f46; padding: 0.5rem 1rem; border-radius: 10px; border-left: 4px solid #10b981; }
.error { background: #fee2e2; color: #b91c1c; padding: 0.5rem 1rem; border-radius: 10px; border-left: 4px solid #ef4444; }
</style>