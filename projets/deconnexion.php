<?php
include '../includes/config.php';
include 'includes/auth.php';

// Déconnecter l'utilisateur
logoutUser();

$_SESSION['success'] = 'Tu as été déconnecté avec succès.';

// Rediriger vers l'accueil
header('Location: /');
exit;
?>