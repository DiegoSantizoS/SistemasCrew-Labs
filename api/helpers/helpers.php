<?php
function leerBody(): array {
    $raw  = file_get_contents('php://input');
    $tipo = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($tipo, 'application/json') !== false) {
        $d = json_decode($raw, true);
        return is_array($d) ? $d : [];
    }
    parse_str($raw, $d);   // application/x-www-form-urlencoded
    return $d;
}