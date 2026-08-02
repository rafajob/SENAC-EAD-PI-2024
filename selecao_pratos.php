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

$restaurantId = filter_input(INPUT_POST, 'restaurante_id', FILTER_VALIDATE_INT);

if (!$restaurantId) {
    http_response_code(422);
    echo json_encode(['error' => 'Restaurante inválido.']);
    exit();
}

require_once __DIR__ . '/conexao.php';

$statement = $pdo->prepare(
    'SELECT id_prato, nome_prato
     FROM pratos
     WHERE id_restaurante = :restaurant_id
     ORDER BY nome_prato'
);
$statement->execute(['restaurant_id' => $restaurantId]);

echo json_encode(
    ['pratos' => $statement->fetchAll()],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
