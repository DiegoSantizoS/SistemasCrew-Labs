<?php
require_once __DIR__ . '/api/helpers/auth.php';
header('Cache-Control: no-store');
if (auth_user()) { header('Location: ' . auth_url('index.php')); exit; }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="csrf-token" content="<?= htmlspecialchars(auth_token(), ENT_QUOTES, 'UTF-8') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Acceso | SistemasCrew Labs</title>
  
  	<link rel="stylesheet" href="Styles/login.css">
</head>
<body>
  <main class="cl-login10-demo">
    <section class="cl-login10" aria-label="Cuenta de SistemasCrew Labs">
      <header class="cl-login10__head">
        <span class="cl-login10__logo" aria-hidden="true">
          <img src="Images/logo.webp" width="40" height="40" alt="">
        </span>
        <p class="cl-login10__brand">SistemasCrew Labs</p>
        <p class="cl-login10__tagline">Gestiona tus equipos de laboratorio.</p>
      </header>

      <div class="cl-login10__tabs" role="tablist" aria-label="Iniciar sesión o crear cuenta" data-tablist>
        <span class="cl-login10__thumb" aria-hidden="true"></span>
        <button class="cl-login10__tab" type="button" role="tab" id="cl-login10-tab-login" aria-controls="cl-login10-panel-login" aria-selected="true">Iniciar sesión</button>
        <button class="cl-login10__tab" type="button" role="tab" id="cl-login10-tab-signup" aria-controls="cl-login10-panel-signup" aria-selected="false" tabindex="-1">Crear cuenta</button>
      </div>

      <div class="cl-login10__panels">
        <div class="cl-login10__panel" role="tabpanel" id="cl-login10-panel-login" aria-labelledby="cl-login10-tab-login" tabindex="0">
          <form class="cl-login10__form" data-form="login" novalidate><h1 class="cl-login10__title">Bienvenido de nuevo</h1><p class="cl-login10__error" role="status" aria-live="polite" data-server></p><div class="cl-login10__field" data-field><label class="cl-login10__label" for="field-login-email">Correo electrónico</label><input class="cl-login10__input" id="field-login-email" name="email" type="email" autocomplete="username" maxlength="150" required aria-describedby="field-login-email-err"><p class="cl-login10__error" id="field-login-email-err" data-error></p></div><div class="cl-login10__field" data-field><label class="cl-login10__label" for="field-login-password">Contraseña</label><div class="cl-login10__pw"><input class="cl-login10__input" id="field-login-password" name="password" type="password" autocomplete="current-password" maxlength="4096" required aria-describedby="field-login-password-err"><button class="cl-login10__eye" type="button" aria-label="Mostrar contraseña" aria-pressed="false" aria-controls="field-login-password" data-pw-toggle>
                  <svg class="cl-login10__eye-on" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg class="cl-login10__eye-off" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18M10.6 5.1A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.1M6.6 6.6C3.9 8.3 2 12 2 12s3.6 7 10 7a9.8 9.8 0 0 0 5.4-1.6M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                </button></div><p class="cl-login10__error" id="field-login-password-err" data-error></p></div><button class="cl-login10__submit" type="submit">Iniciar sesión</button><p class="cl-login10__swap">¿Aún no tienes cuenta? <button class="cl-login10__swap-btn" type="button" data-go="signup">Crear una cuenta</button></p></form>
        </div>

        <div class="cl-login10__panel" role="tabpanel" id="cl-login10-panel-signup" aria-labelledby="cl-login10-tab-signup" tabindex="0" hidden>
          <form class="cl-login10__form" data-form="signup" novalidate><h2 class="cl-login10__title">Crea tu cuenta</h2><p class="cl-login10__error" role="status" aria-live="polite" data-server></p><div class="cl-login10__field" data-field><label class="cl-login10__label" for="field-signup-nombres">Nombres</label><input class="cl-login10__input" id="field-signup-nombres" name="nombres" type="text" autocomplete="given-name" maxlength="100" required aria-describedby="field-signup-nombres-err"><p class="cl-login10__error" id="field-signup-nombres-err" data-error></p></div><div class="cl-login10__field" data-field><label class="cl-login10__label" for="field-signup-apellidos">Apellidos</label><input class="cl-login10__input" id="field-signup-apellidos" name="apellidos" type="text" autocomplete="family-name" maxlength="100" required aria-describedby="field-signup-apellidos-err"><p class="cl-login10__error" id="field-signup-apellidos-err" data-error></p></div><div class="cl-login10__field" data-field><label class="cl-login10__label" for="field-signup-carnet">Carnet</label><input class="cl-login10__input" id="field-signup-carnet" name="carnet" type="text" autocomplete="off" maxlength="50" required aria-describedby="field-signup-carnet-err"><p class="cl-login10__error" id="field-signup-carnet-err" data-error></p></div><div class="cl-login10__field" data-field><label class="cl-login10__label" for="field-signup-username">Nombre de usuario</label><input class="cl-login10__input" id="field-signup-username" name="username" type="text" autocomplete="username" maxlength="50" required aria-describedby="field-signup-username-err"><p class="cl-login10__error" id="field-signup-username-err" data-error></p></div><div class="cl-login10__field" data-field><label class="cl-login10__label" for="field-signup-email">Correo electrónico</label><input class="cl-login10__input" id="field-signup-email" name="email" type="email" autocomplete="email" maxlength="150" required aria-describedby="field-signup-email-err"><p class="cl-login10__error" id="field-signup-email-err" data-error></p></div><div class="cl-login10__field" data-field><label class="cl-login10__label" for="field-signup-telefono">Teléfono (opcional)</label><input class="cl-login10__input" id="field-signup-telefono" name="telefono" type="tel" autocomplete="tel" maxlength="20"  aria-describedby="field-signup-telefono-err"><p class="cl-login10__error" id="field-signup-telefono-err" data-error></p></div><div class="cl-login10__field" data-field><label class="cl-login10__label" for="field-signup-password">Contraseña</label><div class="cl-login10__pw"><input class="cl-login10__input" id="field-signup-password" name="password" type="password" autocomplete="new-password" maxlength="72" required aria-describedby="field-signup-password-err"><button class="cl-login10__eye" type="button" aria-label="Mostrar contraseña" aria-pressed="false" aria-controls="field-signup-password" data-pw-toggle>
                  <svg class="cl-login10__eye-on" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg class="cl-login10__eye-off" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18M10.6 5.1A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.1M6.6 6.6C3.9 8.3 2 12 2 12s3.6 7 10 7a9.8 9.8 0 0 0 5.4-1.6M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                </button></div><p class="cl-login10__error" id="field-signup-password-err" data-error></p></div><div class="cl-login10__field" data-field><label class="cl-login10__label" for="field-signup-confirm">Confirmar contraseña</label><div class="cl-login10__pw"><input class="cl-login10__input" id="field-signup-confirm" name="confirm" type="password" autocomplete="new-password" maxlength="72" required aria-describedby="field-signup-confirm-err"><button class="cl-login10__eye" type="button" aria-label="Mostrar contraseña" aria-pressed="false" aria-controls="field-signup-confirm" data-pw-toggle>
                  <svg class="cl-login10__eye-on" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg class="cl-login10__eye-off" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18M10.6 5.1A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.1M6.6 6.6C3.9 8.3 2 12 2 12s3.6 7 10 7a9.8 9.8 0 0 0 5.4-1.6M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                </button></div><p class="cl-login10__error" id="field-signup-confirm-err" data-error></p></div><p class="cl-login10__hint">Al menos 8 caracteres y máximo 72 bytes.</p><button class="cl-login10__submit" type="submit">Crear cuenta</button><p class="cl-login10__swap">¿Ya tienes cuenta? <button class="cl-login10__swap-btn" type="button" data-go="login">Iniciar sesión</button></p></form>
        </div>
      </div>

      <div class="cl-login10__vh" role="status" aria-live="polite" data-live></div>
    </section>
  </main>
	<script src="Js/login.js"></script>
</body>
</html>
