<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verifica se a requisição veio do domínio permitido
$referer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;
$allowed_domain = 'descubrasegredos.lat';

if (empty($referer['host']) || $referer['host'] !== $allowed_domain) {
    header("HTTP/1.1 403 Forbidden");
    die("Acesso bloqueado. Apenas o domínio $allowed_domain pode acessar este recurso.");
}

// Pega o número
$numero = isset($_GET['numero']) ? preg_replace('/\D/', '', $_GET['numero']) : '';

if (empty($numero)) {
    die("Número inválido.");
}

// Monta URL da fonte original
$url = "https://descubrasegredos.lat/app/api21.php?numero=" . urlencode($numero);

// Requisição cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

$response = curl_exec($ch);
curl_close($ch);

// Procura a imagem no HTML
if (preg_match('/<img\s+[^>]*src=[\'"]([^\'"]+)[\'"]/', $response, $matches)) {
    $link = html_entity_decode($matches[1]);

    // Exibe a imagem
    echo "<img src='$link' alt='Foto do Contato' style='max-width:300px;border:1px solid #ccc'>";
} else {
    echo "<p>Imagem não encontrada para o número informado.</p>";
}
?>
