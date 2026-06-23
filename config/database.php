<?php

$host = "localhost";
$user = "root";
$database = "db-atendelab";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo "Conexão bem sucedida!";
} catch (PDOException $e) {
    // echo "Erro ao conectar com o banco de dados: " . $e->getMessage();
    die('Erro ao conectar com o banco de dados: ' . $e->getMessage());
}
