<?php

// Configurações UltraMsg
$token      = "ljy7fxptja6hrlta";
$instanceId = "instance129609";

// Número via GET
$phone = preg_replace('/\D/', '', $_GET['numero'] ?? '');
if (empty($phone)) {
    die("❌ Número inválido.");
}

$chatId = $phone . "@c.us";

// Puxa a imagem de perfil
$imageUrl = "https://api.ultramsg.com/$instanceId/contacts/image?token=$token&chatId=" . urlencode($chatId);
$ch = curl_init($imageUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Trata resposta
$data = json_decode($response, true);

if ($httpCode === 200 && isset($data['success'])) {
    echo "<h3>✅ Foto de perfil de $phone:</h3>";
    echo "<img src='" . htmlspecialchars($data['success']) . "' alt='Foto do contato' style='max-width:200px;border-radius:8px;box-shadow:0 0 5px rgba(0,0,0,0.2);'>";
} else {
    echo "❌ Não foi possível obter a foto.<br>";
    echo "Código HTTP: $httpCode<br>";
    echo "Resposta: <pre>" . htmlspecialchars($response) . "</pre>";
}
?>
