<?php
require_once __DIR__ . '/api/helpers/auth.php';
require_once __DIR__ . '/api/models/Usuario.php';
$user = auth_require();
$usuarioModelo = new Usuario(new Connection());
$puedeVerInventario = $usuarioModelo->hasPermission($user['id'], 'Equipos', 'Leer');
$puedeVerPrestamos = $usuarioModelo->hasPermission($user['id'], 'Prestamos', 'Leer');
$puedeVerDevoluciones = $usuarioModelo->hasPermission($user['id'], 'Devoluciones', 'Leer');
$identidad = $usuarioModelo->identity($user['id']);
$esEstudiante = ($identidad['rol'] ?? '') === 'Usuario';
$esAdministrador = ($identidad['rol'] ?? '') === 'Administrador';
$puedeVerUsuarios = $esAdministrador && $usuarioModelo->hasPermission($user['id'], 'Usuarios', 'Leer');
$tituloPrestamos = $esEstudiante ? 'Mis préstamos' : 'Préstamos';
$tituloDevoluciones = $esEstudiante ? 'Mis devoluciones' : 'Devoluciones';
$tituloHistorial = $esEstudiante ? 'Mi historial' : 'Historial';

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(auth_token(), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="Styles/index.css?v=<?= filemtime(__DIR__ . '/Styles/index.css') ?>">
    <link rel="stylesheet" href="Styles/inventario.css?v=<?= filemtime(__DIR__ . '/Styles/inventario.css') ?>">
    <link rel="stylesheet" href="Styles/prestamos.css?v=<?= filemtime(__DIR__ . '/Styles/prestamos.css') ?>">
    <link rel="stylesheet" href="Styles/devoluciones.css?v=<?= filemtime(__DIR__ . '/Styles/devoluciones.css') ?>">
    <link rel="stylesheet" href="Styles/modulos.css?v=<?= filemtime(__DIR__ . '/Styles/modulos.css') ?>">
    <title>Inicio | SistemasCrew Labs</title>
    <link rel="icon" type="image/x-icon" href="Images/sistemascrew.ico">
</head>

<body>
    <aside class="barra-lateral" id="barraLateral" aria-label="Panel de navegaci&#243;n">
        <button class="boton-menu" id="botonMenu" type="button" aria-expanded="false" aria-controls="menuPrincipal" aria-label="Expandir men&#250;"><svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg></button>
        <a class="marca" href="#inicio" data-vista="inicio" aria-label="SistemasCrew Labs: inicio">
            <img src="Images/logo.webp" alt="" width="40" height="40">
            <span class="texto-menu">SistemasCrew Labs</span>
        </a>
        <nav class="menu" id="menuPrincipal" aria-label="Navegaci&#243;n principal">
            <a id="InicioNav" href="#inicio" data-vista="inicio" aria-label="Inicio" title="Inicio" aria-current="page">
                <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 10l9-7 9 7M5 9v12h5v-7h4v7h5V9" />
                </svg>
                <span class="texto-menu">Inicio</span>
            </a>
            <?php if ($puedeVerInventario): ?>
                <a id="InventarioNav" href="#inventario" data-vista="inventario" aria-label="Inventario" title="Inventario">
                    <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 7l9-4 9 4-9 4-9-4M3 7v10l9 4 9-4V7M12 11v10" />
                    </svg>
                    <span class="texto-menu">Inventario</span>
                </a>
            <?php endif; ?>
            <?php if ($puedeVerPrestamos): ?>
                <a id="PrestamosNav" href="#prestamos" data-vista="prestamos" aria-label="<?= $tituloPrestamos ?>" title="<?= $tituloPrestamos ?>">
                    <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 7h15M15 3l4 4-4 4M20 17H5M9 13l-4 4 4 4" />
                    </svg>
                    <span class="texto-menu"><?= $tituloPrestamos ?></span>
                </a>
            <?php endif; ?>
            <?php if ($puedeVerDevoluciones): ?>
                <a id="DevolucionesNav" href="#devoluciones" data-vista="devoluciones" aria-label="<?= $tituloDevoluciones ?>" title="<?= $tituloDevoluciones ?>">
                    <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 4H4v5M4 4l7 7M20 13a7 7 0 0 1-12 5M20 4v5h-5" />
                    </svg>
                    <span class="texto-menu"><?= $tituloDevoluciones ?></span>
                </a>
            <?php endif; ?>
            <a id="HistorialNav" href="#historial" data-vista="historial" aria-label="<?= $tituloHistorial ?>" title="<?= $tituloHistorial ?>">
                <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 11a9 9 0 1 1 2 7M3 4v7h7M12 7v5l3 2" />
                </svg>
                <span class="texto-menu"><?= $tituloHistorial ?></span>
            </a>
            <?php if ($puedeVerUsuarios): ?>
                <a id="UsuariosNav" href="#usuarios" data-vista="usuarios" aria-label="Usuarios" title="Usuarios">
                    <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8M17 4a4 4 0 0 1 0 7M22 21v-2a4 4 0 0 0-3-4" />
                    </svg>
                    <span class="texto-menu">Usuarios</span>
                </a>
            <?php endif; ?>
        </nav>
        <div class="sesion">
            <div class="datos-usuario">
                <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M4 22v-3a8 8 0 0 1 16 0v3" />
                </svg>
                <span class="nombre-usuario texto-menu"><?= htmlspecialchars($user['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="opciones-usuario">
                <form action="api/auth/logout.php" method="post">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(auth_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" aria-label="Cerrar sesi&#243;n" title="Cerrar sesi&#243;n"><svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 5H4v14h5M14 8l4 4-4 4M8 12h12" />
                        </svg><span class="texto-menu">Cerrar sesi&#243;n</span></button>
                </form>
            </div>
        </div>
    </aside>
    <main>
        <section class="inicio Bienvenida" id="inicio">
            <div class="bienvenida">
                <p class="etiqueta">SistemasCrew — Equipos de Laboratorio</p>
                <h1>Tu próximo proyecto<br>empieza aquí.</h1>
                <p>Consulta los equipos del laboratorio y solicita lo que necesitas para tus prácticas.</p>
                <?php if ($puedeVerInventario): ?>
                    <a class="boton" href="#inventario" data-vista="inventario">Explorar inventario <span aria-hidden="true">→</span></a>
                <?php endif; ?>
            </div>
            <?php if ($puedeVerInventario || $puedeVerPrestamos): ?>
                <section class="opciones" aria-labelledby="tituloOpciones">
                    <p class="etiqueta">Accesos rápidos</p>
                    <h2 id="tituloOpciones">¿Qué necesitas hacer?</h2>
                    <div class="tarjetas">
                        <?php if ($puedeVerInventario): ?>
                            <a class="tarjeta" href="#inventario" data-vista="inventario">
                                <span class="numero" aria-hidden="true">01</span>
                                <h3>Explorar equipos</h3>
                                <p>Busca equipos y consulta sus características y estado.</p>
                                <span class="enlace">Ver inventario →</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($puedeVerPrestamos): ?>
                            <a class="tarjeta" href="#prestamos" data-vista="prestamos">
                                <span class="numero" aria-hidden="true">02</span>
                                <h3>Solicitar un préstamo</h3>
                                <p>Consulta los equipos disponibles y envía tu solicitud.</p>
                                <span class="enlace">Ver préstamos →</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        </section>
        <div class="contenido Contenido" id="contenido"></div>
    </main>
    <footer class="pie">SistemasCrew Labs — Gestión de equipos de laboratorio</footer>
    <script src="Js/inventario.js?v=<?= filemtime(__DIR__ . '/Js/inventario.js') ?>"></script>
    <script src="Js/prestamos.js?v=<?= filemtime(__DIR__ . '/Js/prestamos.js') ?>"></script>
    <script src="Js/devoluciones.js?v=<?= filemtime(__DIR__ . '/Js/devoluciones.js') ?>"></script>
    <script src="Js/historial.js?v=<?= filemtime(__DIR__ . '/Js/historial.js') ?>"></script>
    <script src="Js/usuarios.js?v=<?= filemtime(__DIR__ . '/Js/usuarios.js') ?>"></script>
    <script src="Js/index.js?v=<?= filemtime(__DIR__ . '/Js/index.js') ?>"></script>
    <script src="Js/menu.js?v=<?= filemtime(__DIR__ . '/Js/menu.js') ?>"></script>
</body>

</html>
