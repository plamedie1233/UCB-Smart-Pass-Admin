<?php
session_start();
require_once 'includes/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($admin = $result->fetch_assoc()) {
        $_SESSION['admin'] = $admin;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Connexion Admin</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="p-5">
    <div class="container col-md-4 offset-md-4">
        <h3 class="mb-4 text-center">Connexion Admin</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Se connecter</button>
        </form>
    </div>
</body>
</html>
