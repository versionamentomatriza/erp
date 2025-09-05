<?php
// Configurações de conexão com o banco de dados
include "config.php";

// Chave e método de criptografia
$encryption_method = 'AES-256-CBC'; // Método de criptografia
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));


try {
    // Conectando ao banco de dados usando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recebe o empresa_id como parâmetro
    $empresa_id = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : 0;
	
	$empresa_id = 6;
	
	$token_api = "fz23YMXJ2BaQzGEqaCGRxNgLE";

    if ($empresa_id > 0) {
        // Consulta para selecionar produtos pelo empresa_id
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE empresa_id = :empresa_id");
        $stmt->bindParam(':empresa_id', $empresa_id, PDO::PARAM_INT);
        $stmt->execute();

        // Obtém todos os produtos como um array associativo
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Converte os dados para JSON
        $jsonData = json_encode($produtos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        
		/*
		// Criptografa o JSON
        $encryptedData = openssl_encrypt($jsonData, $encryption_method, $encryption_key, 0, $iv);

        // Exibe o JSON criptografado e o IV em base64
        $output = [
            'iv' => base64_encode($iv),
            'data' => $encryptedData
        ];

        // Define o cabeçalho do conteúdo para JSON
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($output);
		*/
		
		//echo $jsonData;
    } else {
        echo json_encode(['error' => 'Por favor, forneça um empresa_id válido.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro na conexão: ' . $e->getMessage()]);
}
?>