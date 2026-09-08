<h3>Buscar vehículos disponibles</h3>
<form id="searchForm" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; background: #f8fafc; padding: 1.5rem; border-radius: 16px; margin-bottom: 1.5rem;">
    <div style="flex: 1; min-width: 180px;">
        <label for="lugar" style="display: block; font-weight: 500; margin-bottom: 0.3rem;">Ciudad</label>
        <select id="lugar" class="form-control" style="width: 100%; padding: 0.7rem; border-radius: 10px; border: 1px solid #d1d5db;">
            <option value="">Todas las ciudades</option>
            <?php
            require_once 'includes/ciudades_panama.php';
            $ciudades = obtenerNombresCiudades();
            foreach ($ciudades as $ciudad): ?>
                <option value="<?php echo htmlspecialchars($ciudad); ?>"><?php echo htmlspecialchars($ciudad); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="fecha_inicio" style="font-weight: 500; display: block; margin-bottom: 0.3rem;">Fecha de inicio</label>
        <input type="date" id="fecha_inicio" class="form-control" required style="width: 100%; padding: 0.7rem; border-radius: 10px; border: 1px solid #d1d5db;">
    </div>
    <div>
        <label for="fecha_fin" style="font-weight: 500; display: block; margin-bottom: 0.3rem;">Fecha de fin</label>
        <input type="date" id="fecha_fin" class="form-control" required style="width: 100%; padding: 0.7rem; border-radius: 10px; border: 1px solid #d1d5db;">
    </div>
    <div style="display: flex; align-items: flex-end;">
        <button type="submit" class="btn" style="width: 100%; background: #2563eb; color: white; border: none; padding: 0.7rem; border-radius: 40px; font-weight: 600; cursor: pointer; transition: all 0.2s;">🔍 Buscar</button>
    </div>
</form>
<div id="resultados" style="margin-top:20px;"></div>

<script>
    // Función de escape HTML
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    // Fechas mínimas
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_inicio').setAttribute('min', today);
    document.getElementById('fecha_fin').setAttribute('min', today);

    // Validar fechas
    function validarFechas() {
        const inicio = document.getElementById('fecha_inicio').value;
        const fin = document.getElementById('fecha_fin').value;
        if (inicio && fin && fin < inicio) {
            alert('La fecha de fin no puede ser anterior a la fecha de inicio.');
            document.getElementById('fecha_fin').value = '';
            return false;
        }
        return true;
    }

    document.getElementById('fecha_inicio').addEventListener('change', function() {
        const inicio = this.value;
        document.getElementById('fecha_fin').setAttribute('min', inicio);
        validarFechas();
    });
    document.getElementById('fecha_fin').addEventListener('change', validarFechas);

    // Búsqueda
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        const lugar = $('#lugar').val();

        if (!fechaInicio || !fechaFin) {
            showAlert('Debes ingresar ambas fechas', 'warning');
            return;
        }
        if (fechaInicio > fechaFin) {
            alert('La fecha de inicio no puede ser mayor a la fecha de fin');
            return;
        }

        $('#resultados').html('<div class="loading">Buscando vehículos...</div>');

        $.get('api/vehiculos.php', {
            lugar: lugar,
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin
        }, function(data) {
            if (data.error) {
                $('#resultados').html('<div class="error">' + escapeHtml(data.error) + '</div>');
                return;
            }
            if (data.length === 0) {
                $('#resultados').html('<p>No hay vehículos disponibles en esas fechas.</p>');
                return;
            }
            let html = '<div class="vehiculos-grid">';
            data.forEach(v => {
                const marca = escapeHtml(v.marca);
                const modelo = escapeHtml(v.modelo);
                const compania = escapeHtml(v.compania);
                const precio = v.precio_por_dia;
                const imagenUrl = v.imagen_url ? escapeHtml(v.imagen_url) : '';
                const imgSrc = imagenUrl ? imagenUrl : 'https://via.placeholder.com/280x180?text=Sin+imagen';

                html += `<div class="vehiculo-card">
                        <div class="vehiculo-imagen">
                            <img src="${imgSrc}" alt="${marca} ${modelo}">
                        </div>
                        <div class="vehiculo-info">
                            <h4>${marca} ${modelo}</h4>
                            <p><strong>Precio/día:</strong> $${precio}</p>
                            <p><strong>Compañía:</strong> ${compania}</p>
                            <button onclick="reservar(${v.id})" class="btn-reservar">Reservar</button>
                        </div>
                    </div>`;
            });
            html += '</div>';
            $('#resultados').html(html);
        }).fail(function(jqXHR) {
            let msg = 'Error al cargar los vehículos.';
            if (jqXHR.responseText) {
                try {
                    const err = JSON.parse(jqXHR.responseText);
                    msg += ' ' + (err.error || '');
                } catch (e) {}
            }
            $('#resultados').html('<div class="error">' + escapeHtml(msg) + '</div>');
        });
    });

    function reservar(vehiculo_id) {
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        if (!fechaInicio || !fechaFin) {
            alert('Primero selecciona las fechas de reserva');
            return;
        }
        showConfirm('¿Confirmar reserva para estas fechas?', function() {
    // Código de reserva aquí


        console.log('Enviando reserva:', {
            vehiculo_id,
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin
        });

        $.post('api/reservas.php', {
            vehiculo_id: vehiculo_id,
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin
        }, function(res) {
            if (res.error) {
                alert('❌ ' + res.error);
            } else {
                alert('✅ ' + res.mensaje + '\nDías: ' + res.dias + '\nMonto: $' + res.monto);
                window.location.href = 'dashboard.php?view=mis_reservas';
            }
        }, 'json').fail(function(jqXHR) {
            let msg = 'Error al procesar la reserva';
            try {
                const resp = JSON.parse(jqXHR.responseText);
                if (resp.error) msg = resp.error;
            } catch (e) {}
            alert('❌ ' + msg);
        });
        });
    }
</script>

<style>
    .loading {
        text-align: center;
        padding: 20px;
        font-style: italic;
        color: #555;
    }
</style>