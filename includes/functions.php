<?php

function getEtudiants($conn) {
    return $conn->query("SELECT * FROM etudiants ORDER BY nom");
}

function getSalles($conn) {
    return $conn->query("SELECT * FROM salles ORDER BY nom_salle");
}

function getAutorisations($conn) {
    return $conn->query("
        SELECT a.*, e.nom, e.prenom, s.nom_salle
        FROM autorisations a
        JOIN etudiants e ON a.etudiant_id = e.id
        JOIN salles s ON a.salle_id = s.id
    ");
}

function getHistoriqueAcces($conn) {
    return $conn->query("
        SELECT h.*, e.nom, e.prenom, s.nom_salle
        FROM historiques_acces h
        LEFT JOIN etudiants e ON h.etudiant_id = e.id
        LEFT JOIN salles s ON h.salle_id = s.id
        ORDER BY h.date_entree DESC
    ");
}
