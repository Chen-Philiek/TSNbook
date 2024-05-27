<?php
session_start();
require_once 'config.php'; // Assurez-vous que le fichier de configuration est inclus

if (isset($_SESSION['user_id']) && isset($_POST['publication_id'])) {
    $userId = $_SESSION['user_id'];
    $publicationId = $_POST['publication_id'];

    // Requête pour supprimer le like de la publication par l'utilisateur actuel
    $deleteLikeQuery = "DELETE FROM likes WHERE user_id = :user_id AND publication_id = :publication_id";
    $deleteLikeStmt = $bdd->prepare($deleteLikeQuery);
    $deleteLikeStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $deleteLikeStmt->bindParam(':publication_id', $publicationId, PDO::PARAM_INT);
    
    if ($deleteLikeStmt->execute()) {
        // Retourner le nouveau nombre de likes pour la publication
        $likeCountQuery = "SELECT COUNT(*) AS like_count FROM likes WHERE publication_id = :publication_id";
        $likeCountStmt = $bdd->prepare($likeCountQuery);
        $likeCountStmt->bindParam(':publication_id', $publicationId, PDO::PARAM_INT);
        $likeCountStmt->execute();
        $likeCount = $likeCountStmt->fetchColumn();
        echo $likeCount;
    } else {
        // Gérer les erreurs de suppression du like
        echo "Erreur lors de la suppression du like.";
    }
} else {
    // Gérer les cas où les données requises ne sont pas fournies
    echo "Données manquantes.";
}
?>
