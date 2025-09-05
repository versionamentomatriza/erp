<?php include('config/config.php'); ?>
<?php include('helpers/intermediador_pagamento/token.php'); ?>
<?php
$sql_check_venda = mysqli_query($conexao,"SELECT * FROM status WHERE id_venda = '".$_GET['id_venda']."'") or die("Erro");
$resultado_check_venda = mysqli_fetch_assoc($sql_check_venda);
    
$collector_id = $resultado_check_venda['codigo'];
 
$curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/'.$collector_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'accept: application/json',
        'content-type: application/json',
        'Authorization: Bearer '.$token
    ),
    ));
    $response = curl_exec($curl);
    $resultado = json_decode($response);
    
curl_close($curl);
//var_dump($resultado);
//Pagamento Aprovado
sleep(3);
if($resultado->status == 'approved'){
    $query = "UPDATE status SET status = 'aprovado' WHERE codigo = '".$collector_id."'";
    mysqli_query($conexao, $query);
    
    $query = "SELECT * FROM status WHERE processado = 0 AND status = 'aprovado' AND codigo = '".$collector_id."'";
    $result = mysqli_query($conexao, $query);
    
    if(mysqli_num_rows($result) > 0){
        // Obtém informações da empresa e plano
        $row = mysqli_fetch_assoc($result);
        $id_empresa = $row['id_empresa'];
        $item = $row['item'];
		
        $query2 = "SELECT * FROM plano_empresas WHERE empresa_id = $id_empresa";
        $result2 = mysqli_query($conexao2, $query2);
		
		
		if($item == 2){
			$intervalo_dias = 30;
		}
		if($item == 3){
			$intervalo_dias = 30;
		}
		if($item == 4){
			$intervalo_dias = 30;
		}
		if($item == 5){
			$intervalo_dias = 90;
		}
		if($item == 6){
			$intervalo_dias = 90;
		}
		if($item == 7){
			$intervalo_dias = 90;
		}
		if($item == 8){
			$intervalo_dias = 365;
		}	
		if($item == 9){
			$intervalo_dias = 365;
		}	
		if($item == 10){
			$intervalo_dias = 365;
		}

       
        if(mysqli_num_rows($result2) > 0){
            // Atualiza o plano existente e incrementa 30 dias na data de expiração
            $query3 = "UPDATE plano_empresas SET data_expiracao = DATE_ADD(data_expiracao, INTERVAL $intervalo_dias DAY), plano_id = $item WHERE empresa_id = $id_empresa"; 
            mysqli_query($conexao2, $query3);
        }
		
		$query = "UPDATE status SET processado = 1 WHERE codigo = '".$collector_id."'";
		mysqli_query($conexao, $query);		
    }
}

	
if($resultado->status == 'rejected'){
	$query = "UPDATE status SET status = 'reprovado' WHERE codigo = '".$collector_id."'";
	mysqli_query($conexao, $query);
}
?>



