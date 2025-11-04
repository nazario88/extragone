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
include __DIR__ . '/../../includes/config.php';

// IDs des comptes bots (à adapter après création)
$BOT_USERS = [
    'NiouiNina' => null,
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

// =================== PHRASES GÉNÉRIQUES ===================

$COMMENTS_SIMPLE = [
    "Super projet !",
    "Bravo !",
    "Excellent travail !",
    "Belle réalisation !",
    "Vraiment bien fait !",
    "J'adore ! 😍",
    "Très chouette !",
    "Sympa comme projet !",
    "Beau boulot !",
    "Top !",
    "Génial !",
    "Impressionnant !",
    "Joli !",
    "Bien joué !",
    "Cool ! 👍",
    "Stylé !",
    "Nickel !",
    "GG ! 🔥",
    "Pas mal du tout !",
    "C'est propre ! ✨",
];

$COMMENTS_MEDIUM = [
    "Super projet ! Continue comme ça.",
    "Bravo ! C'est quoi les next steps ?",
    "Excellent travail ! J'ai hâte de voir la suite.",
    "Belle réalisation ! Ça donne envie de tester.",
    "Vraiment bien fait ! Keep it up 💪",
    "J'adore ! Bon courage pour la suite.",
    "Très chouette ! Vivement les prochaines features.",
    "Sympa comme projet ! Tu prévois quoi après ?",
    "Beau boulot ! Je vais suivre ça de près.",
    "Top ! Ça mérite d'être partagé.",
    "Génial ! Continue sur cette lancée.",
    "Impressionnant ! Félicitations.",
    "Joli ! C'est exactement ce qu'il fallait.",
    "Bien joué ! Hâte de voir l'évolution.",
    "Cool ! Je pense l'utiliser régulièrement.",
    "Stylé ! Bravo encore.",
    "Nickel ! Ça fait le job. 👌",
    "GG ! Tu gères ! 🚀",
    "Pas mal du tout ! Bien pensé.",
    "C'est propre ! Rien à redire.",
];

$COMMENTS_LONG = [
    "Super projet ! L'idée est vraiment intéressante. Continue comme ça !",
    "Bravo ! Le design est épuré et l'interface intuitive. C'est quoi les next steps ?",
    "Excellent travail ! On voit que c'est soigné. J'ai hâte de voir la suite.",
    "Belle réalisation ! Le concept est original. Ça donne envie de tester.",
    "Vraiment bien fait ! L'expérience utilisateur est au rendez-vous. Keep it up 💪",
    "J'adore ! Les fonctionnalités sont bien pensées. Bon courage pour la suite.",
    "Très chouette ! C'est exactement ce qu'il manquait. Vivement les prochaines features.",
    "Sympa comme projet ! C'est fluide et agréable à utiliser. Tu prévois quoi après ?",
    "Beau boulot ! Ça répond bien au besoin. Je vais suivre ça de près.",
    "Top ! L'approche est pertinente. Ça mérite d'être partagé.",
    "Génial ! C'est moderne et bien exécuté. Continue sur cette lancée.",
    "Impressionnant ! Tout fonctionne comme attendu. Félicitations.",
    "Joli ! Les détails font la différence. C'est exactement ce qu'il fallait.",
    "Bien joué ! C'est abouti et stable. Hâte de voir l'évolution.",
    "Cool ! L'interface est claire et efficace. Je pense l'utiliser régulièrement.",
    "Stylé ! On sent que c'est du travail pro. Bravo encore.",
    "Nickel ! Ça fait exactement le job. Rien à redire. 👌",
    "GG ! Le projet est solide et prometteur. Tu gères ! 🚀",
    "Pas mal du tout ! C'est bien structuré et pratique. Bien pensé.",
    "C'est propre ! Tout est cohérent du début à la fin. Bravo. ✨",
];

// =================== LOGIQUE ===================

/**
 * Génère un commentaire aléatoire
 */
function generateComment() {
    global $COMMENTS_SIMPLE, $COMMENTS_MEDIUM, $COMMENTS_LONG;
    
    // Distribution aléatoire :
    // 40% courts, 40% moyens, 20% longs
    $rand = rand(1, 100);
    
    if ($rand <= 40) {
        // Commentaire court
        return $COMMENTS_SIMPLE[array_rand($COMMENTS_SIMPLE)];
    } elseif ($rand <= 80) {
        // Commentaire moyen
        return $COMMENTS_MEDIUM[array_rand($COMMENTS_MEDIUM)];
    } else {
        // Commentaire long
        return $COMMENTS_LONG[array_rand($COMMENTS_LONG)];
    }
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