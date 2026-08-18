<?php
/** Espera $t (fila de tarea) en scope. Opcional $mostrarProyecto. */
$plazo = estado_plazo($t['fecha_limite'], $t['estado'] === 'hecha');
?>
<div class="card">
  <div class="item-row">
    <div>
      <p class="item-title"><a href="<?= url('/tareas/' . $t['id']) ?>"><?= e($t['titulo']) ?></a></p>
      <div class="item-meta">
        <span class="badge badge-prioridad-<?= e($t['prioridad']) ?>"><?= e($t['prioridad']) ?></span>
        <span class="badge badge-neutral"><?= e(str_replace('_', ' ', $t['estado'])) ?></span>
        <?php if (!empty($mostrarProyecto)): ?>
          <span><?= $t['proyecto_nombre'] ? e($t['proyecto_nombre']) : 'General' ?></span>
        <?php endif; ?>
        <span>Asignado: <?= $t['asignado_nombre'] ? e($t['asignado_nombre']) : 'Sin asignar' ?></span>
        <span>Vence: <?= formatear_fecha($t['fecha_limite']) ?></span>
      </div>
    </div>
    <?php if ($plazo): ?>
      <span class="badge badge-<?= $plazo ?>"><?= ['vencido' => 'Vencido', 'proximo' => 'Próximo', 'ok' => 'En plazo'][$plazo] ?></span>
    <?php endif; ?>
  </div>
</div>
