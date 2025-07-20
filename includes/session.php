<?php
/**
 * Gestion des sessions pour SmartAccess UCB
 * Fonctions utilitaires pour l'authentification et la sécurité
 */

// Démarrage sécurisé de la session
if (session_status() === PHP_SESSION_NONE) {
    // Configuration sécurisée de la session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    
    session_start();
}

/**
 * Vérifier si l'utilisateur est connecté en tant qu'admin
 * @return bool
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin']) && !empty($_SESSION['admin']);
}

/**
 * Obtenir les informations de l'admin connecté
 * @return array|null
 */
function getLoggedAdmin() {
    return $_SESSION['admin'] ?? null;
}

/**
 * Connecter un administrateur
 * @param array $adminData Les données de l'administrateur
 */
function loginAdmin($adminData) {
    $_SESSION['admin'] = [
        'id' => $adminData['id'],
        'username' => $adminData['username'],
        'email' => $adminData['email'],
        'nom' => $adminData['nom'],
        'prenom' => $adminData['prenom'],
        'login_time' => time(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Régénération de l'ID de session pour la sécurité
    session_regenerate_id(true);
}

/**
 * Déconnecter l'administrateur
 */
function logoutAdmin() {
    // Destruction de toutes les variables de session
    $_SESSION = [];
    
    // Destruction du cookie de session
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destruction de la session
    session_destroy();
}

/**
 * Rediriger vers la page de connexion si non authentifié
 * @param string $redirectUrl URL de redirection après connexion
 */
function requireAdmin($redirectUrl = null) {
    if (!isAdminLoggedIn()) {
        if ($redirectUrl) {
            $_SESSION['redirect_after_login'] = $redirectUrl;
        }
        header('Location: /login.php');
        exit;
    }
}

/**
 * Générer un token CSRF
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier un token CSRF
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Vérifier la validité de la session (timeout, etc.)
 * @param int $maxLifetime Durée maximale en secondes (défaut: 2 heures)
 * @return bool
 */
function isSessionValid($maxLifetime = 7200) {
    if (!isAdminLoggedIn()) {
        return false;
    }
    
    $admin = getLoggedAdmin();
    $loginTime = $admin['login_time'] ?? 0;
    
    // Vérification du timeout
    if (time() - $loginTime > $maxLifetime) {
        logoutAdmin();
        return false;
    }
    
    return true;
}

/**
 * Mettre à jour le timestamp de dernière activité
 */
function updateLastActivity() {
    if (isAdminLoggedIn()) {
        $_SESSION['admin']['last_activity'] = time();
    }
}

// Mise à jour automatique de la dernière activité
updateLastActivity();