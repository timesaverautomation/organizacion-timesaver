<?php
$plazo = estado_plazo($decision['fecha_limite'], $decision['estado'] !== 'abierta');
$estadoLabel = ['abierta' => 'Abierta', 'resuelta' => 'Resuelta', 'vencida' => 'Vencida'][$decision['estado']];
$estadoBadge = ['abierta' => 'accent', 'resuelta' => 'ok', 'vencida' => 'vencido'][$decision['estado']];
?>

<p class="text-muted" style="font-size:0.85rem;margin-bottom:6px;">
  <a href="<?= $decision['proyecto_id'] ? url('/proyecto/' . $decision['proyecto_id']) : url('/') ?>">&larr; <?= $decision['proyecto_nombre'] ? e($decision['proyecto_nombre']) : 'Home' ?></a>
</p>

<div class="flex-between">
  <h1><?= e($decision['titulo']) ?></h1>
  <span class="badge badge-<?= $estadoBadge ?>"><?= $estadoLabel ?></span>
</div>

<div class="item-meta" style="margin-bottom:16px;">
  <span>Creada por <?= e($decision['creado_por_nombre']) ?></span>
  <span>Plazo: <?= formatear_fecha($decision['fecha_limite']) ?></span>
  <?php if ($plazo): ?><span class="badge badge-<?= $plazo ?>"><?= ['vencido' => 'Vencido', 'proximo' => 'Próximo', 'ok' => 'En plazo'][$plazo] ?></span><?php endif; ?>
</div>

<div class="card"><p style="margin:0;white-space:pre-wrap;"><?= e($decision['bajada']) ?></p></div>

<?php if ($decision['estado'] === 'resuelta'): ?>
  <div class="card" style="margin-top:16px;border-left:3px solid var(--green);">
    <p class="text-muted" style="margin:0 0 6px;font-size:0.82rem;">Resolución — <?= e($decision['resuelto_por_nombre']) ?>, <?= (new DateTime($decision['fecha_resolucion']))->format('d/m/Y H:i') ?></p>
    <p style="margin:0;white-space:pre-wrap;"><?= e($decision['resolucion']) ?></p>
  </div>
<?php elseif ($decision['estado'] !== 'resuelta'): ?>
  <div class="card" style="margin-top:16px;">
    <h2>Marcar resolución</h2>
    <form method="post" action="<?= url('/decisiones/' . $decision['id'] . '/resolver') ?>">
      <div class="form-group">
        <textarea name="resolucion" placeholder="Qué se decidió finalmente..." required></textarea>
      </div>
      <button type="submit" class="btn">Resolver decisión</button>
    </form>
  </div>
<?php endif; ?>

<div class="section" style="margin-top:28px;">
  <h2>Links / adjuntos</h2>
  <div class="stack" style="margin-bottom:12px;">
    <?php foreach ($adjuntos as $a): ?>
      <div><a href="<?= e($a['url']) ?>" target="_blank" rel="noopener"><?= e($a['descripcion'] ?: $a['url']) ?></a> <span class="text-muted" style="font-size:0.8rem;">— <?= e($a['creado_por_nombre']) ?></span></div>
    <?php endforeach; ?>
    <?php if (empty($adjuntos)): ?><p class="text-muted">Sin links todavía.</p><?php endif; ?>
  </div>
  <form method="post" action="<?= url('/decisiones/' . $decision['id'] . '/adjuntos') ?>" style="display:flex;gap:8px;">
    <input type="url" name="url" placeholder="https://..." required style="flex:2;">
    <input type="text" name="descripcion" placeholder="Descripción (opcional)" style="flex:1;">
    <button type="submit" class="btn btn-secondary btn-sm">Agregar</button>
  </form>
</div>

<div class="section" style="margin-top:28px;">
  <h2>Ida y vuelta</h2>
  <div class="thread">
    <?php foreach ($mensajes as $m): ?>
      <div class="msg">
        <div class="msg-meta"><?= e($m['usuario_nombre']) ?> · <?= (new DateTime($m['creado_en']))->format('d/m/Y H:i') ?></div>
        <div><?= nl2br(e($m['mensaje'])) ?></div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($mensajes)): ?><p class="text-muted">Todavía no hay mensajes.</p><?php endif; ?>
  </div>
  <form method="post" action="<?= url('/decisiones/' . $decision['id'] . '/mensajes') ?>">
    <div class="form-group">
      <textarea name="mensaje" placeholder="Responder en el hilo..." required></textarea>
    </div>
    <button type="submit" class="btn btn-sm">Responder</button>
  </form>
</div>

<?php if (!empty($actividad)): ?>
<div class="section" style="margin-top:28px;">
  <h2>Historial de actividad</h2>
  <div class="stack">
    <?php foreach ($actividad as $a): ?>
      <div class="text-muted" style="font-size:0.82rem;">
        <?= (new DateTime($a['creado_en']))->format('d/m/Y H:i') ?> — <?= e($a['usuario_nombre']) ?> <?= e(str_replace('_', ' ', $a['accion'])) ?><?= $a['detalle'] ? ': ' . e($a['detalle']) : '' ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
