<?php
/**
 * API REST pour la gestion des étudiants
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
            handleGetStudents();
            break;
            
        case 'POST':
            handleCreateStudent();
            break;
            
        case 'PUT':
            handleUpdateStudent();
            break;
            
        case 'DELETE':
            handleDeleteStudent();
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
 * Récupérer la liste des étudiants
 */
function handleGetStudents() {
    $search = $_GET['search'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 50);
    
    $students = getEtudiants($search, $page, $limit);
    
    echo json_encode([
        'success' => true,
        'students' => $students,
        'count' => count($students)
    ]);
}

/**
 * Créer un nouvel étudiant
 */
function handleCreateStudent() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Données JSON invalides');
    }
    
    // Validation des champs requis
    $required = ['matricule', 'nom', 'prenom'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("Le champ '$field' est requis");
        }
    }
    
    // Validation du format du matricule
    if (!isValidMatricule($input['matricule'])) {
        throw new Exception('Format de matricule invalide. Format attendu: XX/YY/ZZZ');
    }
    
    // Vérification de l'unicité du matricule
    if (getEtudiantByMatricule($input['matricule'])) {
        throw new Exception('Un étudiant avec ce matricule existe déjà');
    }
    
    // Création de l'étudiant
    $studentId = addEtudiant($input);
    
    echo json_encode([
        'success' => true,
        'message' => 'Étudiant créé avec succès',
        'student_id' => $studentId
    ]);
}

/**
 * Mettre à jour un étudiant
 */
function handleUpdateStudent() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['id'])) {
        throw new Exception('ID étudiant requis');
    }
    
    $studentId = (int)$input['id'];
    
    // Vérification de l'existence de l'étudiant
    if (!getEtudiantById($studentId)) {
        throw new Exception('Étudiant introuvable');
    }
    
    // Validation du matricule si modifié
    if (!empty($input['matricule'])) {
        if (!isValidMatricule($input['matricule'])) {
            throw new Exception('Format de matricule invalide');
        }
        
        // Vérification de l'unicité (sauf pour l'étudiant actuel)
        $existing = getEtudiantByMatricule($input['matricule']);
        if ($existing && $existing['id'] != $studentId) {
            throw new Exception('Un autre étudiant avec ce matricule existe déjà');
        }
    }
    
    // Mise à jour
    updateEtudiant($studentId, $input);
    
    echo json_encode([
        'success' => true,
        'message' => 'Étudiant mis à jour avec succès'
    ]);
}

/**
 * Supprimer un étudiant
 */
function handleDeleteStudent() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['id'])) {
        throw new Exception('ID étudiant requis');
    }
    
    $studentId = (int)$input['id'];
    
    // Vérification de l'existence
    if (!getEtudiantById($studentId)) {
        throw new Exception('Étudiant introuvable');
    }
    
    // Suppression (soft delete en mettant actif = 0)
    global $conn;
    $stmt = $conn->prepare("UPDATE etudiants SET actif = 0 WHERE id = ?");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Étudiant supprimé avec succès'
    ]);
}
?>