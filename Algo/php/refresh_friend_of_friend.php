<?php
// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    echo "Vous devez être connecté pour accéder à cette fonctionnalité.";
    exit();
}

// Récupérer l'ID de l'utilisateur connecté à partir de la session
$loggedInUserId = $_SESSION['user'];

// Requête SQL pour obtenir une liste aléatoire d'amis d'amis (limitée à 4 utilisateurs)
$query = "
    SELECT DISTINCT u.full_name
    FROM users u
    JOIN friendships f1 ON f1.user_id = u.user_id
    JOIN friendships f2 ON f2.friend_id = f1.friend_id
    WHERE f2.user_id = :loggedInUserId
    AND u.user_id != :loggedInUserId
    ORDER BY RAND() 
    LIMIT 4
";

// Préparation de la requête
$statement = $bdd->prepare($query);
// Liaison des paramètres
$statement->bindParam(':loggedInUserId', $loggedInUserId, PDO::PARAM_INT);
// Exécution de la requête
$statement->execute();
// Récupération des résultats
$friendOfFriendUsers = $statement->fetchAll(PDO::FETCH_ASSOC);

// Affichage de la liste des utilisateurs friend of friend
foreach ($friendOfFriendUsers as $user) {
    echo "<div class='userContainer'>" . $user['full_name'] . "</div>";
}
?>
