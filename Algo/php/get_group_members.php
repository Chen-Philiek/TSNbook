<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['group_id'])) {
    $groupId = $_POST['group_id'];

    // Préparer la requête pour récupérer les membres du groupe
    $getMembersQuery = "
        SELECT u.user_id, u.full_name 
        FROM group_members gm 
        JOIN users u ON gm.user_id = u.user_id 
        WHERE gm.group_id = :group_id";
    $getMembersStmt = $bdd->prepare($getMembersQuery);
    $getMembersStmt->bindParam(':group_id', $groupId);
    $getMembersStmt->execute();
    $members = $getMembersStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($members);
} else {
    echo json_encode([]);
}
?>
