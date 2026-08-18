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
      <?php if (es_admin($u)): ?>
      <a href="<?= url('/admin/usuarios') ?>">Usuarios</a>
      <?php endif; ?>
      <span class="user-tag"><?= e($u['nombre']) ?></span>
      <a href="<?= url('/logout') ?>">Salir</a>
    </nav>
  </div>
</header>
<?php endif; ?>
<main class="container">
