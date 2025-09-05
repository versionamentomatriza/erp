<?php
$host = 'localhost';
$username = 'root';
$password = 'M@tr1z@$$2024-DB';
$database = 'matriza';

// Criar conexão
$mysqli = new mysqli($host, $username, $password, $database);

// Verificar conexão
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

echo 'Conexão bem-sucedida!';
?>
