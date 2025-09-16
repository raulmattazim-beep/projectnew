<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// === DOMÍNIO AUTORIZADO ===
$dominio_autorizado = 'https://descubrasegredos.lat'; // <- troque aqui
$origem = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';

// Bloqueio por CORS e origem
if (stripos($origem, $dominio_autorizado) === 0) {
    header("Access-Control-Allow-Origin: $dominio_autorizado");
    header("Access-Control-Allow-Methods: GET, POST");
    header("Access-Control-Allow-Headers: Content-Type");
} else {
    http_response_code(403);
    echo json_encode(["error" => "Acesso negado: domínio não autorizado."]);
    exit;
}

header("Content-Type: application/json");

// === CONFIGURAÇÕES DA Z-API ===
$instanceId = "3DEE36262C92908CEF810A1EFCACDE10";
$token = "65D87CDD2FD2CBE10A53450C";
$clientToken = "F0ac002abdea440e6a50e6b432fea8d16S";

// === CONFIGURAÇÕES DO LOG ===
$logFile = __DIR__ . '/log_fotos.txt';
$limite_linhas = 1000;

// Função para gravar log com limite
function gravar_log($linha, $arquivo, $limite = 1000) {
    $linhas = file_exists($arquivo) ? file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    array_unshift($linhas, $linha);
    if (count($linhas) > $limite) {
        $linhas = array_slice($linhas, 0, $limite);
    }
    file_put_contents($arquivo, implode("\n", $linhas));
}

// Sanitiza o número
$phone = isset($_GET['numero']) ? preg_replace('/\D/', '', $_GET['numero']) : '';
if (empty($phone)) {
    gravar_log("[" . date("Y-m-d H:i:s") . "] Número inválido (imagem.php)", $logFile, $limite_linhas);
    echo json_encode(["error" => "Número inválido ou não informado."]);
    exit;
}

// Consulta a Z-API
$url = "https://api.z-api.io/instances/$instanceId/token/$token/profile-picture?phone=$phone";
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Client-Token: $clientToken",
        "Content-Type: application/json"
    ],
    CURLOPT_TIMEOUT => 10
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Processa a resposta
$data = json_decode($response, true);
$linha_log = "[" . date("Y-m-d H:i:s") . "] Número: $phone";

if ($httpCode === 200 && isset($data['link']) && filter_var($data['link'], FILTER_VALIDATE_URL)) {
    $linha_log .= " | FOTO OK: " . $data['link'];
    gravar_log($linha_log, $logFile, $limite_linhas);
    echo json_encode(["link" => $data['link']]);
} elseif (isset($data['errorMessage'])) {
    $linha_log .= " | ERRO API: " . $data['errorMessage'];
    gravar_log($linha_log, $logFile, $limite_linhas);
    echo json_encode(["error" => $data['errorMessage']]);
} else {
    $linha_log .= " | ERRO: resposta vazia ou imagem não encontrada";
    gravar_log($linha_log, $logFile, $limite_linhas);
    echo json_encode(["error" => "Imagem não encontrada na Z-API."]);
}
?>
