<?php
header('Content-Type: application/json');
ini_set('display_errors', 0); // No mostrar errores en producción
error_reporting(E_ALL);
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: listar tickets
if ($method === 'GET' && isset($_GET['tickets'])) {
    try {
        if ($_SESSION['rol'] === 'turista') {
            $stmt = $pdo->prepare("SELECT * FROM tickets WHERE turista_id = ? ORDER BY fecha_creacion DESC");
            $stmt->execute([$_SESSION['user_id']]);
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Soporte o administrador: ver todos los tickets
            $stmt = $pdo->query("SELECT t.*, u.nombre as turista_nombre 
                                 FROM tickets t 
                                 JOIN usuarios u ON t.turista_id = u.id 
                                 ORDER BY t.fecha_creacion DESC");
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($tickets);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
    exit;
}

// POST: crear ticket (turista) o responder (soporte)
if ($method === 'POST') {
    // Caso 1: Turista envía nuevo ticket
    if ($_SESSION['rol'] === 'turista' && isset($_POST['asunto']) && isset($_POST['mensaje'])) {
        $asunto = trim($_POST['asunto']);
        $mensaje = trim($_POST['mensaje']);
        if (empty($asunto) || empty($mensaje)) {
            echo json_encode(['error' => 'Asunto y mensaje son obligatorios']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO tickets (turista_id, asunto, mensaje, estado) VALUES (?, ?, ?, 'abierto')");
            $stmt->execute([$_SESSION['user_id'], $asunto, $mensaje]);
            echo json_encode(['mensaje' => 'Ticket enviado correctamente. Te responderemos pronto.']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al guardar el ticket: ' . $e->getMessage()]);
        }
        exit;
    }

    // Caso 2: Soporte responde a un ticket
    if (($_SESSION['rol'] === 'soporte' || $_SESSION['rol'] === 'administrador') && isset($_POST['responder'])) {
        $ticket_id = intval($_POST['ticket_id'] ?? 0);
        $respuesta = trim($_POST['respuesta'] ?? '');
        if ($ticket_id <= 0 || empty($respuesta)) {
            echo json_encode(['error' => 'Datos incompletos para responder']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("UPDATE tickets SET respuesta = ?, fecha_respuesta = NOW(), estado = 'en proceso' WHERE id = ?");
            $stmt->execute([$respuesta, $ticket_id]);
            echo json_encode(['mensaje' => 'Respuesta enviada correctamente']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al responder: ' . $e->getMessage()]);
        }
        exit;
    }

    // Caso 3: Cerrar ticket (soporte)
    if (($_SESSION['rol'] === 'soporte' || $_SESSION['rol'] === 'administrador') && isset($_POST['cerrar'])) {
        $id = intval($_POST['cerrar']);
        try {
            $stmt = $pdo->prepare("UPDATE tickets SET estado = 'cerrado', cerrado_por = ?, fecha_cerrado = NOW() WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $id]);
            echo json_encode(['mensaje' => 'Ticket cerrado']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al cerrar: ' . $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['error' => 'Acción no permitida']);
    exit;
}
// DELETE: eliminar ticket (solo soporte/admin)
if ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    $ticket_id = intval($data['ticket_id'] ?? 0);
    if ($ticket_id <= 0) {
        echo json_encode(['error' => 'ID de ticket inválido']);
        exit;
    }
    if ($_SESSION['rol'] !== 'soporte' && $_SESSION['rol'] !== 'administrador') {
        echo json_encode(['error' => 'No tienes permiso para eliminar tickets']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = ?");
        $stmt->execute([$ticket_id]);
        echo json_encode(['mensaje' => 'Ticket eliminado correctamente']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al eliminar: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
?>