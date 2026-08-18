<div class="flex-between">
  <div>
    <h1>Usuarios</h1>
    <p class="subtitle">Alta y roles del equipo Time Saver.</p>
  </div>
  <a href="<?= url('/admin/usuarios/nuevo') ?>" class="btn">+ Nuevo usuario</a>
</div>

<div class="card-list">
  <?php foreach ($usuarios as $us): ?>
    <div class="card">
      <div class="item-row">
        <div>
          <p class="item-title"><?= e($us['nombre']) ?></p>
          <div class="item-meta">
            <span><?= e($us['email']) ?></span>
            <span class="badge <?= $us['rol'] === 'admin' ? 'badge-ok' : 'badge-neutral' ?>"><?= e($us['rol']) ?></span>
            <span class="badge <?= $us['activo'] ? 'badge-ok' : 'badge-error' ?>"><?= $us['activo'] ? 'activo' : 'inactivo' ?></span>
          </div>
        </div>
        <form method="post" action="<?= url('/admin/usuarios/' . $us['id'] . '/estado') ?>">
          <input type="hidden" name="activo" value="<?= $us['activo'] ? '0' : '1' ?>">
          <button type="submit" class="btn btn-sm"><?= $us['activo'] ? 'Desactivar' : 'Activar' ?></button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>
