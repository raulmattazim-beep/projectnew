<?php
// Lista de domínios permitidos (sem protocolos)
$allowed_domains = [
    'descubrasegredos.lat',
    'cyberspy.fun'
];

// Função para extrair o domínio principal (sem protocolo, sem subdomínios)
function get_domain($url) {
    // Remove o protocolo (http:// ou https://)
    $url = preg_replace('/^https?:\/\//', '', $url);
    
    // Remove "www" se houver
    $url = preg_replace('/^www\./', '', $url);
    
    // Remove qualquer caminho ou parâmetros
    $url = preg_replace('/\/.*$/', '', $url);
    
    return $url;
}

// Recupera o cabeçalho Origin e Referer, se presente
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

// Verifica o domínio da origem
$origin_domain = get_domain($origin);

// Verifica o domínio do referer
$referer_domain = get_domain($referer);

// Verifica se a origem ou referer está na lista de domínios permitidos
if (in_array($origin_domain, $allowed_domains) || in_array($referer_domain, $allowed_domains)) {
    // Permite o acesso
    header("Access-Control-Allow-Origin: " . $origin);
    header("Vary: Origin"); // Ajuda o cache a controlar as diferentes origens
} else {
    // Se o domínio não for permitido, retorna um erro 403
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(["error" => "Acesso negado. Origem ou referer não permitido."]);
    exit;
}

// Define o tipo de retorno como JSON
header("Content-Type: application/json");

// Habilita exibição de erros para debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verifica o parâmetro 'numero' e limpa caracteres não numéricos
$numero = isset($_GET['numero']) ? preg_replace('/\D/', '', $_GET['numero']) : '';

if (empty($numero)) {
    echo json_encode(["error" => "Número inválido ou não informado."]);
    exit;
}

// URL do serviço para buscar a imagem
$url = "https://descubrasegredos.lat/app/api21.php?numero=" . urlencode($numero);

// Realiza a requisição com cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Seguir redirecionamentos
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignorar a verificação SSL se necessário
$response = curl_exec($ch);
curl_close($ch);

// Processa a resposta da requisição
if ($response !== false) {
    // Usar regex para capturar o link da imagem
    if (preg_match('/<img src=[\'"]([^\'"]+)[\'"][^>]*alt=[\'"][^\'"]*[\'"]/', $response, $matches)) {
        $imageUrl = html_entity_decode($matches[1]); // Decodifica as entidades HTML
        echo json_encode(["link" => $imageUrl]); // Retorna o link da imagem
    } else {
        echo json_encode(["error" => "Imagem não encontrada no conteúdo retornado."]);
    }
} else {
    echo json_encode(["error" => "Erro ao buscar a imagem."]);
}
?>
