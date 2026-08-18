<?php

function tareas_nueva_form(): void
{
    $u = requerir_login();
    $proyectoId = input('proyecto_id') !== null && input('proyecto_id') !== '' ? (int)input('proyecto_id') : null;
    render('tareas/nueva', [
        'usuarios' => obtener_usuarios(),
        'proyectos' => listar_proyectos(),
        'proyectoId' => $proyectoId,
        'esAdmin' => es_admin($u),
        'error' => null,
    ]);
}

function tareas_crear(): void
{
    $u = requerir_login();
    $titulo = trim((string)input('titulo', ''));
    $descripcion = trim((string)input('descripcion', ''));
    $proyectoId = input('proyecto_id', '') !== '' ? (int)input('proyecto_id') : null;
    $prioridad = in_array(input('prioridad'), ['alta', 'media', 'baja'], true) ? input('prioridad') : 'media';
    $fechaInicio = input('fecha_inicio') ?: null;
    $fechaLimite = input('fecha_limite') ?: null;
    $asignadoA = input('asignado_a', '') !== '' ? (int)input('asignado_a') : null;
    $compartidoCon = input('compartido_con', []);
    if (!is_array($compartidoCon)) {
        $compartidoCon = [];
    }

    if ($titulo === '') {
        render('tareas/nueva', [
            'usuarios' => obtener_usuarios(), 'proyectos' => listar_proyectos(),
            'proyectoId' => $proyectoId, 'esAdmin' => es_admin($u), 'error' => 'El título es obligatorio.',
        ]);
        return;
    }

    $stmt = db()->prepare('INSERT INTO tareas (proyecto_id, titulo, descripcion, prioridad, fecha_inicio, fecha_limite, asignado_a, creado_por)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$proyectoId, $titulo, $descripcion, $prioridad, $fechaInicio, $fechaLimite, $asignadoA, $u['id']]);
    $tareaId = (int)db()->lastInsertId();

    if (!empty($compartidoCon)) {
        guardar_acceso_tarea($tareaId, $compartidoCon);
    }

    registrar_actividad('tarea', $tareaId, (int)$u['id'], 'creada', $asignadoA ? "Asignada a usuario #$asignadoA" : null);

    redirigir($proyectoId ? '/proyecto/' . $proyectoId : '/');
}

function tareas_ver(int $id): void
{
    $u = requerir_login();
    $tarea = obtener_tarea($id);
    if (!$tarea) {
        http_response_code(404);
        echo 'Tarea no encontrada';
        return;
    }
    if (!usuario_puede_ver_tarea($tarea, $u)) {
        http_response_code(403);
        echo '403 — No tenés acceso a esta tarea.';
        return;
    }
    render('tareas/ver', [
        'tarea' => $tarea,
        'usuarios' => obtener_usuarios(),
        'esAdmin' => es_admin($u),
        'accesoCompartido' => es_admin($u) ? listar_acceso_tarea($id) : [],
        'comentarios' => listar_comentarios_tarea($id),
        'adjuntos' => listar_adjuntos('tarea', $id),
        'actividad' => listar_actividad('tarea', $id),
    ]);
}

function tareas_actualizar_estado(int $id): void
{
    $u = requerir_login();
    $tarea = obtener_tarea($id);
    if (!$tarea) {
        http_response_code(404);
        return;
    }
    if (!usuario_puede_ver_tarea($tarea, $u)) {
        http_response_code(403);
        return;
    }
    $estado = input('estado');
    if (in_array($estado, ['pendiente', 'en_curso', 'hecha'], true)) {
        $stmt = db()->prepare('UPDATE tareas SET estado = ? WHERE id = ?');
        $stmt->execute([$estado, $id]);
        registrar_actividad('tarea', $id, (int)$u['id'], 'cambio_estado', "Nuevo estado: $estado");
    }
    redirigir('/tareas/' . $id);
}

function tareas_comentar(int $id): void
{
    $u = requerir_login();
    $tarea = obtener_tarea($id);
    if (!$tarea || !usuario_puede_ver_tarea($tarea, $u)) {
        http_response_code($tarea ? 403 : 404);
        return;
    }
    $mensaje = trim((string)input('mensaje', ''));
    if ($mensaje !== '') {
        $stmt = db()->prepare('INSERT INTO tarea_comentarios (tarea_id, usuario_id, mensaje) VALUES (?, ?, ?)');
        $stmt->execute([$id, $u['id'], $mensaje]);
        registrar_actividad('tarea', $id, (int)$u['id'], 'comentario');
    }
    redirigir('/tareas/' . $id);
}

function tareas_actualizar_acceso(int $id): void
{
    $u = requerir_admin();
    $tarea = obtener_tarea($id);
    if (!$tarea) {
        http_response_code(404);
        return;
    }
    $compartidoCon = input('compartido_con', []);
    if (!is_array($compartidoCon)) {
        $compartidoCon = [];
    }
    guardar_acceso_tarea($id, $compartidoCon);
    registrar_actividad('tarea', $id, (int)$u['id'], 'acceso_actualizado');
    redirigir('/tareas/' . $id);
}

function tareas_adjuntar(int $id): void
{
    $u = requerir_login();
    $tarea = obtener_tarea($id);
    if (!$tarea || !usuario_puede_ver_tarea($tarea, $u)) {
        http_response_code($tarea ? 403 : 404);
        return;
    }
    $urlAdjunto = trim((string)input('url', ''));
    $desc = trim((string)input('descripcion', ''));
    if ($urlAdjunto !== '') {
        $stmt = db()->prepare('INSERT INTO adjuntos (tipo_padre, padre_id, url, descripcion, creado_por) VALUES ("tarea", ?, ?, ?, ?)');
        $stmt->execute([$id, $urlAdjunto, $desc, $u['id']]);
        registrar_actividad('tarea', $id, (int)$u['id'], 'adjunto_agregado', $desc ?: $urlAdjunto);
    }
    redirigir('/tareas/' . $id);
}
