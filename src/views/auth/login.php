<div class="auth-wrap">
  <div class="card">
    <h1>Time<span style="color:var(--accent)">Saver</span></h1>
    <p class="subtitle">Organización de tareas y decisiones</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= url('/login') ?>">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus>
      </div>
      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn" style="width:100%">Ingresar</button>
    </form>
  </div>
</div>
