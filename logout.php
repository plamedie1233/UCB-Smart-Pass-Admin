<?php
/**
 * Déconnexion administrateur
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once 'includes/session.php';

// Déconnexion de l'administrateur
logoutAdmin();

// Redirection vers la page de connexion avec message
header('Location: login.php?message=logout_success');
exit;