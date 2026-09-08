<h3>Reportes del sistema</h3>
<div id="stats"></div>
<script>
$.get('api/admin.php?reporte=1', function(data) {
    $('#stats').html(`
        <p>Total usuarios: ${data.total_usuarios}</p>
        <p>Total vehículos: ${data.total_vehiculos}</p>
        <p>Total reservas: ${data.total_reservas}</p>
        <p>Ingresos totales: $${data.ingresos_totales}</p>
    `);
}, 'json');
</script>