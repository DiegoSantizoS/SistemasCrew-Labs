# SistemasCrew-Labs

Aplicación para gestionar equipos de laboratorio con PHP, MySQL/MariaDB, PDO, HTML, CSS y JavaScript. No requiere framework ni compilación.

## Estado de esta copia

- Acceso, registro y cierre de sesión implementados.
- Inventario con listado paginado, detalles, creación, edición y baja lógica de equipos.
- Catálogos de categorías, ubicaciones y estados para el inventario.
- El esquema incluye préstamos, devoluciones, multas, historial y permisos, pero esta copia no incluye sus módulos funcionales completos. `Sites/historial.html` es una página de ejemplo.
- Los enlaces del menú no garantizan una pantalla implementada. No están presentes el mantenimiento de usuarios ni las API de préstamos, devoluciones e historial.

## Requisitos

- Apache y PHP 8 o superior; XAMPP es una opción para desarrollo local.
- MySQL o MariaDB compatible con el esquema. Los datos de ejemplo están preparados para MySQL 8.0 o superior.
- Extensiones PHP `pdo_mysql` y `mbstring`; la prueba de integración también requiere `curl`.
- Navegador moderno con JavaScript habilitado.
- Node.js solamente para las comprobaciones de JavaScript.

## Instalación local

1. Colocar el proyecto en el directorio público de Apache, por ejemplo `C:\xampp\htdocs\SistemasCrew-Labs`.
2. Iniciar Apache y MySQL.
3. Preparar la base:
   - Para una instalación nueva, importar `Scripts/SistemasCrew.sql`: crea la estructura y los catálogos iniciales.
   - **Este script elimina y reconstruye la base. No ejecutarlo sobre información que se deba conservar.**
   - Opcionalmente, importar `Scripts/Inserts.sql` después del esquema y solo en una base recién creada. Supone identificadores iniciales específicos; no es una migración ni debe ejecutarse repetidamente.
   - Si existe una base compatible, conservarla sin volver a importar esos scripts.
4. Crear o configurar localmente `api/core/.env` con estas claves:

   | Clave | Contenido esperado |
   | --- | --- |
   | `DATABASE_URL` | Host del servidor MySQL |
   | `DATABASE_USER` | Usuario de conexión |
   | `DATABASE_PSSW` | Contraseña de conexión |
   | `DATABASE_NAME` | Nombre de la base creada por el esquema |
   | `DATABASE_CHARSET` | Juego de caracteres, normalmente `utf8mb4` |
   | `COOKIE_NAME` | Nombre de cookie definido por la configuración |
   | `COOKIE_KEY` | Clave privada definida por la configuración |

   No publicar `.env` ni sus valores. El acceso actual utiliza la sesión PHP; estas claves no demuestran que exista autenticación por cookie firmada.
5. Abrir `http://localhost/SistemasCrew-Labs/login.php`.
6. Registrar una cuenta o utilizar una cuenta del script de datos de prueba. El registro público asigna rol `Usuario` y estado `Activo`, sin conceder administración. Las contraseñas deben almacenarse con `password_hash`.

Para un servidor público, configurar HTTPS y restricciones de Apache que impidan acceder a `.env`, scripts SQL, pruebas y archivos internos. Esta copia no incluye un `.htaccess` que establezca esas restricciones.

## Organización

| Ruta | Responsabilidad |
| --- | --- |
| `login.php` | Acceso y registro |
| `index.php` | Página principal protegida y navegación |
| `Sites/` | Vistas HTML |
| `Styles/` | Estilos de acceso, inicio e inventario |
| `Js/` | Formularios, navegación y operaciones de inventario |
| `api/auth/` | Acceso, registro y salida |
| `api/admin/` | Equipos y catálogos |
| `api/models/` | Consultas y persistencia |
| `api/helpers/` | Sesión, CSRF y utilidades |
| `api/core/` | Configuración, conexión PDO y respuestas |
| `Scripts/` | Esquema y datos de ejemplo |
| `tests/` | Pruebas disponibles |

Para modificar la apariencia, editar `Styles/login.css`, `Styles/index.css` o `Styles/inventario.css`, según la página.

## Autenticación y seguridad actual

El registro inserta `Persona` y `Usuario` en una transacción, comprueba duplicados y genera el hash con `password_hash`. El acceso utiliza `password_verify` y exige cuenta activa. Los formularios de autenticación utilizan CSRF. Las cookies de sesión se configuran como HttpOnly y SameSite Strict, y Secure bajo HTTPS.

`index.php` exige sesión. El helper `auth_admin()` comprueba identidad y restringe escrituras a Administrador o Encargado, pero **su llamada está comentada en `api/admin/Equipos.php`**. No considerar esta API protegida hasta corregir y verificar esa condición. Las tablas de permisos del esquema no equivalen a autorización aplicada por módulo y acción en el código actual.

## Verificación

Comprobaciones de sintaxis desde la raíz:

```powershell
C:\xampp\php\php.exe -l login.php
C:\xampp\php\php.exe -l index.php
node --check Js/login.js
node --check Js/index.js
node --check Js/inventario.js
```

Con Apache, MySQL y `curl` de PHP activos, preferiblemente en una base de pruebas:

```powershell
C:\xampp\php\php.exe tests/auth-integration.php
```

La prueba crea registros temporales para comprobar autenticación, duplicados, persistencia y restricciones de acceso; su bloque de limpieza intenta eliminarlos. No reconstruye la base. Si cambia la ubicación, configurar `AUTH_TEST_URL` con la URL del proyecto terminada en `/`.

La prueba espera rechazo de solicitudes anónimas: la protección desactivada de Equipos debe resolverse antes de esperar que toda la prueba apruebe. Otras pruebas pueden depender de módulos ausentes; revisar sus dependencias antes de ejecutarlas.

Esta actualización documental se verificó contra los archivos presentes. No se ejecutaron pruebas funcionales ni importaciones SQL. Quedan pendientes la comprobación completa en navegador, la revisión móvil y una instalación limpia.

## Pendientes de entrega

- Recuperar o completar préstamos, devoluciones e historial de movimientos.
- Aplicar y verificar autorización en las API y coherencia con el menú.
- Identificar vencidos por fecha y probar disponibilidad y devolución de principio a fin.
- Revisar la coherencia de los datos de ejemplo.
- Probar escritorio, móvil y navegación por teclado.
- Preparar video, evidencias de participación y defensa técnica de los tres integrantes.
- Revisar las condiciones de autoría y uso de IA del documento del curso.

Las referencias visuales y carpetas de ejemplos no sustituyen módulos funcionales. Conservar las atribuciones y licencias correspondientes a los recursos reutilizados.
