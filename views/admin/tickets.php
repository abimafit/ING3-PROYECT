<h3><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="var(--rw-red)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-4px;margin-right:8px;"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>Todos los tickets de soporte</h3>

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

<div id="ticketsContainer">
    <p style="color:#6b7280;">Cargando tickets...</p>
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
    $.get('api/admin.php?tickets=1')
        .done(function(data) {
            if (!Array.isArray(data)) {
                $('#ticketsContainer').html('<div class="error">Error al cargar tickets</div>');
                return;
            }
            ticketsData = data;
            if (ticketsData.length === 0) {
                $('#ticketsContainer').html('<p style="color:#6b7280;">No hay tickets en el sistema.</p>');
                return;
            }
            renderTickets();
        })
        .fail(function() {
            $('#ticketsContainer').html('<div class="error">Error al cargar tickets</div>');
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
        if (orden === 'fecha_asc') {
            return fa < fb ? -1 : (fa > fb ? 1 : 0);
        }
        if (orden === 'estado_asc') {
            return String(a.estado || '').localeCompare(String(b.estado || ''));
        }
        if (orden === 'estado_desc') {
            return String(b.estado || '').localeCompare(String(a.estado || ''));
        }
        return fa > fb ? -1 : (fa < fb ? 1 : 0);
    });
    return list;
}

function renderTickets() {
    var data = ticketsFiltrados();
    $('#filtroCount').text(data.length + ' de ' + ticketsData.length + ' tickets');
    if (data.length === 0) {
        $('#ticketsContainer').html('<p style="color:#6b7280;">No se encontraron tickets con esos criterios.</p>');
        return;
    }
    let html = '';
    data.forEach(t => {
        const estado = escapeHtml(t.estado);
        const estadoColor = colorEstado(String(t.estado || '').toLowerCase());
        const respuesta = t.respuesta ? escapeHtml(t.respuesta) : '';
        html += `
            <div class="ticket" style="--ticket-color:${estadoColor};">
                <div class="ticket__main">
                    <div class="ticket__head">
                        <strong class="ticket__title">${escapeHtml(t.asunto)}</strong>
                        <span class="ticket__badge">${estado}</span>
                    </div>
                    <p class="ticket__meta">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>${escapeHtml(t.turista_nombre)}</span>
                        <span>|</span>
                        <span>${escapeHtml(t.fecha_creacion)}</span>
                    </p>
                    <p class="ticket__body">${escapeHtml(t.mensaje)}</p>
                    ${respuesta ? `<div class="ticket__respuesta">
                        <p class="ticket__respuesta-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Respuesta:</p>
                        <p>${respuesta}</p>
                        ${t.fecha_respuesta ? `<time>${escapeHtml(t.fecha_respuesta)}</time>` : ''}
                    </div>` : ''}
                    <div class="ticket__acciones">
                        ${estado !== 'cerrado' ? `
                            <button onclick="responderTicket(${t.id})" class="btn-small" style="background:#10b981;border-color:#10b981;">Responder</button>
                            <button onclick="cerrarTicket(${t.id})" class="btn-small" style="background:#6b7280;border-color:#6b7280;">Cerrar</button>
                        ` : ''}
                        <button onclick="eliminarTicket(${t.id})" class="btn-small" style="background:#ef4444;border-color:#ef4444;">Eliminar</button>
                    </div>
                    <div id="respuesta-${t.id}" style="display:none; margin-top:1rem;">
                        <textarea id="respuesta-text-${t.id}" class="form-control" style="min-height:60px;" placeholder="Escribe tu respuesta..."></textarea>
                        <button onclick="enviarRespuesta(${t.id})" class="btn-small" style="background:#10b981;border-color:#10b981;margin-top:0.4rem;">Enviar</button>
                    </div>
                </div>
                <div class="ticket__stub">
                    <span class="ticket__stub-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg></span>
                    <span class="ticket__num">#${t.id}</span>
                    <span class="ticket__fecha">${escapeHtml(String(t.fecha_creacion || '').slice(0, 10))}</span>
                    <span class="ticket__hole ticket__hole--top"></span>
                    <span class="ticket__hole ticket__hole--bottom"></span>
                </div>
            </div>
        `;
    });
    $('#ticketsContainer').html(html);
}

function responderTicket(id) {
    const div = document.getElementById('respuesta-' + id);
    div.style.display = div.style.display === 'none' ? 'block' : 'none';
}

function enviarRespuesta(id) {
    const texto = document.getElementById('respuesta-text-' + id).value.trim();
    if (!texto) { showAlert('Escribe una respuesta', 'warning'); return; }
    $.post('api/soporte.php', { responder: 1, ticket_id: id, respuesta: texto })
        .done(function(res) {
            if (res.error) showAlert(res.error, 'error');
            else { showAlert(res.mensaje, 'success'); cargarTickets(); }
        })
        .fail(function() { showAlert('Error al enviar', 'error'); });
}

function cerrarTicket(id) {
    showConfirm('¿Cerrar este ticket?', function() {
        $.post('api/soporte.php', { cerrar: id })
            .done(function(res) {
                if (res.error) showAlert(res.error, 'error');
                else { showAlert(res.mensaje, 'success'); cargarTickets(); }
            })
            .fail(function() { showAlert('Error al cerrar', 'error'); });
    });
}

function eliminarTicket(id) {
    showConfirm('¿Eliminar este ticket permanentemente?', function() {
        $.ajax({
            url: 'api/soporte.php',
            method: 'DELETE',
            data: { ticket_id: id },
            success: function(res) {
                if (res.error) showAlert(res.error, 'error');
                else { showAlert(res.mensaje, 'success'); cargarTickets(); }
            },
            error: function() { showAlert('Error al eliminar', 'error'); }
        });
    });
}

function aplicarFiltrosTickets() {
    renderTickets();
}

cargarTickets();
</script>