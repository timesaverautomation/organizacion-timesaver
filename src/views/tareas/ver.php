<?php $plazo = estado_plazo($tarea['fecha_limite'], $tarea['estado'] === 'hecha'); ?>

<p class="text-muted" style="font-size:0.85rem;margin-bottom:6px;">
  <a href="<?= $tarea['proyecto_id'] ? url('/proyecto/' . $tarea['proyecto_id']) : url('/') ?>">&larr; <?= $tarea['proyecto_nombre'] ? e($tarea['proyecto_nombre']) : 'Home' ?></a>
</p>

<div class="flex-between">
  <h1><?= e($tarea['titulo']) ?></h1>
  <?php if ($plazo): ?><span class="badge badge-<?= $plazo ?>"><?= ['vencido' => 'Vencido', 'proximo' => 'Próximo', 'ok' => 'En plazo'][$plazo] ?></span><?php endif; ?>
</div>

<div class="item-meta" style="margin-bottom:20px;">
  <span class="badge badge-prioridad-<?= e($tarea['prioridad']) ?>"><?= e($tarea['prioridad']) ?></span>
  <span>Asignado: <?= $tarea['asignado_nombre'] ? e($tarea['asignado_nombre']) : 'Sin asignar' ?></span>
  <span>Creada por <?= e($tarea['creado_por_nombre']) ?></span>
  <span>Inicio: <?= formatear_fecha($tarea['fecha_inicio']) ?></span>
  <span>Límite: <?= formatear_fecha($tarea['fecha_limite']) ?></span>
</div>

<?php if ($tarea['descripcion']): ?>
  <div class="card"><p style="margin:0;white-space:pre-wrap;"><?= e($tarea['descripcion']) ?></p></div>
<?php endif; ?>

<div class="card" style="margin-top:16px;">
  <form method="post" action="<?= url('/tareas/' . $tarea['id'] . '/estado') ?>">
    <label for="estado">Estado</label>
    <div style="display:flex;gap:10px;">
      <select id="estado" name="estado" style="max-width:200px;">
        <option value="pendiente" <?= $tarea['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
        <option value="en_curso" <?= $tarea['estado'] === 'en_curso' ? 'selected' : '' ?>>En curso</option>
        <option value="hecha" <?= $tarea['estado'] === 'hecha' ? 'selected' : '' ?>>Hecha</option>
      </select>
      <button type="submit" class="btn btn-sm">Actualizar</button>
    </div>
  </form>
</div>

<?php if ($esAdmin): ?>
<div class="card" style="margin-top:16px;">
  <form method="post" action="<?= url('/tareas/' . $tarea['id'] . '/asignar') ?>">
    <label for="asignado_a">Reasignar tarea</label>
    <div style="display:flex;gap:10px;">
      <select id="asignado_a" name="asignado_a" style="max-width:200px;">
        <option value="">Sin asignar</option>
        <?php foreach ($usuarios as $us): ?>
          <option value="<?= $us['id'] ?>" <?= (int)$tarea['asignado_a'] === (int)$us['id'] ? 'selected' : '' ?>><?= e($us['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-sm">Reasignar</button>
    </div>
  </form>
</div>
<?php endif; ?>

<?php if ($esAdmin): ?>
<div class="card" style="margin-top:16px;">
  <form method="post" action="<?= url('/tareas/' . $tarea['id'] . '/acceso') ?>">
    <label for="compartido_con">Compartir acceso además del asignado</label>
    <?php $idsConAcceso = array_column($accesoCompartido, 'id'); ?>
    <select id="compartido_con" name="compartido_con[]" multiple size="5" style="width:100%;margin:8px 0;">
      <?php foreach ($usuarios as $us): ?>
        <option value="<?= $us['id'] ?>" <?= in_array($us['id'], $idsConAcceso, true) ? 'selected' : '' ?>><?= e($us['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-sm">Guardar acceso</button>
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
  <form method="post" action="<?= url('/tareas/' . $tarea['id'] . '/adjuntos') ?>" style="display:flex;gap:8px;">
    <input type="url" name="url" placeholder="https://..." required style="flex:2;">
    <input type="text" name="descripcion" placeholder="Descripción (opcional)" style="flex:1;">
    <button type="submit" class="btn btn-secondary btn-sm">Agregar</button>
  </form>
</div>

<div class="section" style="margin-top:28px;">
  <h2>Comentarios</h2>
  <div class="thread">
    <?php foreach ($comentarios as $c): ?>
      <div class="msg">
        <div class="msg-meta"><?= e($c['usuario_nombre']) ?> · <?= (new DateTime($c['creado_en']))->format('d/m/Y H:i') ?></div>
        <div><?= nl2br(e($c['mensaje'])) ?></div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($comentarios)): ?><p class="text-muted">Sin comentarios todavía.</p><?php endif; ?>
  </div>
  <form method="post" action="<?= url('/tareas/' . $tarea['id'] . '/comentarios') ?>">
    <div class="form-group">
      <textarea name="mensaje" placeholder="Escribir un comentario..." required></textarea>
    </div>
    <button type="submit" class="btn btn-sm">Comentar</button>
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
