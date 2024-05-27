<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['publication_id'])) {
        $publicationId = $_POST['publication_id'];

        // Vérifier si l'utilisateur est autorisé à supprimer cette publication
        $stmt = $bdd->prepare("SELECT user_id FROM publications WHERE id = :id");
        $stmt->bindParam(':id', $publicationId, PDO::PARAM_INT);
        $stmt->execute();
        $publication = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($publication && $publication['user_id'] == $_SESSION['user_id']) {
            // L'utilisateur est autorisé à supprimer cette publication
            // Supprimer les notifications liées à cette publication
            $stmt = $bdd->prepare("DELETE FROM notifications WHERE publication_id = :id");
            $stmt->bindParam(':id', $publicationId, PDO::PARAM_INT);
            $stmt->execute();

            // Supprimer la publication de la base de données
            $stmt = $bdd->prepare("DELETE FROM publications WHERE id = :id");
            $stmt->bindParam(':id', $publicationId, PDO::PARAM_INT);
            $stmt->execute();
            // Répondre avec un statut 200 OK si la suppression est réussie
            http_response_code(200);
        } else {
            // L'utilisateur n'est pas autorisé à supprimer cette publication
            // Répondre avec un statut 403 Forbidden
            http_response_code(403);
        }
    } else {
        // Répondre avec un statut 400 Bad Request si l'ID de la publication est manquant
        http_response_code(400);
    }
} else {
    // Répondre avec un statut 405 Method Not Allowed si la méthode de requête n'est pas autorisée
    http_response_code(405);
}
?>
