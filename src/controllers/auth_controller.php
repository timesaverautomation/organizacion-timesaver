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
