<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../api/models/Usuario.php';
class FailingConnection extends Connection { public function prepare(string $query, array $options = []): PDOStatement|false { if (strpos($query, 'INSERT INTO Usuario') === 0) throw new LogicException('Test insert failure'); return parent::prepare($query,$options); } }
class DuplicateConnection extends Connection { public function prepare(string $query, array $options = []): PDOStatement|false { if (strpos($query, 'SELECT 1 FROM ') === 0) $query=str_replace(' WHERE ', ' WHERE 0 AND ', $query); return parent::prepare($query,$options); } }
$db = new Connection();
$stamp='authcheck_'.bin2hex(random_bytes(5));
$email=$stamp.'@example.com';
$d=['nombres'=>'Prueba','apellidos'=>'Autenticación','telefono'=>'','carnet'=>$stamp,'username'=>$stamp,'email'=>$email,'password'=>' Pass12345 ','confirm'=>' Pass12345 '];
$cookie=tempnam(sys_get_temp_dir(),'auth');
$base=getenv('AUTH_TEST_URL') ?: 'http://localhost/SistemasCrew-Labs/';
function request($route,$data=null,$method=null) {
    global $cookie,$base;
    $ch=curl_init($base.$route);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEJAR=>$cookie,CURLOPT_COOKIEFILE=>$cookie,CURLOPT_FOLLOWLOCATION=>false]);
    if ($data !== null) {curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-Type: application/json']);}
    if ($method) curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method);
    $body=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);return [$code,$body];
}
function expect($label,$result,$code) {
    if ($result[0] !== $code) throw new RuntimeException($label.': expected '.$code.', got '.$result[0].' '.substr($result[1],0,200));
    echo 'PASS '.$label."\n";return json_decode($result[1],true);
}
function token() {
    $r=request('login.php');
    if (!preg_match('/name="csrf-token" content="([a-f0-9]+)"/',$r[1],$m)) throw new RuntimeException('No CSRF token');
    return $m[1];
}
try {
    expect('dashboard redirect',request('index.php'),302);
    foreach (['Equipos','Categorias','Ubicaciones','Estados_Equipo','PaginasEquipos'] as $endpoint) expect('anonymous '.$endpoint,request('api/admin/'.$endpoint.'.php'),401);
    expect('missing CSRF',request('api/auth/login.php',['email'=>$email,'password'=>$d['password']]),403);
    $d['csrf']=token();
    $bad=$d;$bad['confirm']='different';expect('password confirmation',request('api/auth/register.php',$bad),422);
    expect('registration',request('api/auth/register.php',$d),201);
    foreach (['email','carnet','username'] as $field) {
        $dupe=$d;$dupe['email']='other_'.$email;$dupe['carnet']='other_'.$stamp;$dupe['username']='other_'.$stamp;$dupe[$field]=$d[$field];
        expect('duplicate '.$field,request('api/auth/register.php',$dupe),409);
    }
    $q=$db->prepare('SELECT u.Id_Usuario,u.Contrasena_Usuario,p.Id_Persona FROM Usuario u JOIN Persona p ON p.Id_Persona=u.Id_Persona WHERE p.Correo_Persona=?');$q->execute([$email]);$u=$q->fetch(PDO::FETCH_ASSOC);
    if (!password_verify($d['password'],$u['Contrasena_Usuario']) || $u['Contrasena_Usuario']===$d['password']) throw new RuntimeException('Hash failed'); echo "PASS hash and password whitespace\n";
    foreach (['email','carnet','username'] as $field) {
        $dupe=$d;$dupe['email']='race_'.$email;$dupe['carnet']='race_'.$stamp;$dupe['username']='race_'.$stamp;$dupe[$field]=$d[$field];
        try {(new Usuario(new DuplicateConnection()))->register($dupe);throw new RuntimeException('Uniqueness violation not detected');}
        catch (DomainException $e) {echo 'PASS database uniqueness '.$field."\n";}
        $q=$db->prepare('SELECT COUNT(*) FROM Persona WHERE Correo_Persona=?');$q->execute(['race_'.$email]);
        if ($q->fetchColumn()!=0) throw new RuntimeException('Uniqueness violation left orphan');
    }
    expect('wrong password',request('api/auth/login.php',['csrf'=>$d['csrf'],'email'=>$email,'password'=>'wrong']),401);
    foreach (['Inactivo','Bloqueado'] as $state) {
        $q=$db->prepare('UPDATE Usuario SET Id_Estado=(SELECT Id_Estado FROM Estado_Usuario WHERE Estado_Usuario=? LIMIT 1) WHERE Id_Usuario=?');$q->execute([$state,$u['Id_Usuario']]);
        expect($state,request('api/auth/login.php',['csrf'=>$d['csrf'],'email'=>$email,'password'=>$d['password']]),401);
    }
    $db->prepare("UPDATE Usuario SET Id_Estado=(SELECT Id_Estado FROM Estado_Usuario WHERE Estado_Usuario='Activo' LIMIT 1) WHERE Id_Usuario=?")->execute([$u['Id_Usuario']]);
    $result=expect('correct login',request('api/auth/login.php',['csrf'=>$d['csrf'],'email'=>$email,'password'=>$d['password']]),200);
    if ($result['data']['redirect']!=='/SistemasCrew-Labs/index.php') throw new RuntimeException('Wrong redirect');
    $dash=request('index.php');expect('session persistence',$dash,200);
    expect('authenticated login redirect',request('login.php'),302);
    preg_match('/name="csrf-token" content="([a-f0-9]+)"/',$dash[1],$m);$csrf=$m[1];
    foreach (['Equipos','Categorias','Ubicaciones','Estados_Equipo','PaginasEquipos'] as $endpoint) expect('inventory read '.$endpoint,request('api/admin/'.$endpoint.'.php'),200);
    expect('ordinary user cannot write',request('api/admin/Equipos.php',[]),403);
    $db->prepare("UPDATE Usuario SET Id_Rol=(SELECT Id_Rol FROM Rol WHERE Nombre_Rol='Administrador' LIMIT 1) WHERE Id_Usuario=?")->execute([$u['Id_Usuario']]);
    expect('admin write requires CSRF',request('api/admin/Equipos.php',[]),403);
    $rollback=$d;$rollback['email']='rollback_'.$email;$rollback['carnet']='rollback_'.$stamp;$rollback['username']='rollback_'.$stamp;$rollback['password']='ValidPass123';
    try {(new Usuario(new FailingConnection()))->register($rollback);throw new RuntimeException('Expected insert failure');} catch (LogicException $e) {}
    $q=$db->prepare('SELECT COUNT(*) FROM Persona WHERE Correo_Persona=?');$q->execute([$rollback['email']]);if ($q->fetchColumn()!=0) throw new RuntimeException('Orphan'); echo "PASS rollback without orphan\n";
    $ch=curl_init($base.'api/auth/logout.php');curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>$cookie,CURLOPT_COOKIEJAR=>$cookie,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['csrf'=>$csrf])]);$body=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);expect('logout',[$code,$body],303);
    expect('API after logout',request('api/admin/Equipos.php'),401);expect('dashboard after logout',request('index.php'),302);
    foreach (['Styles/login.css','Js/login.js','Images/logo.webp','fonts/dm-sans-400-latin.woff2'] as $asset) expect('asset '.$asset,request($asset),200);
} finally {
    $q=$db->prepare('DELETE u FROM Usuario u JOIN Persona p ON p.Id_Persona=u.Id_Persona WHERE p.Correo_Persona IN (?,?)');$q->execute([$email,'rollback_'.$email]);
    $q=$db->prepare('DELETE FROM Persona WHERE Correo_Persona IN (?,?)');$q->execute([$email,'rollback_'.$email]);
    unlink($cookie);
}
