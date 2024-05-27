<?php
require_once 'config.php';
require_once 'functions.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si l'utilisateur est connecté
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $friendId = $_POST['friend_id']; // Assurez-vous de valider et de nettoyer les données reçues

        // Log pour débogage
        error_log("User ID: $userId, Friend ID: $friendId");

        // Vérifier si une conversation existe entre les deux utilisateurs
        $checkConversationQuery = "SELECT conversation_id FROM conversations
                                    WHERE (user1_id = :userId AND user2_id = :friendId)
                                    OR (user1_id = :friendId AND user2_id = :userId)";
        $checkConversationStmt = $bdd->prepare($checkConversationQuery);
        $checkConversationStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $checkConversationStmt->bindParam(':friendId', $friendId, PDO::PARAM_INT);
        $checkConversationStmt->execute();
        $conversation = $checkConversationStmt->fetch(PDO::FETCH_ASSOC);

        // Si aucune conversation n'existe, créer une nouvelle conversation
        if (!$conversation) {
            $conversationId = createConversation($userId, $friendId, $bdd);
        } else {
            $conversationId = $conversation['conversation_id'];
        }

        // Log pour débogage
        error_log("Conversation ID: $conversationId");

        // Mettre à jour la table des messages avec l'ID de conversation approprié
        $updateMessagesQuery = "UPDATE messages SET conversation_id = :conversationId
                                WHERE (sender_id = :userId AND receiver_id = :friendId)
                                OR (sender_id = :friendId AND receiver_id = :userId)";
        $updateMessagesStmt = $bdd->prepare($updateMessagesQuery);
        $updateMessagesStmt->bindParam(':conversationId', $conversationId, PDO::PARAM_STR);
        $updateMessagesStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $updateMessagesStmt->bindParam(':friendId', $friendId, PDO::PARAM_INT);
        $updateMessagesStmt->execute();

        // Insérer le message initial dans la table des messages seulement s'il n'existe pas déjà
        $checkInitialMessageQuery = "SELECT * FROM messages
        WHERE conversation_id = :conversationId
        AND message_content = 'Conversation started'";
        $checkInitialMessageStmt = $bdd->prepare($checkInitialMessageQuery);
        $checkInitialMessageStmt->bindParam(':conversationId', $conversationId, PDO::PARAM_STR);
        $checkInitialMessageStmt->execute();
        $initialMessage = $checkInitialMessageStmt->fetch(PDO::FETCH_ASSOC);

        if (!$initialMessage) {
            $insertInitialMessageQuery = "INSERT INTO messages (conversation_id, sender_id, receiver_id, message_content, datetime)
                VALUES (:conversationId, :userId, :friendId, 'Conversation started', NOW())";
            $insertInitialMessageStmt = $bdd->prepare($insertInitialMessageQuery);
            $insertInitialMessageStmt->bindParam(':conversationId', $conversationId, PDO::PARAM_STR);
            $insertInitialMessageStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $insertInitialMessageStmt->bindParam(':friendId', $friendId, PDO::PARAM_INT);
            $insertInitialMessageStmt->execute();
        }

        if ($conversationId) {
            // Conversation créée avec succès
            http_response_code(200);
            echo json_encode(['success' => true, 'conversation_id' => $conversationId]);
        } else {
            // Erreur lors de la création de la conversation
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création de la conversation']);
        }
    } else {
        // L'utilisateur n'est pas connecté
        http_response_code(401); // Unauthorized
        echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    }
} else {
    // Requête invalide
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requête invalide']);
}
?>
