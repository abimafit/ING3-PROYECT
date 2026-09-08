<h3>👥 Usuarios del sistema</h3>
<div id="usuariosContainer">
    <p style="color:#6b7280;">Cargando usuarios...</p>
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

function cargarUsuarios() {
    $.get('api/admin.php?usuarios=1')
        .done(function(data) {
            if (!Array.isArray(data)) {
                $('#usuariosContainer').html('<div class="error">Error al cargar usuarios</div>');
                return;
            }
            if (data.length === 0) {
                $('#usuariosContainer').html('<p>No hay usuarios registrados.</p>');
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
                            <button onclick="verificarEmpresa(${u.id})" style="background:#2563eb; color:white; border:none; padding:0.2rem 0.6rem; border-radius:12px; cursor:pointer; font-size:0.7rem; margin-top:0.3rem;">Verificar manual</button>
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
        })
        .fail(function() {
            $('#usuariosContainer').html('<div class="error">Error al cargar usuarios</div>');
        });
}

function verificarEmpresa(usuario_id) {
    if (!confirm('¿Verificar manualmente esta empresa?')) return;
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

cargarUsuarios();
</script>