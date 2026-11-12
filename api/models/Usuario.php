<?php
require_once __DIR__ . '/../core/Connection.php';
class Usuario
{
    private $db;
    function __construct(PDO $db)
    {
        $this->db = $db;
    }
    function identity($id)
    {
        $q = $this->db->prepare('SELECT u.Id_Usuario AS id, u.Id_Rol AS role_id, r.Nombre_Rol AS rol, e.Estado_Usuario AS estado, CONCAT(p.Nombres_Persona, \' \', p.Apellidos_Persona) AS nombre FROM Usuario u JOIN Persona p ON p.Id_Persona=u.Id_Persona JOIN Rol r ON r.Id_Rol=u.Id_Rol JOIN Estado_Usuario e ON e.Id_Estado=u.Id_Estado WHERE u.Id_Usuario=?');
        $q->execute([$id]);
        return $q->fetch(PDO::FETCH_ASSOC);
    }
    function hasPermission($id, $module, $action)
    {
        $q = $this->db->prepare('SELECT 1 FROM Usuario u JOIN Estado_Usuario e ON e.Id_Estado=u.Id_Estado JOIN Rol_Permiso rp ON rp.Id_Rol=u.Id_Rol JOIN Permiso p ON p.Id_Permiso=rp.Id_Permiso JOIN Modulo m ON m.Id_Modulo=p.Id_Modulo JOIN Accion a ON a.Id_Accion=p.Id_Accion WHERE u.Id_Usuario=? AND e.Estado_Usuario=\'Activo\' AND m.Nombre_Modulo=? AND a.Nombre_Accion=? LIMIT 1');
        $q->execute([$id, $module, $action]);
        return (bool)$q->fetchColumn();
    }
    function login($email, $password)
    {
        $q = $this->db->prepare('SELECT u.Id_Usuario, u.Contrasena_Usuario, p.Nombres_Persona, p.Apellidos_Persona, e.Estado_Usuario FROM Usuario u JOIN Persona p ON p.Id_Persona=u.Id_Persona JOIN Estado_Usuario e ON e.Id_Estado=u.Id_Estado WHERE p.Correo_Persona=?');
        $q->execute([$email]);
        $u = $q->fetch(PDO::FETCH_ASSOC);
        if (!$u || !password_verify($password, $u['Contrasena_Usuario']) || $u['Estado_Usuario'] !== 'Activo') return null;
        return ['id' => (int)$u['Id_Usuario'], 'nombre' => $u['Nombres_Persona'] . ' ' . $u['Apellidos_Persona']];
    }
    function register($d)
    {
        $this->db->beginTransaction();
        try {
            foreach ([['Persona', 'Correo_Persona', 'email', 'correo'], ['Persona', 'Carnet_Persona', 'carnet', 'carnet'], ['Usuario', 'Nombre_Usuario', 'username', 'nombre de usuario']] as $f) {
                $q = $this->db->prepare("SELECT 1 FROM {$f[0]} WHERE {$f[1]}=?");
                $q->execute([$d[$f[2]]]);
                if ($q->fetchColumn()) throw new DomainException('Ya existe una cuenta con ese ' . $f[3] . '.');
            }
            $role = $this->db->query("SELECT Id_Rol FROM Rol WHERE Nombre_Rol='Usuario' ORDER BY Id_Rol LIMIT 1")->fetchColumn();
            $state = $this->db->query("SELECT Id_Estado FROM Estado_Usuario WHERE Estado_Usuario='Activo' ORDER BY Id_Estado LIMIT 1")->fetchColumn();
            if (!$role || !$state) throw new RuntimeException('Missing reference data');
            $q = $this->db->prepare('INSERT INTO Persona (Nombres_Persona,Apellidos_Persona,Telefono_Persona,Correo_Persona,Carnet_Persona) VALUES (?,?,?,?,?)');
            $q->execute([$d['nombres'], $d['apellidos'], $d['telefono'] ?: null, $d['email'], $d['carnet']]);
            $id = $this->db->lastInsertId();
            $q = $this->db->prepare('INSERT INTO Usuario (Id_Persona,Nombre_Usuario,Contrasena_Usuario,Id_Rol,Id_Estado) VALUES (?,?,?,?,?)');
            $q->execute([$id, $d['username'], password_hash($d['password'], PASSWORD_DEFAULT), $role, $state]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            if ($e instanceof PDOException && ($e->errorInfo[1] ?? 0) == 1062) {
                $msg = $e->getMessage();
                $field = strpos($msg, 'uq_persona_correo') !== false ? 'correo' : (strpos($msg, 'uq_persona_carnet') !== false ? 'carnet' : (strpos($msg, 'uq_usuario_nombre') !== false ? 'nombre de usuario' : 'correo, carnet o nombre de usuario'));
                throw new DomainException('Ya existe una cuenta con ese ' . $field . '.');
            }
            throw $e;
        }
    }
    public function listarUsuarios(string $busqueda = '', int $ultimo = 0, int $pagina = 1): array
    {
        $sql = 'SELECT
                    u.Id_Usuario AS id,
                    p.Nombres_Persona AS nombres,
                    p.Apellidos_Persona AS apellidos,
                    p.Carnet_Persona AS carnet,
                    p.Correo_Persona AS correo,
                    u.Nombre_Usuario AS usuario,
                    r.Nombre_Rol AS rol,
                    e.Estado_Usuario AS estado
                FROM Usuario u
                JOIN Persona p ON p.Id_Persona = u.Id_Persona
                JOIN Rol r ON r.Id_Rol = u.Id_Rol
                JOIN Estado_Usuario e ON e.Id_Estado = u.Id_Estado
                WHERE u.Id_Usuario > ?';
        $parametros = [$busqueda !== '' ? $ultimo : 0];
        $totalPaginas = 1;

        if ($busqueda !== '') {
            $columnas = [
                "CONCAT(p.Nombres_Persona, ' ', p.Apellidos_Persona)",
                'p.Carnet_Persona',
                'p.Correo_Persona',
                'u.Nombre_Usuario',
                'r.Nombre_Rol',
                'e.Estado_Usuario'
            ];
            $condiciones = [];

            foreach ($columnas as $columna) {
                $condiciones[] = 'LOCATE(LOWER(?), LOWER(' . $columna . ')) > 0';
                $parametros[] = $busqueda;
            }

            $sql .= ' AND (' . implode(' OR ', $condiciones) . ')';
        } else {
            $totalUsuarios = (int)$this->db->query('SELECT COUNT(*) FROM Usuario')->fetchColumn();
            $totalPaginas = max(1, (int)ceil($totalUsuarios / 10));
            $pagina = min(max(1, $pagina), $totalPaginas);
        }

        $desplazamiento = $busqueda !== '' ? 0 : ($pagina - 1) * 10;
        $sql .= ' ORDER BY u.Id_Usuario ASC LIMIT 11 OFFSET ' . $desplazamiento;
        $consulta = $this->db->prepare($sql);
        $consulta->execute($parametros);
        $usuarios = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $hayMas = count($usuarios) > 10;
        $usuarios = array_slice($usuarios, 0, 10);
        $siguiente = $usuarios ? (int)$usuarios[count($usuarios) - 1]['id'] : null;

        return [
            'usuarios' => $usuarios,
            'has_more' => $hayMas,
            'next_cursor' => $siguiente,
            'paginacion' => ['hayMas' => $hayMas, 'siguiente' => $siguiente ?? 0],
            'pagina' => $pagina,
            'paginas' => $totalPaginas
        ];
    }
    public function obtenerCatalogos(): array
    {
        return [
            'roles' => $this->db->query('SELECT Id_Rol AS id, Nombre_Rol AS nombre FROM Rol ORDER BY Id_Rol')->fetchAll(PDO::FETCH_ASSOC),
            'estados' => $this->db->query('SELECT Id_Estado AS id, Estado_Usuario AS nombre FROM Estado_Usuario ORDER BY Id_Estado')->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    public function obtenerUsuario(int $idUsuario): array
    {
        $consulta = $this->db->prepare(
            'SELECT u.Id_Usuario AS id, p.Nombres_Persona AS nombres,
                    p.Apellidos_Persona AS apellidos, p.Carnet_Persona AS carnet,
                    p.Correo_Persona AS correo, p.Telefono_Persona AS telefono,
                    u.Nombre_Usuario AS usuario, u.Id_Rol AS id_rol,
                    u.Id_Estado AS id_estado, r.Nombre_Rol AS rol,
                    e.Estado_Usuario AS estado
             FROM Usuario u
             JOIN Persona p ON p.Id_Persona = u.Id_Persona
             JOIN Rol r ON r.Id_Rol = u.Id_Rol
             JOIN Estado_Usuario e ON e.Id_Estado = u.Id_Estado
             WHERE u.Id_Usuario = ?'
        );
        $consulta->execute([$idUsuario]);
        $usuario = $consulta->fetch(PDO::FETCH_ASSOC);
        if (!$usuario) throw new OutOfBoundsException('Usuario no encontrado.');
        return $usuario;
    }

    public function guardarUsuario(array $datos, int $idOperador, ?int $idUsuario = null): int
    {
        $this->db->beginTransaction();
        try {
            $idPersona = null;
            if ($idUsuario !== null) {
                $consulta = $this->db->prepare('SELECT Id_Persona, Id_Rol, Id_Estado FROM Usuario WHERE Id_Usuario = ? FOR UPDATE');
                $consulta->execute([$idUsuario]);
                $actual = $consulta->fetch(PDO::FETCH_ASSOC);
                if (!$actual) throw new OutOfBoundsException('Usuario no encontrado.');
                $idPersona = (int)$actual['Id_Persona'];
                if ($idUsuario === $idOperador && ((int)$actual['Id_Rol'] !== $datos['id_rol'] || (int)$actual['Id_Estado'] !== $datos['id_estado'])) {
                    throw new DomainException('No puedes cambiar tu propio rol o estado desde este mantenimiento.');
                }
            }

            foreach (['Rol' => ['Id_Rol', $datos['id_rol']], 'Estado_Usuario' => ['Id_Estado', $datos['id_estado']]] as $tabla => [$columna, $idCatalogo]) {
                $consulta = $this->db->prepare('SELECT 1 FROM ' . $tabla . ' WHERE ' . $columna . ' = ?');
                $consulta->execute([$idCatalogo]);
                if (!$consulta->fetchColumn()) throw new InvalidArgumentException('Selecciona un rol y estado existentes.');
            }

            foreach (['Correo_Persona' => 'correo', 'Carnet_Persona' => 'carnet'] as $columna => $campo) {
                $consulta = $this->db->prepare('SELECT 1 FROM Persona WHERE ' . $columna . ' = ? AND Id_Persona <> ?');
                $consulta->execute([$datos[$campo], $idPersona ?? 0]);
                if ($consulta->fetchColumn()) throw new DomainException('Ya existe una persona con ese ' . $campo . '.');
            }
            $consulta = $this->db->prepare('SELECT 1 FROM Usuario WHERE Nombre_Usuario = ? AND Id_Usuario <> ?');
            $consulta->execute([$datos['usuario'], $idUsuario ?? 0]);
            if ($consulta->fetchColumn()) throw new DomainException('Ya existe ese nombre de usuario.');

            $parametrosPersona = [$datos['nombres'], $datos['apellidos'], $datos['telefono'] ?: null, $datos['correo'], $datos['carnet']];
            if ($idUsuario === null) {
                $consulta = $this->db->prepare('INSERT INTO Persona (Nombres_Persona, Apellidos_Persona, Telefono_Persona, Correo_Persona, Carnet_Persona) VALUES (?, ?, ?, ?, ?)');
                $consulta->execute($parametrosPersona);
                $idPersona = (int)$this->db->lastInsertId();
                $consulta = $this->db->prepare('INSERT INTO Usuario (Id_Persona, Nombre_Usuario, Contrasena_Usuario, Id_Rol, Id_Estado) VALUES (?, ?, ?, ?, ?)');
                $consulta->execute([$idPersona, $datos['usuario'], password_hash($datos['contrasena'], PASSWORD_DEFAULT), $datos['id_rol'], $datos['id_estado']]);
                $idUsuario = (int)$this->db->lastInsertId();
            } else {
                $consulta = $this->db->prepare('UPDATE Persona SET Nombres_Persona = ?, Apellidos_Persona = ?, Telefono_Persona = ?, Correo_Persona = ?, Carnet_Persona = ? WHERE Id_Persona = ?');
                $consulta->execute([...$parametrosPersona, $idPersona]);
                $sql = 'UPDATE Usuario SET Nombre_Usuario = ?, Id_Rol = ?, Id_Estado = ?';
                $parametrosUsuario = [$datos['usuario'], $datos['id_rol'], $datos['id_estado']];
                if ($datos['contrasena'] !== '') {
                    $sql .= ', Contrasena_Usuario = ?';
                    $parametrosUsuario[] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
                }
                $consulta = $this->db->prepare($sql . ' WHERE Id_Usuario = ?');
                $consulta->execute([...$parametrosUsuario, $idUsuario]);
            }
            $this->db->commit();
            return $idUsuario;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            if ($error instanceof PDOException && ($error->errorInfo[1] ?? 0) == 1062) {
                throw new DomainException('El correo, carnet o nombre de usuario ya esta registrado.');
            }
            throw $error;
        }
    }

    public function desactivarUsuario(int $idUsuario, int $idOperador): void
    {
        if ($idUsuario === $idOperador) throw new DomainException('No puedes eliminar tu propia cuenta.');
        $this->db->beginTransaction();
        try {
            $consulta = $this->db->prepare('SELECT Id_Usuario FROM Usuario WHERE Id_Usuario = ? FOR UPDATE');
            $consulta->execute([$idUsuario]);
            if (!$consulta->fetchColumn()) throw new OutOfBoundsException('Usuario no encontrado.');
            $consulta = $this->db->query("SELECT Id_Estado FROM Estado_Usuario WHERE Estado_Usuario = 'Inactivo' ORDER BY Id_Estado LIMIT 1");
            $idEstado = $consulta->fetchColumn();
            if (!$idEstado) throw new RuntimeException('Estado Inactivo no configurado.');
            $consulta = $this->db->prepare('UPDATE Usuario SET Id_Estado = ? WHERE Id_Usuario = ?');
            $consulta->execute([$idEstado, $idUsuario]);
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }

}
