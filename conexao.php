<?php

declare(strict_types=1);

$configFile = __DIR__ . '/config.local.php';

if (is_file($configFile)) {
    $config = require $configFile;
} else {
    $config = [
        'host' => getenv('DB_HOST'),
        'port' => getenv('DB_PORT') ?: 3306,
        'database' => getenv('DB_NAME'),
        'username' => getenv('DB_USER'),
        'password' => getenv('DB_PASSWORD'),
    ];
}

foreach (['host', 'database', 'username', 'password'] as $requiredKey) {
    if (!array_key_exists($requiredKey, $config) || $config[$requiredKey] === false) {
        error_log('Configuração de banco de dados ausente: ' . $requiredKey);
        http_response_code(500);
        exit('Aplicação não configurada. Consulte o README.');
    }
}

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
    $config['host'],
    (int) ($config['port'] ?? 3306),
    $config['database']
);

try {
    $pdo = new PDO(
        $dsn,
        (string) $config['username'],
        (string) $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    error_log('Falha ao conectar ao banco de dados: ' . $exception->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados.');
}
