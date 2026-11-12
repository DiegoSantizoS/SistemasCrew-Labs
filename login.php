<?php
require_once __DIR__ . '/api/helpers/auth.php';
header('Cache-Control: no-store');
if (auth_current_user()) {
  header('Location: ' . auth_url('index.php'));
  exit;
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="csrf-token" content="<?= htmlspecialchars(auth_token(), ENT_QUOTES, 'UTF-8') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="Images/sistemascrew.ico">
  <title>Acceso | SistemasCrew Labs</title>
  <link rel="stylesheet" href="Styles/login.css?v=<?= filemtime(__DIR__ . '/Styles/login.css') ?>">
  <link rel="stylesheet" href="Styles/equipo.css?v=<?= filemtime(__DIR__ . '/Styles/equipo.css') ?>">
</head>

<body>
  <main class="pagina-acceso">
    <section class="acceso" aria-label="Cuenta de SistemasCrew Labs">
      <header class="encabezado-acceso">
        <span class="logo-acceso" aria-hidden="true">
          <img src="Images/logo.webp" width="80" height="80" alt="">
        </span>
        <p class="nombre-sistema">SistemasCrew Labs</p>
        <p class="descripcion-sistema">Gestiona tus equipos de laboratorio.</p>
      </header>
      <div class="pestanas-formularios" role="tablist" aria-label="Iniciar sesión o crear cuenta" data-lista-pestanas>
        <span class="indicador-pestana" aria-hidden="true"></span>
        <button class="pestana-formulario" type="button" role="tab" id="pestana-ingreso" aria-controls="panel-ingreso" aria-selected="true">Iniciar sesión</button>
        <button class="pestana-formulario" type="button" role="tab" id="pestana-registro" aria-controls="panel-registro" aria-selected="false" tabindex="-1">Crear cuenta</button>
      </div>
      <div class="paneles-formularios">
        <div class="panel-formulario" role="tabpanel" id="panel-ingreso" aria-labelledby="pestana-ingreso" tabindex="0">
          <form class="formulario-cuenta" data-formulario="login" novalidate>
            <h1 class="titulo-formulario">Bienvenido de nuevo</h1>
            <p class="mensaje-error" role="status" aria-live="polite" data-mensaje-servidor></p>
            <div class="campo-formulario" data-campo>
              <label class="etiqueta-campo" for="ingreso-correo">Correo electrónico</label>
              <input
                class="entrada-campo"
                id="ingreso-correo"
                name="email"
                type="email"
                autocomplete="username"
                maxlength="150"
                required
                aria-describedby="error-ingreso-correo">
              <p class="mensaje-error" id="error-ingreso-correo" data-error-campo></p>
            </div>
            <div class="campo-formulario" data-campo>
              <label class="etiqueta-campo" for="ingreso-contrasena">Contraseña</label>
              <div class="grupo-contrasena">
                <input
                  class="entrada-campo"
                  id="ingreso-contrasena"
                  name="password"
                  type="password"
                  autocomplete="current-password"
                  maxlength="4096"
                  required
                  aria-describedby="error-ingreso-contrasena">
                <button class="boton-contrasena" type="button" aria-label="Mostrar contraseña" aria-pressed="false" aria-controls="ingreso-contrasena" data-alternar-contrasena>
                  <svg class="icono-ojo-abierto" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                  <svg class="icono-ojo-cerrado" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 3l18 18M10.6 5.1A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.1M6.6 6.6C3.9 8.3 2 12 2 12s3.6 7 10 7a9.8 9.8 0 0 0 5.4-1.6M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                  </svg>
                </button>
              </div>
              <p class="mensaje-error" id="error-ingreso-contrasena" data-error-campo></p>
            </div>
            <button class="boton-enviar" type="submit">Iniciar sesión</button>
            <p class="texto-cambiar-formulario">
              ¿Aún no tienes cuenta?
              <button class="boton-cambiar-formulario" type="button" data-destino-formulario="signup">Crear una cuenta</button>
            </p>
          </form>
        </div>
        <div class="panel-formulario" role="tabpanel" id="panel-registro" aria-labelledby="pestana-registro" tabindex="0" hidden>
          <form class="formulario-cuenta" data-formulario="signup" novalidate>
            <h2 class="titulo-formulario">Crea tu cuenta</h2>
            <p class="mensaje-error" role="status" aria-live="polite" data-mensaje-servidor></p>
            <div class="campo-formulario" data-campo>
              <label class="etiqueta-campo" for="registro-nombres">Nombres</label>
              <input
                class="entrada-campo"
                id="registro-nombres"
                name="nombres"
                type="text"
                autocomplete="given-name"
                maxlength="100"
                required
                aria-describedby="error-registro-nombres">
              <p class="mensaje-error" id="error-registro-nombres" data-error-campo></p>
            </div>
            <div class="campo-formulario" data-campo>
              <label class="etiqueta-campo" for="registro-apellidos">Apellidos</label>
              <input
                class="entrada-campo"
                id="registro-apellidos"
                name="apellidos"
                type="text"
                autocomplete="family-name"
                maxlength="100"
                required
                aria-describedby="error-registro-apellidos">
              <p class="mensaje-error" id="error-registro-apellidos" data-error-campo></p>
            </div>
            <div class="campo-formulario" data-campo>
              <label class="etiqueta-campo" for="registro-carnet">Carnet</label>
              <input
                class="entrada-campo"
                id="registro-carnet"
                name="carnet"
                type="text"
                autocomplete="off"
                maxlength="50"
                required
                aria-describedby="error-registro-carnet">
              <p class="mensaje-error" id="error-registro-carnet" data-error-campo></p>
            </div>
            <div class="campo-formulario" data-campo>
              <label class="etiqueta-campo" for="registro-usuario">Nombre de usuario</label>
              <input
                class="entrada-campo"
                id="registro-usuario"
                name="username"
                type="text"
                autocomplete="username"
                maxlength="50"
                required
                aria-describedby="error-registro-usuario">
              <p class="mensaje-error" id="error-registro-usuario" data-error-campo></p>
            </div>
            <div class="campo-formulario" data-campo>
              <label class="etiqueta-campo" for="registro-correo">Correo electrónico</label>
              <input
                class="entrada-campo"
                id="registro-correo"
                name="email"
                type="email"
                autocomplete="email"
                maxlength="150"
                required
                aria-describedby="error-registro-correo">
              <p class="mensaje-error" id="error-registro-correo" data-error-campo></p>
            </div>
            <div class="campo-formulario" data-campo>
              <label class="etiqueta-campo" for="registro-telefono">Teléfono (opcional)</label>
              <input
                class="entrada-campo"
                id="registro-telefono"
                name="telefono"
                type="tel"
                autocomplete="tel"
                maxlength="20"
                aria-describedby="error-registro-telefono">
              <p class="mensaje-error" id="error-registro-telefono" data-error-campo></p>
            </div>
            <div class="campo-formulario" data-campo>
              <label class="etiqueta-campo" for="registro-contrasena">Contraseña</label>
              <div class="grupo-contrasena">
                <input
                  class="entrada-campo"
                  id="registro-contrasena"
                  name="password"
                  type="password"
                  autocomplete="new-password"
                  maxlength="72"
                  required
                  aria-describedby="error-registro-contrasena">
                <button class="boton-contrasena" type="button" aria-label="Mostrar contraseña" aria-pressed="false" aria-controls="registro-contrasena" data-alternar-contrasena>
                  <svg class="icono-ojo-abierto" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                  <svg class="icono-ojo-cerrado" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 3l18 18M10.6 5.1A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.1M6.6 6.6C3.9 8.3 2 12 2 12s3.6 7 10 7a9.8 9.8 0 0 0 5.4-1.6M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                  </svg>
                </button>
              </div>
              <p class="mensaje-error" id="error-registro-contrasena" data-error-campo></p>
            </div>
            <div class="campo-formulario" data-campo>
              <label class="etiqueta-campo" for="registro-confirmar-contrasena">Confirmar contraseña</label>
              <div class="grupo-contrasena">
                <input
                  class="entrada-campo"
                  id="registro-confirmar-contrasena"
                  name="confirm"
                  type="password"
                  autocomplete="new-password"
                  maxlength="72"
                  required
                  aria-describedby="error-registro-confirmar-contrasena">
                <button class="boton-contrasena" type="button" aria-label="Mostrar contraseña" aria-pressed="false" aria-controls="registro-confirmar-contrasena" data-alternar-contrasena>
                  <svg class="icono-ojo-abierto" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                  <svg class="icono-ojo-cerrado" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 3l18 18M10.6 5.1A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.1M6.6 6.6C3.9 8.3 2 12 2 12s3.6 7 10 7a9.8 9.8 0 0 0 5.4-1.6M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                  </svg>
                </button>
              </div>
              <p class="mensaje-error" id="error-registro-confirmar-contrasena" data-error-campo></p>
            </div>
            <p class="ayuda-contrasena">Al menos 8 caracteres y máximo 72 bytes.</p>
            <button class="boton-enviar" type="submit">Crear cuenta</button>
            <p class="texto-cambiar-formulario">
              ¿Ya tienes cuenta?
              <button class="boton-cambiar-formulario" type="button" data-destino-formulario="login">Iniciar sesión</button>
            </p>
          </form>
        </div>
      </div>
      <div class="aviso-accesible" role="status" aria-live="polite" data-aviso-accesible></div>
    </section>
  </main>
  <section class="equipo" aria-labelledby="tituloEquipo">
    <header class="equipo-cabecera">
      <p class="equipo-etiqueta">SistemasCrew Labs</p>
      <h2 id="tituloEquipo">Nuestro equipo</h2>
    </header>
    <div class="equipo-lista">
      <article class="perfil">
        <div class="perfil-foto">
          <img src="Images/Programadores/Carlos.jpeg" alt="Carlos Andres Arriaza Lara" width="130" height="130" loading="lazy">
        </div>
        <h3 class="perfil-nombre">Carlos Andres Arriaza Lara</h3>
        <p class="perfil-cargo">0901-23-13862</p>
        <p class="perfil-cargo">Desarrollo de software</p>
        <div class="perfil-enlaces" aria-label="Enlaces de Carlos Andres Arriaza Lara">
          <a class="perfil-boton" href="https://github.com/RadeonHeartz" target="_blank" rel="noopener noreferrer">GitHub</a>
          <a class="perfil-boton" href="https://radeonheartz.github.io/CarlosAndres.github.io/" target="_blank" rel="noopener noreferrer">Portafolio</a>
        </div>
      </article>
      <article class="perfil">
        <div class="perfil-foto">
          <img src="Images/Programadores/Miguel.jpg" alt="Miguel David Contreras Jacinto" width="130" height="130" loading="lazy">
        </div>
        <h3 class="perfil-nombre">Miguel David Contreras Jacinto</h3>
        <p class="perfil-cargo">0901-21-3878</p>
        <p class="perfil-cargo">Desarrollo de software</p>
        <div class="perfil-enlaces" aria-label="Enlaces de Miguel David Contreras Jacinto">
          <a class="perfil-boton" href="https://github.com/miguelcont264" target="_blank" rel="noopener noreferrer">GitHub</a>
          <a class="perfil-boton" href="https://miguelcont264.github.io" target="_blank" rel="noopener noreferrer">Portafolio</a>
        </div>
      </article>
      <article class="perfil">
        <div class="perfil-foto">
          <img src="Images/Programadores/Diego.jpeg" alt="Diego Fernando Santizo Samayoa" width="130" height="130" loading="lazy">
        </div>
        <h3 class="perfil-nombre">Diego Fernando Santizo Samayoa</h3>
        <p class="perfil-cargo">0901-22-15950</p>
        <p class="perfil-cargo">Desarrollo de software</p>
        <div class="perfil-enlaces" aria-label="Enlaces de Diego Fernando Santizo Samayoa">
          <a class="perfil-boton" href="https://github.com/DiegoSantizoS" target="_blank" rel="noopener noreferrer">GitHub</a>
          <a class="perfil-boton" href="https://diegosantizos.github.io/" target="_blank" rel="noopener noreferrer">Portafolio</a>
        </div>
      </article>
    </div>
  </section>
  <script src="Js/login.js?v=<?= filemtime(__DIR__ . '/Js/login.js') ?>">
  </script>
  <script src="Js/equipo.js?v=<?= filemtime(__DIR__ . '/Js/equipo.js') ?>">
  </script>
</body>

</html>