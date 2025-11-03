<?php
/**
 * Script CRON - Commentaires automatiques
 * À exécuter quotidiennement via cron
 * Exemple : 0 10 * * * /usr/bin/php /var/www/includes/cron-auto-comments.php
 */

set_time_limit(0);
ini_set('memory_limit', '256M');

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
include '../../includes/config.php';

// IDs des comptes bots (à adapter après création)
$BOT_USERS = [
    'NiouiNina' => null,  // Sera récupéré dynamiquement
    'JulienM' => null,
    'Youn' => null
];

// Récupérer les IDs des bots
$stmt = $pdo->query("SELECT id, username FROM extra_proj_users WHERE username IN ('NiouiNina', 'JulienM', 'Youn')");
$bots = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($bots as $bot) {
    $BOT_USERS[$bot['username']] = (int)$bot['id'];
}

// Vérifier que les bots existent
if (in_array(null, $BOT_USERS)) {
    error_log('CRON AUTO-COMMENTS: Bots non trouvés en base de données');
    exit(1);
}

// =================== PHRASES ===================

$INTROS = [
    "Super projet !",
    "Bravo !",
    "Excellent travail !",
    "Belle réalisation !",
    "Vraiment bien fait !",
    "J'adore !",
    "Très chouette !",
    "Sympa comme projet !",
    "Beau boulot !",
    "Top !",
    "Génial !",
    "Impressionnant !",
    "Joli !",
    "Bien joué !",
    "Cool !",
    "Stylé !",
    "Nickel !",
];

$CORPS = [
    // UI/Design
    "L'interface est vraiment intuitive.",
    "Le design est épuré, j'aime beaucoup.",
    "Les couleurs sont bien choisies.",
    "L'UI est moderne et agréable.",
    "Le choix des polices est top.",
    "C'est visuellement très réussi.",
    "L'ergonomie est au rendez-vous.",
    "Le design est cohérent du début à la fin.",
    "Les animations sont subtiles et bien dosées.",
    "La navigation est fluide.",
    
    // Technique
    "Le code a l'air propre.",
    "Les performances semblent optimales.",
    "C'est techniquement solide.",
    "La stack technique est bien choisie.",
    "L'architecture est claire.",
    "Le projet est bien structuré.",
    "Les fonctionnalités sont bien implémentées.",
    "C'est responsive, parfait.",
    "Le chargement est rapide.",
    "Les transitions sont smooth.",
    
    // Concept
    "Le concept est original.",
    "L'idée est vraiment intéressante.",
    "C'est exactement ce qu'il manquait.",
    "Le besoin est bien identifié.",
    "La proposition de valeur est claire.",
    "C'est innovant.",
    "L'approche est pertinente.",
    "Le problème est bien résolu.",
    "C'est un vrai gain de temps.",
    "L'utilité est évidente.",
    
    // Expérience utilisateur
    "L'expérience utilisateur est top.",
    "C'est agréable à utiliser.",
    "La prise en main est immédiate.",
    "C'est intuitif dès le premier clic.",
    "On comprend tout de suite comment ça marche.",
    "Les feedbacks visuels sont clairs.",
    "Aucune friction dans le parcours.",
    "L'onboarding est bien pensé.",
    "Les cas d'usage sont bien couverts.",
    "C'est accessible et inclusif.",
    
    // Qualité générale
    "La qualité est au rendez-vous.",
    "Tout est soigné.",
    "On voit le travail accompli.",
    "C'est abouti.",
    "Rien à redire sur la finition.",
    "Les détails font la différence.",
    "C'est du travail professionnel.",
    "La qualité est constante.",
    "Tout fonctionne comme attendu.",
    "C'est stable et fiable.",
    
    // Inspiration/Motivation
    "Ça donne envie de tester.",
    "Je vais l'ajouter à mes bookmarks.",
    "Je pense l'utiliser régulièrement.",
    "Ça m'inspire pour mes propres projets.",
    "Je vais le recommander autour de moi.",
    "C'est le genre d'outil qu'on garde.",
    "Ça mérite d'être plus connu.",
    "Je vais suivre l'évolution.",
    "Hâte de voir les prochaines features.",
    "C'est prometteur.",
];

$CONCLUSIONS = [
    "Continue comme ça !",
    "Vivement la suite !",
    "J'ai hâte de voir les évolutions.",
    "Bon courage pour la suite !",
    "Bravo encore !",
    "Keep it up!",
    "Belle continuation !",
    "Félicitations !",
    "GG !",
    "Bien joué !",
    "💪",
    "🚀",
    "👏",
    "🔥",
    "👍",
    "",  // Pas de conclusion (33% de chances)
    "",
    "",
];

// =================== LOGIQUE ===================

/**
 * Génère un commentaire aléatoire composé
 */
function generateComment() {
    global $INTROS, $CORPS, $CONCLUSIONS;
    
    // 60% de chance d'avoir une intro
    $intro = (rand(1, 100) <= 60) ? $INTROS[array_rand($INTROS)] . ' ' : '';
    
    // Corps (obligatoire)
    $corps = $CORPS[array_rand($CORPS)];
    
    // 40% de chance d'avoir une conclusion
    $conclusion = (rand(1, 100) <= 40) ? ' ' . $CONCLUSIONS[array_rand($CONCLUSIONS)] : '';
    
    return trim($intro . $corps . $conclusion);
}

/**
 * Récupère un projet récent qui peut recevoir un commentaire bot
 */
function getEligibleProject() {
    global $pdo, $BOT_USERS;
    
    // Projets publiés dans les 30 derniers jours
    // Qui n'ont PAS déjà été commentés par un bot aujourd'hui
    $bot_ids = implode(',', array_values($BOT_USERS));
    
    $sql = "
        SELECT p.id, p.title, p.slug, p.user_id
        FROM extra_proj_projects p
        WHERE p.status = 'published'
        AND p.published_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND p.id NOT IN (
            SELECT DISTINCT project_id 
            FROM extra_proj_comments 
            WHERE user_id IN ($bot_ids)
            AND DATE(created_at) = CURDATE()
        )
        ORDER BY RAND()
        LIMIT 1
    ";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Poste un commentaire automatique
 */
function postAutoComment($project, $bot_user_id, $comment_text) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO extra_proj_comments (project_id, user_id, content, created_at) 
            VALUES (?, ?, ?, NOW())
        ');
        $stmt->execute([$project['id'], $bot_user_id, $comment_text]);
        
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log('CRON AUTO-COMMENTS: Erreur insertion commentaire - ' . $e->getMessage());
        return false;
    }
}

/**
 * Log l'action dans extra_proj_logs
 */
function logAutoComment($bot_username, $project_id, $comment_id, $comment_text) {
    global $pdo;
    
    $details = json_encode([
        'bot_username' => $bot_username,
        'project_id' => $project_id,
        'comment_id' => $comment_id,
        'comment_preview' => substr($comment_text, 0, 100)
    ]);
    
    $stmt = $pdo->prepare('
        INSERT INTO extra_proj_logs (action, user_id, project_id, details, created_at) 
        VALUES (?, NULL, ?, ?, NOW())
    ');
    $stmt->execute(['auto_comment', $project_id, $details]);
}

// =================== EXÉCUTION ===================

echo "=== CRON AUTO-COMMENTS ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Récupérer un projet éligible
$project = getEligibleProject();

if (!$project) {
    echo "❌ Aucun projet éligible trouvé (tous déjà commentés aujourd'hui ou trop anciens)\n";
    
    // Log
    $stmt = $pdo->prepare('
        INSERT INTO extra_proj_logs (action, details, created_at) 
        VALUES (?, ?, NOW())
    ');
    $stmt->execute(['auto_comment_skip', json_encode(['reason' => 'no_eligible_project'])]);
    
    exit(0);
}

echo "✅ Projet sélectionné: {$project['title']} (ID: {$project['id']})\n";

// Sélectionner un bot au hasard
$bot_usernames = array_keys($BOT_USERS);
$selected_bot_username = $bot_usernames[array_rand($bot_usernames)];
$selected_bot_id = $BOT_USERS[$selected_bot_username];

echo "✅ Bot sélectionné: {$selected_bot_username} (ID: {$selected_bot_id})\n";

// Générer le commentaire
$comment_text = generateComment();

echo "✅ Commentaire généré: \"{$comment_text}\"\n";

// Poster le commentaire
$comment_id = postAutoComment($project, $selected_bot_id, $comment_text);

if ($comment_id) {
    echo "✅ Commentaire posté avec succès (ID: {$comment_id})\n";
    
    // Log
    logAutoComment($selected_bot_username, $project['id'], $comment_id, $comment_text);
    
    echo "\n🎉 SUCCÈS - Commentaire automatique publié !\n";
    echo "👉 https://projets.extrag.one/projet/{$project['slug']}#comment-{$comment_id}\n";
} else {
    echo "❌ Échec de la publication du commentaire\n";
    exit(1);
}

exit(0);
?>