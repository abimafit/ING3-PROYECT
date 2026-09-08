<?php
require_once 'includes/config.php';

$email = 'admin@rentwheels.com';
$password = 'admin'; // La contraseña que ingresas

$stmt = $pdo->prepare("SELECT password_hash FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$hash = $stmt->fetchColumn();

if ($hash && password_verify($password, $hash)) {
    echo "La contraseña es correcta.";
} else {
    echo "La contraseña NO coincide o el usuario no existe.";
}
?>