-- ========================================
-- DATOS DE PRUEBA — RENTWHEELS
-- ========================================
-- Ejecutar en phpMyAdmin o consola MySQL
-- Base de datos: rentwheels_db
-- Todos los usuarios tienen contraseña: admin
-- Hash bcrypt generado con: password_hash('admin', PASSWORD_DEFAULT)
-- ========================================

-- ========================================
-- USUARIOS DE PRUEBA
-- ========================================

-- TURISTA 1 (verificado, sin saldo pendiente)
INSERT INTO usuarios (nombre, email, password_hash, rol, ciudad, verificado, baneado, saldo_pendiente)
VALUES (
  'Carlos Pérez',
  'turista@test.com',
  '$2y$10$rqTcEtXu5nc0rWsw16onHOGpiVp2rg8PW.96HVB9xjK87mDqh89A2',
  'turista',
  'Ciudad de Panamá',
  1,
  0,
  0.00
);

-- TURISTA 2 (sin verificar)
INSERT INTO usuarios (nombre, email, password_hash, rol, ciudad, verificado, baneado, saldo_pendiente)
VALUES (
  'María López',
  'maria@test.com',
  '$2y$10$rqTcEtXu5nc0rWsw16onHOGpiVp2rg8PW.96HVB9xjK87mDqh89A2',
  'turista',
  'Colón',
  0,
  0,
  0.00
);

-- TURISTA 3 (baneado)
INSERT INTO usuarios (nombre, email, password_hash, rol, ciudad, verificado, baneado, saldo_pendiente)
VALUES (
  'Pedro Baneado',
  'baneado@test.com',
  '$2y$10$rqTcEtXu5nc0rWsw16onHOGpiVp2rg8PW.96HVB9xjK87mDqh89A2',
  'turista',
  'David',
  1,
  1,
  0.00
);

-- COMPAÑÍA 3 (NO verificada — para probar flujo de verificación)
-- Nota: ya existen SON@gmail.com y su@gmail.com (ambas verificadas)
INSERT INTO usuarios (nombre, email, password_hash, rol, ciudad, verificado, baneado, saldo_pendiente, codigo_verificacion, cuenta_bancaria, telefono_contacto, nombre_banco)
VALUES (
  'Autos Express',
  'autos@test.com',
  '$2y$10$rqTcEtXu5nc0rWsw16onHOGpiVp2rg8PW.96HVB9xjK87mDqh89A2',
  'compania',
  'Bocas del Toro',
  0,
  0,
  0.00,
  '123456',
  '22222222',
  '6677-8899',
  'Banco General'
);

-- SOPORTE
INSERT INTO usuarios (nombre, email, password_hash, rol, ciudad, verificado, baneado, saldo_pendiente)
VALUES (
  'Agente Soporte',
  'soporte@test.com',
  '$2y$10$rqTcEtXu5nc0rWsw16onHOGpiVp2rg8PW.96HVB9xjK87mDqh89A2',
  'soporte',
  NULL,
  1,
  0,
  0.00
);

-- ========================================
-- USUARIOS EXISTENTES EN EL DUMP (info)
-- ========================================
-- id=29: SON (compania, verificada, SON@gmail.com)
-- id=31: Yandel Sanchez (turista, xo@gmail.com)
-- id=39: Admin (administrador, admin@rentwheels.com)
-- id=42: Bahía motors (compania, verificada, su@gmail.com)

-- ========================================
-- VEHÍCULOS ADICIONALES (Autos Express)
-- ========================================
-- Los IDs de compania se asignan automáticamente, se usa subquery

INSERT INTO vehiculos (compania_id, marca, modelo, anio, precio_por_dia, disponible, imagen_url)
VALUES
  ((SELECT id FROM usuarios WHERE email = 'autos@test.com'), 'Toyota', 'Corolla', 2024, 45.00, 1, 'https://via.placeholder.com/280x180?text=Toyota+Corolla'),
  ((SELECT id FROM usuarios WHERE email = 'autos@test.com'), 'Nissan', 'Sentra', 2025, 50.00, 1, 'https://via.placeholder.com/280x180?text=Nissan+Sentra'),
  ((SELECT id FROM usuarios WHERE email = 'autos@test.com'), 'Hyundai', 'Tucson', 2026, 75.00, 1, 'https://via.placeholder.com/280x180?text=Hyundai+Tucson'),
  ((SELECT id FROM usuarios WHERE email = 'autos@test.com'), 'Kia', 'Sportage', 2025, 70.00, 0, 'https://via.placeholder.com/280x180?text=Kia+Sportage');

-- ========================================
-- RESERVAS DE PRUEBA
-- ========================================

-- Reserva PENDIENTE (turista@test.com → Acura NSX, 3 días)
INSERT INTO reservas (turista_id, vehiculo_id, fecha_inicio, fecha_fin, monto_total, estado, pagado)
VALUES (
  (SELECT id FROM usuarios WHERE email = 'turista@test.com'),
  32,
  '2026-09-15',
  '2026-09-18',
  270.00,
  'pendiente',
  0
);

-- Reserva CONFIRMADA, sin pagar (turista@test.com → Acura ADX, 2 días)
INSERT INTO reservas (turista_id, vehiculo_id, fecha_inicio, fecha_fin, monto_total, estado, pagado)
VALUES (
  (SELECT id FROM usuarios WHERE email = 'turista@test.com'),
  33,
  '2026-09-10',
  '2026-09-12',
  160.00,
  'confirmada',
  0
);

-- Reserva CONFIRMADA y PAGADA (turista@test.com → Honda CR-V, 2 días)
INSERT INTO reservas (turista_id, vehiculo_id, fecha_inicio, fecha_fin, monto_total, estado, pagado)
VALUES (
  (SELECT id FROM usuarios WHERE email = 'turista@test.com'),
  34,
  '2026-08-01',
  '2026-08-03',
  140.00,
  'confirmada',
  1
);

-- Reserva CANCELADA (turista@test.com → Honda HR-V, 2 días)
INSERT INTO reservas (turista_id, vehiculo_id, fecha_inicio, fecha_fin, monto_total, estado, pagado)
VALUES (
  (SELECT id FROM usuarios WHERE email = 'turista@test.com'),
  36,
  '2026-07-20',
  '2026-07-22',
  160.00,
  'cancelada',
  0
);

-- ========================================
-- TICKETS DE PRUEBA
-- ========================================

-- Ticket ABIERTO (sin respuesta)
INSERT INTO tickets (turista_id, asunto, mensaje, estado)
VALUES (
  (SELECT id FROM usuarios WHERE email = 'turista@test.com'),
  'Problema con reserva',
  'No puedo ver mi reserva confirmada en el panel. Aparece vacía.',
  'abierto'
);

-- Ticket RESPONDIDO
INSERT INTO tickets (turista_id, asunto, mensaje, respuesta, estado, fecha_respuesta)
VALUES (
  (SELECT id FROM usuarios WHERE email = 'turista@test.com'),
  'Consulta sobre pago',
  '¿Puedo pagar con PayPal o solo transferencia bancaria?',
  'Actualmente solo aceptamos transferencia bancaria. Estamos trabajando en integrar más métodos de pago.',
  'respondido',
  NOW()
);

-- Ticket CERRADO
INSERT INTO tickets (turista_id, asunto, mensaje, estado, cerrado_por, fecha_cerrado)
VALUES (
  (SELECT id FROM usuarios WHERE email = 'turista@test.com'),
  'Solicitud antigua',
  'Ya no necesito ayuda con el tema anterior.',
  'cerrado',
  (SELECT id FROM usuarios WHERE email = 'admin@rentwheels.com'),
  NOW()
);

-- ========================================
-- USUARIOS DE PRUEBA — RESUMEN
-- ========================================
-- 
-- Email                  | Password | Rol           | Estado
-- -----------------------|----------|---------------|------------------
-- admin@rentwheels.com   | admin    | administrador | verificado
-- SON@gmail.com          | (verif)  | compania      | verificada, QR
-- su@gmail.com           | (verif)  | compania      | verificada, QR
-- turista@test.com       | admin    | turista       | verificado
-- maria@test.com         | admin    | turista       | NO verificado
-- baneado@test.com       | admin    | turista       | BANEADO
-- autos@test.com         | admin    | compania      | NO verificado (código: 123456)
-- soporte@test.com       | admin    | soporte       | verificado
-- xo@gmail.com           | (verif)  | turista       | NO verificado
