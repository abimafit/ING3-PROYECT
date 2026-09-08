<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (loginUsuario($email, $password)) {
        // Guardar última conexión
        global $pdo;
        $stmt = $pdo->prepare("UPDATE usuarios SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        session_regenerate_id(true);
        header('Location: ' . redirigirSegunRol());
        exit;
    } else {
        header('Location: login.php?error=1');
        exit;
    }
}
?>