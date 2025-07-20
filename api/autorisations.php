<?php
/**
 * API REST pour la gestion des autorisations d'accès
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
            handleGetAutorisations();
            break;
            
        case 'POST':
            handleCreateAutorisation();
            break;
            
        case 'DELETE':
            handleDeleteAutorisation();
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
 * Récupérer la liste des autorisations
 */
function handleGetAutorisations() {
    $autorisations = getAutorisations();
    
    echo json_encode([
        'success' => true,
        'autorisations' => $autorisations,
        'count' => count($autorisations)
    ]);
}

/**
 * Créer une nouvelle autorisation (individuelle ou groupée)
 */
function handleCreateAutorisation() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Données JSON invalides');
    }
    
    $type = $input['type'] ?? 'individual';
    
    if ($type === 'individual') {
        handleIndividualAutorisation($input);
    } elseif ($type === 'group') {
        handleGroupAutorisation($input);
    } else {
        throw new Exception('Type d\'autorisation non supporté');
    }
}

/**
 * Gérer l'attribution individuelle
 */
function handleIndividualAutorisation($input) {
    // Validation des champs requis
    $required = ['etudiant_id', 'salle_id', 'date_debut', 'date_fin'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("Le champ '$field' est requis");
        }
    }
    
    // Validation des dates
    $dateDebut = new DateTime($input['date_debut']);
    $dateFin = new DateTime($input['date_fin']);
    
    if ($dateFin <= $dateDebut) {
        throw new Exception('La date de fin doit être postérieure à la date de début');
    }
    
    // Vérification de l'existence de l'étudiant et de la salle
    if (!getEtudiantById($input['etudiant_id'])) {
        throw new Exception('Étudiant introuvable');
    }
    
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM salles WHERE id = ? AND actif = 1");
    $stmt->bind_param("i", $input['salle_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Salle introuvable');
    }
    
    // Vérification des autorisations existantes
    $stmt = $conn->prepare("
        SELECT id FROM autorisations 
        WHERE etudiant_id = ? AND salle_id = ? AND actif = 1
        AND (
            (date_debut <= ? AND date_fin >= ?) OR
            (date_debut <= ? AND date_fin >= ?) OR
            (date_debut >= ? AND date_fin <= ?)
        )
    ");
    $stmt->bind_param("iissssss", 
        $input['etudiant_id'], 
        $input['salle_id'],
        $input['date_debut'], $input['date_debut'],
        $input['date_fin'], $input['date_fin'],
        $input['date_debut'], $input['date_fin']
    );
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Une autorisation existe déjà pour cette période');
    }
    
    // Création de l'autorisation
    $autorisationId = addAutorisation($input);
    
    echo json_encode([
        'success' => true,
        'message' => 'Autorisation individuelle créée avec succès',
        'autorisation_id' => $autorisationId
    ]);
}

/**
 * Gérer l'attribution groupée
 */
function handleGroupAutorisation($input) {
    // Validation des champs requis
    $required = ['faculte', 'promotion', 'salle_id', 'date_debut', 'date_fin'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("Le champ '$field' est requis");
        }
    }
    
    // Validation des dates
    $dateDebut = new DateTime($input['date_debut']);
    $dateFin = new DateTime($input['date_fin']);
    
    if ($dateFin <= $dateDebut) {
        throw new Exception('La date de fin doit être postérieure à la date de début');
    }
    
    // Vérification de l'existence de la salle
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM salles WHERE id = ? AND actif = 1");
    $stmt->bind_param("i", $input['salle_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Salle introuvable');
    }
    
    // Attribution groupée
    $count = addAutorisationGroupee(
        $input['faculte'],
        $input['promotion'],
        $input['salle_id'],
        $input['date_debut'],
        $input['date_fin']
    );
    
    if ($count === 0) {
        throw new Exception('Aucun étudiant trouvé pour cette faculté/promotion');
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Attribution groupée réussie pour $count étudiant(s)",
        'count' => $count
    ]);
}

/**
 * Supprimer/révoquer une autorisation
 */
function handleDeleteAutorisation() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['id'])) {
        throw new Exception('ID autorisation requis');
    }
    
    $autorisationId = (int)$input['id'];
    
    // Vérification de l'existence
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM autorisations WHERE id = ? AND actif = 1");
    $stmt->bind_param("i", $autorisationId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Autorisation introuvable');
    }
    
    // Révocation (soft delete)
    $stmt = $conn->prepare("UPDATE autorisations SET actif = 0 WHERE id = ?");
    $stmt->bind_param("i", $autorisationId);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Autorisation révoquée avec succès'
    ]);
}
?>