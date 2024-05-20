<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está autenticado
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>Boas-vindas</title>
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

    h2 {
        font-size: 1.5em;
        /* Ajuste o tamanho da fonte conforme necessário */
    }

    p {
        margin-bottom: 10px;
    }

    a {
        display: inline-block;
        /* Torna o link um bloco para aplicar largura e margens */
        width: 50%;
        padding: 10px;
        margin-bottom: 10px;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background-color: #007BFF;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }

    a:hover {
        background-color: #0056b3;
        /* Altere a cor de destaque conforme necessário */
    }

    img {
        display: block;
        margin: 0 auto;
        max-width: 100%;
        height: auto;
    }
    </style>

</head>

<body>
    <div class="container">
        <h2>Bem-vindo!</h2>
        <img src="imagem/garcom.jpg" alt="garçom com uma bandeja" />
        <a href="restaurantes.html">Fazer pedido</a>
        <a href="logout.php">Sair</a>
    </div>
</body>

</html>