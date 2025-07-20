<?php
/**
 * Fonctions utilitaires pour SmartAccess UCB
 * Fonctions de base de données et logique métier
 */

require_once 'db.php';

/**
 * GESTION DES ÉTUDIANTS
 */

/**
 * Obtenir tous les étudiants avec pagination et recherche
 * @param string $search Terme de recherche
 * @param int $page Page actuelle
 * @param int $limit Nombre d'éléments par page
 * @return array
 */
function getEtudiants($search = '', $page = 1, $limit = 50) {
    global $conn;
    
    $offset = ($page - 1) * $limit;
    $whereClause = '';
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $whereClause = "WHERE (matricule LIKE ? OR nom LIKE ? OR prenom LIKE ? OR email LIKE ?)";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        $types = 'ssss';
    }
    
    $query = "SELECT * FROM etudiants $whereClause ORDER BY nom, prenom LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $result = executeQuery($query, $params, $types);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Obtenir un étudiant par son ID
 * @param int $id
 * @return array|null
 */
function getEtudiantById($id) {
    $result = executeQuery("SELECT * FROM etudiants WHERE id = ?", [$id], 'i');
    return $result->fetch_assoc();
}

/**
 * Obtenir un étudiant par son matricule
 * @param string $matricule
 * @return array|null
 */
function getEtudiantByMatricule($matricule) {
    $result = executeQuery("SELECT * FROM etudiants WHERE matricule = ?", [$matricule], 's');
    return $result->fetch_assoc();
}

/**
 * Ajouter un nouvel étudiant
 * @param array $data
 * @return int ID de l'étudiant créé
 */
function addEtudiant($data) {
    $query = "INSERT INTO etudiants (matricule, nom, prenom, email, faculte, promotion, uid_firebase) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $params = [
        $data['matricule'],
        $data['nom'],
        $data['prenom'],
        $data['email'] ?? null,
        $data['faculte'] ?? null,
        $data['promotion'] ?? null,
        $data['uid_firebase'] ?? null
    ];
    
    executeQuery($query, $params, 'sssssss');
    return getLastInsertId();
}

/**
 * Mettre à jour un étudiant
 * @param int $id
 * @param array $data
 * @return bool
 */
function updateEtudiant($id, $data) {
    $query = "UPDATE etudiants SET matricule = ?, nom = ?, prenom = ?, email = ?, 
              faculte = ?, promotion = ?, uid_firebase = ? WHERE id = ?";
    $params = [
        $data['matricule'],
        $data['nom'],
        $data['prenom'],
        $data['email'] ?? null,
        $data['faculte'] ?? null,
        $data['promotion'] ?? null,
        $data['uid_firebase'] ?? null,
        $id
    ];
    
    executeQuery($query, $params, 'sssssssi');
    return true;
}

/**
 * GESTION DES SALLES
 */

/**
 * Obtenir toutes les salles
 * @return array
 */
function getSalles() {
    $result = executeQuery("SELECT * FROM salles WHERE actif = 1 ORDER BY nom_salle");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Ajouter une nouvelle salle
 * @param array $data
 * @return int
 */
function addSalle($data) {
    $query = "INSERT INTO salles (nom_salle, localisation, description, capacite) VALUES (?, ?, ?, ?)";
    $params = [$data['nom_salle'], $data['localisation'], $data['description'], $data['capacite']];
    
    executeQuery($query, $params, 'sssi');
    return getLastInsertId();
}

/**
 * GESTION DES AUTORISATIONS
 */

/**
 * Obtenir les autorisations avec détails
 * @return array
 */
function getAutorisations() {
    $query = "SELECT a.*, e.matricule, e.nom, e.prenom, s.nom_salle 
              FROM autorisations a
              JOIN etudiants e ON a.etudiant_id = e.id
              JOIN salles s ON a.salle_id = s.id
              WHERE a.actif = 1
              ORDER BY a.date_creation DESC";
    
    $result = executeQuery($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Ajouter une autorisation d'accès
 * @param array $data
 * @return int
 */
function addAutorisation($data) {
    $query = "INSERT INTO autorisations (etudiant_id, salle_id, niveau_acces, date_debut, date_fin) 
              VALUES (?, ?, ?, ?, ?)";
    $params = [
        $data['etudiant_id'],
        $data['salle_id'],
        $data['niveau_acces'] ?? 'LECTURE',
        $data['date_debut'],
        $data['date_fin']
    ];
    
    executeQuery($query, $params, 'iisss');
    return getLastInsertId();
}

/**
 * Attribution groupée d'accès par faculté/promotion
 * @param string $faculte
 * @param string $promotion
 * @param int $salle_id
 * @param string $date_debut
 * @param string $date_fin
 * @return int Nombre d'étudiants affectés
 */
function addAutorisationGroupee($faculte, $promotion, $salle_id, $date_debut, $date_fin) {
    global $conn;
    
    // Récupérer les étudiants de la faculté/promotion
    $etudiants = executeQuery(
        "SELECT id FROM etudiants WHERE faculte = ? AND promotion = ? AND actif = 1",
        [$faculte, $promotion],
        'ss'
    );
    
    $count = 0;
    while ($etudiant = $etudiants->fetch_assoc()) {
        // Vérifier si l'autorisation n'existe pas déjà
        $existing = executeQuery(
            "SELECT id FROM autorisations WHERE etudiant_id = ? AND salle_id = ? AND actif = 1",
            [$etudiant['id'], $salle_id],
            'ii'
        );
        
        if ($existing->num_rows === 0) {
            addAutorisation([
                'etudiant_id' => $etudiant['id'],
                'salle_id' => $salle_id,
                'niveau_acces' => 'LECTURE',
                'date_debut' => $date_debut,
                'date_fin' => $date_fin
            ]);
            $count++;
        }
    }
    
    return $count;
}

/**
 * VÉRIFICATION D'ACCÈS
 */

/**
 * Vérifier si un étudiant a accès à une salle
 * @param string $matricule
 * @param int $salle_id
 * @return array
 */
function verifierAcces($matricule, $salle_id) {
    $query = "SELECT a.*, e.nom, e.prenom, s.nom_salle
              FROM autorisations a
              JOIN etudiants e ON a.etudiant_id = e.id
              JOIN salles s ON a.salle_id = s.id
              WHERE e.matricule = ? AND a.salle_id = ? AND a.actif = 1
              AND NOW() BETWEEN a.date_debut AND a.date_fin";
    
    $result = executeQuery($query, [$matricule, $salle_id], 'si');
    
    if ($result->num_rows > 0) {
        $autorisation = $result->fetch_assoc();
        
        // Enregistrer l'accès dans l'historique
        enregistrerAcces($autorisation['etudiant_id'], $salle_id, $matricule, 'ENTREE', 'AUTORISE');
        
        return [
            'status' => 'ACCES AUTORISE',
            'etudiant' => $autorisation['nom'] . ' ' . $autorisation['prenom'],
            'salle' => $autorisation['nom_salle'],
            'niveau' => $autorisation['niveau_acces']
        ];
    } else {
        // Tenter de trouver l'étudiant pour l'historique
        $etudiant = getEtudiantByMatricule($matricule);
        $etudiant_id = $etudiant ? $etudiant['id'] : null;
        
        enregistrerAcces($etudiant_id, $salle_id, $matricule, 'ENTREE', 'REFUSE');
        
        return [
            'status' => 'ACCES REFUSE',
            'message' => 'Aucune autorisation valide trouvée'
        ];
    }
}

/**
 * HISTORIQUE DES ACCÈS
 */

/**
 * Enregistrer un accès dans l'historique
 * @param int|null $etudiant_id
 * @param int $salle_id
 * @param string $matricule
 * @param string $type_acces
 * @param string $statut
 */
function enregistrerAcces($etudiant_id, $salle_id, $matricule, $type_acces, $statut) {
    $query = "INSERT INTO historiques_acces (etudiant_id, salle_id, matricule_utilise, type_acces, statut, date_entree, ip_address, user_agent)
              VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)";
    $params = [
        $etudiant_id,
        $salle_id,
        $matricule,
        $type_acces,
        $statut,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    executeQuery($query, $params, 'iisssss');
}

/**
 * Obtenir l'historique des accès
 * @param int $limit
 * @return array
 */
function getHistoriqueAcces($limit = 100) {
    $query = "SELECT h.*, e.nom, e.prenom, s.nom_salle
              FROM historiques_acces h
              LEFT JOIN etudiants e ON h.etudiant_id = e.id
              LEFT JOIN salles s ON h.salle_id = s.id
              ORDER BY h.date_entree DESC
              LIMIT ?";
    
    $result = executeQuery($query, [$limit], 'i');
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * UTILITAIRES
 */

/**
 * Formater une date pour l'affichage
 * @param string $date
 * @return string
 */
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

/**
 * Valider un matricule UCB
 * @param string $matricule
 * @return bool
 */
function isValidMatricule($matricule) {
    // Format attendu: XX/YY/ZZZ (ex: 05/23/001)
    return preg_match('/^\d{2}\/\d{2}\/\d{3}$/', $matricule);
}