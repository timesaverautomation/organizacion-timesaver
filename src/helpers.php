<?php
require_once __DIR__ . '/db.php';

function render(string $view, array $datos = []): void
{
    extract($datos);
    require __DIR__ . '/views/partials/header.php';
    require __DIR__ . "/views/{$view}.php";
    require __DIR__ . '/views/partials/footer.php';
}

function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function url(string $ruta = ''): string
{
    return BASE_PATH . $ruta;
}

function registrar_actividad(string $tipoEntidad, int $entidadId, int $usuarioId, string $accion, ?string $detalle = null): void
{
    $stmt = db()->prepare('INSERT INTO actividad_log (tipo_entidad, entidad_id, usuario_id, accion, detalle) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$tipoEntidad, $entidadId, $usuarioId, $accion, $detalle]);
}

function obtener_usuarios(): array
{
    return db()->query('SELECT id, nombre, email, rol FROM usuarios WHERE activo = 1 ORDER BY nombre')->fetchAll();
}

/**
 * Calcula el estado visual de un plazo (para tareas y decisiones).
 * Devuelve: 'vencido' | 'proximo' | 'ok' | null (sin fecha)
 */
function estado_plazo(?string $fechaLimite, bool $yaResuelta = false): ?string
{
    if (!$fechaLimite || $yaResuelta) {
        return null;
    }
    $hoy = new DateTime('today');
    $limite = new DateTime($fechaLimite);
    $diff = (int)$hoy->diff($limite)->format('%r%a');

    if ($diff < 0) {
        return 'vencido';
    }
    if ($diff <= DIAS_AVISO_PROXIMO) {
        return 'proximo';
    }
    return 'ok';
}

function formatear_fecha(?string $fecha): string
{
    if (!$fecha) {
        return '—';
    }
    $dt = new DateTime($fecha);
    return $dt->format('d/m/Y');
}

function metodo_es(string $metodo): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($metodo);
}

function input(string $clave, $default = null)
{
    return $_POST[$clave] ?? $_GET[$clave] ?? $default;
}
