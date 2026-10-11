# SistemasCrew-Labs

Sistema de gestión de equipos de laboratorio con PHP, MySQL/PDO y JavaScript, sin frameworks ni compilación.

## Configuración

1. Sirve esta carpeta con Apache/PHP (por ejemplo, XAMPP) en `/SistemasCrew-Labs/`.
2. Habilita `pdo_mysql` y `mbstring`. Las pruebas HTTP también necesitan `curl` en PHP.
3. Conserva la configuración de conexión existente en `api/core/.env`: `DATABASE_URL`, `DATABASE_USER`, `DATABASE_PSSW`, `DATABASE_NAME` y `DATABASE_CHARSET`. No publiques este archivo.
4. Usa la base existente con las tablas de `Scripts/SistemasCrew.sql`. **Ese script elimina la base: no lo ejecutes sobre datos existentes.** La integración no necesita migraciones. Deben existir el rol `Usuario` y el estado `Activo`; sus IDs se resuelven por nombre. Conserva los índices únicos de correo, carnet y nombre de usuario.
5. Abre `http://localhost/SistemasCrew-Labs/login.php`. Las cuentas existentes deben tener hashes generados con `password_hash`; no se aceptan contraseñas almacenadas en texto plano. El registro público siempre crea usuarios ordinarios. Los roles administrativos se asignan por el procedimiento administrativo de tu instalación.

## Autenticación y acceso

`login.php` carga exclusivamente `Styles/login.css`, `Js/login.js` y las fuentes DM Sans locales en `fonts/`. Ofrece pestañas de acceso y registro, validación en español, navegación por teclado y controles para mostrar contraseñas.

El registro requiere nombres y apellidos (100 caracteres cada uno), carnet y usuario (50), correo (150), contraseña y confirmación; el teléfono (20) es opcional. La contraseña admite al menos 8 caracteres y hasta 72 bytes, preserva sus espacios y se almacena con `PASSWORD_DEFAULT`. `Persona` y `Usuario` se insertan en una transacción; los conflictos únicos devuelven HTTP 409. Al terminar, se vuelve a la pestaña de acceso.

Los POST `api/auth/login.php` y `register.php` reciben JSON y el token CSRF de la sesión. El acceso verifica el hash con `password_verify` y exige estado `Activo`, usando el mismo error para credenciales incorrectas y cuentas no habilitadas. Se regenera el ID de sesión y se guarda únicamente el ID y nombre del usuario. El dashboard permanece en `index.php`, protegido y con el nombre escapado.

`CerrarSesion` envía un POST con CSRF a `api/auth/logout.php`, destruye la sesión, elimina su cookie y redirige al acceso. Cookies HttpOnly y SameSite Strict, Secure bajo HTTPS y ruta ajustada al directorio del proyecto; HTTP local también funciona. En un proxy HTTPS, configura Apache para reconocer HTTPS de manera confiable.

Todos los endpoints `api/admin/` exigen sesión y comprueban el estado actual en MySQL. Los usuarios registrados pueden consultar el inventario; las escrituras requieren rol `Administrador` o `Encargado` y cabecera `X-CSRF-Token`. No había reglas de permisos implementadas ni permisos asignados en el esquema inicial; esta política protege las operaciones actuales de inventario. Las peticiones sin sesión reciben JSON 401 y el frontend redirige al acceso antes de interpretar el cuerpo. La API usa rutas relativas para funcionar en subdirectorios y bajo HTTPS.

## Verificación

Con Apache y MySQL activos:

```powershell
C:\xampp\php\php.exe tests/auth-integration.php
node --check Js/login.js
node --check Js/inventario.js
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { C:\xampp\php\php.exe -l $_.FullName }
```

Opcionalmente define `AUTH_TEST_URL` con la URL del proyecto terminada en `/`. La prueba CLI crea cuentas temporales con nombres aleatorios y las elimina en `finally`; no modifica cuentas existentes. Comprueba registro, confirmación, duplicados por validación y restricciones MySQL, hash y espacios en contraseña, acceso correcto e incorrecto, estados Inactivo/Bloqueado, sesión persistente, redirecciones, restricciones de escritura, CSRF, rollback con fallo de inserción inyectado, consultas de inventario, logout y recursos locales.

En esta implementación se ejecutaron las comprobaciones PHP, JavaScript y HTTP/MySQL. La inspección visual del navegador quedó bloqueada por un fallo de inicialización de su entorno; quedan pendientes la comprobación visual móvil/escritorio, interacción de pestañas y contraseñas, y operaciones de escritura de inventario desde la interfaz. Las consultas de los cinco endpoints existentes se verificaron con sesión.

La plantilla visual se adaptó de `login-page-10` de Colorlib; se conservan sus estilos, animaciones y fuentes locales.
