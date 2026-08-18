<h1>Nueva decisión</h1>
<p class="subtitle">Planteá el tema para discutirlo y dejar la resolución asentada dentro de un plazo.</p>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card form-card">
  <form method="post" action="<?= url('/decisiones') ?>">
    <div class="form-group">
      <label for="titulo">Título</label>
      <input type="text" id="titulo" name="titulo" required autofocus>
    </div>
    <div class="form-group">
      <label for="bajada">Bajada / contexto</label>
      <textarea id="bajada" name="bajada" placeholder="Explicá el tema a decidir con el contexto necesario." required></textarea>
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
        <label for="fecha_limite">Plazo para resolver</label>
        <input type="date" id="fecha_limite" name="fecha_limite" required>
      </div>
      <div class="form-group">
        <label for="participantes">Participantes</label>
        <select id="participantes" name="participantes[]" multiple size="4">
          <?php foreach ($usuarios as $us): ?>
            <option value="<?= $us['id'] ?>"><?= e($us['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <button type="submit" class="btn">Crear decisión</button>
  </form>
</div>
