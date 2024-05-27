<?php
session_start();
require_once 'config.php'; // Assurez-vous que le fichier de configuration est inclus
require_once 'functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit();
}

// Récupérer les informations de l'utilisateur à partir de la session
$token = $_SESSION['user'];

// Requête pour récupérer les données de l'utilisateur à partir du token
$getInfoQuery = "SELECT * FROM users WHERE token = :token";
$getInfoStmt = $bdd->prepare($getInfoQuery);
$getInfoStmt->bindParam(':token', $token);
$getInfoStmt->execute();
$userInfo = $getInfoStmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur existe dans la base de données
if (!$userInfo) {
    echo json_encode(['error' => 'User not found']);
    exit();
}

// Gérer l'ajout de commentaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['group_id']) && isset($_POST['message'])) {
    $groupId = htmlspecialchars($_POST['group_id']);
    $message = htmlspecialchars($_POST['message']);
    $userId = $userInfo['user_id'];

    // Ajouter le commentaire à la base de données
    $addCommentQuery = "INSERT INTO messagesgroups (group_id, user_id, message, created_at) VALUES (:group_id, :user_id, :message, NOW())";
    $addCommentStmt = $bdd->prepare($addCommentQuery);
    $addCommentStmt->bindParam(':group_id', $groupId);
    $addCommentStmt->bindParam(':user_id', $userId);
    $addCommentStmt->bindParam(':message', $message);
    $addCommentStmt->execute();

    echo json_encode(['success' => 'Comment added successfully']);
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
