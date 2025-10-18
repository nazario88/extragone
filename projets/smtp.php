<?php
/**
 * Page de test SMTP - À SUPPRIMER après validation
 */

// Charger la config
include '../includes/config.php';

// Vérifier que PHPMailer est installé
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . 'includes/PHPMailer/src/PHPMailer.php')) {
    // Installation manuelle
    require __DIR__ . 'includes//PHPMailer/src/Exception.php';
    require __DIR__ . 'includes/PHPMailer/src/PHPMailer.php';
    require __DIR__ . 'includes/PHPMailer/src/SMTP.php';
} else {
    die('❌ PHPMailer n\'est pas installé.<br><br>');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test SMTP</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; margin-top: 0; }
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 15px; 
            border-radius: 4px; 
            border: 1px solid #c3e6cb;
            margin: 15px 0;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 4px; 
            border: 1px solid #f5c6cb;
            margin: 15px 0;
        }
        .info { 
            background: #d1ecf1; 
            color: #0c5460; 
            padding: 15px; 
            border-radius: 4px; 
            border: 1px solid #bee5eb;
            margin: 15px 0;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .config-item {
            margin: 10px 0;
            padding: 10px;
            background: #f9f9f9;
            border-left: 3px solid #335ca3;
        }
        button {
            background: #335ca3;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #2a4a85;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test SMTP - Projets eXtragone</h1>';

// Vérifier la configuration
echo '<h2>📋 Configuration actuelle</h2>';

$config_ok = true;
$required_vars = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS', 'SMTP_FROM_EMAIL'];

foreach ($required_vars as $var) {
    $value = $_ENV[$var] ?? getenv($var) ?? null;
    $is_set = !empty($value);
    
    if (!$is_set) $config_ok = false;
    
    echo '<div class="config-item">';
    echo '<strong>' . $var . ':</strong> ';
    
    if ($var === 'SMTP_PASS') {
        echo $is_set ? '✅ Défini (caché)' : '❌ Non défini';
    } else {
        echo $is_set ? '✅ ' . htmlspecialchars($value) : '❌ Non défini';
    }
    echo '</div>';
}

if (!$config_ok) {
    echo '<div class="error">⚠️ Configuration incomplète ! Vérifie ton fichier .env</div>';
    echo '</div></body></html>';
    exit;
}

// Formulaire de test
if (!isset($_POST['send_test'])) {
    echo '
        <div class="info">
            <strong>ℹ️ Prêt à tester</strong><br>
            Clique sur le bouton pour envoyer un email de test à <strong>jeremie@innospira.fr</strong>
        </div>
        
        <form method="post">
            <button type="submit" name="send_test">📧 Envoyer un email de test</button>
        </form>
    ';
} else {
    // Envoyer l'email de test
    echo '<h2>📤 Envoi en cours...</h2>';
    
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
        
        // Debug (optionnel)
        $mail->SMTPDebug = 2; // Affiche les détails de connexion
        $mail->Debugoutput = function($str, $level) {
            echo "<pre>$str</pre>";
        };
        
        // Expéditeur
        $from_email = $_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL');
        $from_name = $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?? 'Test SMTP';
        $mail->setFrom($from_email, $from_name);
        
        // Destinataire
        $mail->addAddress('jeremie@innospira.fr', 'Jérémie');
        
        // Contenu
        $mail->isHTML(true);
        $mail->Subject = '🧪 Test SMTP - Projets eXtragone';
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #335ca3 0%, #5a7ec4 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
                .success-box { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🧪 Test SMTP Réussi !</h1>
                </div>
                <div class="content">
                    <div class="success-box">
                        <strong>✅ Félicitations !</strong><br>
                        Ton serveur SMTP OVH fonctionne parfaitement.
                    </div>
                    
                    <h2>Informations du test :</h2>
                    <ul>
                        <li><strong>Serveur SMTP :</strong> ' . htmlspecialchars($mail->Host) . '</li>
                        <li><strong>Port :</strong> ' . $mail->Port . '</li>
                        <li><strong>Email expéditeur :</strong> ' . htmlspecialchars($from_email) . '</li>
                        <li><strong>Heure d\'envoi :</strong> ' . date('d/m/Y à H:i:s') . '</li>
                    </ul>
                    
                    <p>Les notifications par email sont maintenant opérationnelles sur <strong>Projets eXtragone</strong> ! 🚀</p>
                </div>
                <div class="footer">
                    Email de test envoyé depuis projets.extrag.one
                </div>
            </div>
        </body>
        </html>';
        
        $mail->AltBody = 'Test SMTP réussi ! Ton serveur SMTP OVH fonctionne parfaitement. Les notifications sont opérationnelles.';
        
        // Envoyer
        $result = $mail->send();
        
        if ($result) {
            echo '<div class="success">
                <strong>✅ Email envoyé avec succès !</strong><br>
                Vérifie ta boîte mail <strong>jeremie@innospira.fr</strong><br>
                (Regarde aussi dans les spams si tu ne le vois pas)
            </div>';
            
            echo '<div class="info">
                <strong>🎉 Étapes suivantes :</strong>
                <ol>
                    <li>Vérifie que tu as bien reçu l\'email</li>
                    <li>Supprime ce fichier <code>test-smtp.php</code> (sécurité)</li>
                    <li>Les notifications automatiques fonctionneront maintenant !</li>
                </ol>
            </div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="error">
            <strong>❌ Erreur lors de l\'envoi</strong><br>
            ' . htmlspecialchars($mail->ErrorInfo) . '
        </div>';
        
        echo '<div class="info">
            <strong>🔧 Solutions possibles :</strong>
            <ul>
                <li>Vérifie que le mot de passe SMTP est correct dans ton .env</li>
                <li>Vérifie que le serveur <code>ssl0.ovh.net</code> est accessible</li>
                <li>Vérifie que le port 587 n\'est pas bloqué par ton firewall</li>
                <li>Vérifie que l\'adresse <code>noreply@extrag.one</code> existe bien sur OVH</li>
            </ul>
        </div>';
    }
    
    echo '<br><a href="test-smtp.php"><button>🔄 Refaire un test</button></a>';
}

echo '
    </div>
</body>
</html>';
?>