<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <title>Confirmação de Pedido</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f1f1f1;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
    }

    h1 {
        font-size: 1.8em;
    }

    form {
        display: inline-block;
        text-align: left;
        max-width: 400px;
        margin: 0 auto;
    }

    label {
        display: block;
        margin-bottom: 10px;
    }

    input,
    select,
    textarea {
        width: 100%;
        padding: 10px;
        box-sizing: border-box;
        margin-bottom: 15px;
    }

    button {
        padding: 10px 20px;
        background-color: #007BFF;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
    }

    ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        text-align: center;
    }

    @media only screen and (max-width: 600px) {
        form {
            max-width: 100%;
        }

        button {
            font-size: 1em;
        }
    }

    .container {
        width: 100%;
        max-width: 300px;
        margin: 20px;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 5px;
        box-shadow: 0 2px 5px #ccc;
        text-align: center;
        /* Centralizar o conteúdo */
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>Confirmação de Pedido</h1>

        <?php
        include 'conexao.php';

        // Verifica se todos os parâmetros esperados estão presentes
        if (isset($_GET['nomeUsuario'], $_GET['horaAlmoco'], $_GET['opcaoRefeicao'], $_GET['nomeRestaurante'], $_GET['prato'])) {
            $nomeUsuario = htmlspecialchars($_GET['nomeUsuario']);
            $horaAlmoco = htmlspecialchars($_GET['horaAlmoco']);
            $opcaoRefeicao = htmlspecialchars($_GET['opcaoRefeicao']);
            $observacoes = isset($_GET['observacoes']) ? htmlspecialchars($_GET['observacoes']) : '';
            $nomeRestaurante = htmlspecialchars($_GET['nomeRestaurante']);
            $pratosId = array_map('htmlspecialchars', $_GET['prato']);

            // Mostra mensagem de confirmação
            echo "<p>Olá, <strong>{$nomeUsuario}</strong>! Seu pedido no restaurante <strong>{$nomeRestaurante}</strong> foi solicitado.</p>";
            echo "<p><strong>Detalhes do pedido:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Restaurante: </strong>{$nomeRestaurante}</li>";
            echo "<br>";
            echo "<li><strong>Pratos:</strong>";
            echo "<br>";
            foreach ($pratosId as $pratoId) {
                echo "<br>";
                echo "<ul><li>{$pratoId}</li></ul>";
            }
            echo "<br>";
            echo "</li>";
            echo "<li><strong>Hora do Almoço:</strong> {$horaAlmoco}</li>";
            echo "<br>";
            echo "<li><strong>Opção de Refeição:</strong> {$opcaoRefeicao}</li>";
            echo "<br>";
            echo "<li><strong>Observações:</strong> {$observacoes}</li>";
            echo "<br>";
            echo "</ul>";
        } else {
            // Se os parâmetros não estiverem presentes, exibe o formulário
            if (isset($_GET['restaurante']) && isset($_GET['prato'])) {
                $restauranteId = $_GET['restaurante'];
                $pratosId = $_GET['prato'];

                try {
                    $stmt = $pdo->prepare("SELECT nome_restaurante FROM restaurantes WHERE id = :restauranteId");
                    $stmt->bindParam(':restauranteId', $restauranteId, PDO::PARAM_INT);
                    $stmt->execute();

                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $nomeRestaurante = ($row) ? $row['nome_restaurante'] : "Restaurante não encontrado";
        ?>

        <form method="get" action="confirmacao_pedido.php">
            <label for="nomeUsuario">Seu Nome:</label>
            <input type="text" id="nomeUsuario" name="nomeUsuario" required>

            <label for="horaAlmoco">Hora do Almoço:</label>
            <input type="time" id="horaAlmoco" name="horaAlmoco">

            <label for="opcaoRefeicao">Opção de Refeição:</label>
            <select id="opcaoRefeicao" name="opcaoRefeicao">
                <option value="consumo Local">Consumo no Local</option>
                <option value="retirada">Retirada</option>
            </select>

            <label for="observacoes">Observações:</label>
            <textarea id="observacoes" name="observacoes" rows="4"></textarea>
            <input type="hidden" id="nomeRestaurante" name="nomeRestaurante" value="<?php echo $nomeRestaurante; ?>">
            <?php
                        foreach ($pratosId as $pratoId) {
                            echo "<input type='hidden' name='prato[]' value='" . htmlspecialchars($pratoId) . "'>";
                        }
                        ?>
            <button type="submit">Confirmar Pedido</button>
        </form>
    </div>
    <?php
                } catch (PDOException $e) {
                    error_log("Error querying the database: " . $e->getMessage());
                    echo "Erro ao consultar o banco de dados. Por favor, tente novamente mais tarde.";
                    exit();
                }
            } else {
                echo "<p>Erro: Parâmetros de pedido ausentes.</p>";
            }
        }
?>
    <script>
    // Função para enviar os dados via AJAX
    function enviarDados(nomeUsuario, nomeRestaurante, pratosId, horaAlmoco, opcaoRefeicao, observacoes) {
        var url = "processar_pedido.php";
        var dadosPedido = {
            nomeUsuario: nomeUsuario,
            nomeRestaurante: nomeRestaurante,
            pratosId: pratosId,
            horaAlmoco: horaAlmoco,
            opcaoRefeicao: opcaoRefeicao,
            observacoes: observacoes,
        };

        // Enviar dados via AJAX
        $.post(url, dadosPedido, function(resposta) {
                console.log("Resposta do servidor: " + resposta);
                alert("Pedido processado com sucesso!");

            })
            .fail(function(erro) {
                console.error("Erro ao processar o pedido: " + erro.statusText);
            });

        console.log("Enviando dados...");
        return true;
    }


    // Associar a função enviarDados ao evento de envio do formulário
    $('form').submit(function() {
        var nomeUsuario = $('#nomeUsuario').val();
        var nomeRestaurante = '<?php echo isset($nomeRestaurante) ? $nomeRestaurante : ""; ?>';
        var pratosId = <?php echo isset($pratosId) ? json_encode($pratosId) : "[]"; ?>;
        var horaAlmoco = $('#horaAlmoco').val();
        var opcaoRefeicao = $('#opcaoRefeicao').val();
        var observacoes = $('#observacoes').val();

        return enviarDados(nomeUsuario, nomeRestaurante, pratosId, horaAlmoco, opcaoRefeicao, observacoes);
    });
    </script>
</body>

</html>