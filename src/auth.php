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
    return $u;
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
        ];
        return true;
    }
    return false;
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
