<?php
/**
 * Système d'envoi d'emails pour projets.extrag.one
 */

// Vérifier que PHPMailer est installé
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/PHPMailer/src/PHPMailer.php')) {
    // Installation manuelle
    require __DIR__ . '/PHPMailer/src/Exception.php';
    require __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer/src/SMTP.php';
} else {
    die('❌ PHPMailer n\'est pas installé.<br><br>');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envoie un email via SMTP
 */
function sendEmail($to, $to_name, $subject, $html_body, $text_body = null) {
    global $pdo;
    
    // Vérifier que SMTP est configuré
    if (empty($_ENV['SMTP_HOST']) && empty(getenv('SMTP_HOST'))) {
        error_log('SMTP non configuré - Email non envoyé: ' . $subject);
        return false;
    }
    
    try {
        $mail = new PHPMailer(true);
        
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'] ?? getenv('SMTP_USER');
        $mail->Password = $_ENV['SMTP_PASS'] ?? getenv('SMTP_PASS');
        $mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION'] ?? getenv('SMTP_ENCRYPTION') ?? 'tls';
        $mail->Port = $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?? 587;
        $mail->CharSet = 'UTF-8';
        
        // Expéditeur
        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL'), $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME'));
        
        // Destinataire
        $mail->addAddress($to, $to_name);
        
        // Contenu
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_body;
        $mail->AltBody = $text_body ?: strip_tags($html_body);
        
        // Envoyer
        $result = $mail->send();
        
        // Log de succès
        logEmailSent($to, $subject, 'sent');
        
        return true;
        
    } catch (Exception $e) {
        error_log('Erreur envoi email: ' . $mail->ErrorInfo);
        logEmailSent($to, $subject, 'failed', $mail->ErrorInfo);
        return false;
    }
}

/**
 * Log les emails envoyés
 */
function logEmailSent($to, $subject, $status, $error = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO extra_proj_logs (action, user_ip, details) 
            VALUES (?, ?, ?)
        ');
        
        $details = json_encode([
            'to' => $to,
            'subject' => $subject,
            'status' => $status,
            'error' => $error
        ]);
        
        $stmt->execute(['email_sent', getIP(), $details]);
    } catch (Exception $e) {
        error_log('Erreur log email: ' . $e->getMessage());
    }
}

/**
 * Template HTML de base pour les emails
 */
function getEmailTemplate($content, $title = '') {
    return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #335ca3 0%, #5a7ec4 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px 20px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #335ca3;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e0e0e0;
        }
        .footer a {
            color: #335ca3;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . ($title ?: 'Projets eXtragone') . '</h1>
        </div>
        <div class="content">
            ' . $content . '
        </div>
        <div class="footer">
            <p>
                <a href="https://projets.extrag.one">Projets eXtragone</a> | 
                <a href="https://www.extrag.one">eXtrag.one</a> | 
                <a href="https://projets.extrag.one/reglages">Gérer mes notifications</a>
            </p>
            <p style="margin-top: 10px; color: #999;">
                Tu reçois cet email car tu as un compte sur Projets eXtragone.
            </p>
        </div>
    </div>
</body>
</html>';
}

/**
 * Envoie un email de bienvenue après inscription
 */
function sendWelcomeEmail($user) {
    if (!$user['email']) return false;
    
    $content = '
        <h2>Bienvenue ' . $user['display_name'] . ' ! 🎉</h2>
        <p>Ton compte a été créé avec succès sur <strong>Projets eXtragone</strong>.</p>
        <p>Tu peux maintenant :</p>
        <ul>
            <li>✨ Soumettre tes projets pour obtenir une review détaillée</li>
            <li>💬 Commenter et échanger avec la communauté</li>
            <li>🌟 Candidater pour devenir reviewer</li>
        </ul>
        <p style="text-align: center;">
            <a href="https://projets.extrag.one/soumettre" class="button">Soumettre mon premier projet</a>
        </p>
        <p>À bientôt sur la plateforme !</p>
    ';
    
    $html = getEmailTemplate($content, 'Bienvenue sur Projets eXtragone');
    $subject = '🎉 Bienvenue sur Projets eXtragone !';
    
    return sendEmail($user['email'], $user['display_name'], $subject, $html);
}

/**
 * Notifie l'utilisateur que son projet a été publié
 */
function sendProjectPublishedEmail($project, $user) {
    if (!$user['email'] || !$user['email_notif_project_published']) return false;
    
    $content = '
        <h2>Ton projet a été publié ! 🚀</h2>
        <p>Bonjour ' . $user['display_name'] . ',</p>
        <p>Bonne nouvelle ! Ton projet <strong>' . $project['title'] . '</strong> vient d\'être publié avec sa review.</p>
        <p>La communauté peut maintenant le découvrir et le commenter.</p>
        <p style="text-align: center;">
            <a href="https://projets.extrag.one/projet/' . $project['slug'] . '" class="button">Voir mon projet</a>
        </p>
        <p>Merci d\'avoir partagé ton travail avec la communauté ! 💙</p>
    ';
    
    $html = getEmailTemplate($content, 'Ton projet est en ligne !');
    $subject = '🚀 Ton projet "' . $project['title'] . '" est publié !';
    
    return sendEmail($user['email'], $user['display_name'], $subject, $html);
}

/**
 * Notifie l'utilisateur d'un nouveau commentaire sur son projet
 */
function sendNewCommentEmail($project, $comment, $project_owner, $commenter) {
    if (!$project_owner['email'] || !$project_owner['email_notif_new_comment']) return false;
    
    // Ne pas notifier si l'auteur commente son propre projet
    if ($project_owner['id'] == $commenter['id']) return false;
    
    $content = '
        <h2>Nouveau commentaire sur ton projet 💬</h2>
        <p>Bonjour ' . $project_owner['display_name'] . ',</p>
        <p><strong>' . $commenter['display_name'] . '</strong> a commenté ton projet <strong>' . $project['title'] . '</strong> :</p>
        <div style="background-color: #f8f9fa; border-left: 4px solid #335ca3; padding: 15px; margin: 20px 0;">
            <p style="margin: 0;">' . nl2br(substr($comment['content'], 0, 200)) . (strlen($comment['content']) > 200 ? '...' : '') . '</p>
        </div>
        <p style="text-align: center;">
            <a href="https://projets.extrag.one/projet/' . $project['slug'] . '#comment-' . $comment['id'] . '" class="button">Voir le commentaire</a>
        </p>
    ';
    
    $html = getEmailTemplate($content, 'Nouveau commentaire');
    $subject = '💬 Nouveau commentaire sur "' . $project['title'] . '"';
    
    return sendEmail($project_owner['email'], $project_owner['display_name'], $subject, $html);
}

/**
 * Notifie les reviewers qu'un nouveau projet est disponible
 */
function sendNewProjectToReviewersEmail($project, $project_author) {
    global $pdo;
    
    // Récupérer tous les reviewers qui veulent être notifiés
    $stmt = $pdo->prepare('
        SELECT * FROM extra_proj_users 
        WHERE role IN ("reviewer", "admin") 
        AND email_notif_new_review_available = 1 
        AND is_active = 1
    ');
    $stmt->execute();
    $reviewers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($reviewers as $reviewer) {
        $content = '
            <h2>Nouveau projet à reviewer ! 📝</h2>
            <p>Bonjour ' . $reviewer['display_name'] . ',</p>
            <p>Un nouveau projet vient d\'être soumis et attend une review :</p>
            <h3 style="color: #335ca3;">' . $project['title'] . '</h3>
            <p><em>Par ' . $project_author['display_name'] . '</em></p>
            <p>' . substr($project['short_description'], 0, 150) . '...</p>
            <p style="text-align: center;">
                <a href="https://projets.extrag.one/reviewer/dashboard" class="button">Voir le dashboard</a>
            </p>
            <p style="font-size: 12px; color: #666;">Premier arrivé, premier servi ! 🏃</p>
        ';
        
        $html = getEmailTemplate($content, 'Nouveau projet disponible');
        $subject = '📝 Nouveau projet à reviewer : "' . $project['title'] . '"';
        
        sendEmail($reviewer['email'], $reviewer['display_name'], $subject, $html);
    }
}

/**
 * Notifie l'équipe qu'une nouvelle candidature reviewer est arrivée
 */
function sendReviewerApplicationEmail($user, $motivation) {
    // Email à l'équipe (toi)
    $admin_email = 'jeremie@innospira.fr'; // Email à changer
    
    $content = '
        <h2>Nouvelle candidature reviewer 🌟</h2>
        <p><strong>' . htmlspecialchars($user['display_name']) . '</strong> (@' . htmlspecialchars($user['username']) . ') souhaite devenir reviewer.</p>
        <h3>Motivation :</h3>
        <div style="background-color: #f8f9fa; border-left: 4px solid #335ca3; padding: 15px; margin: 20px 0;">
            <p>' . nl2br(htmlspecialchars($motivation)) . '</p>
        </div>
        <p><strong>Email :</strong> ' . htmlspecialchars($user['email']) . '</p>
        <p style="text-align: center;">
            <a href="https://projets.extrag.one/admin/reviewer-requests" class="button">Gérer les candidatures</a>
        </p>
    ';
    
    $html = getEmailTemplate($content, 'Nouvelle candidature');
    $subject = '🌟 Nouvelle candidature reviewer : ' . $user['display_name'];
    
    return sendEmail($admin_email, 'Admin', $subject, $html);
}

/**
 * Notifie l'utilisateur que sa candidature a été acceptée
 */
function sendReviewerApprovedEmail($user) {
    if (!$user['email']) return false;
    
    $content = '
        <h2>Félicitations ! 🎉</h2>
        <p>Bonjour ' . htmlspecialchars($user['display_name']) . ',</p>
        <p>Excellente nouvelle ! Ta candidature pour devenir <strong>reviewer</strong> a été acceptée.</p>
        <p>Tu peux maintenant accéder au dashboard reviewer et commencer à analyser les projets de la communauté.</p>
        <p style="text-align: center;">
            <a href="https://projets.extrag.one/reviewer/dashboard" class="button">Accéder au dashboard</a>
        </p>
        <h3>En tant que reviewer, tu peux :</h3>
        <ul>
            <li>✅ Prendre en charge les projets qui t\'intéressent</li>
            <li>✅ Rédiger des reviews détaillées et constructives</li>
            <li>✅ Aider la communauté à découvrir les meilleurs projets</li>
            <li>✅ Apparaître dans le classement des top reviewers</li>
        </ul>
        <p>Bienvenue dans l\'équipe ! 🌟</p>
    ';
    
    $html = getEmailTemplate($content, 'Bienvenue reviewer !');
    $subject = '🎉 Ta candidature reviewer a été acceptée !';
    
    return sendEmail($user['email'], $user['display_name'], $subject, $html);
}

/**
 * Notifie l'utilisateur que sa candidature a été refusée
 */
function sendReviewerRejectedEmail($user) {
    if (!$user['email']) return false;
    
    $content = '
        <h2>Au sujet de ta candidature</h2>
        <p>Bonjour ' . htmlspecialchars($user['display_name']) . ',</p>
        <p>Merci pour ton intérêt à rejoindre l\'équipe des reviewers de Projets eXtragone.</p>
        <p>Malheureusement, nous ne pouvons pas donner suite à ta candidature pour le moment.</p>
        <p>N\'hésite pas à continuer à participer activement à la communauté en :</p>
        <ul>
            <li>💬 Commentant les projets</li>
            <li>🚀 Partageant tes propres projets</li>
            <li>🤝 Aidant les autres membres</li>
        </ul>
        <p>Tu pourras candidater à nouveau dans le futur !</p>
        <p>À bientôt sur la plateforme.</p>
    ';
    
    $html = getEmailTemplate($content, 'Candidature reviewer');
    $subject = 'Au sujet de ta candidature reviewer';
    
    return sendEmail($user['email'], $user['display_name'], $subject, $html);
}
?>