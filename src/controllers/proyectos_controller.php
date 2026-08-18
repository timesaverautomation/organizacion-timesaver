<?php

function proyectos_index(): void
{
    requerir_login();
    $proyectos = listar_proyectos();
    render('proyectos/index', ['proyectos' => $proyectos]);
}

function proyectos_nueva_form(): void
{
    requerir_login();
    render('proyectos/nuevo', ['usuarios' => obtener_usuarios(), 'error' => null]);
}

function proyectos_crear(): void
{
    $u = requerir_login();
    $nombre = trim((string)input('nombre', ''));
    $cliente = trim((string)input('cliente', ''));
    $descripcion = trim((string)input('descripcion', ''));
    $miembros = input('miembros', []);
    if (!is_array($miembros)) {
        $miembros = [];
    }

    if ($nombre === '' || $cliente === '') {
        render('proyectos/nuevo', ['usuarios' => obtener_usuarios(), 'error' => 'Nombre y cliente son obligatorios.']);
        return;
    }

    $stmt = db()->prepare('INSERT INTO proyectos (nombre, cliente, descripcion, creado_por) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nombre, $cliente, $descripcion, $u['id']]);
    $proyectoId = (int)db()->lastInsertId();

    $miembrosIds = array_unique(array_merge(array_map('intval', $miembros), [(int)$u['id']]));
    $stmtM = db()->prepare('INSERT IGNORE INTO proyecto_miembros (proyecto_id, usuario_id) VALUES (?, ?)');
    foreach ($miembrosIds as $mid) {
        $stmtM->execute([$proyectoId, $mid]);
    }

    redirigir('/proyecto/' . $proyectoId);
}

function proyectos_ver(int $id): void
{
    $u = requerir_login();
    marcar_vencidas_automaticamente();

    $proyecto = obtener_proyecto($id);
    if (!$proyecto) {
        http_response_code(404);
        echo 'Proyecto no encontrado';
        return;
    }

    $vista = input('vista', 'todas') === 'mias' ? 'mias' : 'todas';
    $asignadoA = $vista === 'mias' ? (int)$u['id'] : null;
    $usuarioParticipante = $vista === 'mias' ? (int)$u['id'] : null;

    $tareas = listar_tareas($id, false, $asignadoA);
    $decisiones = listar_decisiones($id, false, $usuarioParticipante);
    $miembros = db()->prepare('SELECT us.id, us.nombre FROM proyecto_miembros pm JOIN usuarios us ON us.id = pm.usuario_id WHERE pm.proyecto_id = ? ORDER BY us.nombre');
    $miembros->execute([$id]);

    render('proyectos/ver', [
        'proyecto' => $proyecto,
        'vista' => $vista,
        'tareas' => $tareas,
        'decisiones' => $decisiones,
        'miembros' => $miembros->fetchAll(),
    ]);
}
