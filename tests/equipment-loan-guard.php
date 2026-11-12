<?php
// Pruebas aisladas del modelo: no modifican la base de datos.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
class Response {
    public bool $ok = false;
    function success(...$args) { $this->ok = true; }
}
class Connection {
    public bool $transaction = false;
    public bool $exists = true;
    public bool $openLoan = false;
    public bool $failWrite = false;
    public string $state = 'Disponible';
    public int $writes = 0;
    public int $commits = 0;
    public int $rollbacks = 0;
    function beginTransaction() { $this->transaction = true; }
    function inTransaction() { return $this->transaction; }
    function commit() { $this->commits++; $this->transaction = false; }
    function rollBack() { $this->rollbacks++; $this->transaction = false; }
    function prepare($sql) { return new GuardStatement($this, $sql); }
}
class GuardStatement {
    function __construct(private Connection $db, private string $sql) {}
    function execute($params) {
        if (!$this->db->transaction) throw new RuntimeException('Consulta fuera de transaccion');
        if (str_starts_with($this->sql, 'UPDATE')) {
            if ($this->db->failWrite) throw new RuntimeException('Fallo simulado');
            $this->db->writes++;
        }
    }
    function fetchColumn() {
        if (str_contains($this->sql, 'FROM Detalle_Prestamo')) return $this->db->openLoan ? 10 : false;
        if (str_contains($this->sql, 'FROM Estado_Equipo')) return $this->db->state;
        return $this->db->exists ? 1 : false;
    }
}
require_once __DIR__ . '/../api/models/Equipo.php';
$data = ['id_equipo'=>1, 'codigo'=>'EQ-TEST', 'categoria'=>1, 'ubicacion'=>1, 'estado'=>1, 'descripcion'=>'Prueba'];
foreach (['update', 'delete'] as $action) {
    foreach (['libre', 'prestamo', 'inexistente', 'fallo'] as $case) {
        $db = new Connection();
        $response = new Response();
        $db->openLoan = $case === 'prestamo';
        $db->exists = $case !== 'inexistente';
        $db->failWrite = $case === 'fallo';
        $error = null;
        try { (new Equipo($db, $response))->$action($action === 'update' ? $data : ['Codigo'=>'EQ-TEST']); }
        catch (Throwable $caught) { $error = $caught; }
        $valid = $case === 'libre'
            ? $response->ok && $db->writes === 1 && $db->commits === 1 && !$error
            : !$response->ok && $db->writes === 0 && $db->rollbacks === 1 && $error !== null;
        if (!$valid || $db->transaction) throw new RuntimeException("FAIL: $action $case");
        echo "PASS: $action $case\n";
    }
}
$db = new Connection();
$db->state = 'Prestado';
try {
    (new Equipo($db, new Response()))->update($data);
    throw new RuntimeException('Se permitio asignar Prestado manualmente');
} catch (DomainException $error) {
    if ($db->writes !== 0 || $db->rollbacks !== 1) throw new RuntimeException('Fallo al revertir');
    echo "PASS: estado Prestado reservado al flujo de prestamos\n";
}
