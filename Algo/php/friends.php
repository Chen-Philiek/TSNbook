<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'messages.php';

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
$friends = getFriendsList($userInfo['user_id'], $bdd);

// Vérifier si la requête est de supprimer un ami
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_friend'])) {
    $friendId = $_POST['friend_id'];

    // Supprimer l'ami de la liste des amis
    removeFriend($userInfo['user_id'], $friendId, $bdd);
    // Rediriger vers la page des amis après la suppression
    header('Location: friends.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />

    <title>Friends</title>
    <link rel="stylesheet" href="../css/global1.css">
</head>
<body>
<header class="header">
    
<h1 class="d-inline-block text-uppercase">TSNbook</h1>
    <div class="logo">
        <i class="fas fa-bars"></i> <!-- Icône pour le menu déroulant -->
    </div>
    <nav class="navbar">
        <ul>
            <!-- Bouton de notification -->
            <li>
                <div class="notification-box">
                    <a href="../php/notifications.php" class="notification-button">Notifications</a>
                </div>
            </li>
            <li><a href="../php/groups.php" id="groups">Groups</a></li> <!-- Lien ajouté -->

            <li><a href="../php/publication.php" id="publish">Publications</a></li>
            <li><a href="../php/Graphes.php" id="graphes">Graphs</a></li>
            <li><a href="../php/home.php">My profil</a></li>
            <li><a href="../html/connexion.html" id="logout">Log out</a></li>
            
        </ul>
    </nav>
</header>
    <div class="cont3">
        
            <h1>Friends List</h1>
            <ul>
                <?php foreach ($friends as $friend) : ?>
                    
                        <?php echo $friend['full_name']; ?>
                        <form method="post" action="friends.php" onsubmit="return confirm('Are you sure you want to remove this friend?');">
                            <input type="hidden" name="friend_id" value="<?php echo $friend['user_id']; ?>">
                            <button type="submit" name="remove_friend">Remove</button>
                        </form>
                        
                <?php endforeach; ?>
                <?php  echo '<button onclick="window.location.href=\'home.php\'">Back</button>'; ?>
            </ul>
       
    </div>
    <!-- Include scripts here if needed -->
    <script src="../javascript/logout.js"></script>
</body>
</html>
