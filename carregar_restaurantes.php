<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_auth();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit();
}

require_csrf();
require_once __DIR__ . '/conexao.php';

$statement = $pdo->query(
    'SELECT id_restaurante, nome_restaurante
     FROM restaurantes
     ORDER BY nome_restaurante'
);

echo json_encode(
    ['restaurantes' => $statement->fetchAll()],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
