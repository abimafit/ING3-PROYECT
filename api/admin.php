<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: listar tickets, reservas, usuarios, reportes
if ($method === 'GET') {
    try {
        // Tickets
        if (isset($_GET['tickets'])) {
            $stmt = $pdo->query("SELECT t.*, u.nombre as turista_nombre 
                                 FROM tickets t 
                                 JOIN usuarios u ON t.turista_id = u.id 
                                 ORDER BY t.fecha_creacion DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }

        // Reservas (todas)
        if (isset($_GET['reservas'])) {
            $sql = "SELECT r.*, 
                           v.marca, v.modelo, v.imagen_url, 
                           u_t.nombre as turista_nombre,
                           u_c.nombre as compania_nombre
                    FROM reservas r 
                    JOIN vehiculos v ON r.vehiculo_id = v.id 
                    JOIN usuarios u_t ON r.turista_id = u_t.id 
                    JOIN usuarios u_c ON v.compania_id = u_c.id 
                    ORDER BY r.fecha_reserva DESC";
            $stmt = $pdo->query($sql);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }

        // Reportes (estadísticas)
        if (isset($_GET['reporte'])) {
            $stats = [];
            $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios"); 
            $stats['total_usuarios'] = $stmt->fetchColumn();
            $stmt = $pdo->query("SELECT COUNT(*) FROM vehiculos"); 
            $stats['total_vehiculos'] = $stmt->fetchColumn();
            $stmt = $pdo->query("SELECT COUNT(*) FROM reservas"); 
            $stats['total_reservas'] = $stmt->fetchColumn();
            $stmt = $pdo->query("SELECT COALESCE(SUM(monto_total), 0) FROM reservas WHERE estado='confirmada'"); 
            $stats['ingresos_totales'] = $stmt->fetchColumn();

            $stmt = $pdo->query("SELECT COUNT(*) FROM vehiculos WHERE disponible = 1");
            $stats['vehiculos_disponibles'] = $stmt->fetchColumn();
            $stats['vehiculos_ocupados'] = max((int)$stats['total_vehiculos'] - (int)$stats['vehiculos_disponibles'], 0);

            $stmt = $pdo->query("SELECT estado, COUNT(*) AS c FROM reservas GROUP BY estado");
            $por_estado = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $por_estado[$row['estado']] = (int)$row['c'];
            }
            $stats['reservas_por_estado'] = $por_estado;

            $stmt = $pdo->query("SELECT rol, COUNT(*) AS c FROM usuarios GROUP BY rol");
            $por_rol = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $por_rol[$row['rol']] = (int)$row['c'];
            }
            $stats['usuarios_por_rol'] = $por_rol;

            $stmt = $pdo->query("SELECT DATE_FORMAT(fecha_reserva, '%Y-%m') AS mes, COUNT(*) AS c 
                                 FROM reservas 
                                 WHERE fecha_reserva >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) 
                                 GROUP BY mes ORDER BY mes");
            $stats['reservas_por_mes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->query("SELECT DATE_FORMAT(fecha_reserva, '%Y-%m') AS mes, COUNT(*) AS c, COALESCE(SUM(monto_total), 0) AS ingresos 
                                 FROM reservas 
                                 WHERE estado = 'confirmada' AND fecha_reserva >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) 
                                 GROUP BY mes ORDER BY mes");
            $stats['ingresos_por_mes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->query("SELECT CONCAT(v.marca, ' ', v.modelo) AS vehiculo, COUNT(r.id) AS reservas, COALESCE(SUM(r.monto_total), 0) AS ingresos 
                                 FROM reservas r 
                                 JOIN vehiculos v ON r.vehiculo_id = v.id 
                                 GROUP BY r.vehiculo_id 
                                 ORDER BY reservas DESC, ingresos DESC 
                                 LIMIT 5");
            $stats['top_vehiculos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->query("SELECT u_c.nombre AS compania, COUNT(r.id) AS reservas, COALESCE(SUM(r.monto_total), 0) AS ingresos 
                                 FROM reservas r 
                                 JOIN vehiculos v ON r.vehiculo_id = v.id 
                                 JOIN usuarios u_c ON v.compania_id = u_c.id 
                                 GROUP BY u_c.id 
                                 ORDER BY reservas DESC, ingresos DESC");
            $stats['reservas_por_compania'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($stats);
            exit;
        }

        // Usuarios (listado)
        if (isset($_GET['usuarios'])) {
           $stmt = $pdo->query("SELECT id, nombre, email, rol, ciudad, baneado, verificado, codigo_verificacion FROM usuarios ORDER BY id");;
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }

        echo json_encode(['error' => 'Parámetro no válido']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al consultar: ' . $e->getMessage()]);
    }
    exit;
}

// POST: acciones del admin (banear, actualizar reserva, etc.)
if ($method === 'POST') {
    // Banear / Desbanear usuario
    if (isset($_POST['banear_usuario'])) {
        $usuario_id = intval($_POST['usuario_id'] ?? 0);
        $baneado = intval($_POST['baneado'] ?? 0); // 1 = baneado, 0 = desbaneado
        $motivo = trim($_POST['motivo'] ?? '');
        if ($usuario_id <= 0) {
            echo json_encode(['error' => 'ID de usuario inválido']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET baneado = ?, motivo_baneo = ? WHERE id = ?");
            $stmt->execute([$baneado, $motivo, $usuario_id]);
            echo json_encode(['mensaje' => $baneado ? 'Usuario baneado' : 'Usuario desbaneado']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
        exit;
    }

    // Actualizar reserva (cambiar estado, pagado, etc.)
    if (isset($_POST['actualizar_reserva'])) {
        $reserva_id = intval($_POST['reserva_id'] ?? 0);
        $campo = $_POST['campo'] ?? '';
        $valor = $_POST['valor'] ?? '';
        if ($reserva_id <= 0 || !in_array($campo, ['estado', 'pagado'])) {
            echo json_encode(['error' => 'Datos inválidos']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("UPDATE reservas SET $campo = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$valor, $reserva_id]);
            echo json_encode(['mensaje' => 'Reserva actualizada']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
        exit;
    }
    // Verificar empresa manualmente (admin)
if (isset($_POST['verificar_empresa'])) {
    $usuario_id = intval($_POST['usuario_id'] ?? 0);
    if ($usuario_id <= 0) {
        echo json_encode(['error' => 'ID de usuario inválido']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET verificado = 1 WHERE id = ? AND rol = 'compania' AND verificado = 0");
        $stmt->execute([$usuario_id]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['mensaje' => 'Empresa verificada manualmente']);
        } else {
            echo json_encode(['error' => 'La empresa ya está verificada o no existe']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al verificar: ' . $e->getMessage()]);
    }
    exit;
}

    echo json_encode(['error' => 'Acción no reconocida']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
?>