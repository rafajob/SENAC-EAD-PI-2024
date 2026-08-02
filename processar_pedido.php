<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_role(['admin', 'restaurante']);
require_once __DIR__ . '/conexao.php';

$sql = '
    SELECT
        p.id_pedido,
        c.nome_cliente,
        r.nome_restaurante,
        p.hora_almoco,
        p.opcao_refeicao,
        p.observacoes,
        p.status,
        p.criado_em,
        GROUP_CONCAT(
            CONCAT(pi.quantidade, " × ", pr.nome_prato)
            ORDER BY pi.id_item
            SEPARATOR ", "
        ) AS pratos
    FROM pedidos p
    INNER JOIN clientes c ON c.id_cliente = p.id_cliente
    INNER JOIN restaurantes r ON r.id_restaurante = p.id_restaurante
    INNER JOIN pedido_itens pi ON pi.id_pedido = p.id_pedido
    INNER JOIN pratos pr ON pr.id_prato = pi.id_prato
';
$parameters = [];

if (($_SESSION['user_role'] ?? '') === 'restaurante') {
    $sql .= ' WHERE p.id_restaurante = :restaurant_id';
    $parameters['restaurant_id'] = (int) ($_SESSION['restaurant_id'] ?? 0);
}

$sql .= '
    GROUP BY
        p.id_pedido,
        c.nome_cliente,
        r.nome_restaurante,
        p.hora_almoco,
        p.opcao_refeicao,
        p.observacoes,
        p.status,
        p.criado_em
    ORDER BY
        FIELD(p.status, "novo", "confirmado", "em_preparo", "pronto", "concluido", "cancelado"),
        p.criado_em DESC
    LIMIT 100
';

$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$orders = $statement->fetchAll();

$statusLabels = [
    'novo' => 'Novo',
    'confirmado' => 'Confirmado',
    'em_preparo' => 'Em preparo',
    'pronto' => 'Pronto',
    'concluido' => 'Concluído',
    'cancelado' => 'Cancelado',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="15">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos | Food in Time</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            color: #172033;
            background: #eef3f8;
            font: 15px/1.5 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        main {
            width: min(100%, 1180px);
            margin: 24px auto;
            padding: 28px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 16px 40px rgba(26, 45, 78, .12);
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #e3e8ef; text-align: left; vertical-align: top; }
        th { background: #f7f9fc; }
        .status { font-weight: 700; }
        a { color: #1769e0; }
    </style>
</head>
<body>
    <main>
        <h1>Pedidos recebidos</h1>
        <p>A página é atualizada automaticamente a cada 15 segundos.</p>

        <?php if (!$orders): ?>
            <p>Nenhum pedido encontrado.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Restaurante</th>
                            <th>Pratos</th>
                            <th>Horário</th>
                            <th>Opção</th>
                            <th>Status</th>
                            <th>Observações</th>
                            <th>Criado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= e($order['id_pedido']) ?></td>
                                <td><?= e($order['nome_cliente']) ?></td>
                                <td><?= e($order['nome_restaurante']) ?></td>
                                <td><?= e($order['pratos']) ?></td>
                                <td><?= e($order['hora_almoco'] ?: '—') ?></td>
                                <td><?= $order['opcao_refeicao'] === 'retirada' ? 'Retirada' : 'Local' ?></td>
                                <td class="status"><?= e($statusLabels[$order['status']] ?? $order['status']) ?></td>
                                <td><?= e($order['observacoes'] ?: '—') ?></td>
                                <td><?= e($order['criado_em']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <p><a href="boas_vindas.php">Voltar ao início</a></p>
    </main>
</body>
</html>
