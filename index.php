<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Styles/index.css">
    <title>Sistema de Gestión de Equipos de Laboratorio</title>
</head>

<body>
    <nav class="MenuToggle" id="NavLinks">

        <div class="Logo">
            <img id="logoimg" src="Images/logo.webp" alt="Logo" width="50" height="50">
        </div>

        <div class="NavBox" id="InicioNav">
            <img src="Images/inicio.webp" alt="Inicio" width="50" height="50">
            <span>Inicio</span>
        </div>

        <div class="NavBox" id="InventarioNav">
            <img src="Images/inventario.webp" alt="Inventario" width="50" height="50">
            <span>Inventario</span>
        </div>
        <a href="Sites/ingresos.html">
            <div class="NavBox">
                <img src="Images/Ingresos.webp" alt="Ingresos" width="50" height="50">
                <span>Ingresos</span>
            </div>
        </a>
        <a href="Sites/prestamos.html">
            <div class="NavBox">
                <img src="Images/prestamos.webp" alt="Préstamos" width="50" height="50">
                <span>Préstamos</span>
            </div>
        </a>
        <a href="Sites/devoluciones.html">
            <div class="NavBox">
                <img src="Images/devoluciones.webp" alt="Devoluciones" width="50" height="50">
                <span>Devoluciones</span>
            </div>
        </a>
        <a href="Sites/historial.html">
            <div class="NavBox">
                <img src="Images/historial.webp" alt="Historial" width="50" height="50">
                <span>Historial</span>
            </div>
        </a>
    </nav>
    <main>
        <div class="Perfil">
            <img id="PerfilImg" src="Images/usuarios.webp" alt="Usuario" width="50px" height="50px">
            <nav id="PerfilNav">
                <a href="">
                    <div id="Usuario">
                        Perfil
                    </div>
                </a>
                <a href="">
                    <div id="CerrarSesion">
                        Cerrar sesión
                    </div>
                </a>
            </nav>
        </div>

        <div class="Bienvenida">
            Bienvenido al sistema de reservas de equipos de laboratorio🙌
        </div>
        <div class="Contenido">

        </div>
        <script src="Js/index.js">
        </script>
        <script src="Js/Inventario.js">
        </script>
    </main>
</body>

</html>