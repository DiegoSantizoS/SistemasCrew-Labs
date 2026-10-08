-- =====================================================================
-- Datos de prueba: dbSistemasCrew
-- Motor: MySQL 8.0+
--
-- REQUISITO: ejecutar DESPUÉS del script de creación de la base de datos
-- (el que ya incluye los datos semilla de Rol, Estado_Usuario, Estado_Equipo,
-- Estado_Prestamo, Modulo y Accion). Este script asume una BD recién creada,
-- con los AUTO_INCREMENT empezando en 1, porque referencia los IDs por orden.
--
-- Tablas que llena:
--   Persona, Usuario, Permiso, Rol_Permiso, Categoria, Ubicacion, Equipo,
--   Tasa_Multa, Prestamo, Detalle_Prestamo, Historial, Bitacora
--
-- Las fechas de los préstamos son RELATIVAS a NOW() para que siempre haya
-- préstamos vigentes y vencidos sin importar cuándo se ejecute el script.
--
-- Contraseña de TODOS los usuarios de prueba:  Test1234!   (hash bcrypt real)
-- Convención de multas: Monto es un cargo fijo por tramo de días de retraso
-- (no se multiplica por día). Moneda: quetzales (Q).
-- =====================================================================

USE dbSistemasCrew;
SET NAMES utf8mb4;
SET @ahora := NOW();

START TRANSACTION;

-- =====================================================================
-- PERSONAS (12) - las personas 11 y 12 NO tienen usuario (prueba de LEFT JOIN)
-- =====================================================================
INSERT INTO Persona (Nombres_Persona, Apellidos_Persona, Telefono_Persona, Correo_Persona, Carnet_Persona) VALUES
    ('Carlos Eduardo',  'Méndez Ruiz',        '+502 5512-3401', 'cmendez@crew.edu.gt',     'ADM-0001'),   -- 1
    ('María José',      'López Orellana',     '+502 5523-4402', 'mlopez@crew.edu.gt',      'ENC-0001'),   -- 2
    ('Jorge Luis',      'Ramírez Castillo',   '+502 5534-4403', 'jramirez@crew.edu.gt',    'ENC-0002'),   -- 3
    ('Ana Lucía',       'Pérez Gómez',        '+502 4145-1104', 'aperez@crew.edu.gt',      '202100123'),  -- 4
    ('Diego Alejandro', 'Hernández Cifuentes','+502 4156-2205', 'dhernandez@crew.edu.gt',  '202100456'),  -- 5
    ('Andrea Sofía',    'García Monzón',      '+502 4167-3306', 'agarcia@crew.edu.gt',     '202200789'),  -- 6
    ('Luis Fernando',   'Morales Barrios',    '+502 4178-4407', 'lmorales@crew.edu.gt',    '202201012'),  -- 7
    ('Valeria',         'Cabrera Juárez',     '+502 4189-5508', 'vcabrera@crew.edu.gt',    '202300345'),  -- 8
    ('Kevin Josué',     'Estrada Paz',        NULL,             'kestrada@crew.edu.gt',    '202000678'),  -- 9
    ('Pamela',          'Reyes Lemus',        '+502 4190-7710', 'preyes@crew.edu.gt',      '201900901'),  -- 10
    ('Roberto',         'Velásquez Sosa',     '+502 4201-8811', 'rvelasquez@crew.edu.gt',  '202301234'),  -- 11
    ('Daniela',         'Castañeda Ordóñez',  NULL,             'dcastaneda@crew.edu.gt',  '202401567');  -- 12

-- =====================================================================
-- USUARIOS (10)
-- Roles:   1=Administrador, 2=Encargado, 3=Usuario
-- Estados: 1=Activo, 2=Inactivo, 3=Bloqueado
-- =====================================================================
INSERT INTO Usuario (Id_Persona, Nombre_Usuario, Contrasena_Usuario, Id_Rol, Id_Estado) VALUES
    (1,  'cmendez',    '$2b$10$ICAnUVr6Z4fxxIUrhEfIFuLmSZLT7XyOe8jAj/pIwm2WTXq1jEWWC', 1, 1),  -- 1 Admin
    (2,  'mlopez',     '$2b$10$ICAnUVr6Z4fxxIUrhEfIFuLmSZLT7XyOe8jAj/pIwm2WTXq1jEWWC', 2, 1),  -- 2 Encargada
    (3,  'jramirez',   '$2b$10$ICAnUVr6Z4fxxIUrhEfIFuLmSZLT7XyOe8jAj/pIwm2WTXq1jEWWC', 2, 1),  -- 3 Encargado
    (4,  'aperez',     '$2b$10$ICAnUVr6Z4fxxIUrhEfIFuLmSZLT7XyOe8jAj/pIwm2WTXq1jEWWC', 3, 1),  -- 4
    (5,  'dhernandez', '$2b$10$ICAnUVr6Z4fxxIUrhEfIFuLmSZLT7XyOe8jAj/pIwm2WTXq1jEWWC', 3, 1),  -- 5
    (6,  'agarcia',    '$2b$10$ICAnUVr6Z4fxxIUrhEfIFuLmSZLT7XyOe8jAj/pIwm2WTXq1jEWWC', 3, 1),  -- 6
    (7,  'lmorales',   '$2b$10$ICAnUVr6Z4fxxIUrhEfIFuLmSZLT7XyOe8jAj/pIwm2WTXq1jEWWC', 3, 1),  -- 7
    (8,  'vcabrera',   '$2b$10$ICAnUVr6Z4fxxIUrhEfIFuLmSZLT7XyOe8jAj/pIwm2WTXq1jEWWC', 3, 1),  -- 8
    (9,  'kestrada',   '$2b$10$ICAnUVr6Z4fxxIUrhEfIFuLmSZLT7XyOe8jAj/pIwm2WTXq1jEWWC', 3, 2),  -- 9  Inactivo
    (10, 'preyes',     '$2b$10$ICAnUVr6Z4fxxIUrhEfIFuLmSZLT7XyOe8jAj/pIwm2WTXq1jEWWC', 3, 3);  -- 10 Bloqueado

-- =====================================================================
-- PERMISOS (RBAC): todas las combinaciones Acción x Módulo (5 x 4 = 20)
-- =====================================================================
INSERT INTO Permiso (Id_Accion, Id_Modulo)
SELECT a.Id_Accion, m.Id_Modulo
FROM Modulo m
CROSS JOIN Accion a
ORDER BY m.Id_Modulo, a.Id_Accion;

-- Administrador: todos los permisos
INSERT INTO Rol_Permiso (Id_Rol, Id_Permiso)
SELECT r.Id_Rol, p.Id_Permiso
FROM Rol r
CROSS JOIN Permiso p
WHERE r.Nombre_Rol = 'Administrador';

-- Encargado: gestiona equipos y préstamos (incluye Autorizar), consulta usuarios y reportes
INSERT INTO Rol_Permiso (Id_Rol, Id_Permiso)
SELECT r.Id_Rol, p.Id_Permiso
FROM Rol r
CROSS JOIN Permiso p
JOIN Modulo m ON m.Id_Modulo = p.Id_Modulo
JOIN Accion a ON a.Id_Accion = p.Id_Accion
WHERE r.Nombre_Rol = 'Encargado'
  AND (   (m.Nombre_Modulo = 'Equipos'   AND a.Nombre_Accion IN ('Crear','Leer','Actualizar'))
       OR (m.Nombre_Modulo = 'Prestamos' AND a.Nombre_Accion IN ('Crear','Leer','Actualizar','Autorizar'))
       OR (m.Nombre_Modulo IN ('Usuarios','Reportes') AND a.Nombre_Accion = 'Leer'));

-- Usuario: consulta equipos y solicita/consulta sus préstamos
INSERT INTO Rol_Permiso (Id_Rol, Id_Permiso)
SELECT r.Id_Rol, p.Id_Permiso
FROM Rol r
CROSS JOIN Permiso p
JOIN Modulo m ON m.Id_Modulo = p.Id_Modulo
JOIN Accion a ON a.Id_Accion = p.Id_Accion
WHERE r.Nombre_Rol = 'Usuario'
  AND (   (m.Nombre_Modulo = 'Equipos'   AND a.Nombre_Accion = 'Leer')
       OR (m.Nombre_Modulo = 'Prestamos' AND a.Nombre_Accion IN ('Crear','Leer')));

-- =====================================================================
-- CATEGORÍAS (8) - la 8 está inactiva
-- =====================================================================
INSERT INTO Categoria (Nombre_Categoria, Descripcion_Categoria, Activo) VALUES
    ('Microscopios',                 'Microscopios ópticos para prácticas de biología',                 1),  -- 1
    ('Instrumentos de medición',     'Multímetros, osciloscopios y fuentes de poder',                   1),  -- 2
    ('Cristalería de laboratorio',   'Beakers, matraces, probetas y material de vidrio',                1),  -- 3
    ('Computadoras portátiles',      'Laptops para uso en laboratorio y proyectos',                     1),  -- 4
    ('Balanzas',                     'Balanzas analíticas y de precisión',                              1),  -- 5
    ('Microcontroladores y kits',    'Arduino, Raspberry Pi y kits de electrónica',                     1),  -- 6
    ('Audiovisual',                  'Proyectores y equipo de presentación',                            1),  -- 7
    ('Equipo obsoleto',              'Categoría en desuso, solo para equipo dado de baja',              0);  -- 8

-- =====================================================================
-- UBICACIONES (6)
-- =====================================================================
INSERT INTO Ubicacion (Nombre_Ubicacion, Edificio_Ubicacion) VALUES
    ('Laboratorio de Física',      'Edificio T-3'),   -- 1
    ('Laboratorio de Química',     'Edificio T-3'),   -- 2
    ('Laboratorio de Electrónica', 'Edificio T-5'),   -- 3
    ('Laboratorio de Cómputo',     'Edificio S-12'),  -- 4
    ('Bodega Central',             'Edificio T-1'),   -- 5
    ('Taller de Mantenimiento',    NULL);             -- 6 (sin edificio: prueba de NULL)

-- =====================================================================
-- EQUIPO (21)
-- Estados: 1=Disponible, 2=Prestado, 3=En reparación, 4=Dado de baja
-- Los equipos 9, 10 y 21 no tienen número de serie a propósito
-- (prueba del COALESCE 'No disponible' en vwEquipo).
-- Coherencia: los equipos "Prestado" (11, 15, 17, 19) son exactamente los que
-- tienen un Detalle_Prestamo sin Fecha_Devolucion en préstamos Activo/Vencido.
-- =====================================================================
INSERT INTO Equipo
    (Codigo_Equipo, Id_Categoria, Id_Ubicacion, Id_Estado_Equipo, Marca_Equipo, Modelo_Equipo, Serie_Equipo,
     Descripcion_Equipo, Imagen_Equipo, Fecha_Registro_Equipo, Fecha_Baja) VALUES
    ('EQ-MIC-001', 1, 2, 1, 'Olympus',   'CX23',                 'OLY-CX23-10021', 'Microscopio binocular, objetivos 4x/10x/40x/100x', 'https://ejemplo.edu.gt/img/equipos/eq-mic-001.jpg', '2024-02-15 09:00:00', NULL),  -- 1
    ('EQ-MIC-002', 1, 2, 1, 'Olympus',   'CX23',                 'OLY-CX23-10022', 'Microscopio binocular, objetivos 4x/10x/40x/100x', 'https://ejemplo.edu.gt/img/equipos/eq-mic-002.jpg', '2024-02-15 09:05:00', NULL),  -- 2
    ('EQ-MIC-003', 1, 2, 1, 'Motic',     'BA210',                'MOT-BA210-3345', 'Microscopio trinocular con cámara integrada',      NULL,                                                   '2024-02-15 09:10:00', NULL),  -- 3
    ('EQ-ELE-001', 2, 3, 1, 'Fluke',     '87V',                  'FLK-87V-5501',   'Multímetro digital True RMS',                      'https://ejemplo.edu.gt/img/equipos/eq-ele-001.jpg', '2024-03-04 10:00:00', NULL),  -- 4
    ('EQ-ELE-002', 2, 3, 1, 'Fluke',     '87V',                  'FLK-87V-5502',   'Multímetro digital True RMS',                      'https://ejemplo.edu.gt/img/equipos/eq-ele-002.jpg', '2024-03-04 10:05:00', NULL),  -- 5
    ('EQ-ELE-003', 2, 3, 1, 'Rigol',     'DS1054Z',              'RG-DS1054Z-0387','Osciloscopio digital 4 canales, 50 MHz',           'https://ejemplo.edu.gt/img/equipos/eq-ele-003.jpg', '2024-03-04 10:30:00', NULL),  -- 6
    ('EQ-ELE-004', 2, 3, 1, 'Tektronix', 'TBS1052B',             'TEK-TBS-2291',   'Osciloscopio digital 2 canales, 50 MHz',           NULL,                                                   '2024-08-19 11:00:00', NULL),  -- 7
    ('EQ-ELE-005', 2, 3, 3, 'Rigol',     'DP832',                'RG-DP832-1150',  'Fuente de poder programable, 3 canales',           NULL,                                                   '2024-03-04 10:45:00', NULL),  -- 8 En reparación
    ('EQ-CRI-001', 3, 2, 1, 'Pyrex',     'Set de beakers',       NULL,             'Juego de 6 beakers de 50 a 1000 ml',               NULL,                                                   '2024-02-20 08:30:00', NULL),  -- 9  sin serie
    ('EQ-CRI-002', 3, 2, 1, 'Pyrex',     'Set Erlenmeyer',       NULL,             'Juego de 6 matraces Erlenmeyer de 50 a 1000 ml',   NULL,                                                   '2024-02-20 08:35:00', NULL),  -- 10 sin serie
    ('EQ-COM-001', 4, 4, 2, 'Dell',      'Latitude 3420',        'DL-3420-7781',   'Laptop i5, 16 GB RAM, 512 GB SSD',                 'https://ejemplo.edu.gt/img/equipos/eq-com-001.jpg', '2025-01-13 09:00:00', NULL),  -- 11 Prestado
    ('EQ-COM-002', 4, 4, 1, 'Dell',      'Latitude 3420',        'DL-3420-7782',   'Laptop i5, 16 GB RAM, 512 GB SSD',                 'https://ejemplo.edu.gt/img/equipos/eq-com-002.jpg', '2025-01-13 09:05:00', NULL),  -- 12
    ('EQ-COM-003', 4, 6, 3, 'Lenovo',    'ThinkPad E14',         'LN-E14-3310',    'Laptop Ryzen 5, 16 GB RAM, 512 GB SSD',            NULL,                                                   '2025-06-02 15:00:00', NULL),  -- 13 En reparación
    ('EQ-BAL-001', 5, 2, 1, 'Ohaus',     'Explorer EX224',       'OH-EX224-6620',  'Balanza analítica, precisión 0.1 mg',              'https://ejemplo.edu.gt/img/equipos/eq-bal-001.jpg', '2024-02-22 09:00:00', NULL),  -- 14
    ('EQ-BAL-002', 5, 2, 2, 'Ohaus',     'Scout SPX222',         'OH-SPX222-4410', 'Balanza de precisión, capacidad 220 g',            NULL,                                                   '2024-02-22 09:05:00', NULL),  -- 15 Prestado
    ('EQ-KIT-001', 6, 3, 1, 'Arduino',   'Starter Kit',          'ARD-KIT-0101',   'Kit de inicio Arduino Uno con sensores',           'https://ejemplo.edu.gt/img/equipos/eq-kit-001.jpg', '2025-01-27 10:00:00', NULL),  -- 16
    ('EQ-KIT-002', 6, 3, 2, 'Arduino',   'Starter Kit',          'ARD-KIT-0102',   'Kit de inicio Arduino Uno con sensores',           'https://ejemplo.edu.gt/img/equipos/eq-kit-002.jpg', '2025-01-27 10:05:00', NULL),  -- 17 Prestado
    ('EQ-KIT-003', 6, 3, 1, 'Raspberry Pi','Pi 4 Model B 8GB',   'RPI-4B-2201',    'Kit Raspberry Pi 4 con fuente, case y microSD',    NULL,                                                   '2025-09-15 10:30:00', NULL),  -- 18
    ('EQ-AUD-001', 7, 4, 2, 'Epson',     'PowerLite X49',        'EP-X49-9012',    'Proyector XGA 3600 lúmenes',                       'https://ejemplo.edu.gt/img/equipos/eq-aud-001.jpg', '2024-05-06 13:00:00', NULL),  -- 19 Prestado
    ('EQ-OBS-001', 8, 5, 4, 'Hameg',     'HM203',                'HM-203-0045',    'Osciloscopio analógico 20 MHz',                    NULL,                                                   '2019-08-12 09:00:00', '2026-03-10 10:15:00'),  -- 20 Baja
    ('EQ-AUD-002', 7, 5, 4, 'Epson',     'EMP-S3',               NULL,             'Proyector SVGA, lámpara agotada y carcasa dañada', NULL,                                                   '2018-05-07 09:00:00', '2026-05-22 14:30:00');  -- 21 Baja

-- =====================================================================
-- TASAS DE MULTA
-- NULL en Id_Categoria = tarifa genérica; NULL en dias_max = tramo abierto.
-- =====================================================================
INSERT INTO Tasa_Multa (Id_Categoria, dias_min, dias_max, Monto, Activo) VALUES
    (NULL, 1,    3,    10.00,  1),   -- 1 genérica: 1 a 3 días
    (NULL, 4,    7,    25.00,  1),   -- 2 genérica: 4 a 7 días
    (NULL, 8,    NULL, 50.00,  1),   -- 3 genérica: 8 días o más
    (1,    1,    3,    30.00,  1),   -- 4 Microscopios: 1 a 3 días
    (1,    4,    7,    60.00,  1),   -- 5 Microscopios: 4 a 7 días
    (1,    8,    NULL, 100.00, 1),   -- 6 Microscopios: 8 días o más
    (4,    1,    3,    20.00,  0);   -- 7 Laptops: tarifa descontinuada (inactiva)

-- =====================================================================
-- PRÉSTAMOS (9)
-- Estados: 1=Pendiente, 2=Activo, 3=Devuelto, 4=Vencido, 5=Cancelado
-- =====================================================================
INSERT INTO Prestamo
    (Id_Usuario, Id_Usuario_Autoriza, id_estado_prestamo, emision_fecha_prestamo, vencimiento_fecha_prestamo, motivo_prestamo) VALUES
    -- 1: Devuelto a tiempo (2 equipos)
    (4, 2,    3, DATE_SUB(@ahora, INTERVAL 40 DAY), DATE_SUB(@ahora, INTERVAL 33 DAY), 'Práctica de Circuitos Eléctricos I'),
    -- 2: Devuelto con 5 días de retraso (multa de microscopio)
    (5, 2,    3, DATE_SUB(@ahora, INTERVAL 30 DAY), DATE_SUB(@ahora, INTERVAL 23 DAY), 'Observación de muestras en Biología General'),
    -- 3: Devuelto a tiempo pero con daño en el equipo
    (6, 3,    3, DATE_SUB(@ahora, INTERVAL 25 DAY), DATE_SUB(@ahora, INTERVAL 18 DAY), 'Desarrollo de proyecto de programación'),
    -- 4: Activo y dentro del plazo (2 equipos fuera)
    (4, 2,    2, DATE_SUB(@ahora, INTERVAL 3 DAY),  DATE_ADD(@ahora, INTERVAL 4 DAY),  'Presentación de proyecto final de curso'),
    -- 5: Vencido, devolución parcial (1 equipo devuelto con retraso, 1 sigue fuera)
    (7, 3,    4, DATE_SUB(@ahora, INTERVAL 15 DAY), DATE_SUB(@ahora, INTERVAL 8 DAY),  'Proyecto de robótica con Arduino'),
    -- 6: Pendiente de autorización (equipos aún en bodega)
    (8, NULL, 1, @ahora,                            DATE_ADD(@ahora, INTERVAL 7 DAY),  'Práctica de laboratorio de Histología'),
    -- 7: Cancelado por el solicitante
    (5, NULL, 5, DATE_SUB(@ahora, INTERVAL 10 DAY), DATE_SUB(@ahora, INTERVAL 3 DAY),  'Práctica de mediciones eléctricas'),
    -- 8: Devuelto con 9 días de retraso (2 equipos, multa genérica de tramo abierto)
    (7, 2,    3, DATE_SUB(@ahora, INTERVAL 60 DAY), DATE_SUB(@ahora, INTERVAL 53 DAY), 'Práctica de Química Analítica'),
    -- 9: Activo y dentro del plazo
    (6, 2,    2, DATE_SUB(@ahora, INTERVAL 5 DAY),  DATE_ADD(@ahora, INTERVAL 2 DAY),  'Pesaje de reactivos para tesis');

-- =====================================================================
-- DETALLE DE PRÉSTAMOS (14)
-- Id_Estado_Salida / Id_Estado_Retorno referencian Estado_Equipo.
-- =====================================================================
INSERT INTO Detalle_Prestamo
    (Id_Prestamo, Id_Equipo, Id_Estado_Salida, Id_Estado_Retorno, Fecha_Devolucion, Id_Tasa_Multa, Multa_Prestamo, Observaciones) VALUES
    -- Préstamo 1: devuelto a tiempo, sin multa
    (1, 4,  1, 1,    DATE_SUB(@ahora, INTERVAL 34 DAY), NULL, 0.00,   'Devuelto sin novedades'),
    (1, 6,  1, 1,    DATE_SUB(@ahora, INTERVAL 34 DAY), NULL, 0.00,   NULL),
    -- Préstamo 2: 5 días tarde -> tasa 5 (microscopios, 4 a 7 días)
    (2, 1,  1, 1,    DATE_SUB(@ahora, INTERVAL 18 DAY), 5,    60.00,  'Devolución con 5 días de retraso'),
    -- Préstamo 3: regresa dañado -> queda En reparación
    (3, 13, 1, 3,    DATE_SUB(@ahora, INTERVAL 19 DAY), NULL, 0.00,   'Pantalla rayada y bisagra floja; enviada a reparación'),
    -- Préstamo 4: activo, equipos fuera
    (4, 11, 1, NULL, NULL,                              NULL, 0.00,   NULL),
    (4, 19, 1, NULL, NULL,                              NULL, 0.00,   NULL),
    -- Préstamo 5: un equipo devuelto 1 día tarde (tasa 1) y otro aún fuera
    (5, 16, 1, 1,    DATE_SUB(@ahora, INTERVAL 7 DAY),  1,    10.00,  'Devuelto con 1 día de retraso'),
    (5, 17, 1, NULL, NULL,                              NULL, 0.00,   'Pendiente de devolución'),
    -- Préstamo 6: pendiente, aún no se entrega
    (6, 2,  1, NULL, NULL,                              NULL, 0.00,   NULL),
    (6, 14, 1, NULL, NULL,                              NULL, 0.00,   NULL),
    -- Préstamo 7: cancelado
    (7, 5,  1, NULL, NULL,                              NULL, 0.00,   'Préstamo cancelado antes de la entrega'),
    -- Préstamo 8: 9 días tarde -> tasa 3 (genérica, 8 días o más)
    (8, 9,  1, 1,    DATE_SUB(@ahora, INTERVAL 44 DAY), 3,    50.00,  'Devuelto con 9 días de retraso'),
    (8, 10, 1, 1,    DATE_SUB(@ahora, INTERVAL 44 DAY), 3,    50.00,  'Falta un matraz de 250 ml; 9 días de retraso'),
    -- Préstamo 9: activo
    (9, 15, 1, NULL, NULL,                              NULL, 0.00,   NULL);

-- =====================================================================
-- HISTORIAL (movimientos de negocio)
-- =====================================================================

-- Altas de todos los equipos (no dependen de un préstamo -> Id_Prestamo NULL)
INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Fecha_Historial, Observacion)
SELECT 1, NULL, Id_Equipo, 'Alta de equipo', Fecha_Registro_Equipo, CONCAT('Alta de ', Codigo_Equipo)
FROM Equipo
ORDER BY Id_Equipo;

-- Movimientos de equipo sin préstamo
INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Fecha_Historial, Observacion) VALUES
    (1, NULL, 20, 'Baja de equipo',    '2026-03-10 10:15:00',            'Equipo obsoleto, sin repuestos disponibles'),
    (1, NULL, 21, 'Baja de equipo',    '2026-05-22 14:30:00',            'Lámpara agotada y carcasa dañada'),
    (3, NULL, 8,  'Cambio de estado',  DATE_SUB(@ahora, INTERVAL 12 DAY), 'Disponible -> En reparación: no regula voltaje en el canal 2');

-- Préstamo 1
INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Fecha_Historial, Observacion) VALUES
    (4, 1, NULL, 'Solicitud de préstamo',  DATE_SUB(@ahora, INTERVAL 40 DAY), NULL),
    (2, 1, NULL, 'Préstamo autorizado',    DATE_SUB(@ahora, INTERVAL 40 DAY), NULL),
    (2, 1, 4,    'Entrega de equipo',      DATE_SUB(@ahora, INTERVAL 40 DAY), 'Estado: Disponible'),
    (2, 1, 6,    'Entrega de equipo',      DATE_SUB(@ahora, INTERVAL 40 DAY), 'Estado: Disponible'),
    (2, 1, 4,    'Devolución de equipo',   DATE_SUB(@ahora, INTERVAL 34 DAY), 'Devuelto a tiempo'),
    (2, 1, 6,    'Devolución de equipo',   DATE_SUB(@ahora, INTERVAL 34 DAY), 'Devuelto a tiempo');

-- Préstamo 2
INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Fecha_Historial, Observacion) VALUES
    (5, 2, NULL, 'Solicitud de préstamo',  DATE_SUB(@ahora, INTERVAL 30 DAY), NULL),
    (2, 2, NULL, 'Préstamo autorizado',    DATE_SUB(@ahora, INTERVAL 30 DAY), NULL),
    (2, 2, 1,    'Entrega de equipo',      DATE_SUB(@ahora, INTERVAL 30 DAY), 'Estado: Disponible'),
    (2, 2, 1,    'Devolución de equipo',   DATE_SUB(@ahora, INTERVAL 18 DAY), 'Devuelto con 5 días de retraso'),
    (2, 2, 1,    'Multa aplicada',         DATE_SUB(@ahora, INTERVAL 18 DAY), 'Q60.00 (tasa 5: Microscopios, 4 a 7 días)');

-- Préstamo 3
INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Fecha_Historial, Observacion) VALUES
    (6, 3, NULL, 'Solicitud de préstamo',  DATE_SUB(@ahora, INTERVAL 25 DAY), NULL),
    (3, 3, NULL, 'Préstamo autorizado',    DATE_SUB(@ahora, INTERVAL 25 DAY), NULL),
    (3, 3, 13,   'Entrega de equipo',      DATE_SUB(@ahora, INTERVAL 25 DAY), 'Estado: Disponible'),
    (3, 3, 13,   'Devolución de equipo',   DATE_SUB(@ahora, INTERVAL 19 DAY), 'Devuelto con daños'),
    (3, 3, 13,   'Cambio de estado',       DATE_SUB(@ahora, INTERVAL 19 DAY), 'Disponible -> En reparación por daños reportados en la devolución');

-- Préstamo 4
INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Fecha_Historial, Observacion) VALUES
    (4, 4, NULL, 'Solicitud de préstamo',  DATE_SUB(@ahora, INTERVAL 3 DAY), NULL),
    (2, 4, NULL, 'Préstamo autorizado',    DATE_SUB(@ahora, INTERVAL 3 DAY), NULL),
    (2, 4, 11,   'Entrega de equipo',      DATE_SUB(@ahora, INTERVAL 3 DAY), 'Estado: Disponible'),
    (2, 4, 19,   'Entrega de equipo',      DATE_SUB(@ahora, INTERVAL 3 DAY), 'Estado: Disponible');

-- Préstamo 5
INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Fecha_Historial, Observacion) VALUES
    (7, 5, NULL, 'Solicitud de préstamo',  DATE_SUB(@ahora, INTERVAL 15 DAY), NULL),
    (3, 5, NULL, 'Préstamo autorizado',    DATE_SUB(@ahora, INTERVAL 15 DAY), NULL),
    (3, 5, 16,   'Entrega de equipo',      DATE_SUB(@ahora, INTERVAL 15 DAY), 'Estado: Disponible'),
    (3, 5, 17,   'Entrega de equipo',      DATE_SUB(@ahora, INTERVAL 15 DAY), 'Estado: Disponible'),
    (3, 5, NULL, 'Préstamo vencido',       DATE_SUB(@ahora, INTERVAL 8 DAY),  'Marcado como vencido al pasar la fecha límite'),
    (3, 5, 16,   'Devolución de equipo',   DATE_SUB(@ahora, INTERVAL 7 DAY),  'Devolución parcial, con 1 día de retraso'),
    (3, 5, 16,   'Multa aplicada',         DATE_SUB(@ahora, INTERVAL 7 DAY),  'Q10.00 (tasa 1: genérica, 1 a 3 días)');

-- Préstamo 6 (pendiente)
INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Fecha_Historial, Observacion) VALUES
    (8, 6, NULL, 'Solicitud de préstamo',  @ahora, 'A la espera de autorización');

-- Préstamo 7 (cancelado)
INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Fecha_Historial, Observacion) VALUES
    (5, 7, NULL, 'Solicitud de préstamo',  DATE_SUB(@ahora, INTERVAL 10 DAY), NULL),
    (5, 7, NULL, 'Préstamo cancelado',     DATE_SUB(@ahora, INTERVAL 9 DAY),  'Cancelado por el solicitante');

-- Préstamo 8
INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Fecha_Historial, Observacion) VALUES
    (7, 8, NULL, 'Solicitud de préstamo',  DATE_SUB(@ahora, INTERVAL 60 DAY), NULL),
    (2, 8, NULL, 'Préstamo autorizado',    DATE_SUB(@ahora, INTERVAL 60 DAY), NULL),
    (2, 8, 9,    'Entrega de equipo',      DATE_SUB(@ahora, INTERVAL 60 DAY), 'Estado: Disponible'),
    (2, 8, 10,   'Entrega de equipo',      DATE_SUB(@ahora, INTERVAL 60 DAY), 'Estado: Disponible'),
    (2, 8, 9,    'Devolución de equipo',   DATE_SUB(@ahora, INTERVAL 44 DAY), 'Devuelto con 9 días de retraso'),
    (2, 8, 10,   'Devolución de equipo',   DATE_SUB(@ahora, INTERVAL 44 DAY), 'Devuelto con 9 días de retraso; falta un matraz de 250 ml'),
    (2, 8, 9,    'Multa aplicada',         DATE_SUB(@ahora, INTERVAL 44 DAY), 'Q50.00 (tasa 3: genérica, 8 días o más)'),
    (2, 8, 10,   'Multa aplicada',         DATE_SUB(@ahora, INTERVAL 44 DAY), 'Q50.00 (tasa 3: genérica, 8 días o más)');

-- Préstamo 9
INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Fecha_Historial, Observacion) VALUES
    (6, 9, NULL, 'Solicitud de préstamo',  DATE_SUB(@ahora, INTERVAL 5 DAY), NULL),
    (2, 9, NULL, 'Préstamo autorizado',    DATE_SUB(@ahora, INTERVAL 5 DAY), NULL),
    (2, 9, 15,   'Entrega de equipo',      DATE_SUB(@ahora, INTERVAL 5 DAY), 'Estado: Disponible');

-- =====================================================================
-- BITÁCORA TÉCNICA (auditoría de cambios en filas, con JSON antes/después)
-- usuario_bitacora es texto a propósito: se conserva aunque el usuario se elimine.
-- =====================================================================
INSERT INTO Bitacora
    (fecha_bitacora, usuario_bitacora, accion_bitacora, tabla_afectada_bitacora, id_Fila_Bitacora, Antes_Bitacora, Nuevo_Bitacora) VALUES
    (DATE_SUB(@ahora, INTERVAL 90 DAY), 'cmendez', 'INSERT', 'Equipo',    18,
        NULL,
        '{"Codigo_Equipo": "EQ-KIT-003", "Id_Categoria": 6, "Id_Ubicacion": 3, "Id_Estado_Equipo": 1, "Marca_Equipo": "Raspberry Pi"}'),
    (DATE_SUB(@ahora, INTERVAL 40 DAY), 'mlopez',  'UPDATE', 'Equipo',    4,
        '{"Id_Estado_Equipo": 1}',
        '{"Id_Estado_Equipo": 2}'),
    (DATE_SUB(@ahora, INTERVAL 34 DAY), 'mlopez',  'UPDATE', 'Equipo',    4,
        '{"Id_Estado_Equipo": 2}',
        '{"Id_Estado_Equipo": 1}'),
    (DATE_SUB(@ahora, INTERVAL 19 DAY), 'jramirez','UPDATE', 'Equipo',    13,
        '{"Id_Estado_Equipo": 2}',
        '{"Id_Estado_Equipo": 3}'),
    (DATE_SUB(@ahora, INTERVAL 18 DAY), 'mlopez',  'UPDATE', 'Detalle_Prestamo', 3,
        '{"Id_Tasa_Multa": null, "Multa_Prestamo": 0.00}',
        '{"Id_Tasa_Multa": 5, "Multa_Prestamo": 60.00}'),
    (DATE_SUB(@ahora, INTERVAL 8 DAY),  'sistema', 'UPDATE', 'Prestamo',  5,
        '{"id_estado_prestamo": 2}',
        '{"id_estado_prestamo": 4}'),
    (DATE_SUB(@ahora, INTERVAL 20 DAY), 'cmendez', 'UPDATE', 'Usuario',   10,
        '{"Id_Estado": 1}',
        '{"Id_Estado": 3}'),
    (DATE_SUB(@ahora, INTERVAL 9 DAY),  'dhernandez', 'UPDATE', 'Prestamo', 7,
        '{"id_estado_prestamo": 1}',
        '{"id_estado_prestamo": 5}'),
    (DATE_SUB(@ahora, INTERVAL 2 DAY),  'cmendez', 'DELETE', 'Ubicacion', 7,
        '{"Id_Ubicacion": 7, "Nombre_Ubicacion": "Almacén temporal", "Edificio_Ubicacion": null}',
        NULL);

COMMIT;

-- =====================================================================
-- CONSULTAS DE VERIFICACIÓN (opcionales, descomentar para probar)
-- =====================================================================
-- Inventario con estado:
--   SELECT * FROM vwEquipo;
--
-- Préstamos vencidos con equipos pendientes de devolución:
--   SELECT p.Id_Prestamo, CONCAT(pe.Nombres_Persona,' ',pe.Apellidos_Persona) AS Solicitante,
--          e.Codigo_Equipo, p.vencimiento_fecha_prestamo,
--          DATEDIFF(NOW(), p.vencimiento_fecha_prestamo) AS Dias_Retraso
--   FROM Prestamo p
--   JOIN Usuario u   ON u.Id_Usuario = p.Id_Usuario
--   JOIN Persona pe  ON pe.Id_Persona = u.Id_Persona
--   JOIN Detalle_Prestamo d ON d.Id_Prestamo = p.Id_Prestamo AND d.Fecha_Devolucion IS NULL
--   JOIN Equipo e    ON e.Id_Equipo = d.Id_Equipo
--   WHERE p.id_estado_prestamo = 4;
--
-- Coherencia: equipos "Prestado" vs. detalles sin devolver en préstamos Activo/Vencido
-- (debe devolver 0 filas):
--   SELECT e.Codigo_Equipo FROM Equipo e
--   WHERE (e.Id_Estado_Equipo = 2) <> EXISTS (
--       SELECT 1 FROM Detalle_Prestamo d JOIN Prestamo p ON p.Id_Prestamo = d.Id_Prestamo
--       WHERE d.Id_Equipo = e.Id_Equipo AND d.Fecha_Devolucion IS NULL AND p.id_estado_prestamo IN (2,4));
--
-- Total de multas por usuario:
--   SELECT u.Nombre_Usuario, SUM(d.Multa_Prestamo) AS Total_Multas
--   FROM Detalle_Prestamo d JOIN Prestamo p ON p.Id_Prestamo = d.Id_Prestamo
--   JOIN Usuario u ON u.Id_Usuario = p.Id_Usuario
--   GROUP BY u.Nombre_Usuario HAVING Total_Multas > 0;