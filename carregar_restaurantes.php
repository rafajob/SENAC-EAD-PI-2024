<?php
include 'conexao.php';

try {
    $stmt = $pdo->query("SELECT * FROM restaurantes");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['nome_restaurante'] . '</option>';
    }
} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
} finally {
    $pdo = null;
}