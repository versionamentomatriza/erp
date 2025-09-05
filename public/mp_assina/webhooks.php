<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Log do que foi recebido
file_put_contents("log_webhook.txt", date('Y-m-d H:i:s') . " - Entrada: " . $input . PHP_EOL, FILE_APPEND);

// Garante que existe tipo
if (!isset($data['type'])) {
    http_response_code(400);
    exit("Tipo não informado");
}

switch ($data['type']) {
    case 'subscription_preapproval': // Notificação de assinatura
        $preapproval_id = $data['data']['id'];
        file_put_contents("log_webhook.txt", "Assinatura atualizada/criada. ID: $preapproval_id" . PHP_EOL, FILE_APPEND);

        // Aqui você pode fazer uma requisição à API com o ID
        // https://api.mercadopago.com/preapproval/$preapproval_id

        break;

    case 'payment': // Notificação de pagamento
        $payment_id = $data['data']['id'];
        file_put_contents("log_webhook.txt", "Pagamento recebido. ID: $payment_id" . PHP_EOL, FILE_APPEND);

        // Aqui você pode consultar o pagamento via:
        // https://api.mercadopago.com/v1/payments/$payment_id

        break;

    default:
        file_put_contents("log_webhook.txt", "Outro tipo: " . json_encode($data) . PHP_EOL, FILE_APPEND);
        break;
}

http_response_code(200);
echo "OK";
?>
