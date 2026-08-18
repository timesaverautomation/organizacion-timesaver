<h1>Nuevo proyecto</h1>
<p class="subtitle">Cargá un cliente/proyecto nuevo para empezar a organizar tareas y decisiones.</p>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card form-card">
  <form method="post" action="<?= url('/proyectos') ?>">
    <div class="form-group">
      <label for="nombre">Nombre del proyecto</label>
      <input type="text" id="nombre" name="nombre" required>
    </div>
    <div class="form-group">
      <label for="cliente">Cliente</label>
      <input type="text" id="cliente" name="cliente" required>
    </div>
    <div class="form-group">
      <label for="descripcion">Descripción</label>
      <textarea id="descripcion" name="descripcion" placeholder="Contexto, alcance, links relevantes..."></textarea>
    </div>
    <div class="form-group">
      <label for="miembros">Equipo asignado</label>
      <select id="miembros" name="miembros[]" multiple size="5">
        <?php foreach ($usuarios as $us): ?>
          <option value="<?= $us['id'] ?>"><?= e($us['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn">Crear proyecto</button>
  </form>
</div>
