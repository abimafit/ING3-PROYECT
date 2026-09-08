<h3><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="var(--rw-red)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-4px;margin-right:8px;"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>Reportes del sistema</h3>

<div class="table-toolbar">
    <button type="button" class="btn btn-primary btn-small" style="margin-left:auto;" id="btnExportar" onclick="exportarCSV()">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>Exportar CSV
    </button>
</div>

<div id="reportesLoad">
    <div class="loading" style="padding:2rem 0;">Cargando reportes...</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function() {
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        var datosReporte = null;

        var iconos = {
            user: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>',
            car: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>',
            book: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M15 2H9a1 1 0 0 0-1 1v2h8V3a1 1 0 0 0-1-1Z"/></svg>',
            money: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>',
            pie: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>',
            bars: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg>',
            line: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
            team: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            trolley: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>'
        };

        function kpi(icono, numero, etiqueta, color) {
            return '<div class="stat-card">' +
                '<div class="stat-icon" style="background:' + (color || '#FBE9EB') + ';color:var(--rw-red);">' + icono + '</div>' +
                '<div>' +
                    '<div class="stat-number">' + (numero === null || numero === undefined ? '&ndash;' : String(numero)) + '</div>' +
                    '<div class="stat-label">' + escapeHtml(etiqueta) + '</div>' +
                '</div>' +
            '</div>';
        }

        function fmtDinero(valor) {
            return '$' + Number(valor || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function mesLabel(mes) {
            var partes = String(mes || '').split('-');
            var nombres = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            return nombres[(parseInt(partes[1], 10) || 1) - 1];
        }

        function cargar() {
            var cont = document.getElementById('reportesLoad');
            cont.innerHTML = '<div class="loading" style="padding:2rem 0;">Cargando reportes...</div>';
            $.get('api/admin.php?reporte=1')
                .done(function(d) {
                    if (!d || d.error) {
                        cont.innerHTML = '<div class="error">' + escapeHtml(d && d.error ? d.error : 'No se pudieron cargar los reportes.') + '</div>';
                        return;
                    }
                    datosReporte = d;
                    render(cont, d);
                })
                .fail(function() {
                    cont.innerHTML = '<div class="error">Error de conexión al cargar los reportes. Recarga la página.</div>';
                });
        }

        function render(cont, d) {
            var html = '<div class="stats-grid">';
            html += kpi(iconos.user, d.total_usuarios, 'Usuarios registrados', '#E7F6EC');
            html += kpi(iconos.car, d.total_vehiculos, 'Vehículos en catálogo', '#FBE9EB');
            html += kpi(iconos.book, d.total_reservas, 'Reservas totales', '#FEF3E2');
            html += kpi(iconos.money, fmtDinero(d.ingresos_totales), 'Ingresos confirmados', '#E9E5F9');
            html += '</div>';

            html += '<div class="reporte-charts">';

            html += '<div class="reporte-chart"><h4>' + iconos.pie + 'Reservas por estado</h4><div class="chart-wrap"><canvas id="chartEstado"></canvas></div></div>';

            html += '<div class="reporte-chart"><h4>' + iconos.line + 'Ingresos confirmados por mes</h4><div class="chart-wrap"><canvas id="chartIngresos"></canvas></div></div>';

            html += '<div class="reporte-chart"><h4>' + iconos.bars + 'Reservas últimos 6 meses</h4><div class="chart-wrap"><canvas id="chartMes"></canvas></div></div>';

            html += '<div class="reporte-chart"><h4>' + iconos.trolley + 'Top 5 vehículos más reservados</h4><table class="reporte-table" id="tablaTop"></table></div>';

            html += '<div class="reporte-chart"><h4>' + iconos.team + 'Reservas e ingresos por compañía</h4><table class="reporte-table" id="tablaCompanias"></table></div>';

            html += '<div class="reporte-chart"><h4>' + iconos.user + 'Usuarios por rol</h4><ul class="rol-list" id="rolList"></ul></div>';

            html += '</div>';

            cont.innerHTML = html;

            // Doughnut reservas por estado
            var porEstado = d.reservas_por_estado || {};
            var totalEstados = 0;
            for (var k in porEstado) totalEstados += porEstado[k];

            if (totalEstados === 0) {
                document.getElementById('chartEstado').parentNode.innerHTML = '<p style="color:var(--rw-gray-500);padding-top:1rem;">Sin reservas registradas.</p>';
            } else {
                var coloresEstado = { pendiente: '#D97706', confirmada: '#16A34A', cancelada: '#DC2626' };
                var etiquetasEstado = Object.keys(porEstado).map(function(s) { return s.charAt(0).toUpperCase() + s.slice(1); });
                new Chart(document.getElementById('chartEstado'), {
                    type: 'doughnut',
                    data: {
                        labels: etiquetasEstado,
                        datasets: [{
                            data: Object.keys(porEstado).map(function(s) { return porEstado[s]; }),
                            backgroundColor: Object.keys(porEstado).map(function(s) { return coloresEstado[s] || '#6b7280'; }),
                            borderWidth: 3,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true, pointStyle: 'circle' } } }
                    }
                });
            }

            // Línea: ingresos por mes
            var ingresosMeses = d.ingresos_por_mes || [];
            var cIngresos = document.getElementById('chartIngresos');
            if (ingresosMeses.length === 0) {
                cIngresos.parentNode.innerHTML = '<p style="color:var(--rw-gray-500);padding-top:1rem;">Sin ingresos confirmados en los últimos 6 meses.</p>';
            } else {
                new Chart(cIngresos, {
                    type: 'line',
                    data: {
                        labels: ingresosMeses.map(function(m) { return mesLabel(m.mes); }),
                        datasets: [{
                            label: 'Ingresos ($)',
                            data: ingresosMeses.map(function(m) { return Number(m.ingresos) || 0; }),
                            borderColor: '#C61A30',
                            backgroundColor: 'rgba(198, 26, 48, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointBackgroundColor: '#C61A30'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { callback: function(v) { return '$' + v; } } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Barras: reservas por mes
            var meses = d.reservas_por_mes || [];
            var cMes = document.getElementById('chartMes');
            if (meses.length === 0) {
                cMes.parentNode.innerHTML = '<p style="color:var(--rw-gray-500);padding-top:1rem;">Sin reservas en los últimos 6 meses.</p>';
            } else {
                new Chart(cMes, {
                    type: 'bar',
                    data: {
                        labels: meses.map(function(m) { return mesLabel(m.mes); }),
                        datasets: [{
                            label: 'Reservas',
                            data: meses.map(function(m) { return Number(m.c) || 0; }),
                            backgroundColor: 'rgba(198, 26, 48, 0.85)',
                            hoverBackgroundColor: '#C61A30',
                            borderRadius: 6,
                            maxBarThickness: 42
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Top vehículos
            var top = d.top_vehiculos || [];
            var tabTop = document.getElementById('tablaTop');
            var hTop = '';
            if (top.length === 0) {
                hTop = '<tbody><tr><td style="padding-top:1rem;">Sin reservas registradas.</td></tr></tbody>';
            } else {
                hTop = '<thead><tr><th>Vehículo</th><th class="num">Reservas</th><th class="num">Ingresos</th></tr></thead><tbody>';
                top.forEach(function(v) {
                    hTop += '<tr><td>' + escapeHtml(v.vehiculo) + '</td><td class="num">' + v.reservas + '</td><td class="num">' + fmtDinero(v.ingresos) + '</td></tr>';
                });
                hTop += '</tbody>';
            }
            tabTop.innerHTML = hTop;

            // Por compañía
            var companias = d.reservas_por_compania || [];
            var tabComp = document.getElementById('tablaCompanias');
            var hComp = '';
            if (companias.length === 0) {
                hComp = '<tbody><tr><td style="padding-top:1rem;">Sin reservas registradas.</td></tr></tbody>';
            } else {
                hComp = '<thead><tr><th>Compañía</th><th class="num">Reservas</th><th class="num">Ingresos</th></tr></thead><tbody>';
                companias.forEach(function(c) {
                    hComp += '<tr><td>' + escapeHtml(c.compania) + '</td><td class="num">' + c.reservas + '</td><td class="num">' + fmtDinero(c.ingresos) + '</td></tr>';
                });
                hComp += '</tbody>';
            }
            tabComp.innerHTML = hComp;

            // Usuarios por rol
            var roles = d.usuarios_por_rol || {};
            var totalRoles = 0;
            for (var r in roles) totalRoles += roles[r];
            var labelsRol = { turista: 'Turistas', compania: 'Compañías', administrador: 'Administradores', soporte: 'Soporte' };
            var ul = document.getElementById('rolList');
            var keysRol = Object.keys(roles);
            var htmlRol = '';
            if (keysRol.length === 0) {
                htmlRol = '<li class="rol-item" style="color:var(--rw-gray-500);">Sin usuarios registrados.</li>';
            } else {
                keysRol.forEach(function(r) {
                    var pct = totalRoles ? Math.round((roles[r] / totalRoles) * 100) : 0;
                    htmlRol += '<li class="rol-item">' +
                        '<strong>' + escapeHtml(labelsRol[r] || r || 'Sin rol') + '</strong>' +
                        '<div class="rol-bar"><span style="width:' + pct + '%"></span></div>' +
                        '<span class="count">' + roles[r] + '</span>' +
                    '</li>';
                });
            }
            ul.innerHTML = htmlRol;
        }

        function csvCell(valor) {
            var s = String(valor === null || valor === undefined ? '' : valor);
            if (/[",\n]/.test(s)) {
                s = '"' + s.replace(/"/g, '""') + '"';
            }
            return s;
        }

        window.exportarCSV = function() {
            var d = datosReporte;
            if (!d) { showAlert('Los reportes aún no están cargados.', 'warning'); return; }
            var filas = [];
            filas.push(csvCell('Reporte RentWheels'));
            filas.push(csvCell('Generado: ' + new Date().toLocaleString()));
            filas.push('');
            filas.push('RESUMEN');
            filas.push(csvCell('Usuarios registrados') + ',' + csvCell(d.total_usuarios));
            filas.push(csvCell('Vehículos en catálogo') + ',' + csvCell(d.total_vehiculos));
            filas.push(csvCell('Vehículos disponibles') + ',' + csvCell(d.vehiculos_disponibles));
            filas.push(csvCell('Reservas totales') + ',' + csvCell(d.total_reservas));
            filas.push(csvCell('Ingresos confirmados') + ',' + csvCell(d.ingresos_totales));
            filas.push('');
            filas.push('RESERVAS POR ESTADO');
            filas.push(csvCell('Estado') + ',' + csvCell('Cantidad'));
            var porEstado = d.reservas_por_estado || {};
            Object.keys(porEstado).forEach(function(k) {
                filas.push(csvCell(k) + ',' + csvCell(porEstado[k]));
            });
            filas.push('');
            filas.push('RESERVAS POR MES');
            filas.push(csvCell('Mes') + ',' + csvCell('Cantidad'));
            (d.reservas_por_mes || []).forEach(function(m) {
                filas.push(csvCell(m.mes) + ',' + csvCell(m.c));
            });
            filas.push('');
            filas.push('INGRESOS POR MES');
            filas.push(csvCell('Mes') + ',' + csvCell('Ingresos'));
            (d.ingresos_por_mes || []).forEach(function(m) {
                filas.push(csvCell(m.mes) + ',' + csvCell(m.ingresos));
            });
            filas.push('');
            filas.push('TOP VEHÍCULOS MÁS RESERVADOS');
            filas.push(csvCell('Vehículo') + ',' + csvCell('Reservas') + ',' + csvCell('Ingresos'));
            (d.top_vehiculos || []).forEach(function(v) {
                filas.push(csvCell(v.vehiculo) + ',' + csvCell(v.reservas) + ',' + csvCell(v.ingresos));
            });
            filas.push('');
            filas.push('RESERVAS POR COMPAÑÍA');
            filas.push(csvCell('Compañía') + ',' + csvCell('Reservas') + ',' + csvCell('Ingresos'));
            (d.reservas_por_compania || []).forEach(function(c) {
                filas.push(csvCell(c.compania) + ',' + csvCell(c.reservas) + ',' + csvCell(c.ingresos));
            });
            filas.push('');
            filas.push('USUARIOS POR ROL');
            filas.push(csvCell('Rol') + ',' + csvCell('Cantidad'));
            Object.keys(porEstado) && (function() {
                var roles = d.usuarios_por_rol || {};
                Object.keys(roles).forEach(function(r) {
                    filas.push(csvCell(r) + ',' + csvCell(roles[r]));
                });
            })();

            var csvContent = '\uFEFF' + filas.join('\r\n');
            var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'reporte_rentwheels.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(a.href);
            showAlert('Reporte exportado en formato CSV', 'success');
        };

        cargar();
    })();
</script>