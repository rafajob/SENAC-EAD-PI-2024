<?php

declare(strict_types=1);

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

start_secure_session();

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function require_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Solicitação inválida.');
    }
}

function is_authenticated(): bool
{
    return ($_SESSION['authenticated'] ?? false) === true;
}

function require_auth(): void
{
    if (!is_authenticated()) {
        header('Location: login.php');
        exit();
    }
}

function require_role(array $roles): void
{
    require_auth();

    if (!in_array($_SESSION['user_role'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('Acesso não autorizado.');
    }
}
