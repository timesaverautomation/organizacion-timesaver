<div class="auth-wrap">
  <div class="card">
    <h1>Cambiar contraseña</h1>
    <p class="subtitle">
      <?= $obligatorio ? 'Por seguridad, tenés que cambiar tu contraseña antes de seguir.' : 'Actualizá tu contraseña.' ?>
    </p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= url('/cambiar-password') ?>">
      <div class="form-group">
        <label for="password_actual">Contraseña actual</label>
        <input type="password" id="password_actual" name="password_actual" required autofocus>
      </div>
      <div class="form-group">
        <label for="password_nueva">Contraseña nueva</label>
        <input type="password" id="password_nueva" name="password_nueva" minlength="8" required>
      </div>
      <div class="form-group">
        <label for="password_confirmar">Confirmar contraseña nueva</label>
        <input type="password" id="password_confirmar" name="password_confirmar" minlength="8" required>
      </div>
      <button type="submit" class="btn" style="width:100%">Guardar</button>
    </form>
  </div>
</div>
