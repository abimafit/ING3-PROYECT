<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: recuperar_contrasena.php');
    exit;
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    header('Location: recuperar_contrasena.php?error=Ingresa un correo electrónico');
    exit;
}

// Verificar que el usuario exista
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: recuperar_contrasena.php?error=No existe una cuenta con ese correo');
    exit;
}

// Generar token único
$token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimales
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Guardar token en la BD
$stmt = $pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
$stmt->execute([$token, $expiry, $usuario['id']]);

// Construir enlace de recuperación (URL completa)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . '://' . $host . '/rentwheels/';
$reset_link = $base_url . 'restablecer_contrasena.php?token=' . $token;

// Redirigir con mensaje de éxito y el token (para pruebas en local)
header('Location: recuperar_contrasena.php?success=' . urlencode('Te hemos enviado un enlace de recuperación.') . '&token=' . urlencode($token));
exit;
?>