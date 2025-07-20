<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$salles = getSalles($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Liste des Salles</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h3>Liste des Salles</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Localisation</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($s = $salles->fetch_assoc()): ?>
            <tr>
                <td><?= $s['nom_salle'] ?></td>
                <td><?= $s['localisation'] ?></td>
                <td><?= $s['description'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
