<?php
/**
 * API REST pour la gestion des salles
 * SmartAccess UCB - Université Catholique de Bukavu
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestion des requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Vérification de l'authentification pour toutes les opérations
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetSalles();
            break;
            
        case 'POST':
            handleCreateSalle();
            break;
            
        case 'PUT':
            handleUpdateSalle();
            break;
            
        case 'DELETE':
            handleDeleteSalle();
            break;
            
        default:
            throw new Exception('Méthode non supportée');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Récupérer la liste des salles
 */
function handleGetSalles() {
    $salles = getSalles();
    
    echo json_encode([
        'success' => true,
        'salles' => $salles,
        'count' => count($salles)
    ]);
}

/**
 * Créer une nouvelle salle
 */
function handleCreateSalle() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Données JSON invalides');
    }
    
    // Validation des champs requis
    if (empty($input['nom_salle'])) {
        throw new Exception('Le nom de la salle est requis');
    }
    
    // Vérification de l'unicité du nom
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM salles WHERE nom_salle = ? AND actif = 1");
    $stmt->bind_param("s", $input['nom_salle']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Une salle avec ce nom existe déjà');
    }
    
    // Création de la salle
    $salleId = addSalle($input);
    
    echo json_encode([
        'success' => true,
        'message' => 'Salle créée avec succès',
        'salle_id' => $salleId
    ]);
}

/**
 * Mettre à jour une salle
 */
function handleUpdateSalle() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['id'])) {
        throw new Exception('ID salle requis');
    }
    
    $salleId = (int)$input['id'];
    
    // Vérification de l'existence de la salle
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM salles WHERE id = ? AND actif = 1");
    $stmt->bind_param("i", $salleId);
    $stmt->execute();
    $salle = $stmt->get_result()->fetch_assoc();
    
    if (!$salle) {
        throw new Exception('Salle introuvable');
    }
    
    // Validation du nom si modifié
    if (!empty($input['nom_salle']) && $input['nom_salle'] !== $salle['nom_salle']) {
        $stmt = $conn->prepare("SELECT id FROM salles WHERE nom_salle = ? AND id != ? AND actif = 1");
        $stmt->bind_param("si", $input['nom_salle'], $salleId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('Une autre salle avec ce nom existe déjà');
        }
    }
    
    // Mise à jour
    $query = "UPDATE salles SET nom_salle = ?, localisation = ?, description = ?, capacite = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssii", 
        $input['nom_salle'], 
        $input['localisation'], 
        $input['description'], 
        $input['capacite'], 
        $salleId
    );
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Salle mise à jour avec succès'
    ]);
}

/**
 * Supprimer une salle
 */
function handleDeleteSalle() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['id'])) {
        throw new Exception('ID salle requis');
    }
    
    $salleId = (int)$input['id'];
    
    // Vérification de l'existence
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM salles WHERE id = ? AND actif = 1");
    $stmt->bind_param("i", $salleId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Salle introuvable');
    }
    
    // Vérification des autorisations liées
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM autorisations WHERE salle_id = ? AND actif = 1");
    $stmt->bind_param("i", $salleId);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];
    
    if ($count > 0) {
        throw new Exception("Impossible de supprimer cette salle car elle a $count autorisation(s) active(s)");
    }
    
    // Suppression (soft delete)
    $stmt = $conn->prepare("UPDATE salles SET actif = 0 WHERE id = ?");
    $stmt->bind_param("i", $salleId);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Salle supprimée avec succès'
    ]);
}
?>