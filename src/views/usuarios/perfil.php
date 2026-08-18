<h1>Mi perfil</h1>
<p class="subtitle"><?= e($usuario['nombre']) ?> · <?= e($usuario['email']) ?></p>

<div class="card form-card">
  <form method="post" action="<?= url('/perfil/preferencias') ?>">
    <div class="form-group">
      <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
        <input type="checkbox" name="recibir_emails" value="1" style="width:auto;" <?= !empty($usuario['recibir_emails']) ? 'checked' : '' ?>>
        Recibir notificaciones por email además de la campanita
      </label>
    </div>
    <button type="submit" class="btn">Guardar</button>
  </form>
</div>

<p class="text-muted" style="font-size:0.85rem;margin-top:20px;">
  Para cambiar tu contraseña, andá a <a href="<?= url('/cambiar-password') ?>">Cambiar contraseña</a>.
</p>
