<?php
include '../../includes/config.php';

echo "<h1>Test API Simple</h1>";

$api_key = $_ENV['MISTRAL_API_KEY'];

// Prompt minimal pour économiser les tokens
$data = [
    'model' => 'mistral-small-latest', // Modèle moins cher
    'messages' => [
        [
            'role' => 'user', 
            'content' => 'Génère 5 noms créatifs pour une app de productivité. Format JSON : {"names":["nom1","nom2","nom3","nom4","nom5"]}'
        ]
    ],
    'temperature' => 0.7,
    'max_tokens' => 100 // Très limité pour économiser
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.mistral.ai/v1/chat/completions',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ],
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "<p><strong>Code HTTP :</strong> " . $http_code . "</p>";

if ($response) {
    echo "<p><strong>Réponse complète :</strong></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    $result = json_decode($response, true);
    if (isset($result['error'])) {
        echo "<p style='color: red;'><strong>Erreur API :</strong> " . $result['error']['message'] . "</p>";
    }
}

curl_close($ch);
?>