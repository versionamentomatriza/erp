<?php
include('../../config/config.php');
include('../../config/produtos.php');
include('token.php');
$sql_check_venda = mysqli_query($conexao,"SELECT * FROM status WHERE id_venda = '".$_GET['id_venda']."'") or die("Erro");
$resultado_check_venda = mysqli_fetch_assoc($sql_check_venda);
$json       = file_get_contents('php://input');
$result_request  = json_decode($json);
$caracteres = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
$filtrado   = str_shuffle($caracteres);
$codigoKey  = substr($filtrado, 0, 5).'-'.substr($filtrado, 0, 8).'-'.substr($filtrado, 0, 4);
$curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
         "transaction_amount": '.$preco_item.',
         "token": "'.$result_request->token.'",
         "description": "'.$result_request->description.'",
         "installments": '.$result_request->installments.',
         "payment_method_id": "'.$result_request->payment_method_id.'",
         "notification_url": "https://'.$hostnotification.'/notification.php?id_venda='.$_GET['id_venda'].'";
         "issuer_id": '.$result_request->issuer_id.',
         "payer": {
           "email": "'.$result_request->payer->email.'"
         }
   }',
    CURLOPT_HTTPHEADER => array(
        'accept: application/json',
        'content-type: application/json',
        'X-Idempotency-Key: '.$codigoKey,
        'Authorization: Bearer '.$token
    ),
    ));
    $response = curl_exec($curl);
    $resultado = json_decode($response);
    
    var_dump($response);
curl_close($curl);

$sql="INSERT INTO status(status, codigo, email, cpf, id_venda, total) VALUES('".$resultado->status."', '".$resultado->id."', '".$result_request->payer->email."', '".$result_request->payer->identification->number."', '".$_GET['id_venda']."', '".$preco_item."')";        
mysqli_query($conexao, $sql);
?>