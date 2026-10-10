<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/Usuario.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Response::error('Método no permitido.', -405, 405);
header('Cache-Control: no-store');
$d=leerBody(); auth_csrf($d['csrf'] ?? null);
foreach ($d as $v) if (!is_string($v)) Response::error('Datos no válidos.', -422, 422);
$labels=['nombres'=>'nombres','apellidos'=>'apellidos','carnet'=>'carnet','username'=>'nombre de usuario','email'=>'correo','telefono'=>'teléfono'];
foreach (['nombres'=>100,'apellidos'=>100,'carnet'=>50,'username'=>50,'email'=>150,'telefono'=>20] as $field=>$limit) {
    $d[$field]=trim($d[$field] ?? '');
    if (($field !== 'telefono' && $d[$field] === '') || mb_strlen($d[$field])>$limit) Response::error('Revisa el campo ' . $labels[$field] . ' (máximo ' . $limit . ' caracteres).', -422, 422);
}
if (!filter_var($d['email'],FILTER_VALIDATE_EMAIL)) Response::error('Introduce un correo válido.', -422, 422);
$d['password']=$d['password'] ?? '';
if (mb_strlen($d['password'])<8 || strlen($d['password'])>72 || strpos($d['password'],"\0") !== false) Response::error('La contraseña debe tener al menos 8 caracteres y como máximo 72 bytes.', -422, 422);
if ($d['password'] !== ($d['confirm'] ?? '')) Response::error('Las contraseñas no coinciden.', -422, 422);
try {
    (new Usuario(new Connection()))->register($d);
    http_response_code(201); Response::success('Cuenta creada. Ya puedes iniciar sesión.', [], 1);
} catch (DomainException $e) { Response::error($e->getMessage(), -409, 409); }
catch (Throwable $e) { Response::error('No se pudo crear la cuenta. Inténtalo nuevamente.', -500, 500); }
