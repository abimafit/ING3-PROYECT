<div class="view-header">
    <h3>Buscar vehículos</h3>
    <p style="color:var(--rw-gray-500);margin-top:0.2rem;">Elige una ciudad y, si quieres, tus fechas, para ver los vehículos disponibles.</p>
</div>

<form id="searchForm" class="search-panel" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end; background:var(--rw-white); border:1px solid var(--rw-gray-200); padding:1.4rem; border-radius:var(--rw-radius-lg); margin-bottom:1.5rem; box-shadow:var(--rw-shadow-sm);">
    <div style="flex:1 1 200px; min-width:200px;">
        <label for="lugar">Ciudad</label>
        <select id="lugar" style="width:100%;">
            <option value="">Seleccionar ciudad</option>
            <option value="todas">Todas las ciudades</option>
            <?php
            require_once 'includes/ciudades_panama.php';
            $ciudades = obtenerNombresCiudades();
            $conteoPorCiudad = [];
            if (isset($pdo)) {
                foreach ($pdo->query("SELECT u.ciudad, COUNT(*) c FROM vehiculos v JOIN usuarios u ON v.compania_id = u.id WHERE v.disponible = 1 GROUP BY u.ciudad")->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                    $conteoPorCiudad[$fila['ciudad']] = (int) $fila['c'];
                }
            }
            foreach ($ciudades as $ciudad):
                $n = $conteoPorCiudad[$ciudad] ?? 0; ?>
                <option value="<?php echo htmlspecialchars($ciudad); ?>"><?php echo htmlspecialchars($ciudad); ?> (<?php echo $n; ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:flex-end;">
        <div>
            <label for="fecha_inicio">Fecha de inicio <span style="color:var(--rw-gray-400);font-weight:400;">(opcional)</span></label>
            <input type="date" id="fecha_inicio" style="width:100%; max-width:140px;">
        </div>
        <div>
            <label for="fecha_fin">Fecha de fin <span style="color:var(--rw-gray-400);font-weight:400;">(opcional)</span></label>
            <input type="date" id="fecha_fin" style="width:100%; max-width:140px;">
        </div>
    </div>
    <div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.75rem; margin-left:auto;">
        <button type="submit" class="btn btn-primary"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:6px;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>Buscar</button>
        <button type="button" class="btn btn-outline" style="border:2px solid var(--rw-red);" onclick="limpiarFiltros()"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:6px;"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>Limpiar filtros</button>
    </div>
</form>

<div id="resultados" style="margin-top:20px;"></div>

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

    var today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_inicio').setAttribute('min', today);
    document.getElementById('fecha_fin').setAttribute('min', today);

    document.getElementById('fecha_inicio').addEventListener('change', function() {
        var inicio = this.value;
        document.getElementById('fecha_fin').setAttribute('min', inicio || today);
        validarFechas();
    });
    document.getElementById('fecha_fin').addEventListener('change', validarFechas);

    function validarFechas() {
        var inicio = document.getElementById('fecha_inicio').value;
        var fin = document.getElementById('fecha_fin').value;
        if (inicio && fin && fin < inicio) {
            showAlert('La fecha de fin no puede ser anterior a la de inicio', 'warning');
            document.getElementById('fecha_fin').value = '';
            return false;
        }
        return true;
    }

    function leerFechas() {
        return {
            inicio: $('#fecha_inicio').val(),
            fin: $('#fecha_fin').val()
        };
    }

    function diasEntre(inicio, fin) {
        if (!inicio || !fin) return 0;
        var d1 = new Date(inicio);
        var d2 = new Date(fin);
        return Math.max(1, Math.round((d2 - d1) / 86400000));
    }

    function mostrarError(mensaje) {
        $('#resultados').html('<div class="error">' + escapeHtml(mensaje) + '</div>');
    }

    function renderResultados(data) {
        if (!data || typeof data !== 'object' || data.error) {
            mostrarError(data && data.error ? data.error : 'La respuesta del servidor no fue válida.');
            return;
        }
        if (!Array.isArray(data) || data.length === 0) {
            $('#resultados').html(
                '<div class="empty-state">' +
                    '<span class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg></span>' +
                    '<h4>No hay vehículos disponibles</h4>' +
                    '<p>Prueba con otras fechas o ciudad.</p>' +
                '</div>'
            );
            return;
        }

        var fechas = leerFechas();
        var tieneFechas = fechas.inicio && fechas.fin;
        var dias = diasEntre(fechas.inicio, fechas.fin);

        var html = '<div class="vehiculos-grid">';
        data.forEach(function(v) {
            var marca = escapeHtml(v.marca);
            var modelo = escapeHtml(v.modelo);
            var compania = escapeHtml(v.compania);
            var ciudad = escapeHtml(v.ciudad || '');
            var anio = escapeHtml(v.anio || '');
            var precio = parseFloat(v.precio_por_dia) || 0;
            var imagenUrl = v.imagen_url ? escapeHtml(v.imagen_url) : '';
            var imgSrc = imagenUrl || 'https://via.placeholder.com/280x180?text=RentWheels';
            var total = tieneFechas ? (precio * dias).toFixed(2) : null;

            html += '<div class="vehiculo-card">' +
                        '<div class="vehiculo-imagen">' +
                            '<img src="' + imgSrc + '" alt="' + marca + ' ' + modelo + '" loading="lazy">' +
                        '</div>' +
                        '<div class="vehiculo-info">' +
                            '<h4>' + marca + ' ' + modelo + ' ' + anio + '</h4>' +
                            '<p><strong>Precio:</strong> $' + precio + '/día</p>' +
                            (tieneFechas ? '<p><strong>Total (' + dias + ' días):</strong> <span style="color:var(--rw-red);font-weight:700;">$' + total + '</span></p>' : '') +
                            '<p><strong>Compañía:</strong> ' + compania + (ciudad ? ' · ' + ciudad : '') + '</p>' +
                            '<button onclick="reservar(' + v.id + ')" class="btn-reservar">Reservar</button>' +
                        '</div>' +
                    '</div>';
        });
        html += '</div>';
        $('#resultados').html(html);
    }

    function buscarVehiculos(conFechas) {
        var fechas = leerFechas();
        var lugar = $('#lugar').val();
        if (!lugar) {
            mostrarMensajeInicial();
            return;
        }
        var lugarEnvio = (lugar === 'todas') ? '' : lugar;

        $('#resultados').html('<div class="loading">Buscando vehículos...</div>');

        var resolvido = false;
        var temporizador = setTimeout(function() {
            if (!resolvido) {
                resolvido = true;
                mostrarError('La búsqueda está tardando más de lo esperado. Inténtalo de nuevo.');
            }
        }, 15000);

        function terminar() {
            if (resolvido) return;
            resolvido = true;
            clearTimeout(temporizador);
        }

        try {
            $.get('api/vehiculos.php', {
                lugar: lugarEnvio,
                fecha_inicio: conFechas ? fechas.inicio : '',
                fecha_fin: conFechas ? fechas.fin : ''
            }, function(data) {
                terminar();
                try {
                    renderResultados(data);
                } catch (e) {
                    mostrarError('Ocurrió un error al mostrar los resultados. Recarga la página e inténtalo de nuevo.');
                }
            }).fail(function(jqXHR) {
                terminar();
                var msg = 'Error al cargar los vehículos.';
                try {
                    var err = JSON.parse(jqXHR.responseText);
                    if (err && err.error) msg = err.error;
                } catch (e) {}
                mostrarError(msg);
            });
        } catch (e) {
            terminar();
            mostrarError('Error al iniciar la búsqueda.');
        }
    }

    function mostrarMensajeInicial() {
        $('#resultados').html(
            '<div class="empty-state">' +
                '<span class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></span>' +
                '<h4>Selecciona una ciudad</h4>' +
                '<p>Elige la ciudad donde vas a alquilar y pulsa Buscar para ver los vehículos disponibles.</p>' +
            '</div>'
        );
    }

    function limpiarFiltros() {
        $('#lugar').val('');
        $('#fecha_inicio').val('');
        $('#fecha_fin').val('');
        mostrarMensajeInicial();
    }

    // Al entrar se muestra un mensaje de indicación hasta que se pulse Buscar
    mostrarMensajeInicial();

    $('#searchForm').submit(function(e) {
        e.preventDefault();
        if (!validarFechas()) return;
        buscarVehiculos(true);
    });

    function reservar(vehiculo_id) {
        var fechas = leerFechas();
        if (!fechas.inicio || !fechas.fin) {
            showAlert('Selecciona las fechas de reserva para continuar', 'warning');
            document.getElementById('fecha_inicio').focus();
            return;
        }
        if (fechas.fin < fechas.inicio) {
            showAlert('Revisa las fechas seleccionadas', 'warning');
            return;
        }
        showConfirm('¿Confirmar reserva para las fechas seleccionadas?', function() {
            $.post('api/reservas.php', {
                vehiculo_id: vehiculo_id,
                fecha_inicio: fechas.inicio,
                fecha_fin: fechas.fin
            }, function(res) {
                if (res.error) {
                    showAlert(res.error, 'error');
                } else {
                    showAlert(res.mensaje + ' · ' + res.dias + ' días · $' + res.monto, 'success');
                    setTimeout(function() {
                        window.location.href = 'dashboard.php?view=mis_reservas';
                    }, 1800);
                }
            }, 'json').fail(function(jqXHR) {
                var msg = 'Error al procesar la reserva';
                try {
                    var resp = JSON.parse(jqXHR.responseText);
                    if (resp && resp.error) msg = resp.error;
                } catch (e) {}
                showAlert(msg, 'error');
            });
        });
    }
</script>