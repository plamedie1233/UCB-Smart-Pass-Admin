<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';

$countEtudiants = $conn->query("SELECT COUNT(*) AS total FROM etudiants")->fetch_assoc()['total'];
$countSalles = $conn->query("SELECT COUNT(*) AS total FROM salles")->fetch_assoc()['total'];
$countAutorisations = $conn->query("SELECT COUNT(*) AS total FROM autorisations")->fetch_assoc()['total'];
$countAcces = $conn->query("SELECT COUNT(*) AS total FROM historiques_acces")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h2>SmartAccess UCB – Tableau de Bord</h2>
    <a href="logout.php" class="btn btn-danger float-end">Se déconnecter</a>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-light p-3">
                <h4>👨‍🎓 Étudiants</h4>
                <p><?= $countEtudiants ?> inscrits</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light p-3">
                <h4>🏢 Salles</h4>
                <p><?= $countSalles ?> salles</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light p-3">
                <h4>🔑 Autorisations</h4>
                <p><?= $countAutorisations ?> actives</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light p-3">
                <h4>📜 Accès</h4>
                <p><?= $countAcces ?> enregistrements</p>
            </div>
        </div>
    </div>
</body>
</html>
