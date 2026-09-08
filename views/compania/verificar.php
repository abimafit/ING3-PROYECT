<?php
// Verificar si ya está verificado para redirigir
$stmt = $pdo->prepare("SELECT verificado FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$verificado = $stmt->fetchColumn();
if ($verificado) {
    header('Location: ?view=gestion_flota');
    exit;
}
?>

<style>
.verificar-container {
    max-width: 500px;
    margin: 2rem auto;
    text-align: center;
}
.verificar-card {
    background: white;
    padding: 2.5rem;
    border-radius: 28px;
    box-shadow: 0 20px 40px -12px rgba(0,0,0,0.15);
}
.verificar-card h2 {
    font-size: 1.8rem;
    color: #1f2937;
    margin-bottom: 0.5rem;
}
.verificar-card p {
    color: #6b7280;
    margin-bottom: 1.5rem;
}
.verificar-card input {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 2px solid #d1d5db;
    border-radius: 12px;
    font-size: 1.2rem;
    text-align: center;
    letter-spacing: 4px;
    font-weight: 600;
}
.verificar-card input:focus {
    border-color: var(--rw-red);
    box-shadow: 0 0 0 3px rgba(198,26,48,0.15);
    outline: none;
}
.verificar-card .btn-verificar {
    width: 100%;
    padding: 0.8rem;
    background: var(--rw-red);
    color: white;
    border: none;
    border-radius: 40px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 1rem;
}
.verificar-card .btn-verificar:hover {
    background: var(--rw-red-dark);
    transform: translateY(-2px);
}
.verificar-card .error-msg {
    background: #fee2e2;
    color: #b91c1c;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    margin-top: 1rem;
    border-left: 4px solid #ef4444;
}
.verificar-card .success-msg {
    background: #d1fae5;
    color: #065f46;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    margin-top: 1rem;
    border-left: 4px solid #10b981;
}
.verificar-card .hint {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.5rem;
}
</style>

<div class="verificar-container">
    <div class="verificar-card">
        <h2>Verificar cuenta</h2>
        <p>Ingresa el código de 6 dígitos que recibiste al registrarte.</p>
        
        <div id="mensaje"></div>
        
        <form id="verificarForm">
            <input type="text" id="codigoInput" placeholder="Código" maxlength="6" required>
            <div class="hint">Ejemplo: 123456</div>
            <button type="submit" class="btn-verificar">Verificar cuenta</button>
        </form>
        
        <div style="margin-top: 1.5rem; font-size: 0.85rem; color: #6b7280;">
            ¿No recibiste el código? <a href="#" onclick="reenviarCodigo()" style="color: var(--rw-red); text-decoration: none;">Reenviar</a>
        </div>
    </div>
</div>

<script>
// Enviar formulario de verificación
$('#verificarForm').submit(function(e) {
    e.preventDefault();
    const codigo = $('#codigoInput').val().trim();
    if (!codigo || codigo.length !== 6) {
        $('#mensaje').html('<div class="error-msg">Ingresa un código de 6 dígitos válido.</div>');
        return;
    }
    
    $('#mensaje').html('<p style="color:#6b7280;">Verificando código...</p>');

    var iconoOk = '<svg viewBox="0 0 24 24" width="16" height="16" style="vertical-align:-2px;margin-right:5px;" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> ';
    var iconoErr = '<svg viewBox="0 0 24 24" width="16" height="16" style="vertical-align:-2px;margin-right:5px;" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg> ';

    $.post('api/verificar.php', { codigo: codigo })
        .done(function(res) {
            if (res.error) {
                $('#mensaje').html('<div class="error-msg">' + iconoErr + res.error + '</div>');
            } else {
                $('#mensaje').html('<div class="success-msg">' + iconoOk + res.mensaje + '</div>');
                setTimeout(function() {
                    window.location.href = '?view=gestion_flota';
                }, 1500);
            }
        })
        .fail(function(jqXHR) {
            let msg = 'Error al verificar.';
            try {
                const resp = JSON.parse(jqXHR.responseText);
                if (resp.error) msg = resp.error;
            } catch(e) {}
            $('#mensaje').html('<div class="error-msg">' + iconoErr + msg + '</div>');
        });
});

function reenviarCodigo() {
    showConfirm('¿Reenviar código de verificación al correo?', function() {
        $.post('api/verificar.php', { reenviar: 1 })
            .done(function(res) {
                if (res.error) showAlert(res.error, 'error');
                else showAlert(res.mensaje, 'success');
            })
            .fail(function() {
                showAlert('Error al reenviar el código.', 'error');
            });
    });
}
</script>