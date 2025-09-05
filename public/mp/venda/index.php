<?php include('../config/config.php'); ?>
<?php include('../config/produtos.php'); ?>
<?php
$sql_check_venda = mysqli_query($conexao,"SELECT * FROM status WHERE id_venda = '".$_GET['id_venda']."'") or die("Erro");
$resultado_check_venda = mysqli_fetch_assoc($sql_check_venda);
?>
<?php 
    if($resultado_check_venda['status'] == 'aprovado'){
        include('aprovado.php');
    }
    if($resultado_check_venda['status'] == 'reprovado'){
        include('reprovado.php');
    }
?>