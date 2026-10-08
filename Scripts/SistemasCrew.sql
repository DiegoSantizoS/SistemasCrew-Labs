DROP DATABASE IF EXISTS dbSistemasCrew;
CREATE DATABASE dbSistemasCrew
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE dbSistemasCrew;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

--Tablas maestras

CREATE TABLE Persona (
    Id_Persona          INT AUTO_INCREMENT PRIMARY KEY,
    Nombres_Persona      VARCHAR(100)  NOT NULL,
    Apellidos_Persona    VARCHAR(100)  NOT NULL,
    Telefono_Persona     VARCHAR(20)   NULL,
    Correo_Persona       VARCHAR(150)  NOT NULL,
    Carnet_Persona       VARCHAR(50)   NOT NULL,
    CONSTRAINT uq_persona_correo UNIQUE (Correo_Persona),
    CONSTRAINT uq_persona_carnet UNIQUE (Carnet_Persona)
) ENGINE=InnoDB;

CREATE TABLE Rol (
    Id_Rol           INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_Rol        VARCHAR(50)   NOT NULL,
    Descripcion_Rol   VARCHAR(255)  NULL
) ENGINE=InnoDB;

CREATE TABLE Estado_Usuario (
    Id_Estado        INT AUTO_INCREMENT PRIMARY KEY,
    Estado_Usuario    VARCHAR(50)   NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Modulo (
    Id_Modulo        INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_Modulo     VARCHAR(50)   NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Accion (
    Id_Accion        INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_Accion     VARCHAR(50)   NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Categoria (
    Id_Categoria           INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_Categoria        VARCHAR(100)  NOT NULL,
    Descripcion_Categoria   VARCHAR(255)  NULL,
    Activo                  TINYINT(1)    NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE Ubicacion (
    Id_Ubicacion         INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_Ubicacion      VARCHAR(100)  NOT NULL,
    Edificio_Ubicacion    VARCHAR(100)  NULL
) ENGINE=InnoDB;

CREATE TABLE Estado_Equipo (
    id_estado_Equipo       INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_Estado_Equipo    VARCHAR(50)   NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Estado_Prestamo (
    id_estado_prestamo    INT AUTO_INCREMENT PRIMARY KEY,
    nombre_estado          VARCHAR(50)   NOT NULL
) ENGINE=InnoDB;


CREATE TABLE Usuario (
    Id_Usuario           INT AUTO_INCREMENT PRIMARY KEY,
    Id_Persona            INT           NOT NULL,
    Nombre_Usuario        VARCHAR(50)   NOT NULL,
    Contrasena_Usuario    VARCHAR(255)  NOT NULL,  -- se debe hashear (bcrypt/argon2)
    Id_Rol                INT           NOT NULL,
    Id_Estado             INT           NOT NULL,
    CONSTRAINT uq_usuario_persona UNIQUE (Id_Persona),
    CONSTRAINT uq_usuario_nombre  UNIQUE (Nombre_Usuario),
    CONSTRAINT fk_usuario_persona FOREIGN KEY (Id_Persona) REFERENCES Persona(Id_Persona)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (Id_Rol) REFERENCES Rol(Id_Rol)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_usuario_estado FOREIGN KEY (Id_Estado) REFERENCES Estado_Usuario(Id_Estado)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE Permiso (
    Id_Permiso    INT AUTO_INCREMENT PRIMARY KEY,
    Id_Accion      INT NOT NULL,
    Id_Modulo      INT NOT NULL,
    CONSTRAINT uq_permiso_accion_modulo UNIQUE (Id_Accion, Id_Modulo),
    CONSTRAINT fk_permiso_accion FOREIGN KEY (Id_Accion) REFERENCES Accion(Id_Accion)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_permiso_modulo FOREIGN KEY (Id_Modulo) REFERENCES Modulo(Id_Modulo)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE Rol_Permiso (
    Id_Rol      INT NOT NULL,
    Id_Permiso  INT NOT NULL,
    PRIMARY KEY (Id_Rol, Id_Permiso),
    CONSTRAINT fk_rolpermiso_rol FOREIGN KEY (Id_Rol) REFERENCES Rol(Id_Rol)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_rolpermiso_permiso FOREIGN KEY (Id_Permiso) REFERENCES Permiso(Id_Permiso)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;



CREATE TABLE Equipo (
    Id_Equipo                INT AUTO_INCREMENT PRIMARY KEY,
    Codigo_Equipo             VARCHAR(50)   NOT NULL,
    Id_Categoria              INT           NOT NULL,
    Id_Ubicacion              INT           NOT NULL,
    Id_Estado_Equipo          INT           NOT NULL,
    Marca_Equipo              VARCHAR(100)  NULL,
    Modelo_Equipo             VARCHAR(100)  NULL,
    Serie_Equipo              VARCHAR(100)  NULL,
    Descripcion_Equipo        VARCHAR(255)  NULL,
    Imagen_Equipo             VARCHAR(255)  NULL,      -- URL de la imagen
    Fecha_Registro_Equipo     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Fecha_Baja                DATETIME      NULL,
    CONSTRAINT uq_equipo_codigo UNIQUE (Codigo_Equipo),
    CONSTRAINT uq_equipo_serie  UNIQUE (Serie_Equipo),
    CONSTRAINT fk_equipo_categoria FOREIGN KEY (Id_Categoria) REFERENCES Categoria(Id_Categoria)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_equipo_ubicacion FOREIGN KEY (Id_Ubicacion) REFERENCES Ubicacion(Id_Ubicacion)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_equipo_estado FOREIGN KEY (Id_Estado_Equipo) REFERENCES Estado_Equipo(id_estado_Equipo)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;



CREATE TABLE Tasa_Multa (
    id_tasa        INT AUTO_INCREMENT PRIMARY KEY,
    Id_Categoria    INT             NULL,   -- si es NULL entonces es una tarifa genérica aplicable a cualquier categoría
    dias_min        INT             NOT NULL,
    dias_max        INT             NULL,   -- NULL = sin límite superior (tramo abierto)
    Monto           DECIMAL(10,2)   NOT NULL,
    Activo          TINYINT(1)      NOT NULL DEFAULT 1,
    CONSTRAINT fk_tasamulta_categoria FOREIGN KEY (Id_Categoria) REFERENCES Categoria(Id_Categoria)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;


CREATE TABLE Prestamo (
    Id_Prestamo                   INT AUTO_INCREMENT PRIMARY KEY,
    Id_Usuario                     INT       NOT NULL,   -- quién solicita
    Id_Usuario_Autoriza            INT       NULL,       -- encargado/admin que autoriza
    id_estado_prestamo             INT       NOT NULL,
    emision_fecha_prestamo         DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    vencimiento_fecha_prestamo     DATETIME  NOT NULL,
    motivo_prestamo                TEXT      NULL,
    CONSTRAINT fk_prestamo_usuario FOREIGN KEY (Id_Usuario) REFERENCES Usuario(Id_Usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_prestamo_autoriza FOREIGN KEY (Id_Usuario_Autoriza) REFERENCES Usuario(Id_Usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_prestamo_estado FOREIGN KEY (id_estado_prestamo) REFERENCES Estado_Prestamo(id_estado_prestamo)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE Detalle_Prestamo (
    Id_Detalle           INT AUTO_INCREMENT PRIMARY KEY,
    Id_Prestamo           INT             NOT NULL,
    Id_Equipo             INT             NOT NULL,
    Id_Estado_Salida      INT             NOT NULL,   -- estado del equipo al momento de prestarlo
    Id_Estado_Retorno     INT             NULL,       -- estado del equipo al momento de devolverlo
    Fecha_Devolucion      DATETIME        NULL,       -- NULL = aún no se devuelve (equipo fuera)
    Id_Tasa_Multa         INT             NULL,
    Multa_Prestamo        DECIMAL(10,2)   NULL DEFAULT 0.00,
    Observaciones         VARCHAR(255)    NULL,
    CONSTRAINT fk_detalle_prestamo FOREIGN KEY (Id_Prestamo) REFERENCES Prestamo(Id_Prestamo)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detalle_equipo FOREIGN KEY (Id_Equipo) REFERENCES Equipo(Id_Equipo)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_detalle_estado_salida FOREIGN KEY (Id_Estado_Salida) REFERENCES Estado_Equipo(id_estado_Equipo)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_detalle_estado_retorno FOREIGN KEY (Id_Estado_Retorno) REFERENCES Estado_Equipo(id_estado_Equipo)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_detalle_tasamulta FOREIGN KEY (Id_Tasa_Multa) REFERENCES Tasa_Multa(id_tasa)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;


CREATE TABLE Historial (
    Id_Historial        INT AUTO_INCREMENT PRIMARY KEY,
    Id_Usuario           INT           NOT NULL,   -- quién ejecuta la acción
    Id_Prestamo          INT           NULL,       -- NULL si la acción no está ligada a un préstamo
    Id_Equipo            INT           NULL,       -- NULL si la acción no está ligada a un equipo específico
    Accion_Historial     VARCHAR(100)  NOT NULL,
    Fecha_Historial      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Observacion          VARCHAR(255)  NULL,
    CONSTRAINT fk_historial_usuario FOREIGN KEY (Id_Usuario) REFERENCES Usuario(Id_Usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_historial_prestamo FOREIGN KEY (Id_Prestamo) REFERENCES Prestamo(Id_Prestamo)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_historial_equipo FOREIGN KEY (Id_Equipo) REFERENCES Equipo(Id_Equipo)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE Bitacora (
    id_bitacora               INT AUTO_INCREMENT PRIMARY KEY,
    fecha_bitacora             DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_bitacora           VARCHAR(100)  NULL,   -- se guarda como texto para conservar el dato aunque el usuario se elimine
    accion_bitacora            TEXT  NULL,
    tabla_afectada_bitacora    VARCHAR(100)  NULL,
    id_Fila_Bitacora           INT           NULL,
    Antes_Bitacora             JSON          NULL,
    Nuevo_Bitacora             JSON          NULL
) ENGINE=InnoDB;

CREATE TABLE IndiceEquipos(
    id_Pagina int primary key AUTO_INCREMENT,
	Ultimo_id_pagina int NOT NULL
) ENGINE=InnoDB;

INSERT INTO IndiceEquipos(Ultimo_id_pagina) VALUES(0);


-- Vistas
CREATE VIEW vwEquipo AS
SELECT e.Id_Equipo as id, e.Codigo_Equipo as Codigo, c.Nombre_Categoria as Categoria,  
u.Nombre_Ubicacion AS Ubicacion, 
eq.Nombre_Estado_Equipo AS Estado, COALESCE(e.Marca_Equipo, 'No Disponible') AS Marca, 
COALESCE(NULLIF(e.Modelo_Equipo, ''), 'No disponible') AS Modelo, 
COALESCE(NULLIF(e.Serie_Equipo, ''), 'No disponible') AS Serie,
coalesce(NULLIF(e.Descripcion_Equipo , ''), 'No disponible') AS Descripcion, 
e.Fecha_Registro_Equipo, e.Fecha_Baja from Equipo e
inner join Categoria c ON e.Id_Categoria = c.Id_Categoria 
inner join Ubicacion u on e.Id_Ubicacion = u.Id_Ubicacion
inner join Estado_Equipo eq on e.Id_Estado_Equipo = eq.Id_Estado_Equipo
ORDER BY e.Id_Equipo ASC;



CREATE INDEX idx_equipo_categoria       ON Equipo(Id_Categoria);
CREATE INDEX idx_equipo_estado          ON Equipo(Id_Estado_Equipo);
CREATE INDEX idx_equipo_ubicacion       ON Equipo(Id_Ubicacion);

CREATE INDEX idx_prestamo_estado        ON Prestamo(id_estado_prestamo);
CREATE INDEX idx_prestamo_vencimiento   ON Prestamo(vencimiento_fecha_prestamo);
CREATE INDEX idx_prestamo_usuario       ON Prestamo(Id_Usuario);

CREATE INDEX idx_detalle_equipo         ON Detalle_Prestamo(Id_Equipo);
CREATE INDEX idx_detalle_fecha_dev      ON Detalle_Prestamo(Fecha_Devolucion);

CREATE INDEX idx_historial_usuario      ON Historial(Id_Usuario);
CREATE INDEX idx_historial_prestamo     ON Historial(Id_Prestamo);
CREATE INDEX idx_historial_equipo       ON Historial(Id_Equipo);

SET FOREIGN_KEY_CHECKS = 1;

--TRIGGERS de auditoría para registrar cambios en la bitácora

--TRIGGERS de la tabla de Equipo
DELIMITER //
CREATE TRIGGER BitacoraEquipoNuevo AFTER INSERT  ON Equipo FOR EACH ROW
BEGIN
	INSERT INTO Bitacora (usuario_bitacora, accion_bitacora, tabla_afectada_bitacora,
    id_Fila_Bitacora, Nuevo_Bitacora) VALUES (
        CURRENT_USER(),
        CONCAT("Insertado el equipo con código: ", NEW.Codigo_Equipo),
        "Equipo",
        NEW.Id_Equipo,
        JSON_OBJECT(
            'Id_Equipo', NEW.Id_Equipo,
            'Codigo_Equipo', NEW.Codigo_Equipo,
            'Id_Categoria', NEW.Id_Categoria,
            'Id_Ubicacion', NEW.Id_Ubicacion,
            'Id_Estado_Equipo', NEW.Id_Estado_Equipo,
            'Marca_Equipo', NEW.Marca_Equipo,
            'Modelo_Equipo', NEW.Modelo_Equipo,
            'Serie_Equipo', NEW.Serie_Equipo,
            'Descripcion_Equipo', NEW.Descripcion_Equipo,
            'Imagen_Equipo', NEW.Imagen_Equipo,
            'Fecha_Registro_Equipo', NEW.Fecha_Registro_Equipo,
            'Fecha_Baja', NEW.Fecha_Baja
        )
    );
END //

CREATE TRIGGER BitacoraEquipoActualizar AFTER UPDATE ON Equipo FOR EACH ROW
BEGIN
	IF(NEW.Id_Estado_Equipo = 1 and OLD.Id_Estado_Equipo <>  NEW.Id_Estado_Equipo)
    THEN
		INSERT INTO Bitacora(usuario_bitacora, accion_bitacora, tabla_afectada_bitacora, 
			id_Fila_Bitacora, Antes_Bitacora, Nuevo_Bitacora)
        VALUES(
			CURRENT_USER(),
            CONCAT("Se eliminó el equipo con código: ", OLD.Codigo_Equipo),
            "Equipo",
            OLd.Id_Equipo,
            JSON_OBJECT(
            'Id_Equipo', OLD.Id_Equipo,
            'Codigo_Equipo', OLD.Codigo_Equipo,
            'Id_Categoria', OLD.Id_Categoria,
            'Id_Ubicacion', OLD.Id_Ubicacion,
            'Id_Estado_Equipo', OLD.Id_Estado_Equipo,
            'Marca_Equipo', OLD.Marca_Equipo,
            'Modelo_Equipo', OLD.Modelo_Equipo,
            'Serie_Equipo', OLD.Serie_Equipo,
            'Descripcion_Equipo', OLD.Descripcion_Equipo,
            'Imagen_Equipo', OLD.Imagen_Equipo,
            'Fecha_Registro_Equipo', OLD.Fecha_Registro_Equipo,
            'Fecha_Baja', OLD.Fecha_Baja
        ),
        JSON_OBJECT(
        'Id_Equipo', NEW.Id_Equipo,
            'Codigo_Equipo', NEW.Codigo_Equipo,
            'Id_Categoria', NEW.Id_Categoria,
            'Id_Ubicacion', NEW.Id_Ubicacion,
            'Id_Estado_Equipo', NEW.Id_Estado_Equipo,
            'Marca_Equipo', NEW.Marca_Equipo,
            'Modelo_Equipo', NEW.Modelo_Equipo,
            'Serie_Equipo', NEW.Serie_Equipo,
            'Descripcion_Equipo', NEW.Descripcion_Equipo,
            'Imagen_Equipo', NEW.Imagen_Equipo,
            'Fecha_Registro_Equipo', NEW.Fecha_Registro_Equipo,
            'Fecha_Baja', NEW.Fecha_Baja)
        );
	ELSE
		INSERT INTO Bitacora(usuario_bitacora, accion_bitacora, tabla_afectada_bitacora, 
			id_Fila_Bitacora, Antes_Bitacora, Nuevo_Bitacora)
            VALUES(
				CURRENT_USER(),
                CONCAT("Se actualizó el equipo con código: ", OLD.Codigo_Equipo),
                "Equipo",
                OLD.Id_Equipo,
                JSON_OBJECT(
            'Id_Equipo', OLD.Id_Equipo,
            'Codigo_Equipo', OLD.Codigo_Equipo,
            'Id_Categoria', OLD.Id_Categoria,
            'Id_Ubicacion', OLD.Id_Ubicacion,
            'Id_Estado_Equipo', OLD.Id_Estado_Equipo,
            'Marca_Equipo', OLD.Marca_Equipo,
            'Modelo_Equipo', OLD.Modelo_Equipo,
            'Serie_Equipo', OLD.Serie_Equipo,
            'Descripcion_Equipo', OLD.Descripcion_Equipo,
            'Imagen_Equipo', OLD.Imagen_Equipo,
            'Fecha_Registro_Equipo', OLD.Fecha_Registro_Equipo,
            'Fecha_Baja', OLD.Fecha_Baja
        ),
        JSON_OBJECT(
        'Id_Equipo', NEW.Id_Equipo,
            'Codigo_Equipo', NEW.Codigo_Equipo,
            'Id_Categoria', NEW.Id_Categoria,
            'Id_Ubicacion', NEW.Id_Ubicacion,
            'Id_Estado_Equipo', NEW.Id_Estado_Equipo,
            'Marca_Equipo', NEW.Marca_Equipo,
            'Modelo_Equipo', NEW.Modelo_Equipo,
            'Serie_Equipo', NEW.Serie_Equipo,
            'Descripcion_Equipo', NEW.Descripcion_Equipo,
            'Imagen_Equipo', NEW.Imagen_Equipo,
            'Fecha_Registro_Equipo', NEW.Fecha_Registro_Equipo,
            'Fecha_Baja', NEW.Fecha_Baja)
            );
    END IF;
END //

CREATE TRIGGER IndiceEquipo AFTER INSERT ON Equipo FOR EACH ROW 
BEGIN
	DECLARE total INT;
    SELECT COUNT(*) INTO total FROM Equipo;
    IF MOD(total,10) = 0 then
    INSERT Into IndiceEquipo VALUES(NEW.Id_Equipo);
    END IF;
END //;

DELIMITER ;
--Fin de Triggers de la tabla de Equipo


-- =====================================================================
-- DATOS SEMILLA MÍNIMOS PARA ARRANCAR EL SISTEMA
-- =====================================================================

INSERT INTO Rol (Nombre_Rol, Descripcion_Rol) VALUES
    ('Administrador', 'Acceso total al sistema, gestiona equipos, usuarios y préstamos'),
    ('Encargado',     'Autoriza y gestiona préstamos, controla el inventario'),
    ('Usuario',       'Puede solicitar préstamos de equipo');

INSERT INTO Estado_Usuario (Estado_Usuario) VALUES
    ('Activo'), ('Inactivo'), ('Bloqueado');

INSERT INTO Estado_Equipo (Nombre_Estado_Equipo) VALUES
    ('Disponible'), ('Prestado'), ('En reparación'), ('Dado de baja');

INSERT INTO Estado_Prestamo (nombre_estado) VALUES
    ('Pendiente'), ('Activo'), ('Devuelto'), ('Vencido'), ('Cancelado');

INSERT INTO Modulo (Nombre_Modulo) VALUES
    ('Equipos'), ('Prestamos'), ('Usuarios'), ('Reportes');

INSERT INTO Accion (Nombre_Accion) VALUES
    ('Crear'), ('Leer'), ('Actualizar'), ('Eliminar'), ('Autorizar');


