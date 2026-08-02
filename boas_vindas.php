<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_auth();

$role = (string) ($_SESSION['user_role'] ?? 'cliente');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início | Food in Time</title>
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
            width: min(100%, 520px);
            padding: 32px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 16px 40px rgba(26, 45, 78, .12);
        }
        .actions { display: grid; gap: 12px; margin-top: 24px; }
        a, button {
            display: block;
            width: 100%;
            padding: 12px;
            border: 0;
            border-radius: 8px;
            color: #fff;
            background: #1769e0;
            text-align: center;
            text-decoration: none;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        button { background: #526176; }
        form { margin: 0; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Olá, <?= e($_SESSION['username'] ?? 'cliente') ?>!</h1>
        <p>Escolha uma ação para continuar.</p>

        <div class="actions">
            <a href="restaurantes.php">Fazer pedido</a>

            <?php if (in_array($role, ['admin', 'restaurante'], true)): ?>
                <a href="processar_pedido.php">Acompanhar pedidos</a>
            <?php endif; ?>

            <form method="post" action="logout.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit">Sair</button>
            </form>
        </div>
    </main>
</body>
</html>
