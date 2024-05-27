<?php
// Inclure votre fichier de configuration et de fonctions
require_once 'config.php';
require_once 'functions.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['newInterests'])) {
    // Récupérer les nouveaux intérêts saisis par l'utilisateur depuis le formulaire
    $newInterests = htmlspecialchars($_POST['newInterests']);

    // Mettre à jour les intérêts de l'utilisateur dans la base de données
    $token = $_SESSION['user'];
    $updateQuery = "UPDATE users SET interests = :interests WHERE token = :token";
    $updateStmt = $bdd->prepare($updateQuery);
    $updateStmt->bindParam(':interests', $newInterests);
    $updateStmt->bindParam(':token', $token);
    $updateStmt->execute();

    // Rediriger l'utilisateur vers une page de confirmation ou de retour à la page précédente
    header('Location: home.php');
    exit();
} else {
    // Si le formulaire n'a pas été soumis correctement, rediriger l'utilisateur vers une page d'erreur ou de retour à la page précédente
    header('Location: erreur.php');
    exit();
}

?>
