<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y limpiar datos
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';
    $rol = $_POST['rol'];
    $ciudad = trim($_POST['ciudad'] ?? '');
    $cuenta_bancaria = trim($_POST['cuenta_bancaria'] ?? '');
    $telefono_contacto = trim($_POST['telefono_contacto'] ?? '');
    $nombre_banco = trim($_POST['nombre_banco'] ?? '');
    $qr_url = ''; // Inicializar vacío

    // Validaciones (sin cambios)
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúñÑüÜ\s\-\'\.]+$/', $nombre)) {
        header('Location: register.php?error=El nombre contiene caracteres no permitidos');
        exit;
    }
    if (strlen($nombre) < 2 || strlen($nombre) > 100) {
        header('Location: register.php?error=El nombre debe tener entre 2 y 100 caracteres');
        exit;
    }
    if (preg_match('/[<>]/', $nombre)) {
        header('Location: register.php?error=El nombre contiene caracteres no permitidos');
        exit;
    }

    if ($password !== $confirm_password) {
        header('Location: register.php?error=Las contraseñas no coinciden');
        exit;
    }

    $roles_permitidos = ['turista', 'compania'];
    if (!in_array($rol, $roles_permitidos)) {
        header('Location: register.php?error=Rol no válido');
        exit;
    }

    if ($rol === 'compania' && empty($ciudad)) {
        header('Location: register.php?error=La ciudad es obligatoria para compañías');
        exit;
    }

    // Procesar imagen QR (solo si es compañía)
    if ($rol === 'compania' && isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['qr_image'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $tipos_permitidos)) {
            header('Location: register.php?error=El QR debe ser una imagen (JPG, PNG, GIF o WEBP)');
            exit;
        }
        if ($archivo['size'] > 2 * 1024 * 1024) {
            header('Location: register.php?error=La imagen QR no puede superar los 2MB');
            exit;
        }
        $nombre_unico = 'qr_' . uniqid() . '.' . $extension;
        $ruta_destino = 'uploads/' . $nombre_unico;
        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            $qr_url = $ruta_destino;
        } else {
            header('Location: register.php?error=Error al guardar la imagen QR');
            exit;
        }
    }

    // Generar código de verificación (para compañías)
    $codigo = ($rol === 'compania') ? str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) : '';

    // Registrar usuario (¡¡AHORA CON $codigo!!)
    $resultado = registrarUsuario(
        $nombre,
        $email,
        $password,
        $rol,
        $ciudad,
        $cuenta_bancaria,
        $telefono_contacto,
        $nombre_banco,
        $qr_url,
        $codigo  // NUEVO PARÁMETRO
    );

    if ($resultado['success']) {
        // Si es compañía, mostrar mensaje con el código (por ahora en la URL)
        if ($rol === 'compania') {
            header('Location: login.php?registro=ok&codigo=' . $codigo);
        } else {
            header('Location: login.php?registro=ok');
        }
    } else {
        header('Location: register.php?error=' . urlencode($resultado['message']));
    }
    exit;
}
?>