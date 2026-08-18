<h1>Nuevo usuario</h1>
<p class="subtitle">Se crea con la contraseña indicada; se le va a pedir cambiarla en su primer login.</p>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card form-card">
  <form method="post" action="<?= url('/admin/usuarios') ?>">
    <div class="form-group">
      <label for="nombre">Nombre</label>
      <input type="text" id="nombre" name="nombre" required>
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
      <label for="password">Contraseña inicial</label>
      <input type="password" id="password" name="password" minlength="8" required>
    </div>
    <div class="form-group">
      <label for="rol">Rol</label>
      <select id="rol" name="rol">
        <option value="vendedor">Vendedor (ve solo sus tareas asignadas o compartidas)</option>
        <option value="admin">Admin (ve y gestiona todo)</option>
      </select>
    </div>
    <button type="submit" class="btn">Crear usuario</button>
  </form>
</div>
