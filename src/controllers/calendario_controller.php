<?php

function calendario_index(): void
{
    $u = requerir_login();
    $esVendedor = !es_admin($u);
    $visibleParaUsuarioId = $esVendedor ? (int)$u['id'] : null;

    $modo = input('modo', 'grilla') === 'lista' ? 'lista' : 'grilla';
    $mesParam = (string)input('mes', date('Y-m'));
    if (!preg_match('#^\d{4}-\d{2}$#', $mesParam)) {
        $mesParam = date('Y-m');
    }
    $inicioMes = new DateTime($mesParam . '-01');
    $finMes = (clone $inicioMes)->modify('last day of this month');

    // La grilla arranca el lunes de la semana que contiene el día 1, y termina
    // el domingo de la semana que contiene el último día del mes.
    $inicioGrilla = (clone $inicioMes);
    $diaSemanaInicio = (int)$inicioGrilla->format('N'); // 1 (lunes) .. 7 (domingo)
    $inicioGrilla->modify('-' . ($diaSemanaInicio - 1) . ' days');

    $finGrilla = (clone $finMes);
    $diaSemanaFin = (int)$finGrilla->format('N');
    $finGrilla->modify('+' . (7 - $diaSemanaFin) . ' days');

    $tareas = listar_tareas_por_rango($inicioGrilla->format('Y-m-d'), $finGrilla->format('Y-m-d'), $visibleParaUsuarioId);
    $reuniones = listar_reuniones($inicioGrilla->format('Y-m-d'), $finGrilla->format('Y-m-d'), $visibleParaUsuarioId);

    $eventosPorDia = [];
    foreach ($tareas as $t) {
        $eventosPorDia[$t['fecha_limite']][] = ['tipo' => 'tarea', 'data' => $t];
    }
    foreach ($reuniones as $r) {
        $eventosPorDia[$r['fecha']][] = ['tipo' => 'reunion', 'data' => $r];
    }
    foreach ($eventosPorDia as &$dia) {
        usort($dia, function ($a, $b) {
            $horaA = $a['tipo'] === 'reunion' ? $a['data']['hora_inicio'] : '99:99';
            $horaB = $b['tipo'] === 'reunion' ? $b['data']['hora_inicio'] : '99:99';
            return $horaA <=> $horaB;
        });
    }
    unset($dia);

    $semanas = [];
    $cursor = clone $inicioGrilla;
    while ($cursor <= $finGrilla) {
        $semana = [];
        for ($i = 0; $i < 7; $i++) {
            $semana[] = clone $cursor;
            $cursor->modify('+1 day');
        }
        $semanas[] = $semana;
    }

    render('calendario/index', [
        'modo' => $modo,
        'mesParam' => $mesParam,
        'inicioMes' => $inicioMes,
        'semanas' => $semanas,
        'eventosPorDia' => $eventosPorDia,
        'usuarios' => obtener_usuarios(),
        'esAdmin' => es_admin($u),
        'usuarioActualId' => (int)$u['id'],
        'mesAnterior' => (clone $inicioMes)->modify('-1 month')->format('Y-m'),
        'mesSiguiente' => (clone $inicioMes)->modify('+1 month')->format('Y-m'),
        'error' => null,
    ]);
}

function calendario_reuniones_crear(): void
{
    $u = requerir_login();
    $titulo = trim((string)input('titulo', ''));
    $fecha = (string)input('fecha', '');
    $horaInicio = (string)input('hora_inicio', '');
    $horaFin = (string)input('hora_fin', '');
    $participantes = input('participantes', []);
    if (!is_array($participantes)) {
        $participantes = [];
    }
    $mesRetorno = $fecha !== '' ? substr($fecha, 0, 7) : date('Y-m');

    if ($titulo === '' || $fecha === '' || $horaInicio === '' || $horaFin === '') {
        redirigir('/calendario?mes=' . $mesRetorno . '&error=faltan_datos');
        return;
    }
    if ($horaFin <= $horaInicio) {
        redirigir('/calendario?mes=' . $mesRetorno . '&error=horario_invalido');
        return;
    }

    crear_reunion($titulo, $fecha, $horaInicio, $horaFin, (int)$u['id'], $participantes);
    redirigir('/calendario?mes=' . $mesRetorno);
}

function calendario_reuniones_eliminar(int $id): void
{
    $u = requerir_login();
    $reunion = obtener_reunion($id);
    if (!$reunion) {
        http_response_code(404);
        return;
    }
    if (!es_admin($u) && (int)$reunion['creado_por'] !== (int)$u['id']) {
        http_response_code(403);
        return;
    }
    $mesRetorno = substr($reunion['fecha'], 0, 7);
    eliminar_reunion($id);
    redirigir('/calendario?mes=' . $mesRetorno);
}
