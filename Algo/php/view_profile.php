<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../css/global1.css">
</head>
<header class="header">
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
            <li><a href="../php/publication.php" id="publish">Publications</a></li>
            <li><a href="../php/friends.php" id="friend">Mes relations</a></li>
            <li><a href="../php/Graphes.php" id="graphes">Graphes</a></li>
            <li><a href="../php/home.php">Mon Profil</a></li>
            <li><a href="../html/connexion.html" id="logout">Se déconnecter</a></li>
            
        </ul>
    </nav>
</header>
<body>
    <div class="container2">
    <?php
    session_start();
    require_once 'config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];
        
        // Récupérer les informations de l'utilisateur à partir de son ID
        $getUserQuery = "SELECT * FROM users WHERE user_id = :userId";
        $getUserStmt = $bdd->prepare($getUserQuery);
        $getUserStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $getUserStmt->execute();
        $userData = $getUserStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            // Afficher les informations du profil de l'utilisateur
            echo "<h1>Profil de {$userData['full_name']}</h1>";
            echo "<p>Nom complet: {$userData['full_name']}</p>";
            echo "<p>Email: {$userData['email']}</p>";
            echo "<p>Genre: {$userData['gender']}</p>";
            echo "<p>Âge: {$userData['age']}</p>";
            echo "<p>Téléphone: {$userData['telephone']}</p>";
            // Ajoutez d'autres informations du profil ici si nécessaire
        } else {
            echo "Utilisateur non trouvé.";
        }
    } else {
        echo "Paramètres invalides.";
    }
    ?>

    <a href="home.php">Retour à la page d'accueil</a>
    </div>
</body>
</html>


