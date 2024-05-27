<?php
session_start();
require_once 'config.php'; // Assurez-vous que le fichier de configuration est inclus

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_id']) && isset($_POST['publication_id']) && isset($_POST['comment'])) {
    $userId = $_SESSION['user_id'];
    $publicationId = $_POST['publication_id'];
    $comment = $_POST['comment'];

    // Validation éventuelle du commentaire

    // Insérer le commentaire dans la base de données
    $insertCommentQuery = "INSERT INTO comments (publication_id, user_id, comment, created_at) VALUES (:publicationId, :userId, :comment, NOW())";
    $insertCommentStmt = $bdd->prepare($insertCommentQuery);
    $insertCommentStmt->bindParam(':publicationId', $publicationId, PDO::PARAM_INT);
    $insertCommentStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $insertCommentStmt->bindParam(':comment', $comment, PDO::PARAM_STR);
    
    if ($insertCommentStmt->execute()) {
        // Le commentaire a été ajouté avec succès
        echo "Commentaire ajouté avec succès";
    } else {
        // Erreur lors de l'ajout du commentaire
        echo "Erreur lors de l'ajout du commentaire";
    }
} else {
    // L'utilisateur n'est pas connecté ou des données sont manquantes
    echo "Erreur : données manquantes ou utilisateur non connecté";
}
$insertNotificationQuery = "INSERT INTO notifications (user_id, type, publication_id, seen, created_at) VALUES (:user_id, 'comment', :publication_id, 0, NOW())";
$insertNotificationStmt = $bdd->prepare($insertNotificationQuery);
$insertNotificationStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$insertNotificationStmt->bindParam(':publication_id', $publicationId, PDO::PARAM_INT);
$insertNotificationStmt->execute();
?>
