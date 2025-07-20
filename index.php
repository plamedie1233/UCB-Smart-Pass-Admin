<?php
/**
 * Page d'accueil - SmartAccess UCB
 * Redirection vers le tableau de bord ou la page de connexion
 */

require_once 'includes/session.php';

// Redirection selon l'état de connexion
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;