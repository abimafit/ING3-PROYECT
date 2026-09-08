<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'compania') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Verificar código
    if (isset($_POST['codigo'])) {
        $codigo = trim($_POST['codigo']);
        if (strlen($codigo) !== 6 || !ctype_digit($codigo)) {
            echo json_encode(['error' => 'El código debe tener 6 dígitos']);
            exit;
        }
        
        try {
            // Buscar usuario con ese código y que no esté verificado
            $stmt = $pdo->prepare("SELECT id, verificado, codigo_verificacion FROM usuarios WHERE id = ? AND codigo_verificacion = ?");
            $stmt->execute([$_SESSION['user_id'], $codigo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                echo json_encode(['error' => 'Código incorrecto. Verifica e inténtalo de nuevo.']);
                exit;
            }
            
            if ($usuario['verificado'] == 1) {
                echo json_encode(['error' => 'Esta cuenta ya está verificada.']);
                exit;
            }
            
            // Marcar como verificado
            $stmt = $pdo->prepare("UPDATE usuarios SET verificado = 1 WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            echo json_encode(['mensaje' => '¡Cuenta verificada exitosamente! Ahora puedes gestionar tu flota.']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al verificar: ' . $e->getMessage()]);
        }
        exit;
    }
    
    // Reenviar código (simulado por ahora)
    if (isset($_POST['reenviar'])) {
        $stmt = $pdo->prepare("SELECT codigo_verificacion FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $codigo = $stmt->fetchColumn();
        
        if ($codigo) {
            // Simular envío de correo (por ahora solo mostramos el código)
            echo json_encode(['mensaje' => 'Código reenviado: ' . $codigo]);
        } else {
            echo json_encode(['error' => 'No se encontró un código de verificación.']);
        }
        exit;
    }
    
    echo json_encode(['error' => 'Acción no válida']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
?>