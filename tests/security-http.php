<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$base = getenv('AUTH_TEST_URL') ?: 'http://localhost/SistemasCrew-Labs/';
$privatePaths = ['api/core/.env', '.git/config', '.git/HEAD', 'api/core/Configuration.php', 'api/helpers/auth.php', 'api/models/Usuario.php', 'Scripts/SistemasCrew.sql', 'tests/auth-integration.php', 'Nesflis/API/auth/login.php', 'README.md', 'login.php.bak', 'backup.zip'];
foreach ($privatePaths as $path) {
    $request = curl_init($base . $path);
    curl_setopt_array($request, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10]);
    curl_exec($request);
    $status = curl_getinfo($request, CURLINFO_RESPONSE_CODE);
    curl_close($request);
    if ($status !== 403) throw new RuntimeException('Private path not denied: ' . $path . ', HTTP ' . $status);
    echo 'PASS private path HTTP 403: ' . $path . PHP_EOL;
}
foreach (['Equipos', 'Categorias', 'Ubicaciones', 'Estados_Equipo', 'PaginasEquipos'] as $endpoint) {
    foreach (['GET', 'POST', 'PUT', 'DELETE'] as $method) {
        $request = curl_init($base . 'api/admin/' . $endpoint . '.php');
        curl_setopt_array($request, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10]);
        curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_RESPONSE_CODE);
        curl_close($request);
        if ($status !== 401) throw new RuntimeException('Anonymous access accepted: ' . $endpoint . ' ' . $method . ', HTTP ' . $status);
    }
    echo 'PASS anonymous GET/POST/PUT/DELETE denied: ' . $endpoint . PHP_EOL;
}
