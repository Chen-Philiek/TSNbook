
<?php
require_once 'config.php';

// Fonction pour vérifier si deux utilisateurs sont amis
function areFriends($user_id, $friend_id, $bdd) {
    $checkFriendshipQuery = "SELECT COUNT(*) FROM friendships WHERE (user_id = :user_id AND friend_id = :friend_id) OR (user_id = :friend_id AND friend_id = :user_id)";
    $checkFriendshipStmt = $bdd->prepare($checkFriendshipQuery);
    $checkFriendshipStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $checkFriendshipStmt->bindParam(':friend_id', $friend_id, PDO::PARAM_INT);
    $checkFriendshipStmt->execute();
    $count = $checkFriendshipStmt->fetchColumn();
    return $count > 0;
}

// Fonction pour obtenir les intérêts communs entre deux utilisateurs
function getCommonInterests($user_id, $friend_id, $bdd) {
    $commonInterestsQuery = "SELECT i.interest_label FROM user_interests ui1 INNER JOIN user_interests ui2 ON ui1.interest_id = ui2.interest_id INNER JOIN interests i ON ui1.interest_id = i.interest_id WHERE ui1.user_id = :user_id AND ui2.user_id = :friend_id";
    $commonInterestsStmt = $bdd->prepare($commonInterestsQuery);
    $commonInterestsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $commonInterestsStmt->bindParam(':friend_id', $friend_id, PDO::PARAM_INT);
    $commonInterestsStmt->execute();
    $commonInterests = $commonInterestsStmt->fetchAll(PDO::FETCH_COLUMN);
    return $commonInterests;
}
// Fonction pour supprimer un ami de la liste d'amis de l'utilisateur
function removeFriend($userId, $friendId, $bdd) {
    // Requête pour supprimer l'ami de la table des amis de l'utilisateur
    $removeFriendQuery = "DELETE FROM friendships WHERE (user_id = :userId AND friend_id = :friendId) OR (user_id = :friendId AND friend_id = :userId)";
    $removeFriendStmt = $bdd->prepare($removeFriendQuery);
    $removeFriendStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $removeFriendStmt->bindParam(':friendId', $friendId, PDO::PARAM_INT);
    $removeFriendStmt->execute();
}


// Fonction pour récupérer la liste des amis d'un utilisateur et créer une conversation si nécessaire
function getFriendsList($userId, $bdd) {
    // Requête pour récupérer les amis de l'utilisateur à partir de la table des amis
    $getFriendsQuery = "SELECT u.user_id, u.full_name FROM friendships f
                        INNER JOIN users u ON f.friend_id = u.user_id
                        WHERE f.user_id = :userId";
    $getFriendsStmt = $bdd->prepare($getFriendsQuery);
    $getFriendsStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $getFriendsStmt->execute();
    $friends = $getFriendsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($friends as $friend) {
        // Vérifier si une conversation existe entre l'utilisateur et l'ami
        $checkConversationQuery = "SELECT conversation_id FROM conversations
                                    WHERE (user1_id = :userId AND user2_id = :friendId)
                                    OR (user1_id = :friendId AND user2_id = :userId)";
        $checkConversationStmt = $bdd->prepare($checkConversationQuery);
        $checkConversationStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $checkConversationStmt->bindParam(':friendId', $friend['user_id'], PDO::PARAM_INT);
        $checkConversationStmt->execute();
        $conversation = $checkConversationStmt->fetch(PDO::FETCH_ASSOC);
        
        // Si aucune conversation n'existe, en créer une
        if (!$conversation) {
            $conversationId = createConversation($userId, $friend['user_id'], $bdd);
        } else {
            $conversationId = $conversation['conversation_id'];
        }

        // Insérer l'ID de conversation dans la table des messages
        $updateMessagesQuery = "UPDATE messages SET conversation_id = :conversationId
                                WHERE sender_id = :userId AND receiver_id = :friendId";
        $updateMessagesStmt = $bdd->prepare($updateMessagesQuery);
        $updateMessagesStmt->bindParam(':conversationId', $conversationId, PDO::PARAM_STR);
        $updateMessagesStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $updateMessagesStmt->bindParam(':friendId', $friend['user_id'], PDO::PARAM_INT);
        $updateMessagesStmt->execute();
    }
    
    return $friends;
}


function loadconversations($userId, $friendId, $bdd) {
    // Requête pour récupérer les messages entre l'utilisateur et son ami
    $query = "SELECT m.*, u.full_name 
              FROM messages m
              INNER JOIN users u ON m.sender_id = u.user_id 
              WHERE (m.sender_id = :userId AND m.receiver_id = :friendId) 
              OR (m.sender_id = :friendId AND m.receiver_id = :userId)
              ORDER BY m.datetime ASC";

    $stmt = $bdd->prepare($query);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':friendId', $friendId, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $messages;
}



function getFriendOfFriendUsers($loggedInUserId, $bdd) {
    // Vérifier si l'utilisateur est connecté
    if (isset($_SESSION['user'])) {
        $token = $_SESSION['user'];
        
        // Recherchez l'ID de l'utilisateur à partir du token dans la base de données
        $userIdQuery = "SELECT user_id FROM users WHERE token = :token";
        $userIdStmt = $bdd->prepare($userIdQuery);
        $userIdStmt->bindParam(':token', $token, PDO::PARAM_STR);
        $userIdStmt->execute();
        $userData = $userIdStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            $loggedInUserId = $userData['user_id'];
        }
    }
    
    // Préparez et exécutez la requête SQL pour obtenir les utilisateurs friend of friend
    $query = "
        SELECT DISTINCT u.username
        FROM users u
        JOIN friendships f1 ON f1.user_id = u.user_id
        JOIN friendships f2 ON f2.friend_id = f1.friend_id
        WHERE f2.user_id = :loggedInUserId
        AND u.user_id != :loggedInUserId
        ORDER BY RAND() 
        LIMIT 4
    ";
    $statement = $bdd->prepare($query);
    $statement->bindParam(':loggedInUserId', $loggedInUserId, PDO::PARAM_INT);
    $statement->execute();
    $friendOfFriendUsers = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $friendOfFriendUsers;
}


function insertMessage($senderId, $receiverId, $messageContent, $bdd) {
    $insertMessageQuery = "INSERT INTO messages (sender_id, receiver_id, message_content, datetime) VALUES (:senderId, :receiverId, :messageContent, NOW())";
    $insertMessageStmt = $bdd->prepare($insertMessageQuery);
    $insertMessageStmt->bindParam(':senderId', $senderId);
    $insertMessageStmt->bindParam(':receiverId', $receiverId);
    $insertMessageStmt->bindParam(':messageContent', $messageContent);
    $insertMessageStmt->execute();
}


// Fonction pour créer une conversation entre deux utilisateurs et récupérer son ID
function createConversation($user1Id, $user2Id, $bdd) {
    // Générer un ID de conversation unique
    $conversationId = uniqid();

    // Insérer l'ID de conversation dans la table des conversations
    $insertConversationQuery = "INSERT INTO conversations (conversation_id, user1_id, user2_id)
                                VALUES (:conversationId, :user1Id, :user2Id)";
    $insertConversationStmt = $bdd->prepare($insertConversationQuery);
    $insertConversationStmt->bindParam(':conversationId', $conversationId, PDO::PARAM_STR);
    $insertConversationStmt->bindParam(':user1Id', $user1Id, PDO::PARAM_INT);
    $insertConversationStmt->bindParam(':user2Id', $user2Id, PDO::PARAM_INT);
    $insertConversationStmt->execute();

    return $conversationId;
}

?>

