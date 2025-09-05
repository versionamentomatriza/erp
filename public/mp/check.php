<?php 
include('config/config.php');
include('helpers/intermediador_pagamento/token.php');
header('Content-Type: application/json');
$return = [];
$sql_check_venda = mysqli_query($conexao,"SELECT * FROM status WHERE id_venda = '".$_GET['id_venda']."'") or die("Erro");
$resultado_check_venda = mysqli_fetch_assoc($sql_check_venda);
    
if($resultado_check_venda['status'] == 'aprovado'){
    $return = [
        'aprovado' => 'sim',
        'link' => 'https://' . $host . '/venda/?id_venda=' . $_GET['id_venda'] . '&item=' . $_GET['item']
    ];    
    echo json_encode($return);
}
if($resultado_check_venda['status'] == 'reprovado'){
    $return = [
        'reprovado' => 'sim',
        'link' => 'https://' . $host . '/venda/?id_venda=' . $_GET['id_venda'] . '&item=' . $_GET['item']
    ];    
    echo json_encode($return);
}