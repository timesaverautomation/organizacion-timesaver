<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
</head>
<body>
<?php $u = usuario_actual(); ?>
<?php if ($u): ?>
<header class="topbar">
  <div class="container">
    <div class="brand">Time<span>Saver</span> · Tareas</div>
    <nav class="nav">
      <a href="<?= url('/') ?>" class="<?= ($_SERVER['REQUEST_URI'] === url('/')) ? 'active' : '' ?>">Home</a>
      <a href="<?= url('/proyectos') ?>">Proyectos</a>
      <a href="<?= url('/calendario') ?>">Calendario</a>
      <?php if (es_admin($u)): ?>
      <a href="<?= url('/admin/usuarios') ?>">Usuarios</a>
      <?php endif; ?>
      <?php $noLeidas = contar_notificaciones_no_leidas((int)$u['id']); ?>
      <div class="notif-wrap">
        <button type="button" class="notif-bell" onclick="toggleNotif(event)" aria-label="Notificaciones">
          🔔<?php if ($noLeidas > 0): ?><span class="notif-dot"><?= $noLeidas > 9 ? '9+' : $noLeidas ?></span><?php endif; ?>
        </button>
        <div class="notif-dropdown" id="notifDropdown">
          <div class="notif-head">
            <span>Notificaciones</span>
            <?php if ($noLeidas > 0): ?>
            <form method="post" action="<?= url('/notificaciones/leidas') ?>">
              <button type="submit" class="notif-marcar">Marcar leídas</button>
            </form>
            <?php endif; ?>
          </div>
          <?php $notifs = listar_notificaciones((int)$u['id']); ?>
          <?php if (empty($notifs)): ?>
            <div class="notif-empty">Sin notificaciones todavía.</div>
          <?php else: foreach ($notifs as $n): ?>
            <a href="<?= $n['link'] ? url($n['link']) : '#' ?>" class="notif-item <?= $n['leida'] ? '' : 'notif-item-unread' ?>">
              <div><?= e($n['mensaje']) ?></div>
              <div class="notif-fecha"><?= (new DateTime($n['creado_en']))->format('d/m/Y H:i') ?></div>
            </a>
          <?php endforeach; endif; ?>
        </div>
      </div>
      <a href="<?= url('/perfil') ?>" class="user-tag"><?= e($u['nombre']) ?></a>
      <a href="<?= url('/logout') ?>">Salir</a>
    </nav>
  </div>
</header>
<script>
function toggleNotif(ev) {
  ev.stopPropagation();
  document.getElementById('notifDropdown').classList.toggle('notif-open');
}
document.addEventListener('click', function (ev) {
  var dd = document.getElementById('notifDropdown');
  if (dd && dd.classList.contains('notif-open') && !dd.contains(ev.target)) {
    dd.classList.remove('notif-open');
  }
});
</script>
<?php endif; ?>
<main class="container">
