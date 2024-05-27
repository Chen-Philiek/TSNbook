<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Accepter une demande d'amis</title>
    <link rel="stylesheet" href="../css/accueil.css">
    
</head>
<body>
<header class="header">
    
    <h1 class="d-inline-block text-uppercase">TSNbook</h1>
</header>
    <div class="container">
        <h2>Recherche</h2>
<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['searchUser'])) {
    $searchUser = htmlspecialchars($_POST['searchUser']);
    
    // Recherchez les utilisateurs dont le nom correspond à la recherche
    $searchQuery = "SELECT * FROM users WHERE full_name LIKE :searchUser";
    $searchStmt = $bdd->prepare($searchQuery);
    $searchStmt->bindValue(':searchUser', '%' . $searchUser . '%', PDO::PARAM_STR);
    $searchStmt->execute();
    $searchResults = $searchStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Afficher les résultats de la recherche
    if ($searchResults) {
        foreach ($searchResults as $user) {
            echo "<div>{$user['full_name']} - <form action='addfriend1.php' method='post'><input type='hidden' id='name' name='friend_id' value='{$user['user_id']}'><input type='submit' value='Ajouter comme ami'></form></div>";
        }
    } else {
        echo "Aucun utilisateur trouvé.";
    }
}
?>
 <button onclick="window.location.href='home.php'">Retour</button>
    </div>
</body>
</html>
