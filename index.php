
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SmartAccess UCB</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="p-5 text-center">
    <h1>🔐 SmartAccess UCB</h1>
    <p>Système de contrôle d’accès pour les étudiants de l’UCB.</p>
    <div class="d-grid gap-2 col-6 mx-auto mt-4">
        <a href="dashboard.php" class="btn btn-primary">Tableau de Bord</a>
        <a href="etudiants.php" class="btn btn-secondary">Étudiants</a>
        <a href="salles.php" class="btn btn-secondary">Salles</a>
        <a href="autorisations.php" class="btn btn-secondary">Autorisations</a>
        <a href="historiques.php" class="btn btn-secondary">Historique d'accès</a>
        <a href="ajout_etudiant.php" class="btn btn-success">Ajouter Étudiant</a>
    </div>
</body>
</html>
