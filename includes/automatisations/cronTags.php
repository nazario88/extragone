<?php
// =======================================================
// Script d'attribution automatique de tags pour eXtragone
// =======================================================

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
set_time_limit(0);
ini_set('memory_limit', '512M');

include __DIR__ . '/../config.php';

// --- CONFIGURATION ---
$apiKey = $_ENV['CHATGPT_API_KEY'] ?? getenv('CHATGPT_API_KEY') ?? null;
$model = 'gpt-4.1-mini';
$limit = 30; // Nombre d'outils à traiter par exécution

// --- LISTE OFFICIELLE DE TAGS AUTORISÉS ---
$allowedTags = ALLOWED_TAGS;

// --- RÉCUPÉRATION DES OUTILS SANS TAGS ---
$tools = $pdo->prepare("SELECT id, nom, description_longue FROM extra_tools WHERE is_valid IS NOT NULL AND tags IS NULL LIMIT $limit");
$tools->execute();
$tools = $tools->fetchAll(PDO::FETCH_ASSOC);

if (!$tools) {
    die("🎉 Aucun outil à tagger.\n");
}

foreach ($tools as $tool) {
    echo "\n🧩 Traitement : {$tool['nom']}\n";

    // --- PROMPT OPTIMISÉ ---
    $prompt = "
        Tu es un expert en catégorisation d’outils numériques.
        Lis la description de l’outil ci-dessous et attribue-lui 2 à 4 tags pertinents
        choisis UNIQUEMENT parmi la liste autorisée suivante :
        
        ".implode(', ', $allowedTags)."

        Règles :
        - Les tags doivent venir EXCLUSIVEMENT de cette liste.
        - Évite les tags génériques si un tag plus spécifique s’applique.
        - Utilise le tag \"productivité\" uniquement si l’outil aide à automatiser une tâche ou à mieux s’organiser.
        - Réponds uniquement au format JSON, par exemple :
          [\"design\",\"photo\",\"marketing\"]
        
        Nom : {$tool['nom']}
        Description : {$tool['description_longue']}
    ";

    // --- APPEL OPENAI ---
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer $apiKey",
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'Tu es un moteur d’étiquetage JSON strict.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
        ]),
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        echo "❌ Erreur réseau : " . curl_error($ch) . "\n";
        continue;
    }

    $data = json_decode($response, true);
    curl_close($ch);

    $tags = $data['choices'][0]['message']['content'] ?? '';
    $tags = trim($tags);

    // --- VALIDATION DU FORMAT ---
    if (!str_starts_with($tags, '[')) {
        echo "⚠️ Format inattendu : $tags\n";
        continue;
    }

    // --- PARSE + NETTOYAGE DU JSON ---
    $decoded = json_decode($tags, true);
    if (!is_array($decoded)) {
        echo "⚠️ JSON invalide : $tags\n";
        continue;
    }

    if (is_array($decoded)) {
        $flatTags = implode(',', $decoded);
    } else {
        $flatTags = $tags;
    }

    $stmt = $pdo->prepare("UPDATE extra_tools SET tags = :tags WHERE id = :id");
    $stmt->execute(['tags' => $flatTags, 'id' => $tool['id']]);

    //echo "✅ Tags ajoutés : $flatTags<br>\n";
}

echo "\n✅ Fin du traitement d'attribution des tags.\n";