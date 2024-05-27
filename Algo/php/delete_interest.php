<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    // Rediriger vers la page de connexion ou afficher un message d'erreur
    header("Location: login.php");
    exit();
}

// Récupérer les informations de l'utilisateur actuel depuis la session
$userToken = $_SESSION['user'];

// Effectuer une requête pour obtenir les informations de l'utilisateur à partir du token
$userQuery = "SELECT * FROM users WHERE token = :token";
$userStmt = $bdd->prepare($userQuery);
$userStmt->bindParam(':token', $userToken);
$userStmt->execute();
$userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['interest_id'])) {
        $interestId = $_POST['interest_id'];
        $userId = $_SESSION['user_id']; // Utilisation de l'ID de l'utilisateur actuellement connecté

        // Supprimer l'entrée de la table user_interests correspondant à l'utilisateur et à l'intérêt spécifié
        $stmt = $bdd->prepare("DELETE FROM user_interests WHERE user_id = :user_id AND interest_id = :interest_id");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':interestId', $interestId, PDO::PARAM_INT);
        
        // Exécution de la requête de suppression
        if ($stmt->execute()) {
            // Répondre avec un statut 200 OK si la suppression est réussie
            http_response_code(200);
        } else {
            // En cas d'échec de la suppression, répondre avec un statut 500 Internal Server Error
            http_response_code(500);
        }
    } else {
        // Répondre avec un statut 400 Bad Request si l'ID de l'intérêt est manquant
        http_response_code(400);
    }
} else {
    // Répondre avec un statut 405 Method Not Allowed si la méthode de requête n'est pas autorisée
    http_response_code(405);
}
?>
