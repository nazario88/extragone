<?php
include 'includes/config.php';
include 'includes/auth.php';

// Déconnecter l'utilisateur
logoutUser();

$_SESSION['success'] = 'Tu as été déconnecté avec succès.';

// Déterminer la redirection en fonction du referer
$referer = $_SERVER['HTTP_REFERER'] ?? '';

if (str_contains($referer, 'projets.extrag.one')) {
    header('Location: https://projets.extrag.one');
} else {
    header('Location: https://www.extrag.one');
}
exit;