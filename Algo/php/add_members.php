<?php
session_start();
require_once 'config.php'; // Assurez-vous que le fichier de configuration est inclus
require_once 'functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
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
    echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé']);
    exit();
}

// Vérifier les données POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['group_id']) && isset($_POST['user_id'])) {
    $groupId = $_POST['group_id'];
    $userId = $_POST['user_id'];

    // Ajouter le membre au groupe
    $addMemberQuery = "INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)";
    $addMemberStmt = $bdd->prepare($addMemberQuery);
    $addMemberStmt->bindParam(':group_id', $groupId);
    $addMemberStmt->bindParam(':user_id', $userId);

    if ($addMemberStmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'ajout du membre']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Données POST invalides']);
}
?>
