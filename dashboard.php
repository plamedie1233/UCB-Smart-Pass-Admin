<?php
/**
 * Tableau de bord administrateur
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Vérification de l'authentification
requireAdmin();

// Récupération des statistiques
try {
    $stats = [
        'etudiants' => $conn->query("SELECT COUNT(*) as total FROM etudiants WHERE actif = 1")->fetch_assoc()['total'],
        'salles' => $conn->query("SELECT COUNT(*) as total FROM salles WHERE actif = 1")->fetch_assoc()['total'],
        'autorisations' => $conn->query("SELECT COUNT(*) as total FROM autorisations WHERE actif = 1")->fetch_assoc()['total'],
        'acces_aujourd_hui' => $conn->query("SELECT COUNT(*) as total FROM historiques_acces WHERE DATE(date_entree) = CURDATE()")->fetch_assoc()['total']
    ];
    
    // Récupération des derniers accès
    $derniers_acces = getHistoriqueAcces(10);
    
    // Récupération des autorisations récentes
    $autorisations_recentes = executeQuery(
        "SELECT a.*, e.matricule, e.nom, e.prenom, s.nom_salle 
         FROM autorisations a
         JOIN etudiants e ON a.etudiant_id = e.id
         JOIN salles s ON a.salle_id = s.id
         WHERE a.actif = 1
         ORDER BY a.date_creation DESC
         LIMIT 5"
    )->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    error_log("Erreur dashboard: " . $e->getMessage());
    $stats = ['etudiants' => 0, 'salles' => 0, 'autorisations' => 0, 'acces_aujourd_hui' => 0];
    $derniers_acces = [];
    $autorisations_recentes = [];
}

$admin = getLoggedAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - SmartAccess UCB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .stat-card.primary { border-left-color: #667eea; }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.info { border-left-color: #17a2b8; }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .icon-primary { background: linear-gradient(135deg, #667eea, #764ba2); }
        .icon-success { background: linear-gradient(135deg, #28a745, #20c997); }
        .icon-warning { background: linear-gradient(135deg, #ffc107, #fd7e14); }
        .icon-info { background: linear-gradient(135deg, #17a2b8, #6f42c1); }
        
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .content-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
        }
        .quick-action-btn {
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-shield-lock-fill me-2"></i>
                SmartAccess UCB
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/etudiants.php">
                            <i class="bi bi-people me-1"></i>Étudiants
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/salles.php">
                            <i class="bi bi-building me-1"></i>Salles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/acces.php">
                            <i class="bi bi-key me-1"></i>Accès
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3 mb-1">Tableau de Bord</h1>
                <p class="text-muted">Vue d'ensemble du système SmartAccess UCB</p>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card primary">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon icon-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= number_format($stats['etudiants']) ?></h3>
                            <p class="text-muted mb-0">Étudiants inscrits</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card success">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon icon-success">
                            <i class="bi bi-building-fill"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= number_format($stats['salles']) ?></h3>
                            <p class="text-muted mb-0">Salles disponibles</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card warning">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon icon-warning">
                            <i class="bi bi-key-fill"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= number_format($stats['autorisations']) ?></h3>
                            <p class="text-muted mb-0">Autorisations actives</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card info">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon icon-info">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= number_format($stats['acces_aujourd_hui']) ?></h3>
                            <p class="text-muted mb-0">Accès aujourd'hui</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="row mb-4">
            <div class="col">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning-fill me-2"></i>
                            Actions Rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="admin/etudiants.php" class="btn btn-primary quick-action-btn w-100">
                                    <i class="bi bi-person-plus me-2"></i>
                                    Ajouter Étudiant
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="admin/salles.php" class="btn btn-success quick-action-btn w-100">
                                    <i class="bi bi-building-add me-2"></i>
                                    Ajouter Salle
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="admin/acces.php" class="btn btn-warning quick-action-btn w-100">
                                    <i class="bi bi-key-fill me-2"></i>
                                    Gérer Accès
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="api/verifier_acces.php" class="btn btn-info quick-action-btn w-100" target="_blank">
                                    <i class="bi bi-search me-2"></i>
                                    Tester API
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="row">
            <!-- Derniers accès -->
            <div class="col-lg-8 mb-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Derniers Accès
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($derniers_acces)): ?>
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-1 mb-3"></i>
                                <p>Aucun accès enregistré pour le moment.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Étudiant</th>
                                            <th>Salle</th>
                                            <th>Statut</th>
                                            <th>Date/Heure</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($derniers_acces as $acces): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">
                                                            <?= htmlspecialchars($acces['nom'] . ' ' . $acces['prenom']) ?>
                                                        </div>
                                                        <small class="text-muted"><?= htmlspecialchars($acces['matricule_utilise']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($acces['nom_salle'] ?? 'Salle inconnue') ?></td>
                                            <td>
                                                <?php if ($acces['statut'] === 'AUTORISE'): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>Autorisé
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-x-circle me-1"></i>Refusé
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?= formatDate($acces['date_entree']) ?></small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Autorisations récentes -->
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-key me-2"></i>
                            Autorisations Récentes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($autorisations_recentes)): ?>
                            <div class="text-center text-muted">
                                <i class="bi bi-key fs-1 mb-3"></i>
                                <p>Aucune autorisation récente.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($autorisations_recentes as $auth): ?>
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="bi bi-person text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($auth['nom'] . ' ' . $auth['prenom']) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($auth['nom_salle']) ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <?= formatDate($auth['date_creation']) ?>
                                    </small>
                                </div>
                                <span class="badge bg-success"><?= htmlspecialchars($auth['niveau_acces']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>