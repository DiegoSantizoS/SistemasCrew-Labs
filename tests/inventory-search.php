<?php
// Prueba aislada: no usa la base de datos ni modifica equipos.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
class SearchResult extends RuntimeException {
    public array $payload;
    function __construct(array $payload) { parent::__construct(); $this->payload = $payload; }
}
class Response {
    function success($message, $data, $code, $extra = null) {
        throw new SearchResult(['data' => $data, 'extra' => $extra]);
    }
}
class SearchStatement {
    public array $parameters = [];
    public array $rows = [];
    function execute($parameters) { $this->parameters = $parameters; }
    function fetchAll($mode) { return $this->rows; }
}
class Connection {
    public string $sql = '';
    public SearchStatement $statement;
    function __construct() { $this->statement = new SearchStatement(); }
    function prepare($sql) { $this->sql = $sql; return $this->statement; }
}
require_once __DIR__ . '/../api/models/Equipo.php';
function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
$db = new Connection();
$model = new Equipo($db, new Response());
foreach ([11, 10, 0] as $count) {
    $db->statement->rows = [];
    for ($id = 21; $id < 21 + $count; $id++) {
        $db->statement->rows[] = ['id' => $id, 'Codigo' => "EQ-$id"];
    }
    try { $model->getPreview(20, "monitor%'_"); }
    catch (SearchResult $result) {
        $data = $result->payload['data'];
        $page = $result->payload['extra']['paginacion'];
        check(count($data) === min(10, $count), 'Límite de diez resultados');
        check($page['hayMas'] === ($count > 10), 'Detección de siguiente página');
        check($page['siguiente'] === ($count ? 20 + min(10, $count) : 0), 'Cursor correcto');
        check(!$data || !isset($data[0]['id']), 'No expone el cursor en las filas');
        check(strpos($db->sql, 'id > :ultimo AND (') !== false, 'Cursor aplicado a ambos campos');
        check(strpos($db->sql, 'LIMIT 11') !== false, 'Consulta acotada');
        check(strpos($db->sql, "monitor") === false, 'Texto separado del SQL');
        check($db->statement->parameters['ultimo'] === 20, 'Cursor enlazado');
        echo "PASS: $count coincidencias\n";
    }
}
try { $model->getPreview(20); }
catch (SearchResult $result) {
    check(strpos($db->sql, 'LIMIT 10') !== false, 'Listado habitual acotado');
    check($db->statement->parameters === ['ultimo' => 20], 'Listado habitual sin filtro');
    echo "PASS: listado sin búsqueda\n";
}
