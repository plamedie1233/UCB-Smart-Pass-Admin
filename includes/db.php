<?php
/**
 * Configuration et connexion à la base de données MySQL
 * SmartAccess UCB - Université Catholique de Bukavu
 */

// Configuration de la base de données
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'smartaccess_ucb',
    'charset' => 'utf8mb4'
];

try {
    // Création de la connexion MySQLi
    $conn = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database']
    );

    // Vérification de la connexion
    if ($conn->connect_error) {
        throw new Exception("Erreur de connexion à la base de données: " . $conn->connect_error);
    }

    // Configuration du charset
    $conn->set_charset($config['charset']);

    // Configuration des options MySQL
    $conn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);

} catch (Exception $e) {
    // Log de l'erreur (en production, utiliser un système de log approprié)
    error_log("Erreur DB: " . $e->getMessage());
    
    // Affichage d'une erreur générique en production
    if (getenv('APP_ENV') === 'production') {
        die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
    } else {
        die("Erreur de développement: " . $e->getMessage());
    }
}

/**
 * Fonction utilitaire pour exécuter des requêtes préparées
 * @param string $query La requête SQL avec des placeholders
 * @param array $params Les paramètres à lier
 * @param string $types Les types des paramètres (i=integer, s=string, d=double, b=blob)
 * @return mysqli_result|bool
 */
function executeQuery($query, $params = [], $types = '') {
    global $conn;
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $result = $stmt->execute();
    if (!$result) {
        throw new Exception("Erreur d'exécution de la requête: " . $stmt->error);
    }
    
    return $stmt->get_result();
}

/**
 * Fonction pour obtenir le dernier ID inséré
 * @return int
 */
function getLastInsertId() {
    global $conn;
    return $conn->insert_id;
}

/**
 * Fonction pour échapper les chaînes (sécurité)
 * @param string $string
 * @return string
 */
function escapeString($string) {
    global $conn;
    return $conn->real_escape_string($string);
}