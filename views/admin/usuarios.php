<h3><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="var(--rw-red)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-4px;margin-right:8px;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Usuarios del sistema</h3>
<div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
    <select id="ordenUsuarios" class="form-control" style="max-width:220px;">
        <option value="id_asc">ID ascendente</option>
        <option value="id_desc">ID descendente</option>
        <option value="nombre_asc">Nombre A-Z</option>
        <option value="nombre_desc">Nombre Z-A</option>
        <option value="rol_asc">Rol</option>
        <option value="ciudad_asc">Ciudad A-Z</option>
    </select>
    <input type="text" id="filtroUsuarios" placeholder="Buscar por nombre, email, ciudad o rol..." class="form-control" style="flex:1;">
    <button type="button" onclick="aplicarFiltrosUsuarios()" class="btn btn-primary" style="align-self:flex-end;">Filtrar</button>
    <span class="toolbar-count" id="filtroCount" style="align-self:flex-end; margin-left:0;"></span>
</div>
<div id="usuariosContainer">
    <p style="color:#6b7280;">Cargando usuarios...</p>
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

var usuariosData = [];

function cargarUsuarios() {
    $.get('api/admin.php?usuarios=1')
        .done(function(data) {
            if (!Array.isArray(data)) {
                $('#usuariosContainer').html('<div class="error">Error al cargar usuarios</div>');
                return;
            }
            usuariosData = data;
            if (usuariosData.length === 0) {
                $('#usuariosContainer').html('<p>No hay usuarios registrados.</p>');
                return;
            }
            renderUsuarios();
        })
        .fail(function() {
            $('#usuariosContainer').html('<div class="error">Error al cargar usuarios</div>');
        });
}

function usuariosFiltrados() {
    var q = ($('#filtroUsuarios').val() || '').toLowerCase().trim();
    var orden = $('#ordenUsuarios').val();
    var list = usuariosData.filter(u => {
        if (!q) return true;
        return String(u.nombre || '').toLowerCase().indexOf(q) !== -1 ||
               String(u.email || '').toLowerCase().indexOf(q) !== -1 ||
               String(u.ciudad || '').toLowerCase().indexOf(q) !== -1 ||
               String(u.rol || '').toLowerCase().indexOf(q) !== -1 ||
               String(u.id).indexOf(q) !== -1;
    });
    list.sort(function(a, b) {
        if (orden === 'id_desc') return b.id - a.id;
        if (orden === 'nombre_asc') return String(a.nombre || '').localeCompare(String(b.nombre || ''));
        if (orden === 'nombre_desc') return String(b.nombre || '').localeCompare(String(a.nombre || ''));
        if (orden === 'rol_asc') return String(a.rol || '').localeCompare(String(b.rol || ''));
        if (orden === 'ciudad_asc') return String(a.ciudad || '').localeCompare(String(b.ciudad || ''));
        return a.id - b.id;
    });
    return list;
}

function renderUsuarios() {
    var data = usuariosFiltrados();
    $('#filtroCount').text(data.length + ' de ' + usuariosData.length + ' usuarios');
    if (data.length === 0) {
        $('#usuariosContainer').html('<p style="color:#6b7280;">No se encontraron usuarios con esos criterios.</p>');
        return;
    }
    let html = '<table class="admin-table">';
    html += '<thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Ciudad</th><th>Estado</th><th>Verificación</th><th>Acciones</th></tr></thead><tbody>';
    data.forEach(u => {
        const baneado = parseInt(u.baneado) === 1;
        const estadoText = baneado ? ' Baneado' : ' Activo';
        const estadoColor = baneado ? 'badge-danger' : 'badge-success';
        const verificado = parseInt(u.verificado) === 1;
        const esCompania = u.rol === 'compania';

        // Columna de verificación
        let verificacionHtml = '';
        if (esCompania) {
            if (verificado) {
                verificacionHtml = '<span class="badge badge-success"> Verificada</span>';
            } else {
                verificacionHtml = `
                    <span class="badge badge-warning"> Pendiente</span>
                    <button onclick="verificarEmpresa(${u.id})" style="background:var(--rw-red); color:white; border:none; padding:0.2rem 0.6rem; border-radius:12px; cursor:pointer; font-size:0.7rem; margin-top:0.3rem;">Verificar manual</button>
                `;
            }
        } else {
            verificacionHtml = '<span class="badge">-</span>';
        }

        html += `<tr>
            <td>${u.id}</td>
            <td>${escapeHtml(u.nombre)}</td>
            <td>${escapeHtml(u.email)}</td>
            <td>${escapeHtml(u.rol)}</td>
            <td>${escapeHtml(u.ciudad || '-')}</td>
            <td><span class="badge ${estadoColor}">${estadoText}</span></td>
            <td>${verificacionHtml}</td>
            <td>
                <button onclick="toggleBan(${u.id}, ${baneado ? 0 : 1})" style="background:${baneado ? '#10b981' : '#ef4444'}; color:white; border:none; padding:0.3rem 0.8rem; border-radius:12px; cursor:pointer; font-size:0.7rem;">
                    ${baneado ? 'Desbanear' : 'Banear'}
                </button>
            </td>
        </tr>`;
    });
    html += '</tbody></table>';
    $('#usuariosContainer').html(html);
}

function verificarEmpresa(usuario_id) {
    showConfirm('¿Verificar manualmente esta empresa?', function() {
        $.post('api/admin.php', { verificar_empresa: 1, usuario_id: usuario_id })
            .done(function(res) {
                if (res.error) {
                    showAlert(res.error, 'error');
                } else {
                    showAlert(res.mensaje, 'success');
                    cargarUsuarios();
                }
            })
            .fail(function(jqXHR) {
                handleAjaxError(jqXHR);
            });
    });
}

function toggleBan(usuario_id, baneado) {
    const accion = baneado ? 'banear' : 'desbanear';
    showConfirm(`¿${accion} a este usuario?`, function() {
        $.post('api/admin.php', {
                banear_usuario: 1,
                usuario_id,
                baneado,
                motivo: 'Acción del administrador'
            })
            .done(function(res) {
                if (res.error) {
                    showAlert(res.error, 'error');
                } else {
                    showAlert(res.mensaje, 'success');
                    cargarUsuarios();
                }
            })
            .fail(function(jqXHR) {
                handleAjaxError(jqXHR);
            });
    });
}

function aplicarFiltrosUsuarios() {
    renderUsuarios();
}

cargarUsuarios();
</script>