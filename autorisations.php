<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $etudiant_id = $_POST['etudiant_id'];
    $salle_id = $_POST['salle_id'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];

    $stmt = $conn->prepare("INSERT INTO autorisations (etudiant_id, salle_id, date_debut, date_fin) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $etudiant_id, $salle_id, $date_debut, $date_fin);
    $stmt->execute();
}

$etudiants = getEtudiants($conn);
$salles = getSalles($conn);
$autorisations = getAutorisations($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Autorisations</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h3>Nouvelle Autorisation</h3>
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Étudiant</label>
            <select name="etudiant_id" class="form-control" required>
                <?php while ($e = $etudiants->fetch_assoc()): ?>
                    <option value="<?= $e['id'] ?>"><?= $e['nom'] . ' ' . $e['prenom'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Salle</label>
            <select name="salle_id" class="form-control" required>
                <?php while ($s = $salles->fetch_assoc()): ?>
                    <option value="<?= $s['id'] ?>"><?= $s['nom_salle'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Date début</label>
            <input type="datetime-local" name="date_debut" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label>Date fin</label>
            <input type="datetime-local" name="date_fin" class="form-control" required>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Ajouter</button>
        </div>
    </form>

    <h3>Liste des Autorisations</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Étudiant</th>
                <th>Salle</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($a = $autorisations->fetch_assoc()): ?>
            <tr>
                <td><?= $a['nom'] . ' ' . $a['prenom'] ?></td>
                <td><?= $a['nom_salle'] ?></td>
                <td><?= $a['date_debut'] ?></td>
                <td><?= $a['date_fin'] ?></td>
                <td><?= $a['actif'] ? 'Actif' : 'Inactif' ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
