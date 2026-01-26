<?php
// ===============================================
// SCRIPT CRON : Détection automatique nouvelles pages alternatives
// À exécuter quotidiennement (ex: 3h du matin)
// Cron: 0 3 * * * /usr/bin/php /home/innospy/eXtragone/includes/cronAutoDetectAlternatives.php
// ===============================================
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include __DIR__ . '/../config.php';

// ===============================================
// 1. RÉCUPÉRER LES OUTILS AVEC ≥3 ALTERNATIVES FR
//    QUI N'ONT PAS ENCORE DE PAGE ALTERNATIVE
// ===============================================

$sql = "
    SELECT 
        t.id,
        t.slug,
        t.nom,
        COUNT(DISTINCT alt.id) as nb_alternatives_fr
    FROM extra_tools t
    INNER JOIN extra_alternatives a ON a.id_outil = t.id
    INNER JOIN extra_tools alt ON alt.id = a.id_alternative
    LEFT JOIN extra_alternatives_content ac ON ac.slug = t.slug
    WHERE alt.is_french = 1
      AND t.is_valid = 1
      AND ac.id IS NULL  -- N'existe pas encore
    GROUP BY t.id, t.slug, t.nom
    HAVING nb_alternatives_fr >= 3
    ORDER BY t.nom ASC
";

$stmt = $pdo->query($sql);
$newTools = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($newTools)) {
    echo date('Y-m-d H:i:s') . " - Aucun nouvel outil détecté.\n";
    exit;
}

echo date('Y-m-d H:i:s') . " - 🆕 " . count($newTools) . " nouveaux outils détectés\n";

// ===============================================
// 2. CRÉER LES ENTRÉES AVEC last_updated_at = -100 JOURS
//    Pour déclencher immédiatement le workflow N8N
// ===============================================

$created = 0;

foreach ($newTools as $tool) {
    
    // Double vérification pour éviter les doublons (race condition)
    $check = $pdo->prepare('SELECT id FROM extra_alternatives_content WHERE slug = ?');
    $check->execute([$tool['slug']]);
    
    if ($check->fetch()) {
        echo "  ⏭️  {$tool['nom']} (déjà existant)\n";
        continue;
    }
    
    // Créer l'entrée avec une date old (NOW() - 100 jours)
    $insert = $pdo->prepare("
        INSERT INTO extra_alternatives_content 
        (slug, tool_id, intro_text, comparison_table_json, tools_details_json, faq_json, is_active, word_count, last_updated_by, updated_at)
        VALUES (?, ?, NULL, NULL, NULL, NULL, 1, 0, 'cron_auto_detect', DATE_SUB(NOW(), INTERVAL 100 DAY))
    ");
    
    $insert->execute([$tool['slug'], $tool['id']]);
    
    echo "  ✅ {$tool['nom']} ({$tool['nb_alternatives_fr']} alternatives) → Créé avec date -100 jours\n";
    $created++;
}

// ===============================================
// 3. LOG FINAL
// ===============================================

$total = $pdo->query('SELECT COUNT(*) FROM extra_alternatives_content')->fetchColumn();

echo "\n📊 Résumé :\n";
echo "  - Créés : {$created}\n";
echo "  - Total pages : {$total}\n";
echo "\n✅ Script terminé\n";

// ===============================================
// 4. OPTIONNEL : Envoyer un email si nouvelles pages créées
// ===============================================

if ($created > 0) {
    $to = $_ENV['CONTACT_EMAIL'];
    if(!$to) {
        echo "⚠️ Email de notification non envoyé : CONTACT_EMAIL non défini.\n";
        exit;
    }
    $subject = "[eXtragone] {$created} nouvelles pages alternatives créées";
    
    $message = "Le script de détection automatique a créé {$created} nouvelles pages alternatives :\n\n";
    
    foreach ($newTools as $tool) {
        $message .= "- {$tool['nom']} ({$tool['nb_alternatives_fr']} alternatives)\n";
    }
    
    $message .= "\nCes pages seront traitées par le workflow N8N lors de la prochaine exécution.";
    
    $headers = "From: eXtragone <no-reply@extrag.one>\r\n";
    mail($to, $subject, $message, $headers);
    
    echo "📧 Email de notification envoyé à {$to}\n";
}
?>