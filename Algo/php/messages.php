<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
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
    // Rediriger vers la page de connexion si l'utilisateur n'existe pas
    header('Location: connexion.php');
    exit();
}

// Récupérer la liste des amis de l'utilisateur
$getFriendsQuery = "SELECT u.* FROM users u INNER JOIN friendships f ON u.user_id = f.friend_id WHERE f.user_id = :userId";
$getFriendsStmt = $bdd->prepare($getFriendsQuery);
$getFriendsStmt->bindParam(':userId', $userInfo['user_id'], PDO::PARAM_INT);
$getFriendsStmt->execute();
$friends = $getFriendsStmt->fetchAll(PDO::FETCH_ASSOC);

?>
