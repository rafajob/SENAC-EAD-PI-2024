<?php
session_start(); // Inicia a sessão

// Verifica se o usuário já está autenticado
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    header("Location: boas_vindas.php");
    exit();
	
}else{header("Location: login.php");}

// Se não estiver autenticado, exibe o formulário de login

?>
