<h3>🚗 Gestión de flota</h3>

<!-- Formulario para agregar vehículo -->
<div style="background: #f8fafc; padding: 1.5rem; border-radius: 16px; margin-bottom: 2rem;">
    <h4 style="margin-bottom: 1rem;"> Agregar nuevo vehículo</h4>
    <form id="vehiculoForm" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <input type="text" name="marca" placeholder="Marca" required style="padding: 0.7rem; border-radius: 10px; border: 1px solid #d1d5db;">
        <input type="text" name="modelo" placeholder="Modelo" required style="padding: 0.7rem; border-radius: 10px; border: 1px solid #d1d5db;">
        <input type="number" name="anio" placeholder="Año" style="padding: 0.7rem; border-radius: 10px; border: 1px solid #d1d5db;">
        <input type="number" step="0.01" name="precio_por_dia" placeholder="Precio por día" required style="padding: 0.7rem; border-radius: 10px; border: 1px solid #d1d5db;">
        <input type="text" name="imagen_url" placeholder="URL de imagen (opcional)" style="padding: 0.7rem; border-radius: 10px; border: 1px solid #d1d5db;">
        <button type="submit" style="background: #2563eb; color: white; border: none; padding: 0.7rem; border-radius: 40px; font-weight: 600; cursor: pointer; transition: all 0.2s;">Agregar vehículo</button>
    </form>
    <div id="formMessage" style="margin-top: 0.5rem;"></div>
</div>

<!-- Sección: Últimos vehículos añadidos -->
<div style="margin-bottom: 2rem;">
    <h4>Últimos vehículos añadidos</h4>
    <div id="ultimosVehiculos" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
        <!-- Se carga vía AJAX -->
    </div>
</div>

<!-- Sección: Vehículos actuales (todos) -->
<div>
    <h4>Vehículos actuales</h4>
    <div id="listaVehiculos" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
        <!-- Se carga vía AJAX -->
    </div>
</div>

<script>
    // Función para escapar HTML
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    // Función para obtener estado de reserva (si tiene reservas activas)
    function obtenerEstadoReserva(vehiculoId, reservasActivas) {
        // reservasActivas es un array de IDs de vehículos con reservas activas
        return reservasActivas.includes(vehiculoId) ? 'Reservado' : 'Disponible';
    }

    // Cargar vehículos y mostrar en ambas secciones
    function cargarVehiculos() {
        $.get('api/vehiculos.php?compania=1', function(data) {
            if (!data || data.length === 0) {
                $('#ultimosVehiculos').html('<p style="color:#6b7280;">No has agregado vehículos aún.</p>');
                $('#listaVehiculos').html('<p style="color:#6b7280;">No hay vehículos en tu flota.</p>');
                return;
            }

            // Obtener IDs de vehículos con reservas activas (pendiente o confirmada)
            // Hacemos una llamada adicional para obtener reservas activas de esta compañía
            $.get('api/reservas.php?recibidas=1', function(reservas) {
                const reservasActivas = reservas
                    .filter(r => r.estado === 'pendiente' || r.estado === 'confirmada')
                    .map(r => r.vehiculo_id);

                // Ordenar por id descendente (más reciente primero)
                const sorted = [...data].sort((a, b) => b.id - a.id);
                const ultimos = sorted.slice(0, 5); // últimos 5

                // Renderizar últimos vehículos
                let htmlUltimos = '';
                ultimos.forEach(v => {
                    const imagen = v.imagen_url ? escapeHtml(v.imagen_url) : 'https://via.placeholder.com/220x140?text=Sin+imagen';
                    const estado = obtenerEstadoReserva(v.id, reservasActivas);
                    htmlUltimos += `
                    <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0;">
                        <img src="${imagen}" alt="${escapeHtml(v.marca)} ${escapeHtml(v.modelo)}" style="width:100%; height:140px; object-fit:cover;">
                        <div style="padding: 0.8rem;">
                            <strong>${escapeHtml(v.marca)} ${escapeHtml(v.modelo)}</strong>
                            <p style="font-size:0.9rem; color:#6b7280;">$${v.precio_por_dia}/día</p>
                            <p style="font-size:0.85rem; font-weight:500; ${estado.includes('Reservado') ? 'color:#dc2626;' : 'color:#16a34a;'}">${estado}</p>
                        </div>
                    </div>
                `;
                });
                $('#ultimosVehiculos').html(htmlUltimos);

                // Renderizar todos los vehículos
                let htmlTodos = '';
                data.forEach(v => {
                    const imagen = v.imagen_url ? escapeHtml(v.imagen_url) : 'https://via.placeholder.com/220x140?text=Sin+imagen';
                    const estado = obtenerEstadoReserva(v.id, reservasActivas);
                    htmlTodos += `
                    <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; position: relative;">
                        <img src="${imagen}" alt="${escapeHtml(v.marca)} ${escapeHtml(v.modelo)}" style="width:100%; height:140px; object-fit:cover;">
                        <div style="padding: 0.8rem;">
                            <strong>${escapeHtml(v.marca)} ${escapeHtml(v.modelo)}</strong>
                            <p style="font-size:0.9rem; color:#6b7280;">$${v.precio_por_dia}/día</p>
                            <p style="font-size:0.85rem; font-weight:500; ${estado.includes('Reservado') ? 'color:#dc2626;' : 'color:#16a34a;'}">${estado}</p>
                            <button onclick="eliminar(${v.id})" style="background: #ef4444; color: white; border: none; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; cursor: pointer; margin-top: 0.5rem; transition: all 0.2s;">Eliminar</button>
                        </div>
                    </div>
                `;
                });
                $('#listaVehiculos').html(htmlTodos);
            }).fail(function() {
                // Si falla la carga de reservas, mostrar vehículos sin estado
                let htmlTodos = '';
                data.forEach(v => {
                    const imagen = v.imagen_url ? escapeHtml(v.imagen_url) : 'https://via.placeholder.com/220x140?text=Sin+imagen';
                    htmlTodos += `
                    <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0;">
                        <img src="${imagen}" alt="${escapeHtml(v.marca)} ${escapeHtml(v.modelo)}" style="width:100%; height:140px; object-fit:cover;">
                        <div style="padding: 0.8rem;">
                            <strong>${escapeHtml(v.marca)} ${escapeHtml(v.modelo)}</strong>
                            <p style="font-size:0.9rem; color:#6b7280;">$${v.precio_por_dia}/día</p>
                            <p style="font-size:0.85rem; color:#6b7280;">Estado desconocido</p>
                            <button onclick="eliminar(${v.id})" style="background: #ef4444; color: white; border: none; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; cursor: pointer; margin-top: 0.5rem;">Eliminar</button>
                        </div>
                    </div>
                `;
                });
                $('#listaVehiculos').html(htmlTodos);
                // También mostrar mensaje en últimos
                $('#ultimosVehiculos').html('<p style="color:#6b7280;">No se pudo cargar el estado de reservas.</p>');
            });
        }).fail(function() {
            $('#ultimosVehiculos').html('<div class="error">Error al cargar vehículos.</div>');
            $('#listaVehiculos').html('<div class="error">Error al cargar vehículos.</div>');
        });
    }

    // Eliminar vehículo (con confirmación)
    function eliminar(id) {
        showConfirm('¿Eliminar este vehículo?', function() {
            // Llamada AJAX para eliminar

            $.ajax({
                url: 'api/vehiculos.php?id=' + id,
                method: 'DELETE',
                success: function(res) {
                    alert(res.mensaje);
                    cargarVehiculos(); // Recargar listas
                },
                error: function(xhr) {
                    const resp = xhr.responseJSON;
                    alert(resp ? resp.error : 'Error al eliminar');
                }
            });
        });
    }

    // Agregar vehículo
    $('#vehiculoForm').submit(function(e) {
        e.preventDefault();
        $.post('api/vehiculos.php', $(this).serialize())
            .done(function(res) {
                if (res.error) {
                    $('#formMessage').html('<div class="error">❌ ' + res.error + '</div>');
                } else {
                    $('#formMessage').html('<div class="success">✅ ' + res.mensaje + '</div>');
                    cargarVehiculos(); // Recargar listas
                    $('#vehiculoForm')[0].reset();
                    setTimeout(function() {
                        $('#formMessage').empty();
                    }, 3000);
                }
            })
            .fail(function(jqXHR) {
                $('#formMessage').html('<div class="error"> Error: ' + jqXHR.responseText + '</div>');
            });
    });

    // Cargar al inicio
    cargarVehiculos();
</script>

<style>
    /* Estilos adicionales para esta vista (opcional, pero se integran con style.css) */
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
</style>