<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Datos del nuevo administrador
$nombre = 'Admin';
$email = 'admin@rentwheels.com';
$password = 'admin';
$rol = 'administrador';

// Generar hash de la contraseña
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Verificar si ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo " El usuario admin@rentwheels.com ya existe.\n";
        echo "Puedes eliminarlo primero desde phpMyAdmin o cambiar el email.\n";
        exit;
    }

    // Insertar administrador
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, saldo_pendiente) VALUES (?, ?, ?, ?, 0)");
    $stmt->execute([$nombre, $email, $hash, $rol]);
    
    echo "Administrador creado exitosamente.\n";
    echo "Email: $email\n";
    echo "Contraseña: $password\n";
    echo "Rol: $rol\n";
    echo "\nAhora puedes iniciar sesión en login.php\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>