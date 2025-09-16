<?php
// Configurações da API Z-API
$instanceId = "3DEE36262C92908CEF810A1EFCACDE10";
$token = "65D87CDD2FD2CBE10A53450C";
$clientToken = "F0ac002abdea440e6a50e6b432fea8d16S";

// Arquivo de log (no mesmo diretório do script)
$logFile = __DIR__ . '/log_fotos.txt';

// Captura e sanitiza o número da URL
$phone = isset($_GET['numero']) ? preg_replace('/\D/', '', $_GET['numero']) : '';
if (strlen($phone) < 10) {
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] Número inválido ou não informado\n", FILE_APPEND);
    die("Número inválido ou não informado.");
}

// Monta a URL da Z-API
$url = "https://api.z-api.io/instances/$instanceId/token/$token/profile-picture?phone=$phone";

// Faz a requisição cURL
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Client-Token: $clientToken",
        "Accept: application/json"
    ],
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Converte JSON da resposta
$data = json_decode($response, true);

// Monta a linha de log
$log = "[" . date("Y-m-d H:i:s") . "] Número: $phone | HTTP: $httpCode";

// Exibe e registra resultado
if ($httpCode === 200 && isset($data['link']) && filter_var($data['link'], FILTER_VALIDATE_URL)) {
    $log .= " | FOTO OK: " . $data['link'] . "\n";
    file_put_contents($logFile, $log, FILE_APPEND);
    echo "<strong>HTTP Code:</strong> $httpCode<br><hr>";
    echo "<img src='" . htmlspecialchars($data['link']) . "' alt='Foto do Contato' style='max-width:300px;'>";
} elseif (isset($data['errorMessage'])) {
    // Compatível com PHP 7.x (sem match)
    switch ($data['errorMessage']) {
        case 'item-not-found':
            $mensagem = "Número não está registrado no WhatsApp ou a foto está indisponível.";
            break;
        case 'unauthorized':
            $mensagem = "Instância ou token inválido.";
            break;
        case 'invalid-parameters':
            $mensagem = "Parâmetros inválidos enviados.";
            break;
        default:
            $mensagem = "Erro da API: " . $data['errorMessage'];
    }

    $log .= " | ERRO: " . $data['errorMessage'] . "\n";
    file_put_contents($logFile, $log, FILE_APPEND);
    echo "<strong>HTTP Code:</strong> $httpCode<br><pre>";
    print_r($data);
    echo "</pre><hr><strong>$mensagem</strong>";
} else {
    $log .= " | ERRO: resposta vazia ou desconhecida\n";
    file_put_contents($logFile, $log, FILE_APPEND);
    echo "<strong>HTTP Code:</strong> $httpCode<br><hr><strong>Foto não disponível ou número inválido.</strong>";
}
?>
