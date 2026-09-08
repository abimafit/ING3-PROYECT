<h3>Soporte técnico</h3>

<!-- Formulario para enviar ticket -->
<div style="background: var(--rw-white); border:1px solid var(--rw-gray-200); padding: 1.5rem; border-radius: var(--rw-radius-lg); margin-bottom: 2rem; box-shadow: var(--rw-shadow-sm);">
    <h4>Enviar nuevo ticket</h4>
    <form id="ticketForm">
        <div style="margin-bottom: 1rem;">
            <label for="asunto" style="display: block; font-weight: 500; margin-bottom: 0.3rem;">Asunto</label>
            <input type="text" name="asunto" id="asunto" placeholder="Breve descripción" required class="form-control">
        </div>
        <div style="margin-bottom: 1rem;">
            <label for="mensaje" style="display: block; font-weight: 500; margin-bottom: 0.3rem;">Mensaje</label>
            <textarea name="mensaje" id="mensaje" rows="4" placeholder="Describe tu problema en detalle" required class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Enviar ticket</button>
    </form>
    <div id="respuesta" style="margin-top: 0.5rem;"></div>
</div>

<!-- Lista de tickets del usuario -->
<h4>Mis tickets</h4>
<div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
    <select id="ordenTickets" class="form-control" style="max-width:220px;">
        <option value="fecha_desc">Más recientes</option>
        <option value="fecha_asc">Más antiguos</option>
        <option value="estado_asc">Estado A-Z</option>
        <option value="estado_desc">Estado Z-A</option>
    </select>
    <input type="text" id="filtroTickets" placeholder="Buscar por asunto o mensaje..." class="form-control" style="flex:1;">
    <button type="button" onclick="aplicarFiltrosTickets()" class="btn btn-primary" style="align-self:flex-end;">Filtrar</button>
    <span class="toolbar-count" id="filtroCount" style="align-self:flex-end; margin-left:0;"></span>
</div>
<div id="listaTickets">
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

// Cargar tickets del turista
function cargarTickets() {
    $.get('api/soporte.php?tickets=1')
        .done(function(data) {

            // Verificar si la respuesta es un array
            if (!Array.isArray(data)) {
                // Si no es array, probablemente es un error
                const msg = data.error || 'Error desconocido';
                $('#listaTickets').html('<div class="error">' + escapeHtml(msg) + '</div>');
                return;
            }

            ticketsData = data;
            if (ticketsData.length === 0) {
                $('#listaTickets').html('<p style="color:#6b7280;">No has enviado tickets aún.</p>');
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
            $('#listaTickets').html('<div class="error">' + escapeHtml(msg) + '</div>');
        });
}

function ticketsFiltrados() {
    var q = ($('#filtroTickets').val() || '').toLowerCase().trim();
    var orden = $('#ordenTickets').val();
    var list = ticketsData.filter(t => {
        if (!q) return true;
        return String(t.asunto || '').toLowerCase().indexOf(q) !== -1 ||
               String(t.mensaje || '').toLowerCase().indexOf(q) !== -1 ||
               String(t.respuesta || '').toLowerCase().indexOf(q) !== -1;
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
        $('#listaTickets').html('<p style="color:#6b7280;">No se encontraron tickets con esos criterios.</p>');
        return;
    }
    let html = '';
    data.forEach(t => {
        const estado = escapeHtml(t.estado);
        const estadoColor = colorEstado(String(t.estado || '').toLowerCase());
        html += `
            <div class="ticket" style="--ticket-color:${estadoColor};">
                <div class="ticket__main">
                    <div class="ticket__head">
                        <strong class="ticket__title">${escapeHtml(t.asunto)}</strong>
                        <span class="ticket__badge">${estado}</span>
                    </div>
                    <p class="ticket__body">${escapeHtml(t.mensaje)}</p>
                    <p class="ticket__meta">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>Enviado: ${escapeHtml(t.fecha_creacion)}</span>
                    </p>
                    ${t.respuesta ? `
                        <div class="ticket__respuesta">
                            <p class="ticket__respuesta-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Respuesta del soporte:</p>
                            <p>${escapeHtml(t.respuesta)}</p>
                            ${t.fecha_respuesta ? `<time>${escapeHtml(t.fecha_respuesta)}</time>` : ''}
                        </div>
                    ` : ''}
                </div>
                <div class="ticket__stub">
                    <span class="ticket__stub-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg></span>
                    <span class="ticket__num">#${t.id}</span>
                    <span class="ticket__fecha">${String(escapeHtml(t.fecha_creacion) || '').slice(0, 10)}</span>
                    <span class="ticket__hole ticket__hole--top"></span>
                    <span class="ticket__hole ticket__hole--bottom"></span>
                </div>
            </div>
        `;
    });
    $('#listaTickets').html(html);
}

// Enviar nuevo ticket
function aplicarFiltrosTickets() {
    renderTickets();
}

$('#ticketForm').submit(function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    $.post('api/soporte.php', formData)
        .done(function(res) {
            if (res.error) {
                showAlert(res.error, 'error');
            } else {
                showAlert(res.mensaje, 'success');
                $('#ticketForm')[0].reset();
                cargarTickets();
            }
        })
        .fail(function() {
            showAlert('Error de conexión con el servidor.', 'error');
        });
});

cargarTickets();
</script>