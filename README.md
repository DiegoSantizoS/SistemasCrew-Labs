# SistemasCrew-Labs

Sistema de inventario, préstamos y devolución de equipos de laboratorio. Desarrollado con PHP, MySQL/MariaDB, PDO, HTML, CSS y JavaScript con Fetch API, sin framework ni compilación de frontend.

## Funcionalidades

- Inicio de sesión, registro y cierre de sesión.
- Inventario con búsqueda, paginación, detalles, creación, edición y baja lógica de equipos.
- Clasificación por categoría, ubicación y estado; generación del prefijo del código según la categoría.
- Registro de préstamos con validación de disponibilidad y asignación de usuario por el administrador.
- Consulta y registro de devoluciones, estado de retorno, observaciones y multas según tarifas configuradas.
- Identificación por fecha de préstamos vencidos en Devoluciones y presentación separada para el administrador.
- Historial con búsqueda y paginación: consulta préstamos, solicitantes, fechas, estados y motivos. No es una consulta de la tabla de eventos `Historial` ni de `Bitacora`.
- Mantenimiento de usuarios con búsqueda, paginación, detalles, creación, edición y desactivación.
- Navegación lateral según identidad y permisos, tema oscuro y tablas adaptables al espacio disponible.

La edición y baja de equipos se bloquean cuando existe un préstamo Pendiente, Activo o Vencido sin devolución. Las bajas de equipos y usuarios conservan sus registros para mantener las relaciones existentes.

## Requisitos

- Apache 2.4 con `mod_rewrite` y permisos para aplicar `.htaccess` (`AllowOverride` compatible).
- PHP 8.1 o superior con `pdo_mysql` y `mbstring`.
- MySQL o MariaDB compatible con el esquema; los datos de ejemplo están preparados para MySQL 8.0 o superior.
- Navegador moderno con JavaScript habilitado. La presentación de tablas como tarjetas utiliza consultas de contenedor CSS; sin ese soporte se conserva la tabla desplazable.
- Para pruebas: Node.js y, en las pruebas HTTP de PHP, extensión `curl`.

## Instalación local

1. Colocar el proyecto en el directorio público de Apache, por ejemplo `C:\xampp\htdocs\SistemasCrew-Labs`.
2. Iniciar Apache y MySQL.
3. Preparar la base:
   - Para una instalación nueva, importar `Scripts/SistemasCrew.sql`, que crea estructura y catálogos iniciales.
   - **Ese script elimina y reconstruye la base. No ejecutarlo sobre información que se deba conservar.**
   - Opcionalmente, importar `Scripts/Inserts.sql` después del esquema, únicamente en una base recién creada. Supone identificadores iniciales específicos y no debe ejecutarse repetidamente.
   - Si ya existe una base compatible, conservarla sin volver a importar esquema e inserciones.
4. Configurar localmente `api/core/.env`:

   | Clave | Contenido esperado |
   | --- | --- |
   | `DATABASE_URL` | Host del servidor MySQL |
   | `DATABASE_USER` | Usuario de conexión |
   | `DATABASE_PSSW` | Contraseña de conexión |
   | `DATABASE_NAME` | Nombre de la base |
   | `DATABASE_CHARSET` | Juego de caracteres, normalmente `utf8mb4` |
   | `COOKIE_NAME` | Nombre de la cookie de autenticación |
   | `COOKIE_KEY` | Clave declarada por la configuración; la firma actual utiliza una clave aleatoria guardada en sesión |

   No publicar `.env` ni sus valores.
5. Importar `Scripts/Permisos.sql` en la base seleccionada para completar permisos de los módulos, incluido Usuarios. El script selecciona `dbSistemasCrew`: revisar ese nombre si la instalación utiliza otro. Agrega asignaciones sin reconstruir tablas; no reemplaza una revisión de permisos personalizados existentes.
6. Abrir `http://localhost/SistemasCrew-Labs/login.php`.
7. Registrar una cuenta o utilizar una cuenta del script de datos de ejemplo. El registro público asigna rol `Usuario` y estado `Activo`. Las contraseñas se almacenan mediante `password_hash`.

`Scripts/permisos-auth.sql` contiene asignaciones para Equipos, Prestamos y Devoluciones; no cubre por sí solo el mantenimiento de Usuarios. Ninguno de los scripts de permisos implementa operaciones que no existan en los controladores.

El acceso HTTP se permite desde `127.0.0.1` y `::1`; las conexiones remotas requieren HTTPS. `.htaccess` bloquea acceso a archivos ocultos, configuración interna, SQL, pruebas y documentación Markdown. La documentación se consulta desde el repositorio o editor, no como página pública de Apache.

## Acceso y permisos

La autorización se consulta en MySQL mediante Usuario, Estado_Usuario, Rol_Permiso, Permiso, Modulo y Accion. El middleware relaciona GET con Leer, POST con Crear, PUT con Actualizar y DELETE con Eliminar, y exige CSRF para escrituras.

- Administrador: las asignaciones de `Scripts/Permisos.sql` permiten administrar los módulos; el menú de Usuarios también exige este rol.
- Encargado: recibe operaciones de inventario y préstamos y lectura de devoluciones según el script. Registrar una devolución exige además rol Administrador en el controlador.
- Usuario: el script asigna lectura y creación de préstamos; no agrega acceso al mantenimiento de inventario. Las asignaciones previas personalizadas pueden ampliar lo visible.

Historial exige autenticación: el administrador consulta todos los préstamos y los demás usuarios solo los propios. La consulta de devoluciones también limita los resultados al propietario cuando no es administrador, además de exigir el permiso del módulo.

## Seguridad de autenticación

El registro valida datos, comprueba duplicados e inserta Persona y Usuario en una transacción. El acceso utiliza `password_verify`, exige cuenta activa y regenera el ID de sesión y el token CSRF.

La autenticación combina sesión PHP y cookie firmada con HMAC-SHA256 cuya clave aleatoria permanece en la sesión. Las cookies son HttpOnly y SameSite Strict, y Secure bajo HTTPS. La vigencia configurada es de ocho horas. La identidad y el estado actual se consultan en MySQL para los accesos protegidos.

El login limita fallos por cuenta y por IP: ventana de quince minutos, tres fallos por cuenta y treinta por IP, con bloqueo de cinco minutos. Los contadores se guardan en archivos temporales con bloqueo exclusivo.

## Organización

| Ruta | Responsabilidad |
| --- | --- |
| `login.php` | Acceso, registro y tarjetas de integrantes |
| `index.php` | Inicio protegido, navegación y carga de módulos |
| `Sites/` | Vistas de inventario, usuarios, préstamos, devoluciones e historial |
| `Styles/` | Tema, navegación, formularios, tablas, tarjetas y estilos de módulos |
| `Js/` | Interacción, solicitudes API, filtros y adaptación de tablas |
| `api/auth/` | Autenticación, límites de intentos y middleware |
| `api/admin/` | Controladores de módulos y catálogos |
| `api/models/` | Consultas y operaciones de persistencia |
| `api/helpers/` | Sesión, cookie, CSRF y lectura de solicitudes |
| `api/core/` | Configuración, conexión PDO y respuestas |
| `Scripts/` | Esquema, datos de ejemplo y asignaciones de permisos |
| `tests/` | Pruebas disponibles |

## Personalización visual

| Archivo | Elementos |
| --- | --- |
| `Styles/index.css` | Tema del inicio y barra lateral |
| `Styles/modulos.css` | Controles, tablas, estados y diálogos compartidos |
| `Styles/login.css` | Acceso y registro |
| `Styles/equipo.css` | Tarjetas de integrantes |
| `Styles/inventario.css` | Disposición del inventario |
| `Styles/prestamos.css` | Tarjetas y formulario de préstamos |
| `Styles/devoluciones.css` | Consulta y formulario de devolución |

Los colores y otras medidas compartidas se definen mediante variables CSS. `Styles/index-carbon.css` conserva una variante del tema; su presencia no la activa automáticamente. `Js/tablas.js` agrega etiquetas a las celdas para la presentación móvil sin reemplazar los botones existentes.

## Comprobaciones

Desde la raíz, con PHP y Node.js disponibles:

```powershell
C:\xampp\php\php.exe -l login.php
C:\xampp\php\php.exe -l index.php
node --check Js/login.js
node --check Js/inventario.js
node --check Js/usuarios.js
node --check Js/tablas.js
node tests/usuarios-interfaz.js
node tests/historial-interfaz.js
node tests/tablas-responsive.js
```

Las pruebas JavaScript de interfaz utilizan entornos simulados; no sustituyen una inspección visual en navegador.

Prueba de integración con Apache, MySQL y `curl` de PHP activos, preferiblemente en una base de pruebas:

```powershell
C:\xampp\php\php.exe tests/auth-integration.php
```

Esta prueba crea registros temporales y utiliza limpieza para retirarlos; no reconstruye la base. Puede configurarse `AUTH_TEST_URL` con la URL del proyecto terminada en `/`. Los comandos se proporcionan para ejecutar las comprobaciones y no constituyen una afirmación de que todas las pruebas hayan aprobado en una instalación determinada.
