<?php
require_once __DIR__ . '/db.php';

/**
 * Trae tareas con filtros opcionales: proyecto_id (null = todas, false = solo generales),
 * asignado_a (para vista "mías"), estado.
 *
 * $visibleParaUsuarioId: si se pasa, restringe el resultado a tareas asignadas
 * a ese usuario o compartidas con él vía tarea_acceso (uso: rol vendedor).
 */
function listar_tareas(?int $proyectoId = null, bool $soloGenerales = false, ?int $asignadoA = null, ?int $visibleParaUsuarioId = null): array
{
    $sql = 'SELECT t.*, u.nombre AS asignado_nombre, p.nombre AS proyecto_nombre
            FROM tareas t
            LEFT JOIN usuarios u ON u.id = t.asignado_a
            LEFT JOIN proyectos p ON p.id = t.proyecto_id
            WHERE 1=1';
    $params = [];

    if ($soloGenerales) {
        $sql .= ' AND t.proyecto_id IS NULL';
    } elseif ($proyectoId !== null) {
        $sql .= ' AND t.proyecto_id = ?';
        $params[] = $proyectoId;
    }

    if ($asignadoA !== null) {
        $sql .= ' AND t.asignado_a = ?';
        $params[] = $asignadoA;
    }

    if ($visibleParaUsuarioId !== null) {
        $sql .= ' AND (t.asignado_a = ? OR EXISTS (SELECT 1 FROM tarea_acceso ta WHERE ta.tarea_id = t.id AND ta.usuario_id = ?))';
        $params[] = $visibleParaUsuarioId;
        $params[] = $visibleParaUsuarioId;
    }

    $sql .= ' ORDER BY (t.fecha_limite IS NULL), t.fecha_limite ASC, t.creado_en DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function usuario_puede_ver_tarea(array $tarea, array $usuario): bool
{
    if (es_admin($usuario)) {
        return true;
    }
    if ((int)($tarea['asignado_a'] ?? 0) === (int)$usuario['id']) {
        return true;
    }
    $stmt = db()->prepare('SELECT 1 FROM tarea_acceso WHERE tarea_id = ? AND usuario_id = ?');
    $stmt->execute([$tarea['id'], $usuario['id']]);
    return (bool)$stmt->fetchColumn();
}

function listar_acceso_tarea(int $tareaId): array
{
    $stmt = db()->prepare('SELECT u.id, u.nombre FROM tarea_acceso ta JOIN usuarios u ON u.id = ta.usuario_id WHERE ta.tarea_id = ? ORDER BY u.nombre');
    $stmt->execute([$tareaId]);
    return $stmt->fetchAll();
}

function guardar_acceso_tarea(int $tareaId, array $usuarioIds): void
{
    $pdo = db();
    $pdo->prepare('DELETE FROM tarea_acceso WHERE tarea_id = ?')->execute([$tareaId]);
    $stmt = $pdo->prepare('INSERT IGNORE INTO tarea_acceso (tarea_id, usuario_id) VALUES (?, ?)');
    foreach (array_unique(array_map('intval', $usuarioIds)) as $uid) {
        if ($uid > 0) {
            $stmt->execute([$tareaId, $uid]);
        }
    }
}

function listar_decisiones(?int $proyectoId = null, bool $soloGenerales = false, ?int $usuarioId = null): array
{
    $sql = 'SELECT d.*, u.nombre AS creado_por_nombre, p.nombre AS proyecto_nombre
            FROM decisiones d
            LEFT JOIN usuarios u ON u.id = d.creado_por
            LEFT JOIN proyectos p ON p.id = d.proyecto_id';
    $params = [];

    if ($usuarioId !== null) {
        $sql .= ' JOIN decision_participantes dp ON dp.decision_id = d.id AND dp.usuario_id = ?';
        $params[] = $usuarioId;
    }

    $sql .= ' WHERE 1=1';

    if ($soloGenerales) {
        $sql .= ' AND d.proyecto_id IS NULL';
    } elseif ($proyectoId !== null) {
        $sql .= ' AND d.proyecto_id = ?';
        $params[] = $proyectoId;
    }

    $sql .= ' ORDER BY (d.estado = "abierta") DESC, d.fecha_limite ASC, d.creado_en DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function obtener_proyecto(int $id): ?array
{
    $stmt = db()->prepare('SELECT p.*, u.nombre AS creado_por_nombre FROM proyectos p JOIN usuarios u ON u.id = p.creado_por WHERE p.id = ?');
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    return $p ?: null;
}

function listar_proyectos(): array
{
    return db()->query('SELECT p.*, u.nombre AS creado_por_nombre,
            (SELECT COUNT(*) FROM tareas t WHERE t.proyecto_id = p.id AND t.estado != "hecha") AS tareas_pendientes,
            (SELECT COUNT(*) FROM decisiones d WHERE d.proyecto_id = p.id AND d.estado = "abierta") AS decisiones_abiertas
        FROM proyectos p JOIN usuarios u ON u.id = p.creado_por
        ORDER BY (p.estado = "activo") DESC, p.nombre')->fetchAll();
}

function obtener_tarea(int $id): ?array
{
    $stmt = db()->prepare('SELECT t.*, u.nombre AS asignado_nombre, p.nombre AS proyecto_nombre, c.nombre AS creado_por_nombre
        FROM tareas t
        LEFT JOIN usuarios u ON u.id = t.asignado_a
        LEFT JOIN usuarios c ON c.id = t.creado_por
        LEFT JOIN proyectos p ON p.id = t.proyecto_id
        WHERE t.id = ?');
    $stmt->execute([$id]);
    $t = $stmt->fetch();
    return $t ?: null;
}

function listar_comentarios_tarea(int $tareaId): array
{
    $stmt = db()->prepare('SELECT tc.*, u.nombre AS usuario_nombre FROM tarea_comentarios tc
        JOIN usuarios u ON u.id = tc.usuario_id WHERE tc.tarea_id = ? ORDER BY tc.creado_en ASC');
    $stmt->execute([$tareaId]);
    return $stmt->fetchAll();
}

function obtener_decision(int $id): ?array
{
    $stmt = db()->prepare('SELECT d.*, u.nombre AS creado_por_nombre, p.nombre AS proyecto_nombre, r.nombre AS resuelto_por_nombre
        FROM decisiones d
        LEFT JOIN usuarios u ON u.id = d.creado_por
        LEFT JOIN usuarios r ON r.id = d.resuelto_por
        LEFT JOIN proyectos p ON p.id = d.proyecto_id
        WHERE d.id = ?');
    $stmt->execute([$id]);
    $d = $stmt->fetch();
    return $d ?: null;
}

function listar_mensajes_decision(int $decisionId): array
{
    $stmt = db()->prepare('SELECT dm.*, u.nombre AS usuario_nombre FROM decision_mensajes dm
        JOIN usuarios u ON u.id = dm.usuario_id WHERE dm.decision_id = ? ORDER BY dm.creado_en ASC');
    $stmt->execute([$decisionId]);
    return $stmt->fetchAll();
}

function listar_adjuntos(string $tipoPadre, int $padreId): array
{
    $stmt = db()->prepare('SELECT a.*, u.nombre AS creado_por_nombre FROM adjuntos a
        JOIN usuarios u ON u.id = a.creado_por WHERE a.tipo_padre = ? AND a.padre_id = ? ORDER BY a.creado_en ASC');
    $stmt->execute([$tipoPadre, $padreId]);
    return $stmt->fetchAll();
}

function listar_actividad(string $tipoEntidad, int $entidadId): array
{
    $stmt = db()->prepare('SELECT al.*, u.nombre AS usuario_nombre FROM actividad_log al
        JOIN usuarios u ON u.id = al.usuario_id WHERE al.tipo_entidad = ? AND al.entidad_id = ? ORDER BY al.creado_en DESC');
    $stmt->execute([$tipoEntidad, $entidadId]);
    return $stmt->fetchAll();
}

function marcar_vencidas_automaticamente(): void
{
    db()->exec("UPDATE decisiones SET estado = 'vencida' WHERE estado = 'abierta' AND fecha_limite < CURDATE()");
}

function listar_usuarios_admin(): array
{
    return db()->query('SELECT id, nombre, email, rol, activo, creado_en FROM usuarios ORDER BY activo DESC, nombre')->fetchAll();
}

function obtener_usuario_por_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function crear_usuario(string $nombre, string $email, string $password, string $rol): int
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare('INSERT INTO usuarios (nombre, email, password_hash, debe_cambiar_password, rol) VALUES (?, ?, ?, 1, ?)');
    $stmt->execute([$nombre, $email, $hash, $rol]);
    return (int)db()->lastInsertId();
}

function actualizar_estado_usuario(int $id, bool $activo): void
{
    $stmt = db()->prepare('UPDATE usuarios SET activo = ? WHERE id = ?');
    $stmt->execute([$activo ? 1 : 0, $id]);
}
