<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$historiques = getHistoriqueAcces($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Historique des Accès</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h3>Historique des Accès</h3>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Étudiant</th>
                <th>Salle</th>
                <th>Type d’accès</th>
                <th>Matricule utilisé</th>
                <th>Entrée</th>
                <th>Sortie</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($h = $historiques->fetch_assoc()): ?>
            <tr>
                <td><?= $h['nom'] . ' ' . $h['prenom'] ?></td>
                <td><?= $h['nom_salle'] ?></td>
                <td><?= $h['type_acces'] ?></td>
                <td><?= $h['matricule_utilise'] ?></td>
                <td><?= $h['date_entree'] ?></td>
                <td><?= $h['date_sortie'] ?? '-' ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
