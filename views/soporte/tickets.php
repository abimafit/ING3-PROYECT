<h3>Tickets de soporte</h3>

<div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
    <select id="ordenTickets" class="form-control" style="max-width:220px;">
        <option value="fecha_desc">Más recientes</option>
        <option value="fecha_asc">Más antiguos</option>
        <option value="estado_asc">Estado A-Z</option>
        <option value="estado_desc">Estado Z-A</option>
    </select>
    <input type="text" id="filtroTickets" placeholder="Buscar por asunto, mensaje o usuario..." class="form-control" style="flex:1;">
    <button type="button" onclick="aplicarFiltrosTickets()" class="btn btn-primary" style="align-self:flex-end;">Filtrar</button>
    <span class="toolbar-count" id="filtroCount" style="align-self:flex-end; margin-left:0;"></span>
</div>

<div id="tickets">
    <div class="loading">Cargando tickets...</div>
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

var ticketsData = [];

function colorEstado(estado) {
    if (estado === 'abierto') return '#f59e0b';
    if (estado === 'respondido') return '#10b981';
    if (estado === 'cerrado') return '#6b7280';
    return '#f59e0b';
}

function cargarTickets() {
    $('#tickets').html('<div class="loading">Cargando tickets...</div>');
    $.get('api/soporte.php?tickets=1')
        .done(function(data) {
            if (!Array.isArray(data)) {
                const msg = data.error || 'Error desconocido';
                $('#tickets').html('<div class="error"> ' + escapeHtml(msg) + '</div>');
                return;
            }
            ticketsData = data;
            if (ticketsData.length === 0) {
                $('#tickets').html('<p style="color:#6b7280;">No hay tickets.</p>');
                return;
            }
            renderTickets();
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

function ticketsFiltrados() {
    var q = ($('#filtroTickets').val() || '').toLowerCase().trim();
    var orden = $('#ordenTickets').val();
    var list = ticketsData.filter(t => {
        if (!q) return true;
        return String(t.asunto || '').toLowerCase().indexOf(q) !== -1 ||
               String(t.mensaje || '').toLowerCase().indexOf(q) !== -1 ||
               String(t.turista_nombre || '').toLowerCase().indexOf(q) !== -1 ||
               String(t.estado || '').toLowerCase().indexOf(q) !== -1;
    });
    list.sort(function(a, b) {
        var fa = String(a.fecha_creacion || ''), fb = String(b.fecha_creacion || '');
        if (orden === 'fecha_asc') return fa < fb ? -1 : (fa > fb ? 1 : 0);
        if (orden === 'estado_asc') return String(a.estado || '').localeCompare(String(b.estado || ''));
        if (orden === 'estado_desc') return String(b.estado || '').localeCompare(String(a.estado || ''));
        return fa > fb ? -1 : (fa < fb ? 1 : 0);
    });
    return list;
}

function renderTickets() {
    var data = ticketsFiltrados();
    $('#filtroCount').text(data.length + ' de ' + ticketsData.length + ' tickets');
    if (data.length === 0) {
        $('#tickets').html('<p style="color:#6b7280;">No se encontraron tickets con esos criterios.</p>');
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
        const estadoColor = colorEstado(String(t.estado || '').toLowerCase());

        html += `
            <div class="ticket" style="--ticket-color:${estadoColor};">
                <div class="ticket__main">
                    <div class="ticket__head">
                        <h4 class="ticket__title">${asunto}</h4>
                        <span class="ticket__badge">${estado.toUpperCase()}</span>
                    </div>
                    <p class="ticket__meta">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>${turistaNombre}</span>
                        <span>|</span>
                        <span>${fechaCreacion}</span>
                    </p>
                    <p class="ticket__body">${mensaje}</p>
                    ${respuesta ? `
                        <div class="ticket__respuesta">
                            <p class="ticket__respuesta-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Respuesta:</p>
                            <p>${respuesta}</p>
                            ${fechaRespuesta ? `<time>${fechaRespuesta}</time>` : ''}
                        </div>
                    ` : ''}
                    <div class="ticket__acciones">
                        ${estado !== 'cerrado' ? `
                            <button onclick="responderTicket(${t.id})" class="btn-small" style="background:#10b981;border-color:#10b981;">Responder</button>
                            <button onclick="cerrarTicket(${t.id})" class="btn-small" style="background:#6b7280;border-color:#6b7280;">Cerrar</button>
                        ` : ''}
                        <button onclick="eliminarTicket(${t.id})" class="btn-small" style="background:#ef4444;border-color:#ef4444;">Eliminar</button>
                    </div>
                    <div id="respuesta-${t.id}" style="display:none; margin-top:1rem;">
                        <textarea id="respuesta-text-${t.id}" class="form-control" style="min-height:60px;" placeholder="Escribe tu respuesta..."></textarea>
                        <button onclick="enviarRespuesta(${t.id})" class="btn-small" style="background:#10b981;border-color:#10b981;margin-top:0.4rem;">Enviar respuesta</button>
                    </div>
                </div>
                <div class="ticket__stub">
                    <span class="ticket__stub-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg></span>
                    <span class="ticket__num">#${t.id}</span>
                    <span class="ticket__fecha">${String(fechaCreacion || '').slice(0, 10)}</span>
                    <span class="ticket__hole ticket__hole--top"></span>
                    <span class="ticket__hole ticket__hole--bottom"></span>
                </div>
            </div>
        `;
    });
    $('#tickets').html(html);
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
        showAlert('Escribe una respuesta antes de enviar.', 'warning');
        return;
    }
    $.post('api/soporte.php', { 
        responder: 1, 
        ticket_id: id, 
        respuesta: texto 
    }, function(res) {
        if (res.error) {
            showAlert(res.error, 'error');
        } else {
            showAlert(res.mensaje, 'success');
            cargarTickets();
        }
    }, 'json').fail(function() {
        showAlert('Error al enviar la respuesta', 'error');
    });
}

function cerrarTicket(id) {
    showConfirm('¿Cerrar este ticket?', function() {
        $.post('api/soporte.php', { cerrar: id }, function(res) {
            if (res.error) showAlert(res.error, 'error');
            else {
                showAlert(res.mensaje, 'success');
                cargarTickets();
            }
        }, 'json').fail(function() {
            showAlert('Error al cerrar el ticket', 'error');
        });
    });
}

function eliminarTicket(id) {
    showConfirm('¿Eliminar este ticket permanentemente? Esta acción no se puede deshacer.', function() {
        $.ajax({
            url: 'api/soporte.php',
            method: 'DELETE',
            data: { ticket_id: id },
            success: function(res) {
                if (res.error) showAlert(res.error, 'error');
                else {
                    showAlert(res.mensaje, 'success');
                    cargarTickets();
                }
            },
            error: function(xhr) {
                const resp = xhr.responseJSON;
                showAlert(resp ? resp.error : 'Error al eliminar', 'error');
            }
        });
    });
}

function aplicarFiltrosTickets() {
    renderTickets();
}

cargarTickets();
</script>