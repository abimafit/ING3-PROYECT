<h3><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="var(--rw-red)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-4px;margin-right:8px;"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>Gestión de flota</h3>

<!-- Encabezado: botón para abrir el modal de agregar vehículo -->
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; background: var(--rw-white); border:1px solid var(--rw-gray-200); padding:1.5rem; border-radius: var(--rw-radius-lg); margin-bottom:2rem; box-shadow: var(--rw-shadow-sm);">
    <div>
        <h4 style="margin-bottom:0.25rem;">Vehículos de tu flota</h4>
        <p style="color:var(--rw-gray-500); font-size:0.88rem;">Agrega autos para que aparezcan en el catálogo de los turistas.</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="abrirModalVehiculo()">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:6px;"><path d="M12 5v14"/><path d="M5 12h14"/></svg>Agregar vehículo
    </button>
</div>

<!-- Sección: Últimos vehículos añadidos -->
<div style="margin-bottom:2rem;">
    <h4>Últimos vehículos añadidos</h4>
    <div id="ultimosVehiculos" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
        <!-- Se carga vía AJAX -->
    </div>
</div>

<!-- Sección: Vehículos actuales (todos) con filtros -->
<div>
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem; margin-bottom:0.6rem;">
        <h4>Vehículos actuales</h4>
        <button type="button" class="btn btn-outline" onclick="abrirModalVehiculo()">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:6px;"><path d="M12 5v14"/><path d="M5 12h14"/></svg>Agregar vehículo
        </button>
    </div>

    <!-- Barra de filtros: state, búsqueda, orden y botón Filtrar -->
    <div class="table-toolbar">
        <select id="filtroEstado" class="form-control" style="max-width:220px;">
            <option value="">Todos los estados</option>
            <option value="disponible">Disponible</option>
            <option value="reservado">Reservado</option>
        </select>
        <input type="text" id="buscarVehiculo" class="form-control" placeholder="Buscar por marca o modelo..." style="flex:1; min-width:160px; max-width:none;">
        <select id="ordenVehiculos" class="form-control" style="max-width:240px;">
            <option value="reciente_desc">Recientes primero</option>
            <option value="antiguo_asc">Antiguos primero</option>
            <option value="marca_asc">Marca A→Z</option>
            <option value="marca_desc">Marca Z→A</option>
            <option value="precio_asc">Precio: menor a mayor</option>
            <option value="precio_desc">Precio: mayor a menor</option>
        </select>
        <button type="button" class="btn btn-primary" onclick="aplicarFiltros()">Filtrar</button>
        <span class="toolbar-count" id="flotaCount">0 vehículos</span>
    </div>

    <div id="listaVehiculos" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
        <!-- Se carga vía AJAX -->
    </div>
</div>

<!-- Modal: Agregar vehículo -->
<div class="flota-modal-overlay" id="vehiculoModalOverlay">
    <div class="flota-modal" role="dialog" aria-modal="true" aria-labelledby="flotaModalTitle">
        <div class="flota-modal-header">
            <h4 id="flotaModalTitle">Agregar nuevo vehículo</h4>
            <button type="button" class="flota-modal-close" onclick="cerrarModalVehiculo()" aria-label="Cerrar">×</button>
        </div>
        <form id="vehiculoForm">
            <div class="flota-modal-grid">
                <input type="text" name="marca" placeholder="Marca" required class="form-control">
                <input type="text" name="modelo" placeholder="Modelo" required class="form-control">
                <input type="number" name="anio" placeholder="Año" class="form-control">
                <input type="number" step="0.01" name="precio_por_dia" placeholder="Precio por día (USD)" required class="form-control">
                <input type="text" name="imagen_url" id="flotaImagenUrl" placeholder="URL de imagen (opcional)" class="form-control" style="grid-column:1 / -1;">
            </div>

            <label class="flota-preview-label">Vista previa de la imagen</label>
            <div class="flota-preview" id="flotaPreview">
                <img id="flotaPreviewImg" src="" alt="Vista previa de la imagen" style="display:none;">
                <span id="flotaPreviewPlaceholder">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                </span>
            </div>

            <div class="flota-modal-actions">
                <button type="button" class="btn btn-outline" onclick="cerrarModalVehiculo()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Agregar vehículo</button>
            </div>
        </form>
    </div>
</div>

<script>
    var vehiculosData = [];
    var reservasActivas = [];

    // Función para escapar HTML
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    // Estado del vehículo según reservas activas
    function estadoVehiculo(vehiculoId) {
        return reservasActivas.indexOf(vehiculoId) !== -1 ? 'Reservado' : 'Disponible';
    }

    // Plantilla de tarjeta de vehículo
    function cardHtml(v) {
        const imagen = v.imagen_url ? escapeHtml(v.imagen_url) : 'https://via.placeholder.com/220x140?text=Sin+imagen';
        const estado = estadoVehiculo(v.id);
        return `
        <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0;">
            <img src="${imagen}" alt="${escapeHtml(v.marca)} ${escapeHtml(v.modelo)}" style="width:100%; height:140px; object-fit:cover;">
            <div style="padding: 0.8rem;">
                <strong>${escapeHtml(v.marca)} ${escapeHtml(v.modelo)}</strong>
                <p style="font-size:0.9rem; color:#6b7280;">$${v.precio_por_dia}/día</p>
                <p style="font-size:0.85rem; font-weight:500; ${estado === 'Reservado' ? 'color:#dc2626;' : 'color:#16a34a;'}">${estado}</p>
                <button onclick="eliminar(${v.id})" style="background: #ef4444; color: white; border: none; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; cursor: pointer; margin-top: 0.5rem; transition: all 0.2s;">Eliminar</button>
            </div>
        </div>`;
    }

    // Aplica filtros/orden y pinta la lista completa
    function aplicarFiltros() {
        const estF = document.getElementById('filtroEstado').value;
        const q = document.getElementById('buscarVehiculo').value.trim().toLowerCase();
        const ord = document.getElementById('ordenVehiculos').value;

        let lista = vehiculosData.filter(function(v) {
            const est = estadoVehiculo(v.id);
            if (estF === 'disponible' && est === 'Reservado') return false;
            if (estF === 'reservado' && est === 'Disponible') return false;
            if (q && !(String(v.marca || '').toLowerCase().indexOf(q) !== -1 || String(v.modelo || '').toLowerCase().indexOf(q) !== -1)) return false;
            return true;
        });

        const cmpTexto = function(a, b) { return String(a).localeCompare(String(b)); };
        switch (ord) {
            case 'antiguo_asc': lista.sort(function(a, b) { return a.id - b.id; }); break;
            case 'marca_asc': lista.sort(function(a, b) { return cmpTexto(a.marca, b.marca); }); break;
            case 'marca_desc': lista.sort(function(a, b) { return cmpTexto(b.marca, a.marca); }); break;
            case 'precio_asc': lista.sort(function(a, b) { return parseFloat(a.precio_por_dia) - parseFloat(b.precio_por_dia); }); break;
            case 'precio_desc': lista.sort(function(a, b) { return parseFloat(b.precio_por_dia) - parseFloat(a.precio_por_dia); }); break;
            default: lista.sort(function(a, b) { return b.id - a.id; });
        }

        const cont = document.getElementById('flotaCount');
        cont.textContent = lista.length + (lista.length === 1 ? ' vehículo' : ' vehículos');

        const contenedor = document.getElementById('listaVehiculos');
        if (lista.length === 0) {
            contenedor.innerHTML = '<p style="color:#6b7280;">No se encontraron vehículos con esos filtros.</p>';
        } else {
            contenedor.innerHTML = lista.map(cardHtml).join('');
        }
    }

    // Cargar vehículos y refrescar ambas secciones
    function cargarVehiculos() {
        $.get('api/vehiculos.php?compania=1', function(data) {
            if (!data || data.length === 0) {
                vehiculosData = [];
                document.getElementById('ultimosVehiculos').innerHTML = '<p style="color:#6b7280;">No has agregado vehículos aún.</p>';
                aplicarFiltros();
                return;
            }
            vehiculosData = data;

            $.get('api/reservas.php?recibidas=1', function(reservas) {
                reservasActivas = reservas
                    .filter(function(r) { return r.estado === 'pendiente' || r.estado === 'confirmada'; })
                    .map(function(r) { return r.vehiculo_id; });

                const sorted = [...vehiculosData].sort(function(a, b) { return b.id - a.id; });
                document.getElementById('ultimosVehiculos').innerHTML = sorted.slice(0, 5).map(cardHtml).join('');
                aplicarFiltros();
            }).fail(function() {
                reservasActivas = [];
                document.getElementById('ultimosVehiculos').innerHTML = '<p style="color:#6b7280;">No se pudo cargar el estado de reservas.</p>';
                aplicarFiltros();
            });
        }).fail(function() {
            document.getElementById('ultimosVehiculos').innerHTML = '<div class="error">Error al cargar vehículos.</div>';
            document.getElementById('listaVehiculos').innerHTML = '<div class="error">Error al cargar vehículos.</div>';
        });
    }

    // Eliminar vehículo (con confirmación)
    function eliminar(id) {
        showConfirm('¿Eliminar este vehículo?', function() {
            $.ajax({
                url: 'api/vehiculos.php?id=' + id,
                method: 'DELETE',
                success: function(res) {
                    showAlert(res.mensaje, 'success');
                    cargarVehiculos();
                },
                error: function(xhr) {
                    const resp = xhr.responseJSON;
                    showAlert(resp ? resp.error : 'Error al eliminar', 'error');
                }
            });
        });
    }

    // Modal de agregar vehículo
    function abrirModalVehiculo() {
        document.getElementById('vehiculoModalOverlay').classList.add('open');
    }

    function cerrarModalVehiculo() {
        document.getElementById('vehiculoModalOverlay').classList.remove('open');
    }

    // Vista previa de imagen según la URL escrita
    function actualizarPreview() {
        const url = document.getElementById('flotaImagenUrl').value.trim();
        const img = document.getElementById('flotaPreviewImg');
        const placeholder = document.getElementById('flotaPreviewPlaceholder');
        if (url === '') {
            img.style.display = 'none';
            img.src = '';
            placeholder.style.display = 'flex';
            return;
        }
        img.style.display = 'none';
        img.onload = function() {
            placeholder.style.display = 'none';
            img.style.display = 'block';
        };
        img.onerror = function() {
            img.style.display = 'none';
            placeholder.style.display = 'flex';
        };
        img.src = url;
    }

    document.getElementById('flotaImagenUrl').addEventListener('input', actualizarPreview);

    document.getElementById('vehiculoModalOverlay').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalVehiculo();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('vehiculoModalOverlay').classList.contains('open')) {
            cerrarModalVehiculo();
        }
    });

    // Agregar vehículo desde el modal
    $('#vehiculoForm').submit(function(e) {
        e.preventDefault();
        $.post('api/vehiculos.php', $(this).serialize())
            .done(function(res) {
                if (res.error) {
                    showAlert(res.error, 'error');
                } else {
                    showAlert(res.mensaje, 'success');
                    $('#vehiculoForm')[0].reset();
                    actualizarPreview();
                    cerrarModalVehiculo();
                    cargarVehiculos();
                }
            })
            .fail(function() {
                showAlert('Error al guardar el vehículo', 'error');
            });
    });

    // Cargar al inicio
    cargarVehiculos();
</script>

<style>
    #vehiculoForm input,
    #vehiculoForm button {
        font-family: 'Inter', sans-serif;
    }

    #ultimosVehiculos,
    #listaVehiculos {
        min-height: 100px;
    }

    .success {
        background: #d1fae5;
        color: #065f46;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        border-left: 4px solid #10b981;
    }

    .error {
        background: #fee2e2;
        color: #b91c1c;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        border-left: 4px solid #ef4444;
    }

    /* ---------- Modal de agregar vehículo ---------- */
    .flota-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1200;
        background: rgba(15, 15, 15, 0.55);
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .flota-modal-overlay.open {
        display: flex;
    }

    .flota-modal {
        background: var(--rw-white);
        border-radius: var(--rw-radius-xl);
        box-shadow: var(--rw-shadow-lg);
        width: 100%;
        max-width: 520px;
        max-height: 92vh;
        overflow-y: auto;
        padding: 1.5rem 1.5rem 1.25rem;
        animation: flotaModalIn 0.18s ease-out;
    }

    @keyframes flotaModalIn {
        from { opacity: 0; transform: translateY(12px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .flota-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.1rem;
    }

    .flota-modal-header h4 {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--rw-ink);
    }

    .flota-modal-close {
        background: none;
        border: none;
        font-size: 1.6rem;
        line-height: 1;
        color: var(--rw-gray-500);
        cursor: pointer;
        padding: 0.1rem 0.4rem;
        border-radius: 8px;
        transition: var(--rw-transition);
    }

    .flota-modal-close:hover {
        color: var(--rw-red);
        background: var(--rw-red-tint);
    }

    .flota-modal-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 0.75rem;
    }

    .flota-preview-label {
        display: block;
        margin-top: 1rem;
        margin-bottom: 0.4rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--rw-gray-600);
    }

    .flota-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 130px;
        max-height: 220px;
        background: var(--rw-gray-100);
        border: 1px dashed var(--rw-gray-300);
        border-radius: var(--rw-radius-lg);
        overflow: hidden;
        text-align: center;
    }

    .flota-preview img {
        width: 100%;
        height: 100%;
        max-height: 220px;
        object-fit: cover;
    }

    .flota-preview #flotaPreviewPlaceholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.4rem;
        color: var(--rw-gray-400);
        font-size: 0.82rem;
        padding: 1rem;
    }

    .flota-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.65rem;
        margin-top: 1.25rem;
    }
</style>