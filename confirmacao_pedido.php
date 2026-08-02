<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_auth();
require_once __DIR__ . '/conexao.php';

$order = null;
$orderItems = [];
$restaurant = null;
$dishes = [];
$restaurantId = null;
$dishIds = [];
$error = '';

$orderId = filter_input(INPUT_GET, 'pedido', FILTER_VALIDATE_INT);

if ($orderId) {
    $sql = '
        SELECT
            p.id_pedido,
            p.hora_almoco,
            p.opcao_refeicao,
            p.observacoes,
            p.status,
            p.criado_em,
            r.nome_restaurante
        FROM pedidos p
        INNER JOIN restaurantes r
            ON r.id_restaurante = p.id_restaurante
        WHERE p.id_pedido = :order_id
    ';
    $parameters = ['order_id' => $orderId];
    $role = (string) ($_SESSION['user_role'] ?? 'cliente');

    if ($role === 'cliente') {
        $sql .= ' AND p.id_cliente = :user_id';
        $parameters['user_id'] = (int) $_SESSION['user_id'];
    } elseif ($role === 'restaurante') {
        $sql .= ' AND p.id_restaurante = :restaurant_id';
        $parameters['restaurant_id'] = (int) ($_SESSION['restaurant_id'] ?? 0);
    }

    $statement = $pdo->prepare($sql);
    $statement->execute($parameters);
    $order = $statement->fetch();

    if (!$order) {
        http_response_code(404);
        exit('Pedido não encontrado.');
    }

    $itemStatement = $pdo->prepare(
        'SELECT pr.nome_prato, pi.quantidade
         FROM pedido_itens pi
         INNER JOIN pratos pr ON pr.id_prato = pi.id_prato
         WHERE pi.id_pedido = :order_id
         ORDER BY pi.id_item'
    );
    $itemStatement->execute(['order_id' => $orderId]);
    $orderItems = $itemStatement->fetchAll();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $restaurantId = filter_input(INPUT_POST, 'restaurant_id', FILTER_VALIDATE_INT);
    $rawDishIds = $_POST['prato_ids'] ?? [];

    if (!$restaurantId || !is_array($rawDishIds) || count($rawDishIds) < 1 || count($rawDishIds) > 20) {
        http_response_code(422);
        exit('Seleção de pedido inválida.');
    }

    foreach ($rawDishIds as $rawDishId) {
        $dishId = filter_var(
            $rawDishId,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($dishId === false) {
            http_response_code(422);
            exit('Um dos pratos selecionados é inválido.');
        }

        $dishIds[] = (int) $dishId;
    }

    $restaurantStatement = $pdo->prepare(
        'SELECT id_restaurante, nome_restaurante
         FROM restaurantes
         WHERE id_restaurante = :restaurant_id'
    );
    $restaurantStatement->execute(['restaurant_id' => $restaurantId]);
    $restaurant = $restaurantStatement->fetch();

    if (!$restaurant) {
        http_response_code(404);
        exit('Restaurante não encontrado.');
    }

    $uniqueDishIds = array_values(array_unique($dishIds));
    $placeholders = implode(',', array_fill(0, count($uniqueDishIds), '?'));
    $dishStatement = $pdo->prepare(
        "SELECT id_prato, nome_prato
         FROM pratos
         WHERE id_restaurante = ? AND id_prato IN ($placeholders)"
    );
    $dishStatement->execute(array_merge([$restaurantId], $uniqueDishIds));

    $dishMap = [];

    foreach ($dishStatement->fetchAll() as $dish) {
        $dishMap[(int) $dish['id_prato']] = $dish;
    }

    if (count($dishMap) !== count($uniqueDishIds)) {
        http_response_code(422);
        exit('Há pratos que não pertencem ao restaurante selecionado.');
    }

    foreach ($dishIds as $dishId) {
        $dishes[] = $dishMap[$dishId];
    }

    if (isset($_POST['confirmar'])) {
        $mealOption = (string) ($_POST['opcao_refeicao'] ?? '');
        $lunchTime = trim((string) ($_POST['hora_almoco'] ?? ''));
        $notes = trim((string) ($_POST['observacoes'] ?? ''));

        if (!in_array($mealOption, ['consumo_local', 'retirada'], true)) {
            $error = 'Escolha uma opção de refeição válida.';
        } elseif ($lunchTime !== '' && !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $lunchTime)) {
            $error = 'Informe um horário válido.';
        } elseif (strlen($notes) > 500) {
            $error = 'As observações devem ter no máximo 500 caracteres.';
        }

        if ($error === '') {
            try {
                $pdo->beginTransaction();

                $orderStatement = $pdo->prepare(
                    'INSERT INTO pedidos (
                        id_cliente,
                        id_restaurante,
                        hora_almoco,
                        opcao_refeicao,
                        observacoes
                    ) VALUES (
                        :client_id,
                        :restaurant_id,
                        :lunch_time,
                        :meal_option,
                        :notes
                    )'
                );
                $orderStatement->execute([
                    'client_id' => (int) $_SESSION['user_id'],
                    'restaurant_id' => $restaurantId,
                    'lunch_time' => $lunchTime !== '' ? $lunchTime : null,
                    'meal_option' => $mealOption,
                    'notes' => $notes,
                ]);

                $newOrderId = (int) $pdo->lastInsertId();
                $itemStatement = $pdo->prepare(
                    'INSERT INTO pedido_itens (id_pedido, id_prato, quantidade)
                     VALUES (:order_id, :dish_id, 1)'
                );

                foreach ($dishIds as $dishId) {
                    $itemStatement->execute([
                        'order_id' => $newOrderId,
                        'dish_id' => $dishId,
                    ]);
                }

                $pdo->commit();

                header('Location: confirmacao_pedido.php?pedido=' . $newOrderId);
                exit();
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log('Falha ao criar pedido: ' . $exception->getMessage());
                $error = 'Não foi possível registrar o pedido. Tente novamente.';
            }
        }
    }
} else {
    header('Location: restaurantes.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação | Food in Time</title>
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            color: #172033;
            background: #eef3f8;
            font: 16px/1.5 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        main {
            width: min(100%, 680px);
            margin: 40px auto;
            padding: 32px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 16px 40px rgba(26, 45, 78, .12);
        }
        label { display: block; margin-top: 16px; font-weight: 600; }
        input, select, textarea, button {
            width: 100%;
            margin-top: 6px;
            padding: 12px;
            border-radius: 8px;
            font: inherit;
        }
        input, select, textarea { border: 1px solid #c9d3e0; }
        button {
            margin-top: 24px;
            border: 0;
            color: #fff;
            background: #1769e0;
            font-weight: 700;
            cursor: pointer;
        }
        .error {
            padding: 10px 12px;
            border-radius: 8px;
            color: #8a1c1c;
            background: #fde8e8;
        }
        .success {
            padding: 12px;
            border-radius: 8px;
            color: #155b34;
            background: #e2f5e9;
        }
        a { display: inline-block; margin-top: 20px; color: #1769e0; }
    </style>
</head>
<body>
    <main>
        <?php if ($order): ?>
            <h1>Pedido #<?= e($order['id_pedido']) ?> registrado</h1>
            <p class="success">O restaurante já pode visualizar este pedido.</p>

            <dl>
                <dt><strong>Restaurante</strong></dt>
                <dd><?= e($order['nome_restaurante']) ?></dd>

                <dt><strong>Pratos</strong></dt>
                <dd>
                    <ul>
                        <?php foreach ($orderItems as $item): ?>
                            <li><?= e($item['quantidade']) ?> × <?= e($item['nome_prato']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </dd>

                <dt><strong>Opção</strong></dt>
                <dd><?= $order['opcao_refeicao'] === 'retirada' ? 'Retirada' : 'Consumo no local' ?></dd>

                <dt><strong>Horário</strong></dt>
                <dd><?= e($order['hora_almoco'] ?: 'Não informado') ?></dd>

                <dt><strong>Status</strong></dt>
                <dd><?= e($order['status']) ?></dd>

                <dt><strong>Observações</strong></dt>
                <dd><?= e($order['observacoes'] ?: 'Nenhuma') ?></dd>
            </dl>

            <a href="restaurantes.php">Fazer outro pedido</a>
            <a href="boas_vindas.php">Voltar ao início</a>
        <?php else: ?>
            <h1>Revise seu pedido</h1>
            <p><strong>Restaurante:</strong> <?= e($restaurant['nome_restaurante']) ?></p>

            <ul>
                <?php foreach ($dishes as $dish): ?>
                    <li><?= e($dish['nome_prato']) ?></li>
                <?php endforeach; ?>
            </ul>

            <?php if ($error !== ''): ?>
                <p class="error" role="alert"><?= e($error) ?></p>
            <?php endif; ?>

            <form method="post" action="confirmacao_pedido.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="restaurant_id" value="<?= e($restaurantId) ?>">
                <input type="hidden" name="confirmar" value="1">

                <?php foreach ($dishIds as $dishId): ?>
                    <input type="hidden" name="prato_ids[]" value="<?= e($dishId) ?>">
                <?php endforeach; ?>

                <label for="hora_almoco">Horário desejado</label>
                <input
                    type="time"
                    id="hora_almoco"
                    name="hora_almoco"
                    value="<?= e($_POST['hora_almoco'] ?? '') ?>"
                >

                <label for="opcao_refeicao">Opção de refeição</label>
                <select id="opcao_refeicao" name="opcao_refeicao" required>
                    <option value="consumo_local">Consumo no local</option>
                    <option
                        value="retirada"
                        <?= ($_POST['opcao_refeicao'] ?? '') === 'retirada' ? 'selected' : '' ?>
                    >Retirada</option>
                </select>

                <label for="observacoes">Observações</label>
                <textarea
                    id="observacoes"
                    name="observacoes"
                    maxlength="500"
                    rows="4"
                ><?= e($_POST['observacoes'] ?? '') ?></textarea>

                <button type="submit">Confirmar pedido</button>
            </form>

            <a href="pratos.php?restaurante_id=<?= e($restaurantId) ?>">Alterar pratos</a>
        <?php endif; ?>
    </main>
</body>
</html>
