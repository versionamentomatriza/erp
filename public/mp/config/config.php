<?php
// Conexao com banco de dados
$servername     = "localhost";
$username       = "root";
$password       = "M@tr1z@$$2025_BR_DB#3006";
$db_name        = "pagamentos";
$db_name2       = "erp";

$conexao = mysqli_connect($servername, $username, $password, $db_name);
$conexao2 = mysqli_connect($servername, $username, $password, $db_name2);
 
$host               = 'matriza.net/mp';
$hostnotification   = 'matriza.net/mp';
?>