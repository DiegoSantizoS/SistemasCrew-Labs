<?php
require_once __DIR__ . '/../core/Connection.php';
class Usuario {
    private $db;
    function __construct(PDO $db) { $this->db = $db; }
    function identity($id) {
        $q = $this->db->prepare('SELECT u.Id_Usuario AS id, r.Nombre_Rol AS rol, e.Estado_Usuario AS estado FROM Usuario u JOIN Rol r ON r.Id_Rol=u.Id_Rol JOIN Estado_Usuario e ON e.Id_Estado=u.Id_Estado WHERE u.Id_Usuario=?');
        $q->execute([$id]); return $q->fetch(PDO::FETCH_ASSOC);
    }
    function login($email, $password) {
        $q = $this->db->prepare('SELECT u.Id_Usuario, u.Contrasena_Usuario, p.Nombres_Persona, p.Apellidos_Persona, e.Estado_Usuario FROM Usuario u JOIN Persona p ON p.Id_Persona=u.Id_Persona JOIN Estado_Usuario e ON e.Id_Estado=u.Id_Estado WHERE p.Correo_Persona=?');
        $q->execute([$email]); $u = $q->fetch(PDO::FETCH_ASSOC);
        if (!$u || !password_verify($password, $u['Contrasena_Usuario']) || $u['Estado_Usuario'] !== 'Activo') return null;
        return ['id' => (int)$u['Id_Usuario'], 'nombre' => $u['Nombres_Persona'] . ' ' . $u['Apellidos_Persona']];
    }
    function register($d) {
        $this->db->beginTransaction();
        try {
            foreach ([['Persona','Correo_Persona','email','correo'], ['Persona','Carnet_Persona','carnet','carnet'], ['Usuario','Nombre_Usuario','username','nombre de usuario']] as $f) {
                $q=$this->db->prepare("SELECT 1 FROM {$f[0]} WHERE {$f[1]}=?"); $q->execute([$d[$f[2]]]);
                if ($q->fetchColumn()) throw new DomainException('Ya existe una cuenta con ese ' . $f[3] . '.');
            }
            $role=$this->db->query("SELECT Id_Rol FROM Rol WHERE Nombre_Rol='Usuario' ORDER BY Id_Rol LIMIT 1")->fetchColumn();
            $state=$this->db->query("SELECT Id_Estado FROM Estado_Usuario WHERE Estado_Usuario='Activo' ORDER BY Id_Estado LIMIT 1")->fetchColumn();
            if (!$role || !$state) throw new RuntimeException('Missing reference data');
            $q=$this->db->prepare('INSERT INTO Persona (Nombres_Persona,Apellidos_Persona,Telefono_Persona,Correo_Persona,Carnet_Persona) VALUES (?,?,?,?,?)');
            $q->execute([$d['nombres'],$d['apellidos'],$d['telefono'] ?: null,$d['email'],$d['carnet']]);
            $id=$this->db->lastInsertId();
            $q=$this->db->prepare('INSERT INTO Usuario (Id_Persona,Nombre_Usuario,Contrasena_Usuario,Id_Rol,Id_Estado) VALUES (?,?,?,?,?)');
            $q->execute([$id,$d['username'],password_hash($d['password'], PASSWORD_DEFAULT),$role,$state]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            if ($e instanceof PDOException && ($e->errorInfo[1] ?? 0) == 1062) {
                $msg=$e->getMessage();
                $field=strpos($msg,'uq_persona_correo') !== false ? 'correo' : (strpos($msg,'uq_persona_carnet') !== false ? 'carnet' : (strpos($msg,'uq_usuario_nombre') !== false ? 'nombre de usuario' : 'correo, carnet o nombre de usuario'));
                throw new DomainException('Ya existe una cuenta con ese ' . $field . '.');
            }
            throw $e;
        }
    }
}
