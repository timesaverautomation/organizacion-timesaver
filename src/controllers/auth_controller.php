<?php

function auth_login_form(): void
{
    if (usuario_actual()) {
        redirigir('/');
    }
    render('auth/login', ['error' => null]);
}

function auth_login_submit(): void
{
    $email = trim((string)input('email', ''));
    $password = (string)input('password', '');

    if ($email !== '' && $password !== '' && intentar_login($email, $password)) {
        redirigir('/');
    }
    render('auth/login', ['error' => 'Email o contraseña incorrectos.']);
}

function auth_logout(): void
{
    cerrar_sesion();
    redirigir('/login');
}

function auth_cambiar_password_form(): void
{
    $u = usuario_actual();
    if (!$u) {
        redirigir('/login');
    }
    render('auth/cambiar_password', ['error' => null, 'obligatorio' => !empty($u['debe_cambiar_password'])]);
}

function auth_cambiar_password_submit(): void
{
    $u = usuario_actual();
    if (!$u) {
        redirigir('/login');
    }
    $actual = (string)input('password_actual', '');
    $nueva = (string)input('password_nueva', '');
    $confirmar = (string)input('password_confirmar', '');
    $obligatorio = !empty($u['debe_cambiar_password']);

    $stmt = db()->prepare('SELECT password_hash FROM usuarios WHERE id = ?');
    $stmt->execute([$u['id']]);
    $hashActual = $stmt->fetchColumn();

    $error = null;
    if (!password_verify($actual, $hashActual)) {
        $error = 'La contraseña actual no es correcta.';
    } elseif (strlen($nueva) < 8) {
        $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
    } elseif ($nueva !== $confirmar) {
        $error = 'Las contraseñas nuevas no coinciden.';
    }

    if ($error) {
        render('auth/cambiar_password', ['error' => $error, 'obligatorio' => $obligatorio]);
        return;
    }

    cambiar_password_usuario_actual($nueva);
    redirigir('/');
}
