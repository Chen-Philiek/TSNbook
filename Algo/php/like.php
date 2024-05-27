<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['publication_id']) && isset($_SESSION['user_id'])) {
        $publicationId = $_POST['publication_id'];
        $userId = $_SESSION['user_id'];

        // Vérifier si l'utilisateur n'a pas déjà aimé cette publication
        $checkLikeQuery = "SELECT * FROM likes WHERE publication_id = :publicationId AND user_id = :userId";
        $checkLikeStmt = $bdd->prepare($checkLikeQuery);
        $checkLikeStmt->bindParam(':publicationId', $publicationId, PDO::PARAM_INT);
        $checkLikeStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $checkLikeStmt->execute();

        if ($checkLikeStmt->rowCount() == 0) {
            // L'utilisateur n'a pas encore aimé cette publication, donc ajouter un like
            $insertLikeQuery = "INSERT INTO likes (publication_id, user_id) VALUES (:publicationId, :userId)";
            $insertLikeStmt = $bdd->prepare($insertLikeQuery);
            $insertLikeStmt->bindParam(':publicationId', $publicationId, PDO::PARAM_INT);
            $insertLikeStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $insertLikeStmt->execute();

            // Répondre avec un statut 200 OK pour indiquer que le like a été ajouté avec succès
            http_response_code(200);
        } else {
            // L'utilisateur a déjà aimé cette publication, donc retourner un statut 403 Forbidden
            http_response_code(403);
        }
    } else {
        // Répondre avec un statut 400 Bad Request si l'ID de la publication ou l'ID de l'utilisateur est manquant
        http_response_code(400);
    }
} else {
    // Répondre avec un statut 405 Method Not Allowed si la méthode de requête n'est pas autorisée
    http_response_code(405);
}

// Vérifiez si l'ID de la publication est envoyé via la requête POST
if(isset($_POST['publication_id'])) {
    $publicationId = $_POST['publication_id'];

    // Requête SQL pour compter le nombre de likes pour cette publication
    $likeCountQuery = "SELECT COUNT(*) AS like_count FROM likes WHERE publication_id = :publicationId";
    $likeCountStmt = $bdd->prepare($likeCountQuery);
    $likeCountStmt->bindParam(':publicationId', $publicationId, PDO::PARAM_INT);
    $likeCountStmt->execute();
    $likeCountResult = $likeCountStmt->fetch(PDO::FETCH_ASSOC);

    // Vérifiez si la requête a réussi
    if($likeCountResult) {
        // Récupérer le nombre de likes
        $likeCount = $likeCountResult['like_count'];

        // Envoyer le nombre de likes en réponse
        echo $likeCount;
    } else {
        // En cas d'erreur, envoyer une réponse vide
        echo "";
    }
} else {
    // Si l'ID de la publication n'est pas fourni, renvoyer une réponse vide
    echo "";
}
$insertNotificationQuery = "INSERT INTO notifications (user_id, type, publication_id, seen, created_at) VALUES (:user_id, 'like', :publication_id, 0, NOW())";
$insertNotificationStmt = $bdd->prepare($insertNotificationQuery);
$insertNotificationStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$insertNotificationStmt->bindParam(':publication_id', $publicationId, PDO::PARAM_INT);
$insertNotificationStmt->execute();
?>

