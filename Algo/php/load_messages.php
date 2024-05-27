<?php
require_once 'config.php';
require_once 'functions.php';

// Assurez-vous que la requête est de type POST et que l'ID de l'ami et de la conversation sont présents
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['friend_id']) && isset($_POST['conversation_id'])) {
    // Récupérer l'ID de l'ami et de la conversation depuis la requête POST
    $friendId = $_POST['friend_id'];
    $conversationId = $_POST['conversation_id'];

    // Récupérer les messages de la conversation entre l'utilisateur actuel et l'ami spécifié
    $getMessagesQuery = "SELECT * FROM messages
                        WHERE conversation_id = :conversationId
                        ORDER BY datetime ASC"; // Supposons que datetime est la colonne contenant la date et l'heure des messages
    $getMessagesStmt = $bdd->prepare($getMessagesQuery);
    $getMessagesStmt->bindParam(':conversationId', $conversationId, PDO::PARAM_STR); // Lier l'ID de la conversation
    $getMessagesStmt->execute();
    $messages = $getMessagesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Afficher les messages ou un message invitant l'utilisateur à envoyer le premier message
    if ($messages) {
        foreach ($messages as $message) {
            // Vous pouvez formater les messages selon vos besoins
            echo '<div><strong>' . $message['sender_id'] . ': </strong>' . $message['message_content'] . '</div>';
        }
    } else {
        // Aucun message trouvé, inviter l'utilisateur à envoyer le premier message
        echo '<div>Aucun message trouvé. Soyez le premier à envoyer un message à cet ami !</div>';
    }
} else {
    // Requête invalide
    http_response_code(400);
    echo 'Requête invalide';
}

?>
