<?php
// Vérifier si la requête est une requête POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifier si l'ID de la notification est présent dans la requête
    if (isset($_POST['notification_id'])) {
        // Récupérer l'ID de la notification à supprimer depuis la requête
        $notificationId = $_POST['notification_id'];

        // Importer le fichier de configuration de la base de données
        require_once 'config.php';

        try {
            // Préparer la requête SQL pour supprimer la notification
            $deleteNotificationQuery = "DELETE FROM notifications WHERE id = :notificationId";
            $deleteNotificationStmt = $bdd->prepare($deleteNotificationQuery);
            $deleteNotificationStmt->bindParam(':notificationId', $notificationId, PDO::PARAM_INT);
            
            // Exécuter la requête
            if ($deleteNotificationStmt->execute()) {
                // La notification a été supprimée avec succès
                http_response_code(200); // OK
            } else {
                // Erreur lors de la suppression de la notification
                http_response_code(500); // Internal Server Error
            }
        } catch (PDOException $e) {
            // Gérer les erreurs de base de données
            http_response_code(500); // Internal Server Error
            echo "Erreur de base de données : " . $e->getMessage();
        }
    } else {
        // ID de la notification manquant dans la requête
        http_response_code(400); // Bad Request
        echo "ID de la notification manquant dans la requête.";
    }
} else {
    // Requête non autorisée
    http_response_code(405); // Method Not Allowed
    echo "Méthode non autorisée. Utilisez la méthode POST.";
}
?>
