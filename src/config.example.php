<?php
// Plantilla de configuración por entorno.
// Copiar este archivo como config.php (ignorado por git) y completar con los
// datos reales de tu entorno (local o Hostinger). config.php nunca se sube
// al repo, así que cada entorno mantiene su propia config sin pisarse.

define('DB_HOST', 'localhost');
define('DB_NAME', 'timesaver_tasks');
define('DB_USER', 'root');
define('DB_PASS', '');

define('APP_NAME', 'Time Saver — Organización');
// Base path público de la app (ej: '/timesaver-tasks/public' en XAMPP local, '' si vive en la raíz del dominio en Hostinger)
define('BASE_PATH', '/timesaver-tasks/public');

// Días de anticipación para avisar que algo está por vencer
define('DIAS_AVISO_PROXIMO', 2);

// Notificaciones por email (ajustar con datos SMTP reales de Hostinger si mail() nativo no funciona)
define('MAIL_FROM', 'no-reply@timesaver.local');
define('MAIL_FROM_NAME', 'Time Saver Tasks');

date_default_timezone_set('America/Argentina/Buenos_Aires');
