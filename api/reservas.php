<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: listar reservas
if ($method === 'GET') {
    try {
        if (isset($_GET['mis_reservas'])) {
            if ($_SESSION['rol'] !== 'turista') {
                echo json_encode(['error' => 'Solo turistas pueden ver sus reservas']);
                exit;
            }

            $historial = isset($_GET['historial']) && $_GET['historial'] == 1;
            if ($historial) {
                $sql = "SELECT r.*, v.marca, v.modelo, v.imagen_url 
                        FROM reservas r 
                        JOIN vehiculos v ON r.vehiculo_id = v.id 
                        WHERE r.turista_id = ? 
                        AND r.fecha_fin < CURDATE() 
                        AND r.estado IN ('confirmada', 'cancelada')
                        ORDER BY r.fecha_fin DESC";
            } else {
                $sql = "SELECT r.*, v.marca, v.modelo, v.imagen_url 
                        FROM reservas r 
                        JOIN vehiculos v ON r.vehiculo_id = v.id 
                        WHERE r.turista_id = ? AND r.fecha_fin >= CURDATE() 
                        ORDER BY r.fecha_inicio ASC";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user_id']]);

            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $hoy = new DateTime('today');
            foreach ($reservas as &$r) {
                $fecha_fin = new DateTime($r['fecha_fin']);
                $recargo = 0;
                $recargo_porcentaje = 0;
                if ($hoy > $fecha_fin && $r['estado'] === 'confirmada') {
                    $recargo_porcentaje = 25;
                    $recargo = round($r['monto_total'] * 0.25, 2);
                }
                $r['recargo_aplicado'] = $recargo;
                $r['recargo_porcentaje'] = $recargo_porcentaje;
                $r['monto_total_con_recargo'] = round($r['monto_total'] + $recargo, 2);
            }
            echo json_encode($reservas);
        } elseif (isset($_GET['recibidas'])) {
            if ($_SESSION['rol'] !== 'compania') {
                echo json_encode(['error' => 'Solo compañías pueden ver reservas recibidas']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT r.*, v.marca, v.modelo, u.nombre as turista_nombre 
                                   FROM reservas r 
                                   JOIN vehiculos v ON r.vehiculo_id = v.id 
                                   JOIN usuarios u ON r.turista_id = u.id 
                                   WHERE v.compania_id = ? 
                                   ORDER BY r.fecha_reserva DESC");
            $stmt->execute([$_SESSION['user_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            echo json_encode(['error' => 'Parámetro no válido']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al consultar reservas: ' . $e->getMessage()]);
    }
    exit;
}

// POST: crear reserva (solo turista)
if ($method === 'POST' && !isset($_POST['pagar'])) {
    if ($_SESSION['rol'] !== 'turista') {
        http_response_code(403);
        echo json_encode(['error' => 'Solo los turistas pueden hacer reservas']);
        exit;
    }

    // Verificar saldo pendiente ANTES de cualquier otra validación
    $stmt = $pdo->prepare("SELECT saldo_pendiente FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $saldo = $stmt->fetchColumn();
    if ($saldo > 0) {
        echo json_encode(['error' => "Tienes un saldo pendiente de $${saldo}. Debes pagarlo antes de hacer una nueva reserva."]);
        exit;
    }

    $vehiculo_id = intval($_POST['vehiculo_id'] ?? 0);
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';

    if ($vehiculo_id <= 0 || empty($fecha_inicio) || empty($fecha_fin)) {
        echo json_encode(['error' => 'Datos incompletos para la reserva']);
        exit;
    }

    try {
        $hoy = new DateTime('today');
        $inicio = new DateTime($fecha_inicio);
        $fin = new DateTime($fecha_fin);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Formato de fecha inválido']);
        exit;
    }

    if ($inicio < $hoy) {
        echo json_encode(['error' => 'La fecha de inicio no puede ser anterior a hoy']);
        exit;
    }

    if ($fin <= $inicio) {
        echo json_encode(['error' => 'La fecha de fin debe ser al menos un día después de la fecha de inicio']);
        exit;
    }

    $dias = $inicio->diff($fin)->days;

    try {
        $stmt = $pdo->prepare("SELECT precio_por_dia FROM vehiculos WHERE id = ?");
        $stmt->execute([$vehiculo_id]);
        $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$vehiculo) {
            echo json_encode(['error' => 'Vehículo no existe']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas 
                               WHERE vehiculo_id = ? AND estado = 'confirmada'
                               AND (fecha_inicio <= ? AND fecha_fin >= ?)");
        $stmt->execute([$vehiculo_id, $fecha_fin, $fecha_inicio]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['error' => 'El vehículo ya está reservado en esas fechas']);
            exit;
        }

        $monto = $vehiculo['precio_por_dia'] * $dias;

        $stmt = $pdo->prepare("INSERT INTO reservas (turista_id, vehiculo_id, fecha_inicio, fecha_fin, monto_total, estado) VALUES (?, ?, ?, ?, ?, 'pendiente')");
        $stmt->execute([$_SESSION['user_id'], $vehiculo_id, $fecha_inicio, $fecha_fin, $monto]);

        //  Al crear la reserva, sumar el monto al saldo pendiente (débito)
        // Pero NO sumamos aún porque la reserva está pendiente. Solo cuando la compañía confirma se añade al saldo.

        echo json_encode(['mensaje' => 'Reserva creada. Espera a que la compañía la confirme.', 'monto' => $monto, 'dias' => $dias]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    }

    // Verificar datos
error_log("vehiculo_id: $vehiculo_id, fecha_inicio: $fecha_inicio, fecha_fin: $fecha_fin");
if ($vehiculo_id <= 0 || empty($fecha_inicio) || empty($fecha_fin)) {
    echo json_encode(['error' => 'Datos incompletos para la reserva. Vehículo: ' . $vehiculo_id . ', Inicio: ' . $fecha_inicio . ', Fin: ' . $fecha_fin]);
    exit;
}
    exit;
}

// POST: simular pago (turista)
if ($method === 'POST' && isset($_POST['pagar'])) {
    if ($_SESSION['rol'] !== 'turista') {
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
    $reserva_id = intval($_POST['reserva_id'] ?? 0);
    if ($reserva_id <= 0) {
        echo json_encode(['error' => 'ID de reserva inválido']);
        exit;
    }

    try {
        // Verificar que la reserva exista, esté confirmada y no pagada
        $stmt = $pdo->prepare("SELECT estado, pagado FROM reservas WHERE id = ? AND turista_id = ?");
        $stmt->execute([$reserva_id, $_SESSION['user_id']]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reserva) {
            echo json_encode(['error' => 'Reserva no encontrada']);
            exit;
        }
        if ($reserva['estado'] !== 'confirmada') {
            echo json_encode(['error' => 'La reserva debe estar confirmada para pagar']);
            exit;
        }
        if ($reserva['pagado'] == 1) {
            echo json_encode(['error' => 'Esta reserva ya fue pagada']);
            exit;
        }

        //  Marcar como pagada
        $stmt = $pdo->prepare("UPDATE reservas SET pagado = 1, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$reserva_id]);

        //  Actualizar saldo pendiente (opcional, si usas saldo)
        $stmt = $pdo->prepare("UPDATE usuarios SET saldo_pendiente = 0 WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        echo json_encode(['mensaje' => 'Pago confirmado. La reserva ha sido pagada.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al procesar pago: ' . $e->getMessage()]);
    }
    exit;
}

// PUT: actualizar estado (compañía/admin o turista cancelando)
if ($method === 'PUT') {
    parse_str(file_get_contents("php://input"), $data);
    if (!isset($data['actualizar_estado'])) {
        echo json_encode(['error' => 'Falta parámetro actualizar_estado']);
        exit;
    }

    $reserva_id = intval($data['reserva_id'] ?? 0);
    $nuevo_estado = $data['estado'] ?? '';
    if (!in_array($nuevo_estado, ['confirmada', 'cancelada'])) {
        echo json_encode(['error' => 'Estado no válido']);
        exit;
    }

    // Turista: solo puede cancelar si la reserva está pendiente
    if ($_SESSION['rol'] === 'turista') {
        if ($nuevo_estado !== 'cancelada') {
            echo json_encode(['error' => 'Los turistas solo pueden cancelar reservas']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("SELECT estado FROM reservas WHERE id = ? AND turista_id = ?");
            $stmt->execute([$reserva_id, $_SESSION['user_id']]);
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$reserva) {
                echo json_encode(['error' => 'Reserva no encontrada']);
                exit;
            }
            if ($reserva['estado'] !== 'pendiente') {
                echo json_encode(['error' => 'Solo puedes cancelar reservas pendientes']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE reservas SET estado = 'cancelada', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$reserva_id]);
            echo json_encode(['mensaje' => 'Reserva cancelada']);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al cancelar: ' . $e->getMessage()]);
            exit;
        }
    }

    // Compañía o Administrador: pueden confirmar o cancelar
    if (($_SESSION['rol'] === 'compania' || $_SESSION['rol'] === 'administrador')) {
        try {
            if ($_SESSION['rol'] === 'compania') {
                $stmt = $pdo->prepare("SELECT v.compania_id FROM reservas r JOIN vehiculos v ON r.vehiculo_id = v.id WHERE r.id = ?");
                $stmt->execute([$reserva_id]);
                $compania_id = $stmt->fetchColumn();
                if ($compania_id != $_SESSION['user_id']) {
                    echo json_encode(['error' => 'No tienes permiso para modificar esta reserva']);
                    exit;
                }
            }

            // Si la compañía confirma la reserva, sumar el monto al saldo pendiente del turista
            if ($nuevo_estado === 'confirmada') {
                // Obtener el monto de la reserva
                $stmt = $pdo->prepare("SELECT monto_total, turista_id FROM reservas WHERE id = ?");
                $stmt->execute([$reserva_id]);
                $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($reserva) {
                    // Sumar al saldo pendiente del turista
                    $stmt = $pdo->prepare("UPDATE usuarios SET saldo_pendiente = saldo_pendiente + ? WHERE id = ?");
                    $stmt->execute([$reserva['monto_total'], $reserva['turista_id']]);
                }
            }

            $stmt = $pdo->prepare("UPDATE reservas SET estado = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$nuevo_estado, $reserva_id]);
            echo json_encode(['mensaje' => 'Reserva ' . $nuevo_estado]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
            exit;
        }
    }

    echo json_encode(['error' => 'Acción no permitida']);
    exit;
}

// DELETE: eliminar reserva cancelada (solo turista)
if ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    $reserva_id = intval($data['reserva_id'] ?? 0);
    if ($reserva_id <= 0) {
        echo json_encode(['error' => 'ID de reserva inválido']);
        exit;
    }
    if ($_SESSION['rol'] !== 'turista') {
        echo json_encode(['error' => 'Solo los turistas pueden eliminar sus reservas']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT estado FROM reservas WHERE id = ? AND turista_id = ?");
        $stmt->execute([$reserva_id, $_SESSION['user_id']]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reserva) {
            echo json_encode(['error' => 'Reserva no encontrada']);
            exit;
        }
        if ($reserva['estado'] !== 'cancelada') {
            echo json_encode(['error' => 'Solo se pueden eliminar reservas canceladas']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ?");
        $stmt->execute([$reserva_id]);
        echo json_encode(['mensaje' => 'Reserva eliminada correctamente']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al eliminar: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
