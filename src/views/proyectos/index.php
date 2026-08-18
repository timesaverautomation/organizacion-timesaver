<div class="flex-between">
  <div>
    <h1>Proyectos</h1>
    <p class="subtitle">Clientes y proyectos activos en Time Saver.</p>
  </div>
  <a href="<?= url('/proyectos/nueva') ?>" class="btn">+ Nuevo proyecto</a>
</div>

<div class="card-list">
  <?php if (empty($proyectos)): ?>
    <div class="empty-state">Todavía no hay proyectos cargados.</div>
  <?php else: foreach ($proyectos as $p): ?>
    <div class="card">
      <div class="item-row">
        <div>
          <p class="item-title"><a href="<?= url('/proyecto/' . $p['id']) ?>"><?= e($p['nombre']) ?></a></p>
          <div class="item-meta">
            <span class="badge badge-neutral"><?= e($p['cliente']) ?></span>
            <span class="badge <?= $p['estado'] === 'activo' ? 'badge-ok' : 'badge-neutral' ?>"><?= e($p['estado']) ?></span>
            <span><?= (int)$p['tareas_pendientes'] ?> tareas pendientes</span>
            <span><?= (int)$p['decisiones_abiertas'] ?> decisiones abiertas</span>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>
