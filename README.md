# Time Saver — Organización de tareas y decisiones

App interna para organizar el trabajo de Time Saver: tareas generales y por
proyecto (con responsable, prioridad y fechas), y un módulo de toma de
decisiones/disputas con hilo de discusión, plazo y resolución asentada.

## Stack

PHP nativo + PDO/MySQL, sin frameworks pesados. Pensado para correr igual en
XAMPP (desarrollo local) y en hosting compartido tipo Hostinger.

## Instalación local (XAMPP)

1. El proyecto ya vive en `C:\xampp\htdocs\timesaver-tasks`.
2. Con Apache y MySQL corriendo, la base ya fue creada ejecutando
   `sql/schema.sql` (crea la base `timesaver_tasks`, todas las tablas y un
   usuario admin inicial).
3. Abrir: **http://localhost/timesaver-tasks/public/**
4. Login inicial:
   - Email: `admin@timesaver.local`
   - Password: `timesaver123`
   - **Cambiar esta contraseña** (o crear usuarios nuevos) apenas se pueda —
     de momento no hay pantalla de gestión de usuarios, se cargan directo en
     la tabla `usuarios` de MySQL usando `password_hash()` de PHP para el
     hash. Ejemplo:
     ```
     C:\xampp\php\php.exe -r "echo password_hash('tu_password', PASSWORD_DEFAULT);"
     ```
     y luego un INSERT en phpMyAdmin.

## Notificaciones (vencidos / próximos a vencer)

Además del resaltado visual (rojo = vencido, amarillo = próximo, según
`DIAS_AVISO_PROXIMO` en `src/config.php`), hay un script que manda emails:

```
scripts/notificar_vencimientos.php
```

Se debe programar para correr 1 vez por día:

- **XAMPP (prueba manual)**: `C:\xampp\php\php.exe C:\xampp\htdocs\timesaver-tasks\scripts\notificar_vencimientos.php`
- **Hostinger**: configurar un Cron Job en el panel (Avanzado → Cron Jobs)
  apuntando a `php /home/USUARIO/domains/TUDOMINIO/public_html/timesaver-tasks/scripts/notificar_vencimientos.php`,
  frecuencia diaria.

El envío usa `mail()` nativo de PHP. Si en Hostinger no llegan los correos,
puede requerir SMTP autenticado — en ese caso hay que sumar PHPMailer y
configurar host/usuario/clave SMTP reales del hosting en ese script.

## Subir a Hostinger

1. Subir toda la carpeta del proyecto por FTP o el File Manager de Hostinger
   (fuera de `public_html` si se puede, o dentro de una subcarpeta).
2. Crear la base de datos MySQL desde hPanel, importar `sql/schema.sql` desde
   phpMyAdmin.
3. Editar `src/config.php` con los datos reales de conexión (`DB_HOST`,
   `DB_NAME`, `DB_USER`, `DB_PASS` que da Hostinger) y ajustar `BASE_PATH`
   según dónde quede publicada la carpeta `public/` (lo ideal es apuntar el
   dominio/subdominio directo a esa carpeta como document root; si no se
   puede, `BASE_PATH` debe reflejar la ruta pública real).
4. Configurar el Cron Job de notificaciones (ver arriba).

## Estructura

```
public/            -> document root: index.php (router), assets (css/js)
src/
  config.php        -> configuración (DB, BASE_PATH, avisos)
  db.php             -> conexión PDO
  auth.php           -> login/logout/sesión
  helpers.php         -> helpers de vistas, fechas, actividad
  models.php          -> consultas a tareas/decisiones/proyectos
  controllers/         -> lógica por módulo
  views/                -> templates PHP por sección
sql/schema.sql       -> creación de base y tablas + usuario admin inicial
scripts/notificar_vencimientos.php -> cron diario de notificaciones
```

## Funcionalidad

- **Home**: resumen general de tareas y decisiones (todas / solo mías).
- **Proyectos**: alta de proyecto (cliente, descripción, equipo), y dentro de
  cada uno sus tareas y decisiones propias, con el mismo toggle todas/mías.
- **Tareas**: título, descripción, prioridad, fechas, responsable, estado,
  comentarios, links/adjuntos e historial de actividad.
- **Decisiones**: título + bajada, plazo, participantes, hilo de mensajes
  (ida y vuelta) y resolución final que queda asentada; se marcan como
  vencidas automáticamente si se pasa el plazo sin resolver.
