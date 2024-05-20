<?php
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['restauranteId'])) {
    $restauranteId = $_POST['restauranteId'];

    try {
        $stmt = $pdo->prepare('SELECT nome_prato FROM pratos WHERE id_restaurante = :id_restaurante');
        $stmt->bindParam(':id_restaurante', $restauranteId, PDO::PARAM_INT);
        $stmt->execute();

        $pratos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo '<label for="prato">Escolha um prato:</label>';
        echo '<select id="prato" name="prato">';
        foreach ($pratos as $prato) {
            echo '<option value="' . $prato . '">' . $prato . '</option>';
        }
        echo '</select>';
    } catch (PDOException $e) {
        echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
    } finally {
        $pdo = null;
    }
}
?>
