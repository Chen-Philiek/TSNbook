<?php
require_once 'config.php'; // Assurez-vous que le fichier de configuration est inclus

if (isset($_POST['group_id']) && isset($_POST['user_id'])) {
    $groupId = $_POST['group_id'];
    $userId = $_POST['user_id'];

    $deleteMemberQuery = "
        DELETE FROM group_members
        WHERE group_id = :group_id AND user_id = :user_id";
    $deleteMemberStmt = $bdd->prepare($deleteMemberQuery);
    $deleteMemberStmt->bindParam(':group_id', $groupId);
    $deleteMemberStmt->bindParam(':user_id', $userId);
    $success = $deleteMemberStmt->execute();

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete member']);
    }
}
?>
