<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: recuperar_contrasena.php');
    exit;
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($token) || empty($password) || empty($confirm_password)) {
    header('Location: restablecer_contrasena.php?token=' . urlencode($token) . '&error=Todos los campos son obligatorios');
    exit;
}

if ($password !== $confirm_password) {
    header('Location: restablecer_contrasena.php?token=' . urlencode($token) . '&error=Las contraseñas no coinciden');
    exit;
}

if (strlen($password) < 4) {
    header('Location: restablecer_contrasena.php?token=' . urlencode($token) . '&error=La contraseña debe tener al menos 4 caracteres');
    exit;
}

// Verificar token
$stmt = $pdo->prepare("SELECT id, reset_token_expiry FROM usuarios WHERE reset_token = ?");
$stmt->execute([$token]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: recuperar_contrasena.php?error=Token inválido');
    exit;
}

if (strtotime($usuario['reset_token_expiry']) < time()) {
    header('Location: recuperar_contrasena.php?error=El enlace ha expirado. Solicita uno nuevo.');
    exit;
}

// Actualizar contraseña y limpiar token
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
$stmt->execute([$hash, $usuario['id']]);

// Redirigir a login con mensaje de éxito
header('Location: login.php?recuperado=ok');
exit;
?>