<?php
require_once __DIR__ . '/api/helpers/auth.php';
$usuario = auth_require();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(auth_token(), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="Styles/index.css?v=<?= filemtime(__DIR__ . '/Styles/index.css') ?>">
    <link rel="stylesheet" href="Styles/prestamos.css">
    <title>Inicio | SistemasCrew Labs</title>
</head>

<body>
    <aside class="barra-lateral" id="barraLateral" aria-label="Panel de navegaci&#243;n">
        <button class="boton-menu" id="botonMenu" type="button" aria-expanded="false" aria-controls="menuPrincipal" aria-label="Expandir men&#250;"><svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <a class="marca" href="#inicio" data-vista="inicio" aria-label="SistemasCrew Labs: inicio">
            <img src="Images/logo.webp" alt="" width="40" height="40">
            <span class="texto-menu">SistemasCrew Labs</span>
        </a>
        <nav class="menu" id="menuPrincipal" aria-label="Navegaci&#243;n principal">
            <a href="#inicio" data-vista="inicio" aria-label="Inicio" title="Inicio" aria-current="page">
                <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10l9-7 9 7M5 9v12h5v-7h4v7h5V9"/></svg>
                <span class="texto-menu">Inicio</span>
            </a>
            <a href="#inventario" data-vista="inventario" aria-label="Inventario" title="Inventario">
                <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7l9-4 9 4-9 4-9-4M3 7v10l9 4 9-4V7M12 11v10"/></svg>
                <span class="texto-menu">Inventario</span>
            </a>
            <a href="#prestamos" data-vista="prestamos" aria-label="Préstamos" title="Préstamos">
                <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h15M15 3l4 4-4 4M20 17H5M9 13l-4 4 4 4"/></svg>
                <span class="texto-menu">Préstamos</span>
            </a>
            <a href="#historial" data-vista="historial" aria-label="Historial" title="Historial">
                <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11a9 9 0 1 1 2 7M3 4v7h7M12 7v5l3 2"/></svg>
                <span class="texto-menu">Historial</span>
            </a>
        </nav>
        <div class="sesion">
            <span class="nombre-usuario texto-menu"><?= htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
            <form action="api/auth/logout.php" method="post">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(auth_token(), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" aria-label="Cerrar sesi&#243;n" title="Cerrar sesi&#243;n"><svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5H4v14h5M14 8l4 4-4 4M8 12h12"/></svg><span class="texto-menu">Cerrar sesi&#243;n</span></button>
            </form>
        </div>
    </aside>
    <main>
        <section class="inicio" id="inicio">
            <div class="bienvenida">
                <p class="etiqueta">SistemasCrew — Equipos de Laboratorio</p>
                <h1>Tu próximo proyecto<br>empieza aquí.</h1>
                <p>Consulta los equipos del laboratorio y solicita lo que necesitas para tus prácticas.</p>
                <a class="boton" href="#inventario" data-vista="inventario">Explorar inventario <span aria-hidden="true">→</span></a>
            </div>
            <section class="opciones" aria-labelledby="tituloOpciones">
                <p class="etiqueta">Accesos rápidos</p>
                <h2 id="tituloOpciones">¿Qué necesitas hacer?</h2>
                <div class="tarjetas">
                    <a class="tarjeta" href="#inventario" data-vista="inventario">
                        <span class="numero" aria-hidden="true">01</span>
                        <h3>Explorar equipos</h3>
                        <p>Busca equipos y consulta sus características y estado.</p>
                        <span class="enlace">Ver inventario →</span>
                    </a>
                    <a class="tarjeta" href="#prestamos" data-vista="prestamos">
                        <span class="numero" aria-hidden="true">02</span>
                        <h3>Solicitar un préstamo</h3>
                        <p>Consulta los equipos disponibles y envía tu solicitud.</p>
                        <span class="enlace">Ver préstamos →</span>
                    </a>
                </div>
            </section>
        </section>
        <div class="contenido" id="contenido" hidden></div>
    </main>
    <footer class="pie">SistemasCrew Labs — Gestión de equipos de laboratorio</footer>
    <script src="Js/index.js?v=<?= filemtime(__DIR__ . '/Js/index.js') ?>"></script>
    <script src="Js/inventario.js"></script>
</body>

</html>