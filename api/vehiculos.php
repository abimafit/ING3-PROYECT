<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar verificación SOLO para compañías
if ($_SESSION['rol'] === 'compania') {
    $stmt = $pdo->prepare("SELECT verificado FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $verificado = $stmt->fetchColumn();
    if (!$verificado) {
        http_response_code(403);
        echo json_encode(['error' => 'Tu cuenta no está verificada. Contacta al administrador.']);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: obtener vehículos
if ($method === 'GET') {
    $fecha_inicio = $_GET['fecha_inicio'] ?? null;
    $fecha_fin = $_GET['fecha_fin'] ?? null;
    $compania = isset($_GET['compania']);

    try {
        if ($compania) {
            // Compañía: listar sus propios vehículos
            $stmt = $pdo->prepare("SELECT * FROM vehiculos WHERE compania_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Búsqueda pública (turista)
            $sql = "SELECT v.*, u.nombre as compania, u.ciudad 
                    FROM vehiculos v 
                    JOIN usuarios u ON v.compania_id = u.id 
                    WHERE v.disponible = 1";
            if ($fecha_inicio && $fecha_fin) {
                $sql .= " AND v.id NOT IN (
                            SELECT vehiculo_id FROM reservas 
                            WHERE estado = 'confirmada' 
                            AND (fecha_inicio <= :fecha_fin AND fecha_fin >= :fecha_inicio)
                        )";
            }
            //  CORRECCIÓN: filtrar por ciudad, no por nombre de compañía
            if (!empty($_GET['lugar'])) {
                $sql .= " AND u.ciudad LIKE :lugar";
            }
            $stmt = $pdo->prepare($sql);
            if ($fecha_inicio && $fecha_fin) {
                $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                $stmt->bindParam(':fecha_fin', $fecha_fin);
            }
            if (!empty($_GET['lugar'])) {
                $lugar = '%' . $_GET['lugar'] . '%';
                $stmt->bindParam(':lugar', $lugar);
            }
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al consultar vehículos: ' . $e->getMessage()]);
    }
    exit;
}

// POST: agregar vehículo (solo compañía)
if ($method === 'POST') {
    if ($_SESSION['rol'] !== 'compania' && $_SESSION['rol'] !== 'compañia') {
        http_response_code(403);
        echo json_encode(['error' => 'Permiso denegado. Solo compañías pueden agregar vehículos.']);
        exit;
    }

    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $anio = intval($_POST['anio'] ?? 0);
    $precio = floatval($_POST['precio_por_dia'] ?? 0);
    $imagen_url = trim($_POST['imagen_url'] ?? '');

    if ($marca === '' || $modelo === '' || $precio <= 0) {
        echo json_encode(['error' => 'Marca, modelo y precio son obligatorios']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO vehiculos (compania_id, marca, modelo, anio, precio_por_dia, imagen_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $marca, $modelo, $anio, $precio, $imagen_url]);
        echo json_encode(['mensaje' => 'Vehículo agregado correctamente']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar: ' . $e->getMessage()]);
    }
    exit;
}

// DELETE: eliminar vehículo (con validación de reservas activas)
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['error' => 'ID inválido']);
        exit;
    }
    try {
        // Verificar si tiene reservas activas (pendiente o confirmada)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE vehiculo_id = ? AND estado IN ('pendiente','confirmada')");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['error' => 'No se puede eliminar un vehículo con reservas activas']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM vehiculos WHERE id = ? AND compania_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        echo json_encode(['mensaje' => 'Vehículo eliminado']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al eliminar: ' . $e->getMessage()]);
    }
    exit;
}


// Si llega otro método
http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
?>