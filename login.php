<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (is_authenticated()) {
    header('Location: boas_vindas.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $now = time();
    $windowSeconds = 300;
    $maxAttempts = 5;
    $attempts = is_array($_SESSION['login_attempts'] ?? null)
        ? $_SESSION['login_attempts']
        : [];

    $attempts = array_values(array_filter(
        $attempts,
        static fn ($attempt): bool => is_int($attempt) && $attempt > $now - $windowSeconds
    ));

    if (count($attempts) >= $maxAttempts) {
        $error = 'Muitas tentativas. Aguarde alguns minutos.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['senha'] ?? '');

        require_once __DIR__ . '/conexao.php';

        $statement = $pdo->prepare(
            'SELECT id_cliente, nome_cliente, senha_hash, perfil, id_restaurante
             FROM clientes
             WHERE nome_cliente = :username
             LIMIT 1'
        );
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();

        if ($user && password_verify($password, $user['senha_hash'])) {
            session_regenerate_id(true);
            unset($_SESSION['login_attempts']);

            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = (int) $user['id_cliente'];
            $_SESSION['username'] = $user['nome_cliente'];
            $_SESSION['user_role'] = $user['perfil'];
            $_SESSION['restaurant_id'] = $user['id_restaurante'] !== null
                ? (int) $user['id_restaurante']
                : null;

            header('Location: boas_vindas.php');
            exit();
        }

        $attempts[] = $now;
        $_SESSION['login_attempts'] = $attempts;
        $error = 'Usuário ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar | Food in Time</title>
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            color: #172033;
            background: #eef3f8;
            font: 16px/1.5 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .card {
            width: min(100%, 420px);
            padding: 32px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 16px 40px rgba(26, 45, 78, .12);
        }
        h1 { margin: 0 0 8px; }
        p { color: #536178; }
        label { display: block; margin-top: 16px; font-weight: 600; }
        input {
            width: 100%;
            margin-top: 6px;
            padding: 12px;
            border: 1px solid #c9d3e0;
            border-radius: 8px;
            font: inherit;
        }
        button {
            width: 100%;
            margin-top: 24px;
            padding: 12px;
            border: 0;
            border-radius: 8px;
            color: #fff;
            background: #1769e0;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        .error {
            padding: 10px 12px;
            border-radius: 8px;
            color: #8a1c1c;
            background: #fde8e8;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Food in Time</h1>
        <p>Entre para reservar sua refeição.</p>

        <?php if ($error !== ''): ?>
            <p class="error" role="alert"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <label for="username">Usuário</label>
            <input
                type="text"
                id="username"
                name="username"
                autocomplete="username"
                required
            >

            <label for="senha">Senha</label>
            <input
                type="password"
                id="senha"
                name="senha"
                autocomplete="current-password"
                required
            >

            <button type="submit">Entrar</button>
        </form>
    </main>
</body>
</html>
