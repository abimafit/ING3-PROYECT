<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$user_id = $_SESSION['user_id'];
$rol = $_SESSION['rol'];

try {
    $stats = [];
    
    if ($rol === 'turista') {
        // Estadísticas para turista
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE turista_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_reservas'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE turista_id = ? AND estado = 'confirmada'");
        $stmt->execute([$user_id]);
        $stats['reservas_confirmadas'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE turista_id = ? AND estado = 'pendiente'");
        $stmt->execute([$user_id]);
        $stats['reservas_pendientes'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE turista_id = ? AND estado = 'cancelada'");
        $stmt->execute([$user_id]);
        $stats['reservas_canceladas'] = $stmt->fetchColumn();
        
    } elseif ($rol === 'compania') {
        // Estadísticas para compañía
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehiculos WHERE compania_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_vehiculos'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas r JOIN vehiculos v ON r.vehiculo_id = v.id WHERE v.compania_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_reservas'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas r JOIN vehiculos v ON r.vehiculo_id = v.id WHERE v.compania_id = ? AND r.estado = 'confirmada'");
        $stmt->execute([$user_id]);
        $stats['reservas_confirmadas'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas r JOIN vehiculos v ON r.vehiculo_id = v.id WHERE v.compania_id = ? AND r.estado = 'pendiente'");
        $stmt->execute([$user_id]);
        $stats['reservas_pendientes'] = $stmt->fetchColumn();
        
        // Ingresos totales
        $stmt = $pdo->prepare("SELECT SUM(r.monto_total) FROM reservas r JOIN vehiculos v ON r.vehiculo_id = v.id WHERE v.compania_id = ? AND r.estado = 'confirmada'");
        $stmt->execute([$user_id]);
        $stats['ingresos_totales'] = $stmt->fetchColumn() ?? 0;
        
    } elseif ($rol === 'administrador') {
        // Estadísticas para administrador
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
        $stats['total_usuarios'] = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM vehiculos");
        $stats['total_vehiculos'] = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM reservas");
        $stats['total_reservas'] = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado = 'confirmada'");
        $stats['reservas_confirmadas'] = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT SUM(monto_total) FROM reservas WHERE estado = 'confirmada'");
        $stats['ingresos_totales'] = $stmt->fetchColumn() ?? 0;
    }
    
    // Obtener última conexión
    $stmt = $pdo->prepare("SELECT ultima_conexion FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $stats['ultima_conexion'] = $stmt->fetchColumn();
    
    echo json_encode($stats);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener estadísticas: ' . $e->getMessage()]);
}
?>