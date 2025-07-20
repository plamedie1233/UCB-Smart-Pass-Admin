<?php
/**
 * API de vérification d'accès
 * Endpoint: GET /api/verifier_acces.php?matricule=XXX&salle_id=YYY
 * SmartAccess UCB - Université Catholique de Bukavu
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestion des requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

try {
    // Vérification de la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Méthode non autorisée. Utilisez GET.');
    }

    // Récupération et validation des paramètres
    $matricule = $_GET['matricule'] ?? '';
    $salle_id = $_GET['salle_id'] ?? '';

    if (empty($matricule)) {
        throw new Exception('Le paramètre "matricule" est requis.');
    }

    if (empty($salle_id) || !is_numeric($salle_id)) {
        throw new Exception('Le paramètre "salle_id" est requis et doit être numérique.');
    }

    // Validation du format du matricule
    if (!isValidMatricule($matricule)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'ACCES REFUSE',
            'message' => 'Format de matricule invalide. Format attendu: XX/YY/ZZZ',
            'matricule' => $matricule,
            'salle_id' => (int)$salle_id,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    // Vérification de l'accès
    $result = verifierAcces($matricule, (int)$salle_id);

    // Ajout d'informations supplémentaires à la réponse
    $result['matricule'] = $matricule;
    $result['salle_id'] = (int)$salle_id;
    $result['timestamp'] = date('Y-m-d H:i:s');

    // Code de statut HTTP selon le résultat
    if ($result['status'] === 'ACCES AUTORISE') {
        http_response_code(200);
    } else {
        http_response_code(403);
    }

    echo json_encode($result, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Gestion des erreurs
    http_response_code(500);
    echo json_encode([
        'status' => 'ERREUR',
        'message' => $e->getMessage(),
        'matricule' => $matricule ?? null,
        'salle_id' => isset($salle_id) ? (int)$salle_id : null,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
    // Log de l'erreur
    error_log("Erreur API vérification accès: " . $e->getMessage());
}
?>