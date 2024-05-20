<?php
session_start(); // Inicia a sessão

// Verifica se o usuário já está autenticado
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    header("Location: boas_vindas.php");
    exit();
}

// Se não estiver autenticado, exibe o formulário de login
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login Mobile</title>
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
    }

    input[type="text"],
    input[type="password"],
    input[type="submit"] {
        width: 100%;
        padding: 10px;
        margin: 5px 0;
        border: 1px solid #ccc;
        border-radius: 3px;
        box-sizing: border-box;
    }

    input[type="submit"] {
        background-color: #007BFF;
        color: #fff;
        border: none;
        cursor: pointer;
    }

    img {
        display: block;
        margin: 0 auto;
    }
    </style>

</head>

<body>
    <div class="container">
        <h1>Food in Time app</h1>

        <p>Olá, bem vindo(a) ao app de reserva de refeições</p>
        <img src="imagem/mesa.png" alt="casal sentado a mesa" />
        <h2>Login</h2>
        <form method="post" action="login.php">
            <label for="username">Usuário:</label>
            <input type="text" id="username" name="username" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>

            <input type="submit" value="Login">
        </form>
    </div>
</body>

</html>

<?php
// Verifica as credenciais quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $senha = $_POST["senha"];

    try {
        // Detalhes da conexão com o banco de dados MySQL
        $host = 'localhost';
        $dbname = 'alimentacao';
        $db_username = 'root';
        $db_password = 'root';

        // Conexão com o banco de dados MySQL
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consulta para verificar as credenciais
        $query = "SELECT * FROM clientes WHERE nome_cliente = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $senha == $row["senha"]) {
            // Define a sessão como autenticada e redireciona para a página de boas-vindas
            $_SESSION['authenticated'] = true;
            header("Location: boas_vindas.php");
            exit();
        } else {
            echo "Credenciais inválidas. Tente novamente.<br>";
        }
    } catch (PDOException $e) {
        echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
    } finally {
        // Fechamento da conexão com o banco de dados
        $pdo = null;
    }
}
?>