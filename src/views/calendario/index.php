<?php
$hoy = (new DateTime('today'))->format('Y-m-d');
$nombresMes = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
$diasSemana = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
$mesActualNombre = $nombresMes[(int)$inicioMes->format('n')] . ' ' . $inicioMes->format('Y');
$errorParam = input('error');
$errores = [
  'faltan_datos' => 'Completá título, fecha y horario de la reunión.',
  'horario_invalido' => 'La hora de fin debe ser posterior a la hora de inicio.',
];
?>

<div class="flex-between">
  <div>
    <h1>Calendario</h1>
    <p class="subtitle">Tareas con vencimiento y reuniones del equipo.</p>
  </div>
  <div class="toggle-group">
    <a href="<?= url('/calendario?mes=' . $mesParam . '&modo=grilla') ?>" class="<?= $modo === 'grilla' ? 'active' : '' ?>">Mes</a>
    <a href="<?= url('/calendario?mes=' . $mesParam . '&modo=lista') ?>" class="<?= $modo === 'lista' ? 'active' : '' ?>">Lista</a>
  </div>
</div>

<?php if ($errorParam && isset($errores[$errorParam])): ?>
  <div class="alert alert-error"><?= e($errores[$errorParam]) ?></div>
<?php endif; ?>

<div class="cal-nav">
  <a href="<?= url('/calendario?mes=' . $mesAnterior . '&modo=' . $modo) ?>" class="btn btn-secondary btn-sm">&larr; Anterior</a>
  <div class="cal-nav-title"><?= e($mesActualNombre) ?></div>
  <a href="<?= url('/calendario?mes=' . $mesSiguiente . '&modo=' . $modo) ?>" class="btn btn-secondary btn-sm">Siguiente &rarr;</a>
</div>

<?php if ($modo === 'grilla'): ?>

<div class="cal-grid">
  <?php foreach ($diasSemana as $d): ?><div class="cal-dow"><?= $d ?></div><?php endforeach; ?>

  <?php foreach ($semanas as $semana): foreach ($semana as $dia): ?>
    <?php
      $fechaStr = $dia->format('Y-m-d');
      $esDelMes = $dia->format('n') === $inicioMes->format('n');
      $esHoy = $fechaStr === $hoy;
      $eventos = $eventosPorDia[$fechaStr] ?? [];
    ?>
    <div class="cal-cell <?= $esDelMes ? '' : 'cal-cell-out' ?> <?= $esHoy ? 'cal-cell-today' : '' ?>">
      <div class="cal-cell-head">
        <span class="cal-daynum"><?= (int)$dia->format('j') ?></span>
        <button type="button" class="cal-add-btn" onclick="abrirModalReunion('<?= $fechaStr ?>')" title="Agregar reunión">+</button>
      </div>
      <div class="cal-events">
        <?php foreach (array_slice($eventos, 0, 4) as $ev): ?>
          <?php if ($ev['tipo'] === 'tarea'): $t = $ev['data']; ?>
            <a class="cal-ev cal-ev-tarea" href="<?= url('/tareas/' . $t['id']) ?>" title="<?= e($t['titulo']) ?>">📋 <?= e($t['titulo']) ?></a>
          <?php else: $r = $ev['data']; ?>
            <span class="cal-ev cal-ev-reunion" title="<?= e($r['titulo']) ?>">🗓 <?= substr($r['hora_inicio'], 0, 5) ?> <?= e($r['titulo']) ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
        <?php if (count($eventos) > 4): ?>
          <span class="cal-ev-mas">+<?= count($eventos) - 4 ?> más</span>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; endforeach; ?>
</div>

<?php else: ?>

<div class="stack">
  <?php
    $huboEventos = false;
    foreach ($semanas as $semana): foreach ($semana as $dia):
      $fechaStr = $dia->format('Y-m-d');
      if ($dia->format('n') !== $inicioMes->format('n')) continue;
      $eventos = $eventosPorDia[$fechaStr] ?? [];
      if (empty($eventos)) continue;
      $huboEventos = true;
  ?>
    <div class="section" style="margin-bottom:24px;">
      <h2 style="font-size:1rem;"><?= (int)$dia->format('j') ?> de <?= $nombresMes[(int)$dia->format('n')] ?><?= $fechaStr === $hoy ? ' · Hoy' : '' ?></h2>
      <div class="card-list">
        <?php foreach ($eventos as $ev): ?>
          <?php if ($ev['tipo'] === 'tarea'): $t = $ev['data']; $mostrarProyecto = true; ?>
            <?php require __DIR__ . '/../partials/tarea_card.php'; ?>
          <?php else: $r = $ev['data']; ?>
            <div class="card">
              <div class="item-row">
                <div>
                  <p class="item-title">🗓 <?= e($r['titulo']) ?></p>
                  <div class="item-meta">
                    <span class="badge badge-accent">Reunión</span>
                    <span><?= substr($r['hora_inicio'], 0, 5) ?>–<?= substr($r['hora_fin'], 0, 5) ?></span>
                    <span>Organiza: <?= e($r['creado_por_nombre']) ?></span>
                  </div>
                </div>
                <?php if ($esAdmin || (int)$r['creado_por'] === $usuarioActualId): ?>
                <form method="post" action="<?= url('/calendario/reuniones/' . $r['id'] . '/eliminar') ?>" onsubmit="return confirm('¿Eliminar esta reunión?');">
                  <button type="submit" class="btn btn-secondary btn-sm">Eliminar</button>
                </form>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; endforeach; ?>
  <?php if (!$huboEventos): ?>
    <div class="empty-state">No hay tareas ni reuniones este mes.</div>
  <?php endif; ?>
</div>

<?php endif; ?>

<!-- Modal nueva reunión -->
<div class="modal-overlay" id="modalReunion" onclick="if(event.target===this) cerrarModalReunion()">
  <div class="modal-box">
    <div class="flex-between" style="margin-bottom:16px;">
      <h2 style="margin:0;">Nueva reunión</h2>
      <button type="button" class="cal-modal-close" onclick="cerrarModalReunion()">&times;</button>
    </div>
    <form method="post" action="<?= url('/calendario/reuniones') ?>">
      <div class="form-group">
        <label for="reunion_titulo">Título</label>
        <input type="text" id="reunion_titulo" name="titulo" required autofocus>
      </div>
      <div class="form-group">
        <label for="reunion_fecha">Fecha</label>
        <input type="date" id="reunion_fecha" name="fecha" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="reunion_hora_inicio">Hora inicio</label>
          <input type="time" id="reunion_hora_inicio" name="hora_inicio" required>
        </div>
        <div class="form-group">
          <label for="reunion_hora_fin">Hora fin</label>
          <input type="time" id="reunion_hora_fin" name="hora_fin" required>
        </div>
      </div>
      <div class="form-group">
        <label for="reunion_participantes">Participantes</label>
        <select id="reunion_participantes" name="participantes[]" multiple size="5">
          <?php foreach ($usuarios as $us): ?>
            <option value="<?= $us['id'] ?>"><?= e($us['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn" style="width:100%;">Crear reunión</button>
    </form>
  </div>
</div>

<script>
function abrirModalReunion(fecha) {
  document.getElementById('reunion_fecha').value = fecha;
  document.getElementById('modalReunion').classList.add('modal-open');
}
function cerrarModalReunion() {
  document.getElementById('modalReunion').classList.remove('modal-open');
}
</script>
