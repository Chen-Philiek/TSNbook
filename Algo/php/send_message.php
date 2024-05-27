<?php
require_once 'config.php';
require_once 'functions.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si l'utilisateur est connecté
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];

        // Récupérer les données du formulaire de message
        $messageContent = $_POST['message_content'];
        $friendId = $_POST['friend_id'];
        $conversationId = $_POST['conversation_id'];

        // Vérifier si les variables sont définies
        if (isset($userId, $messageContent, $friendId, $conversationId)) {
           // Préparer la requête d'insertion
        $insertMessageQuery = "INSERT INTO messages (sender_id, receiver_id, message_content, datetime, conversation_id) VALUES (:userId, :friendId, :messageContent, NOW(), :conversationId)";
    
            // Préparer et exécuter la requête
        $stmt = $pdo->prepare($insertMessageQuery);
        $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":friendId", $friendId, PDO::PARAM_INT);
        $stmt->bindParam(":messageContent", $messageContent, PDO::PARAM_STR);
        $stmt->bindParam(":conversationId", $conversationId, PDO::PARAM_STR);
    
         // Exécuter la requête
    if ($stmt->execute()) {
        // Succès de l'insertion
        echo json_encode(array("success" => true));
    } else {
        // Erreur lors de l'insertion
        echo json_encode(array("success" => false));
    }
} else {
    // Requête non autorisée
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Méthode non autorisée"));
}
    }
}
?>
