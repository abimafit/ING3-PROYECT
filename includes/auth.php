<?php
require_once 'config.php';

function registrarUsuario($nombre, $email, $password, $rol, $ciudad = '', $cuenta_bancaria = '', $telefono_contacto = '', $nombre_banco = '', $qr_url = '', $codigo_verificacion = '') {
    global $pdo;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, ciudad, cuenta_bancaria, telefono_contacto, nombre_banco, qr_url, codigo_verificacion, verificado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$nombre, $email, $hash, $rol, $ciudad, $cuenta_bancaria, $telefono_contacto, $nombre_banco, $qr_url, $codigo_verificacion]);
        return ['success' => true, 'message' => 'Usuario registrado correctamente'];
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            return ['success' => false, 'message' => 'El correo electrónico ya está registrado'];
        } else {
            return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }
}  

function loginUsuario($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password_hash'])) {
        //  Verificar si el usuario está baneado
        if ($user['baneado'] == 1) {
            return false; // No permitir login
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
        return true;
    }
    return false;
}

function estaLogueado() {
    return isset($_SESSION['user_id']);
}

function tieneRol($rolesPermitidos) {
    return estaLogueado() && in_array($_SESSION['rol'], $rolesPermitidos);
}

function redirigirSegunRol() {
    if (!estaLogueado()) return 'login.php';
    switch ($_SESSION['rol']) {
        case 'turista': return 'dashboard.php?view=panel';
        case 'compania': return 'dashboard.php?view=panel';
        case 'administrador': return 'dashboard.php?view=dashboard';
        case 'soporte': return 'dashboard.php?view=tickets';
        default: return 'login.php';
    }
}
?>