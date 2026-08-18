<h1>Nueva tarea</h1>
<p class="subtitle">Cargá una tarea general o de un proyecto específico.</p>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card form-card">
  <form method="post" action="<?= url('/tareas') ?>">
    <div class="form-group">
      <label for="titulo">Título</label>
      <input type="text" id="titulo" name="titulo" required autofocus>
    </div>
    <div class="form-group">
      <label for="descripcion">Descripción</label>
      <textarea id="descripcion" name="descripcion"></textarea>
    </div>
    <div class="form-group">
      <label for="proyecto_id">Proyecto</label>
      <select id="proyecto_id" name="proyecto_id">
        <option value="">General (sin proyecto)</option>
        <?php foreach ($proyectos as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $proyectoId === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['nombre']) ?> — <?= e($p['cliente']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="asignado_a">Asignar a</label>
        <select id="asignado_a" name="asignado_a">
          <option value="">Sin asignar</option>
          <?php foreach ($usuarios as $us): ?>
            <option value="<?= $us['id'] ?>"><?= e($us['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="prioridad">Prioridad</label>
        <select id="prioridad" name="prioridad">
          <option value="media" selected>Media</option>
          <option value="alta">Alta</option>
          <option value="baja">Baja</option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="fecha_inicio">Fecha inicio</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio">
      </div>
      <div class="form-group">
        <label for="fecha_limite">Fecha límite</label>
        <input type="date" id="fecha_limite" name="fecha_limite">
      </div>
    </div>
    <button type="submit" class="btn">Crear tarea</button>
  </form>
</div>
