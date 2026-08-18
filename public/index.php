<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/models.php';
require_once __DIR__ . '/../src/controllers/auth_controller.php';
require_once __DIR__ . '/../src/controllers/home_controller.php';
require_once __DIR__ . '/../src/controllers/proyectos_controller.php';
require_once __DIR__ . '/../src/controllers/tareas_controller.php';
require_once __DIR__ . '/../src/controllers/decisiones_controller.php';
require_once __DIR__ . '/../src/controllers/usuarios_controller.php';
require_once __DIR__ . '/../src/controllers/calendario_controller.php';

iniciar_sesion_segura();

$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$path = substr($uri, strlen(BASE_PATH));
$path = '/' . trim($path, '/');
$metodo = strtoupper($_SERVER['REQUEST_METHOD']);

// [método, patrón regex, handler]
$rutas = [
    ['GET', '#^/login$#', 'auth_login_form'],
    ['POST', '#^/login$#', 'auth_login_submit'],
    ['GET', '#^/logout$#', 'auth_logout'],

    ['GET', '#^/cambiar-password$#', 'auth_cambiar_password_form'],
    ['POST', '#^/cambiar-password$#', 'auth_cambiar_password_submit'],

    ['GET', '#^/$#', 'home_index'],

    ['GET', '#^/calendario$#', 'calendario_index'],
    ['POST', '#^/calendario/reuniones$#', 'calendario_reuniones_crear'],
    ['POST', '#^/calendario/reuniones/(\d+)/eliminar$#', 'calendario_reuniones_eliminar'],

    ['POST', '#^/notificaciones/leidas$#', 'notificaciones_marcar_leidas'],

    ['GET', '#^/perfil$#', 'perfil_ver'],
    ['POST', '#^/perfil/preferencias$#', 'perfil_actualizar_preferencias'],

    ['GET', '#^/admin/usuarios$#', 'usuarios_index'],
    ['GET', '#^/admin/usuarios/nuevo$#', 'usuarios_nueva_form'],
    ['POST', '#^/admin/usuarios$#', 'usuarios_crear'],
    ['POST', '#^/admin/usuarios/(\d+)/estado$#', 'usuarios_actualizar_estado'],

    ['GET', '#^/proyectos$#', 'proyectos_index'],
    ['GET', '#^/proyectos/nueva$#', 'proyectos_nueva_form'],
    ['POST', '#^/proyectos$#', 'proyectos_crear'],
    ['GET', '#^/proyecto/(\d+)$#', 'proyectos_ver'],

    ['GET', '#^/tareas/nueva$#', 'tareas_nueva_form'],
    ['POST', '#^/tareas$#', 'tareas_crear'],
    ['GET', '#^/tareas/(\d+)$#', 'tareas_ver'],
    ['POST', '#^/tareas/(\d+)/estado$#', 'tareas_actualizar_estado'],
    ['POST', '#^/tareas/(\d+)/asignar$#', 'tareas_reasignar'],
    ['POST', '#^/tareas/(\d+)/comentarios$#', 'tareas_comentar'],
    ['POST', '#^/tareas/(\d+)/adjuntos$#', 'tareas_adjuntar'],
    ['POST', '#^/tareas/(\d+)/acceso$#', 'tareas_actualizar_acceso'],

    ['GET', '#^/decisiones/nueva$#', 'decisiones_nueva_form'],
    ['POST', '#^/decisiones$#', 'decisiones_crear'],
    ['GET', '#^/decisiones/(\d+)$#', 'decisiones_ver'],
    ['POST', '#^/decisiones/(\d+)/mensajes$#', 'decisiones_mensaje'],
    ['POST', '#^/decisiones/(\d+)/resolver$#', 'decisiones_resolver'],
    ['POST', '#^/decisiones/(\d+)/adjuntos$#', 'decisiones_adjuntar'],
];

foreach ($rutas as [$rutaMetodo, $patron, $handler]) {
    if ($rutaMetodo !== $metodo) {
        continue;
    }
    if (preg_match($patron, $path, $m)) {
        array_shift($m);
        $args = array_map('intval', $m);
        call_user_func($handler, ...$args);
        exit;
    }
}

http_response_code(404);
echo '404 — Página no encontrada';
