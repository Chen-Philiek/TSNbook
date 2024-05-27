<?php
require_once 'config.php';

if (isset($_POST['group_id'])) {
    $groupId = $_POST['group_id'];
    $query = "SELECT m.*, u.full_name FROM messages m JOIN users u ON m.user_id = u.user_id WHERE m.group_id = :group_id ORDER BY m.created_at ASC";
    $stmt = $bdd->prepare($query);
    $stmt->bindParam(':group_id', $groupId);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($messages as $message) {
        echo '<div class="chat-message"><strong>' . htmlspecialchars($message['full_name']) . ':</strong> ' . htmlspecialchars($message['message']) . ' <small>(' . $message['created_at'] . ')</small></div>';
    }
}
?>
