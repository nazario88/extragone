<?php
/**
 * Redirection permanente vers le profil centralisé sur extrag.one
 * Ancien : projets.extrag.one/membre/username
 * Nouveau : www.extrag.one/membre/username
 */

$username = $_GET['username'] ?? '';

if (empty($username)) {
    header('Location: https://projets.extrag.one', true, 301);
    exit;
}

// Redirection 301 permanente
header('Location: https://www.extrag.one/membre/' . urlencode($username), true, 301);
exit;