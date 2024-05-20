<?php
$host = 'localhost';
$dbname = 'alimentacao';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log the error to a file or a log system
    error_log("Error connecting to the database: " . $e->getMessage());

    // Show a generic error message to the user
    echo "Erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.";
    // You may want to redirect the user to an error page or perform some other action.
    exit();
}
?>
