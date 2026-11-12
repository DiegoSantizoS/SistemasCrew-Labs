USE dbSistemasCrew;
START TRANSACTION;

INSERT INTO Permiso (Id_Accion, Id_Modulo)
SELECT a.Id_Accion, m.Id_Modulo
FROM Accion a CROSS JOIN Modulo m
WHERE NOT EXISTS (
    SELECT 1 FROM Permiso p WHERE p.Id_Accion = a.Id_Accion AND p.Id_Modulo = m.Id_Modulo
);


INSERT INTO Rol_Permiso (Id_Rol, Id_Permiso)
SELECT r.Id_Rol, p.Id_Permiso
FROM Rol r CROSS JOIN Permiso p
JOIN Modulo m ON m.Id_Modulo = p.Id_Modulo
JOIN Accion a ON a.Id_Accion = p.Id_Accion
WHERE (
    r.Nombre_Rol = 'Administrador'
    OR (r.Nombre_Rol = 'Encargado' AND (
        (m.Nombre_Modulo = 'Equipos' AND a.Nombre_Accion IN ('Crear', 'Leer', 'Actualizar'))
        OR (m.Nombre_Modulo = 'Prestamos' AND a.Nombre_Accion IN ('Crear', 'Leer', 'Actualizar', 'Autorizar'))
        OR (m.Nombre_Modulo IN ('Usuarios', 'Reportes') AND a.Nombre_Accion = 'Leer')
    ))
    OR (r.Nombre_Rol = 'Usuario' AND (
        (m.Nombre_Modulo = 'Prestamos' AND a.Nombre_Accion IN ('Crear', 'Leer'))
    ))
)
AND NOT EXISTS (
    SELECT 1 FROM Rol_Permiso rp WHERE rp.Id_Rol = r.Id_Rol AND rp.Id_Permiso = p.Id_Permiso
);

COMMIT;
