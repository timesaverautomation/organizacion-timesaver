<?php

function decisiones_nueva_form(): void
{
    requerir_login();
    $proyectoId = input('proyecto_id') !== null && input('proyecto_id') !== '' ? (int)input('proyecto_id') : null;
    render('decisiones/nueva', [
        'usuarios' => obtener_usuarios(),
        'proyectos' => listar_proyectos(),
        'proyectoId' => $proyectoId,
        'error' => null,
    ]);
}

function decisiones_crear(): void
{
    $u = requerir_login();
    $titulo = trim((string)input('titulo', ''));
    $bajada = trim((string)input('bajada', ''));
    $proyectoId = input('proyecto_id', '') !== '' ? (int)input('proyecto_id') : null;
    $fechaLimite = input('fecha_limite') ?: null;
    $participantes = input('participantes', []);
    if (!is_array($participantes)) {
        $participantes = [];
    }

    if ($titulo === '' || $bajada === '' || !$fechaLimite) {
        render('decisiones/nueva', [
            'usuarios' => obtener_usuarios(), 'proyectos' => listar_proyectos(),
            'proyectoId' => $proyectoId, 'error' => 'Título, bajada y plazo son obligatorios.',
        ]);
        return;
    }

    $stmt = db()->prepare('INSERT INTO decisiones (proyecto_id, titulo, bajada, fecha_limite, creado_por) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$proyectoId, $titulo, $bajada, $fechaLimite, $u['id']]);
    $decisionId = (int)db()->lastInsertId();

    $participantesIds = array_unique(array_merge(array_map('intval', $participantes), [(int)$u['id']]));
    $stmtP = db()->prepare('INSERT IGNORE INTO decision_participantes (decision_id, usuario_id) VALUES (?, ?)');
    foreach ($participantesIds as $pid) {
        $stmtP->execute([$decisionId, $pid]);
    }

    registrar_actividad('decision', $decisionId, (int)$u['id'], 'creada');

    foreach ($participantesIds as $pid) {
        if ($pid !== (int)$u['id']) {
            notificar_usuario($pid, 'decision_participante', "Te sumaron a la decisión \"$titulo\"", '/decisiones/' . $decisionId);
        }
    }

    redirigir($proyectoId ? '/proyecto/' . $proyectoId : '/');
}

function decisiones_ver(int $id): void
{
    requerir_login();
    $decision = obtener_decision($id);
    if (!$decision) {
        http_response_code(404);
        echo 'Decisión no encontrada';
        return;
    }
    if ($decision['estado'] === 'abierta' && $decision['fecha_limite'] < date('Y-m-d')) {
        db()->prepare("UPDATE decisiones SET estado = 'vencida' WHERE id = ?")->execute([$id]);
        $decision['estado'] = 'vencida';
    }
    render('decisiones/ver', [
        'decision' => $decision,
        'mensajes' => listar_mensajes_decision($id),
        'adjuntos' => listar_adjuntos('decision', $id),
        'actividad' => listar_actividad('decision', $id),
    ]);
}

function decisiones_mensaje(int $id): void
{
    $u = requerir_login();
    $mensaje = trim((string)input('mensaje', ''));
    if ($mensaje !== '') {
        $stmt = db()->prepare('INSERT INTO decision_mensajes (decision_id, usuario_id, mensaje) VALUES (?, ?, ?)');
        $stmt->execute([$id, $u['id'], $mensaje]);
        db()->prepare('INSERT IGNORE INTO decision_participantes (decision_id, usuario_id) VALUES (?, ?)')->execute([$id, $u['id']]);
        registrar_actividad('decision', $id, (int)$u['id'], 'comentario');
    }
    redirigir('/decisiones/' . $id);
}

function decisiones_resolver(int $id): void
{
    $u = requerir_login();
    $resolucion = trim((string)input('resolucion', ''));
    if ($resolucion !== '') {
        $stmt = db()->prepare("UPDATE decisiones SET estado = 'resuelta', resolucion = ?, resuelto_por = ?, fecha_resolucion = NOW() WHERE id = ?");
        $stmt->execute([$resolucion, $u['id'], $id]);
        registrar_actividad('decision', $id, (int)$u['id'], 'resuelta', $resolucion);
    }
    redirigir('/decisiones/' . $id);
}

function decisiones_adjuntar(int $id): void
{
    $u = requerir_login();
    $urlAdjunto = trim((string)input('url', ''));
    $desc = trim((string)input('descripcion', ''));
    if ($urlAdjunto !== '') {
        $stmt = db()->prepare('INSERT INTO adjuntos (tipo_padre, padre_id, url, descripcion, creado_por) VALUES ("decision", ?, ?, ?, ?)');
        $stmt->execute([$id, $urlAdjunto, $desc, $u['id']]);
        registrar_actividad('decision', $id, (int)$u['id'], 'adjunto_agregado', $desc ?: $urlAdjunto);
    }
    redirigir('/decisiones/' . $id);
}
