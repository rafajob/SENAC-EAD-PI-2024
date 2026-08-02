-- Schema para uma instalação nova do Food in Time.
-- Importe este arquivo em um banco vazio chamado "alimentacao".

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE restaurantes (
    id_restaurante INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome_restaurante VARCHAR(100) NOT NULL,
    PRIMARY KEY (id_restaurante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pratos (
    id_prato INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome_prato VARCHAR(100) NOT NULL,
    id_restaurante INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_prato),
    INDEX idx_pratos_restaurante (id_restaurante),
    CONSTRAINT fk_pratos_restaurante
        FOREIGN KEY (id_restaurante) REFERENCES restaurantes (id_restaurante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clientes (
    id_cliente INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome_cliente VARCHAR(100) NOT NULL,
    email_cliente VARCHAR(190) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('cliente', 'restaurante', 'admin') NOT NULL DEFAULT 'cliente',
    id_restaurante INT UNSIGNED NULL,
    PRIMARY KEY (id_cliente),
    UNIQUE KEY uq_clientes_email (email_cliente),
    INDEX idx_clientes_restaurante (id_restaurante),
    CONSTRAINT fk_clientes_restaurante
        FOREIGN KEY (id_restaurante) REFERENCES restaurantes (id_restaurante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pedidos (
    id_pedido INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_cliente INT UNSIGNED NOT NULL,
    id_restaurante INT UNSIGNED NOT NULL,
    hora_almoco TIME NULL,
    opcao_refeicao ENUM('consumo_local', 'retirada') NOT NULL,
    observacoes VARCHAR(500) NOT NULL DEFAULT '',
    status ENUM('novo', 'confirmado', 'em_preparo', 'pronto', 'concluido', 'cancelado')
        NOT NULL DEFAULT 'novo',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_pedido),
    INDEX idx_pedidos_cliente (id_cliente),
    INDEX idx_pedidos_restaurante_status (id_restaurante, status, criado_em),
    CONSTRAINT fk_pedidos_cliente
        FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
    CONSTRAINT fk_pedidos_restaurante
        FOREIGN KEY (id_restaurante) REFERENCES restaurantes (id_restaurante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pedido_itens (
    id_item INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_pedido INT UNSIGNED NOT NULL,
    id_prato INT UNSIGNED NOT NULL,
    quantidade SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (id_item),
    INDEX idx_itens_pedido (id_pedido),
    CONSTRAINT fk_itens_pedido
        FOREIGN KEY (id_pedido) REFERENCES pedidos (id_pedido) ON DELETE CASCADE,
    CONSTRAINT fk_itens_prato
        FOREIGN KEY (id_prato) REFERENCES pratos (id_prato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO restaurantes (nome_restaurante) VALUES
    ('Lagunas'),
    ('Pratas'),
    ('Bistecas');

INSERT INTO pratos (nome_prato, id_restaurante) VALUES
    ('Peixe Grelhado', 1),
    ('Sushi', 1),
    ('Bacalhau', 1),
    ('Bife a Cavalo', 2),
    ('Burger de Costela', 2),
    ('Massa Carbonara', 2),
    ('Filé com Fritas', 3),
    ('Costela na Brasa', 3),
    ('X-Picanha', 3);

-- Usuário demonstrativo: Rafael / TroqueEstaSenha123!
-- Troque ou remova este usuário antes de publicar a aplicação.
INSERT INTO clientes (
    nome_cliente,
    email_cliente,
    senha_hash,
    perfil,
    id_restaurante
) VALUES (
    'Rafael',
    'rafael@example.test',
    '$2b$12$t6Lnr6W4CSBJTQtFdbZqpeIQ8H43Av30BjnsBUmeFI81zVeuee4cO',
    'admin',
    NULL
);
