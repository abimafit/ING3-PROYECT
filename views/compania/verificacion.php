<?php
$stmt = $pdo->prepare("SELECT verificado, codigo_verificacion FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="verificacion-panel">
    <?php if ($user['verificado'] == 1): ?>
        <div class="success" style="background:#d1fae5; border-left:4px solid #10b981; padding:1rem; border-radius:12px; margin-bottom:1rem;">
            Tu cuenta está verificada. Puedes gestionar tu flota sin restricciones.
        </div>
    <?php else: ?>
        <div class="warning" style="background:#fef3c7; border-left:4px solid #f59e0b; padding:1rem; border-radius:12px; margin-bottom:1rem;">
            <p style="font-weight:600; color:#92400e;">Tu cuenta aún no está verificada.</p>
            <p style="color:#6b7280;">Introduce el código de verificación que recibiste al registrarte:</p>
            <form id="formVerificacion" style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:0.5rem;">
                <input type="text" name="codigo" id="codigoVerificacion" placeholder="Código de 6 dígitos" maxlength="6" style="padding:0.5rem; border-radius:10px; border:1px solid #d1d5db; flex:1; min-width:150px;">
                <button type="submit" style="background:#2563eb; color:white; border:none; padding:0.5rem 1.5rem; border-radius:40px; cursor:pointer;">Verificar</button>
            </form>
            <p style="font-size:0.8rem; color:#6b7280; margin-top:0.3rem;">Si no tienes el código, contacta al administrador.</p>
            <div id="mensajeVerificacion" style="margin-top:0.5rem;"></div>
        </div>
    <?php endif; ?>
</div>

<script>
$('#formVerificacion').submit(function(e) {
    e.preventDefault();
    const codigo = $('#codigoVerificacion').val().trim();
    if (codigo.length !== 6 || isNaN(codigo)) {
        showAlert('Ingresa un código válido de 6 dígitos.', 'warning');
        return;
    }
    $.post('api/compania.php', { verificar_codigo: 1, codigo: codigo })
        .done(function(res) {
            if (res.error) {
                showAlert(res.error, 'error');
            } else {
                showAlert(res.mensaje, 'success');
                location.reload();
            }
        })
        .fail(function(jqXHR) {
            handleAjaxError(jqXHR);
        });
});
</script>