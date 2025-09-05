<?php
// Defina o caminho do arquivo de log
$logFile = __DIR__ . '/logs/post_get_log.txt';

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados GET da URL
    $getData = print_r($_GET, true);
    
    // Obtém os dados do POST
    $postData = print_r($_POST, true);
    
    // Formata a mensagem de log com dados GET e POST
    $logMessage = "[" . date('Y-m-d H:i:s') . "]\nGET Data:\n" . $getData . "\nPOST Data:\n" . $postData . "\n\n";
    
    // Grava a mensagem de log no arquivo especificado
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    // Resposta para o cliente (opcional)
    echo "Dados recebidos e logados com sucesso.";
} else {
    echo "Esta página aceita apenas requisições POST.";
}
?>
