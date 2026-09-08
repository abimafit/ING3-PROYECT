<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['verificar_codigo'])) {
    $codigo = trim($_POST['codigo'] ?? '');
    if (strlen($codigo) !== 6 || !ctype_digit($codigo)) {
        echo json_encode(['error' => 'Código inválido. Debe tener 6 dígitos.']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ? AND codigo_verificacion = ? AND verificado = 0");
        $stmt->execute([$_SESSION['user_id'], $codigo]);
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("UPDATE usuarios SET verificado = 1 WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            echo json_encode(['mensaje' => ' Cuenta verificada exitosamente.']);
        } else {
            echo json_encode(['error' => 'Código incorrecto o la cuenta ya está verificada.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al verificar: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
?>