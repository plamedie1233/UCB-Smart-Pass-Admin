<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}



require_once 'includes/db.php';
require_once 'includes/functions.php';


$etudiants = getEtudiants($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Liste des Étudiants</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h3>Liste des Étudiants</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($e = $etudiants->fetch_assoc()): ?>
            <tr>
                <td><?= $e['matricule'] ?></td>
                <td><?= $e['nom'] ?></td>
                <td><?= $e['prenom'] ?></td>
                <td><?= $e['email'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
