<div class="flex-between">
  <div>
    <h1>Home</h1>
    <p class="subtitle">Panorama general de tareas y decisiones de Time Saver.</p>
  </div>
  <?php if (!$esVendedor): ?>
  <div class="toggle-group">
    <a href="<?= url('/?vista=todas') ?>" class="<?= $vista === 'todas' ? 'active' : '' ?>">Todas</a>
    <a href="<?= url('/?vista=mias') ?>" class="<?= $vista === 'mias' ? 'active' : '' ?>">Solo mías</a>
  </div>
  <?php endif; ?>
</div>

<div class="stat-row">
  <div class="stat"><div class="num"><?= $totalPendientes ?></div><div class="label">Tareas pendientes</div></div>
  <div class="stat"><div class="num"><?= $totalAbiertas ?></div><div class="label">Decisiones abiertas</div></div>
  <div class="stat"><div class="num" style="color:var(--red)"><?= $totalVencidos ?></div><div class="label">Vencidos</div></div>
</div>

<div class="section">
  <div class="section-head">
    <h2>Tareas</h2>
    <a href="<?= url('/tareas/nueva') ?>" class="btn btn-sm">+ Nueva tarea</a>
  </div>
  <div class="card-list">
    <?php if (empty($tareas)): ?>
      <div class="empty-state">No hay tareas pendientes<?= $vista === 'mias' ? ' asignadas a vos' : '' ?>.</div>
    <?php else: foreach ($tareas as $t): $mostrarProyecto = true; require __DIR__ . '/../partials/tarea_card.php'; endforeach; endif; ?>
  </div>
</div>

<div class="section">
  <div class="section-head">
    <h2>Decisiones</h2>
    <a href="<?= url('/decisiones/nueva') ?>" class="btn btn-sm">+ Nueva decisión</a>
  </div>
  <div class="card-list">
    <?php if (empty($decisiones)): ?>
      <div class="empty-state">No hay decisiones<?= $vista === 'mias' ? ' en las que participes' : '' ?>.</div>
    <?php else: foreach ($decisiones as $d): $mostrarProyecto = true; require __DIR__ . '/../partials/decision_card.php'; endforeach; endif; ?>
  </div>
</div>
