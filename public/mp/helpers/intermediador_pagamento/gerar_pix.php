<?php
$curl = curl_init();
    $caracteres = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
    $filtrado   = str_shuffle($caracteres);
    $codigoKey  = substr($filtrado, 0, 5).'-'.substr($filtrado, 0, 8).'-'.substr($filtrado, 0, 4);
    $nome  = str_replace(array("#", "'", ";", "*", "=", "INSERT", "insert", "delete", "DELETE", "where", "WHERE", "update", "UPDATE"), '', $_POST['nome']);
    $primeiroNome = substr($nome, 0, strpos($nome, ' '));
    $sobrenome = trim(str_replace($primeiroNome, "", $nome));
    
    $email  = str_replace(array("#", "'", ";", "*", "=", "INSERT", "insert", "delete", "DELETE", "where", "WHERE", "update", "UPDATE"), '', $_POST['email']);
    $cpf    = str_replace(array("#", "'", ";", "*", "=", "INSERT", "insert", "delete", "DELETE", "where", "WHERE", "update", "UPDATE"), '', $_POST['cpf']);

    $dados["transaction_amount"]                    = floatval($preco_item);
    $dados["description"]                           = $_GET['item'];
    $dados["external_reference"]                    = "2"; 
    $dados["payment_method_id"]                     = "pix";
    $dados["notification_url"]                      = "https://".$hostnotification."/notification.php?id_venda=".$_GET['id_venda'];
    $dados["payer"]["email"]                        = $email;
    $dados["payer"]["first_name"]                   = $primeiroNome;
    $dados["payer"]["last_name"]                    = $sobrenome;
    
    $dados["payer"]["identification"]["type"]       = "CPF";
    $dados["payer"]["identification"]["number"]     = $cpf;
    
    //$dados["payer"]["address"]["zip_code"]          = "06233200";
    //$dados["payer"]["address"]["street_name"]       = "Av. das Nações Unidas";
    //$dados["payer"]["address"]["street_number"]     = "3003";
    //$dados["payer"]["address"]["neighborhood"]      = "Bonfim";
    //$dados["payer"]["address"]["city"]              = "Osasco";
    //$dados["payer"]["address"]["federal_unit"]      = "SP";

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($dados),
    CURLOPT_HTTPHEADER => array(
        'accept: application/json',
        'content-type: application/json',
        'X-Idempotency-Key: '.$codigoKey,
        'Authorization: Bearer '.$token
    ),
    ));
    $response = curl_exec($curl);
    $resultado = json_decode($response);
    //var_dump($response);
curl_close($curl);
?>