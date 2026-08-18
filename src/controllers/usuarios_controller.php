<?php

function usuarios_index(): void
{
    requerir_admin();
    render('usuarios/index', ['usuarios' => listar_usuarios_admin()]);
}

function usuarios_nueva_form(): void
{
    requerir_admin();
    render('usuarios/nuevo', ['error' => null]);
}

function usuarios_crear(): void
{
    requerir_admin();
    $nombre = trim((string)input('nombre', ''));
    $email = trim((string)input('email', ''));
    $password = (string)input('password', '');
    $rol = in_array(input('rol'), ['admin', 'vendedor'], true) ? input('rol') : 'vendedor';

    $error = null;
    if ($nombre === '' || $email === '' || $password === '') {
        $error = 'Nombre, email y contraseña son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif (obtener_usuario_por_email($email)) {
        $error = 'Ya existe un usuario con ese email.';
    }

    if ($error) {
        render('usuarios/nuevo', ['error' => $error]);
        return;
    }

    crear_usuario($nombre, $email, $password, $rol);
    redirigir('/admin/usuarios');
}

function usuarios_actualizar_estado(int $id): void
{
    requerir_admin();
    $activo = input('activo') === '1';
    actualizar_estado_usuario($id, $activo);
    redirigir('/admin/usuarios');
}
