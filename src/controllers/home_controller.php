<?php

function home_index(): void
{
    $u = requerir_login();
    marcar_vencidas_automaticamente();

    $vista = input('vista', 'todas') === 'mias' ? 'mias' : 'todas';
    $asignadoA = $vista === 'mias' ? (int)$u['id'] : null;
    $usuarioParticipante = $vista === 'mias' ? (int)$u['id'] : null;

    $tareas = listar_tareas(null, false, $asignadoA);
    $decisiones = listar_decisiones(null, false, $usuarioParticipante);

    $tareasPendientes = array_filter($tareas, fn($t) => $t['estado'] !== 'hecha');
    $decisionesAbiertas = array_filter($decisiones, fn($d) => $d['estado'] === 'abierta');
    $vencidos = array_filter($tareas, fn($t) => estado_plazo($t['fecha_limite'], $t['estado'] === 'hecha') === 'vencido')
        + array_filter($decisiones, fn($d) => estado_plazo($d['fecha_limite'], $d['estado'] !== 'abierta') === 'vencido');

    render('home/index', [
        'vista' => $vista,
        'tareas' => $tareasPendientes,
        'decisiones' => $decisiones,
        'totalPendientes' => count($tareasPendientes),
        'totalAbiertas' => count($decisionesAbiertas),
        'totalVencidos' => count($vencidos),
    ]);
}
