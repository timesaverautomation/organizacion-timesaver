<?php
/** Espera $d (fila de decisión) en scope. Opcional $mostrarProyecto. */
$plazo = estado_plazo($d['fecha_limite'], $d['estado'] !== 'abierta');
$estadoLabel = ['abierta' => 'Abierta', 'resuelta' => 'Resuelta', 'vencida' => 'Vencida'][$d['estado']];
$estadoBadge = ['abierta' => 'accent', 'resuelta' => 'ok', 'vencida' => 'vencido'][$d['estado']];
?>
<div class="card">
  <div class="item-row">
    <div>
      <p class="item-title"><a href="<?= url('/decisiones/' . $d['id']) ?>"><?= e($d['titulo']) ?></a></p>
      <p class="text-muted" style="margin:0 0 8px;font-size:0.88rem;"><?= e($d['bajada']) ?></p>
      <?php if ($d['estado'] === 'resuelta' && $d['resolucion']): ?>
        <p class="resolution-box"><strong>Resolución:</strong> <?= e($d['resolucion']) ?></p>
      <?php endif; ?>
      <div class="item-meta">
        <span class="badge badge-<?= $estadoBadge ?>"><?= $estadoLabel ?></span>
        <?php if (!empty($mostrarProyecto)): ?>
          <span><?= $d['proyecto_nombre'] ? e($d['proyecto_nombre']) : 'General' ?></span>
        <?php endif; ?>
        <span>Creada por: <?= e($d['creado_por_nombre']) ?></span>
        <span>Plazo: <?= formatear_fecha($d['fecha_limite']) ?></span>
      </div>
    </div>
    <?php if ($plazo): ?>
      <span class="badge badge-<?= $plazo ?>"><?= ['vencido' => 'Vencido', 'proximo' => 'Próximo', 'ok' => 'En plazo'][$plazo] ?></span>
    <?php endif; ?>
  </div>
</div>
