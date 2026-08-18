<div class="flex-between">
  <div>
    <h1><?= e($proyecto['nombre']) ?></h1>
    <p class="subtitle"><?= e($proyecto['cliente']) ?> · <span class="badge <?= $proyecto['estado'] === 'activo' ? 'badge-ok' : 'badge-neutral' ?>"><?= e($proyecto['estado']) ?></span></p>
  </div>
  <div class="toggle-group">
    <a href="<?= url('/proyecto/' . $proyecto['id'] . '?vista=todas') ?>" class="<?= $vista === 'todas' ? 'active' : '' ?>">Todas</a>
    <a href="<?= url('/proyecto/' . $proyecto['id'] . '?vista=mias') ?>" class="<?= $vista === 'mias' ? 'active' : '' ?>">Solo mías</a>
  </div>
</div>

<?php if ($proyecto['descripcion']): ?>
  <div class="card" style="margin-bottom:28px;">
    <p style="margin:0;white-space:pre-wrap;"><?= e($proyecto['descripcion']) ?></p>
    <?php if (!empty($miembros)): ?>
      <p class="text-muted" style="margin:12px 0 0;font-size:0.85rem;">Equipo: <?= e(implode(', ', array_column($miembros, 'nombre'))) ?></p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="section">
  <div class="section-head">
    <h2>Tareas del proyecto</h2>
    <a href="<?= url('/tareas/nueva?proyecto_id=' . $proyecto['id']) ?>" class="btn btn-sm">+ Nueva tarea</a>
  </div>
  <div class="card-list">
    <?php if (empty($tareas)): ?>
      <div class="empty-state">No hay tareas<?= $vista === 'mias' ? ' asignadas a vos' : '' ?> en este proyecto.</div>
    <?php else: foreach ($tareas as $t): $mostrarProyecto = false; require __DIR__ . '/../partials/tarea_card.php'; endforeach; endif; ?>
  </div>
</div>

<div class="section">
  <div class="section-head">
    <h2>Decisiones del proyecto</h2>
    <a href="<?= url('/decisiones/nueva?proyecto_id=' . $proyecto['id']) ?>" class="btn btn-sm">+ Nueva decisión</a>
  </div>
  <div class="card-list">
    <?php if (empty($decisiones)): ?>
      <div class="empty-state">No hay decisiones<?= $vista === 'mias' ? ' en las que participes' : '' ?> en este proyecto.</div>
    <?php else: foreach ($decisiones as $d): $mostrarProyecto = false; require __DIR__ . '/../partials/decision_card.php'; endforeach; endif; ?>
  </div>
</div>
