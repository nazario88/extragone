<?php
/**
 * Redirection vers la page de connexion centralisée
 * Cette page redirige automatiquement vers www.extrag.one/connexion
 */

session_start();

// Récupérer le paramètre de redirection si présent
$redirect = $_GET['redirect'] ?? '';

// Construire l'URL de redirection
$redirect_url = 'https://www.extrag.one/connexion';

if ($redirect) {
    // Préfixer avec "projets/" pour indiquer qu'on revient sur projets.extrag.one
    $redirect_url .= '?redirect=projets/' . ltrim($redirect, '/');
}

header('Location: ' . $redirect_url);
exit;