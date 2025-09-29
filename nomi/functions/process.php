<?php
include '../../includes/config.php';

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: https://nomi.extrag.one/generate');
    exit;
}

// Récupération et validation des données
$project_description = sanitizeInput($_POST['project_description'] ?? '');
$example_names = sanitizeInput($_POST['example_names'] ?? '');
$keywords = sanitizeInput($_POST['keywords'] ?? '');
$preferences_length = $_POST['preferences_length'] ?? 'moyen';
$preferences_style = $_POST['preferences_style'] ?? 'moderne';

// Validation basique
if (empty($project_description) || strlen($project_description) < 20) {
    $_SESSION['error'] = 'La description du projet doit contenir au moins 20 caractères.';
    header('Location: https://nomi.extrag.one/generate');
    exit;
}

// Vérifier la clé API
if (empty($_ENV['MISTRAL_API_KEY'])) {
    error_log('Nomi Error - Clé API Mistral manquante');
    $_SESSION['error'] = 'Configuration API manquante.';
    header('Location: https://nomi.extrag.one/generate');
    exit;
}

// Générer un token de partage unique
$share_token = bin2hex(random_bytes(16));

// Récupérer l'IP utilisateur
$user_ip = getIP();

try {
    // Appel à l'API Mistral
    $generated_names = generateNamesWithMistral($project_description, $example_names, $keywords, $preferences_length, $preferences_style);
    
    if (!$generated_names) {
        throw new Exception('Erreur lors de la génération des noms');
    }
    
    // Insertion en base de données
    $stmt = $pdo->prepare('
        INSERT INTO nomi_generations 
        (user_ip, project_description, example_names, keywords, preferences_length, preferences_style, generated_names, share_token) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $user_ip,
        $project_description,
        $example_names,
        $keywords,
        $preferences_length,
        $preferences_style,
        json_encode($generated_names),
        $share_token
    ]);
    
    $generation_id = $pdo->lastInsertId();
    
    // Log de l'action
    $log_stmt = $pdo->prepare('
        INSERT INTO nomi_logs (action, user_ip, generation_id, details) 
        VALUES (?, ?, ?, ?)
    ');
    
    $log_details = [
        'preferences_length' => $preferences_length,
        'preferences_style' => $preferences_style,
        'has_examples' => !empty($example_names),
        'has_keywords' => !empty($keywords)
    ];
    
    $log_stmt->execute([
        'generate',
        $user_ip,
        $generation_id,
        json_encode($log_details)
    ]);
    
    // Redirection vers les résultats
    header('Location: https://nomi.extrag.one/results?token=' . $share_token);
    exit;
    
} catch (Exception $e) {
    error_log('Nomi generation error: ' . $e->getMessage());
    $_SESSION['error'] = 'Une erreur est survenue lors de la génération. Veuillez réessayer.';
    header('Location: https://nomi.extrag.one/generate');
    exit;
}

/**
 * Génère des noms avec l'API Mistral AI
 */
function generateNamesWithMistral($description, $examples, $keywords, $length, $style) {
    // Essayer plusieurs méthodes pour récupérer la clé API
    $api_key = $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY') ?? null;
    
    if (empty($api_key)) {
        throw new Exception('Clé API Mistral non configurée');
    }
    
    // Construction du prompt - VERSION SIMPLIFIEE pour éviter les timeouts
    $prompt = buildSimplePrompt($description, $examples, $keywords, $length, $style);
    
    $data = [
        'model' => 'mistral-small-latest', // Modèle plus rapide
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.8,
        'max_tokens' => 2000 // Réduire pour accélérer
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
        CURLOPT_TIMEOUT => 60, // Augmenter le timeout à 60 secondes
        CURLOPT_CONNECTTIMEOUT => 10 // Timeout de connexion
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        throw new Exception('Erreur CURL: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    if ($http_code !== 200) {
        $error_details = '';
        if ($response) {
            $error_data = json_decode($response, true);
            if (isset($error_data['message'])) {
                $error_details = ' - ' . $error_data['message'];
            }
        }
        throw new Exception('Erreur API Mistral: HTTP ' . $http_code . $error_details);
    }
    
    $result = json_decode($response, true);
    
    if (!isset($result['choices'][0]['message']['content'])) {
        throw new Exception('Réponse API invalide');
    }
    
    $content = $result['choices'][0]['message']['content'];
    
    // Parser le JSON retourné par l'IA
    return parseAIResponse($content);
}

/**
 * Version simplifiée du prompt pour éviter les timeouts
 */
function buildSimplePrompt($description, $examples, $keywords, $length, $style) {
    $prompt = "Génère 30 noms créatifs pour ce projet : {$description}";
    
    if (!empty($examples)) {
        $prompt .= "\nExemples aimés: {$examples}";
    }
    
    if (!empty($keywords)) {
        $prompt .= "\nMots-clés: {$keywords}";
    }
    
    $prompt .= "\nStyle: {$style}, Longueur: {$length}";
    
    $prompt .= "\n\nRéponds en JSON strict :
{
  \"categories\": [
    {
      \"name\": \"Nom de catégorie descriptif\",
      \"description\": \"Description de cette catégorie\",
      \"names\": [
        {\"name\": \"NomExemple\", \"explanation\": \"Courte explication\"}
      ]
    }
  ]
}

IMPORTANT: 
- Génère 30 noms UNIQUES répartis en 3 catégories thématiques différentes (10 noms par catégorie)
- Chaque catégorie doit avoir un nom et une description pertinente
- AUCUN doublon autorisé
- Exemples de catégories : Noms modernes, Noms évocateurs, Noms techniques, etc.

JSON uniquement, sans texte avant/après.
";

    return $prompt;
}

/**
 * Construit le prompt pour l'API (version complète - gardée pour plus tard)
 */
function buildPrompt($description, $examples, $keywords, $length, $style) {
    $length_desc = [
        'court' => 'courts (3-5 lettres)',
        'moyen' => 'moyens (6-10 lettres)', 
        'long' => 'longs (plus de 10 lettres)'
    ];
    
    $style_desc = [
        'moderne' => 'moderne et épuré (comme Notion, Slack, Stripe)',
        'tech' => 'technique et professionnel (comme GitHub, Docker, Redis)',
        'creatif' => 'créatif et artistique (comme Figma, Canva, Dribbble)',
        'classique' => 'classique et établi (comme Microsoft, Adobe, Oracle)'
    ];
    
    $prompt = "Tu es un expert en naming et branding. Je vais te décrire un projet et tu dois générer 30 noms créatifs organisés en différentes catégories.

DESCRIPTION DU PROJET:
{$description}";

    if (!empty($examples)) {
        $prompt .= "\n\nNOMS QUE J'AIME:
{$examples}";
    }
    
    if (!empty($keywords)) {
        $prompt .= "\n\nMOTS-CLÉS IMPORTANTS:
{$keywords}";
    }
    
    $prompt .= "\n\nPRÉFÉRENCES:
- Longueur: " . $length_desc[$length] . "
- Style: " . $style_desc[$style] . "

INSTRUCTIONS:
1. Génère exactement 30 noms UNIQUES créatifs et pertinents
2. Organise-les en 3 catégories thématiques (10 noms par catégorie)
3. Pour chaque nom, fournis une explication courte (1 phrase)
4. Assure-toi que les noms sont:
   - Faciles à prononcer
   - Mémorisables
   - Évocateurs du projet
   - Disponibles potentiellement (.com)
5. Aucun doublon autorisé

FORMAT DE RÉPONSE (JSON strict):
{
  \"categories\": [
    {
      \"name\": \"Nom de la catégorie\",
      \"description\": \"Description de cette catégorie\",
      \"names\": [
        {
          \"name\": \"NomExemple\",
          \"explanation\": \"Courte explication du nom\"
        }
      ]
    }
  ]
}

Réponds UNIQUEMENT avec le JSON, sans texte supplémentaire.";

    return $prompt;
}

/**
 * Parse la réponse de l'IA et valide le format
 */
function parseAIResponse($content) {
    // Nettoyer la réponse (enlever les ```json si présents)
    $content = trim($content);
    $content = preg_replace('/^```json\s*/', '', $content);
    $content = preg_replace('/\s*```$/', '', $content);
    
    $data = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Réponse JSON invalide de l\'IA');
    }
    
    // Validation de la structure
    if (!isset($data['categories']) || !is_array($data['categories'])) {
        throw new Exception('Structure de données invalide');
    }
    
    foreach ($data['categories'] as $category) {
        if (!isset($category['name'], $category['names']) || !is_array($category['names'])) {
            throw new Exception('Structure de catégorie invalide');
        }
        
        foreach ($category['names'] as $nameData) {
            if (!isset($nameData['name'], $nameData['explanation'])) {
                throw new Exception('Structure de nom invalide');
            }
        }
    }
    
    return removeDuplicateNames($data);
}

// Supprimer les doublons
function removeDuplicateNames($data) {
    foreach ($data['categories'] as &$category) {
        $uniqueNames = [];
        $seenNames = [];
        
        foreach ($category['names'] as $nameData) {
            $nameLower = strtolower($nameData['name']);
            if (!in_array($nameLower, $seenNames)) {
                $uniqueNames[] = $nameData;
                $seenNames[] = $nameLower;
            }
        }
        
        $category['names'] = $uniqueNames;
    }
    
    return $data;
}

?>