<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

// Solo administradores
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: listar usuarios
if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT id, nombre, email, rol, ciudad, baneado, created_at, last_login FROM usuarios ORDER BY id DESC");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($usuarios);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener usuarios: ' . $e->getMessage()]);
    }
    exit;
}

// POST: banear/desbanear o cambiar rol
if ($method === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $accion = $_POST['accion'] ?? '';
    $valor = $_POST['valor'] ?? '';

    if ($user_id <= 0 || empty($accion)) {
        echo json_encode(['error' => 'Datos incompletos']);
        exit;
    }

    // No permitir banearse a sí mismo
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['error' => 'No puedes modificar tu propio usuario']);
        exit;
    }

    try {
        if ($accion === 'baneado') {
            $nuevo_estado = $valor == '1' ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE usuarios SET baneado = ? WHERE id = ?");
            $stmt->execute([$nuevo_estado, $user_id]);
            $mensaje = $nuevo_estado ? 'Usuario baneado' : 'Usuario desbaneado';
        } elseif ($accion === 'rol') {
            $roles_permitidos = ['turista', 'compania', 'administrador', 'soporte'];
            if (!in_array($valor, $roles_permitidos)) {
                echo json_encode(['error' => 'Rol no válido']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
            $stmt->execute([$valor, $user_id]);
            $mensaje = 'Rol actualizado a ' . $valor;
        } else {
            echo json_encode(['error' => 'Acción no válida']);
            exit;
        }
        echo json_encode(['mensaje' => $mensaje]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
?>