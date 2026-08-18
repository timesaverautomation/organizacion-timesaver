<?php
require_once __DIR__ . '/db.php';

function iniciar_sesion_segura(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
        session_start();
    }
}

function usuario_actual(): ?array
{
    iniciar_sesion_segura();
    return $_SESSION['usuario'] ?? null;
}

function requerir_login(): array
{
    $u = usuario_actual();
    if (!$u) {
        redirigir('/login');
    }
    if (!empty($u['debe_cambiar_password']) && ($_SERVER['REQUEST_URI'] ?? '') !== url('/cambiar-password')) {
        redirigir('/cambiar-password');
    }
    return $u;
}

function requerir_admin(): array
{
    $u = requerir_login();
    if ($u['rol'] !== 'admin') {
        http_response_code(403);
        echo '403 — No tenés permiso para ver esta página.';
        exit;
    }
    return $u;
}

function es_admin(?array $usuario = null): bool
{
    $usuario = $usuario ?? usuario_actual();
    return $usuario && $usuario['rol'] === 'admin';
}

function intentar_login(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM usuarios WHERE email = ? AND activo = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        iniciar_sesion_segura();
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'rol' => $user['rol'],
            'debe_cambiar_password' => (bool)$user['debe_cambiar_password'],
        ];
        return true;
    }
    return false;
}

function cambiar_password_usuario_actual(string $passwordNueva): void
{
    $u = usuario_actual();
    if (!$u) {
        return;
    }
    $hash = password_hash($passwordNueva, PASSWORD_DEFAULT);
    $stmt = db()->prepare('UPDATE usuarios SET password_hash = ?, debe_cambiar_password = 0 WHERE id = ?');
    $stmt->execute([$hash, $u['id']]);
    $_SESSION['usuario']['debe_cambiar_password'] = false;
}

function cerrar_sesion(): void
{
    iniciar_sesion_segura();
    $_SESSION = [];
    session_destroy();
}

function redirigir(string $ruta): void
{
    header('Location: ' . BASE_PATH . $ruta);
    exit;
}
