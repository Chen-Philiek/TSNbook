
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
        <h2>Ajout d'amis</h2>
        <?php
        session_start();
        require_once 'config.php';

        if (isset($_SESSION['user'])) {
            $token = $_SESSION['user'];
            
            // Recherchez l'ID de l'utilisateur à partir du token dans la base de données
            $userIdQuery = "SELECT user_id FROM users WHERE token = :token";
            $userIdStmt = $bdd->prepare($userIdQuery);
            $userIdStmt->bindParam(':token', $token, PDO::PARAM_STR);
            $userIdStmt->execute();
            $userData = $userIdStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($userData) {
                $userId = $userData['user_id'];
                
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['friend_id'])) {
                    $friendId = $_POST['friend_id'];
                    
                    // Vérifiez d'abord si une demande d'amis existe déjà entre ces deux utilisateurs
                    $checkRequestQuery = "SELECT * FROM friend_requests WHERE from_user_id = :userId AND to_user_id = :friendId AND status = 'pending'";
                    $checkRequestStmt = $bdd->prepare($checkRequestQuery);
                    $checkRequestStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                    $checkRequestStmt->bindParam(':friendId', $friendId, PDO::PARAM_INT);
                    $checkRequestStmt->execute();
                    $existingRequest = $checkRequestStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existingRequest) {
                        echo "Friend request already sent.";
                    } else {
                        // Insérer une nouvelle demande d'amis dans la table des demandes d'amis en attente
                        $insertRequestQuery = "INSERT INTO friend_requests (from_user_id, to_user_id) VALUES (:userId, :friendId)";
                        $insertRequestStmt = $bdd->prepare($insertRequestQuery);
                        $insertRequestStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                        $insertRequestStmt->bindParam(':friendId', $friendId, PDO::PARAM_INT);
                        if ($insertRequestStmt->execute()) {
                            echo "Friend request sent successfully.";
                        } else {
                            echo "Une erreur est survenue lors de l'envoi de la demande d'amis.";
                        }
                    }
                } else {
                    echo "Paramètres invalides.";
                }
            } else {
                echo "User not found.";
            }
        } else {
            echo "Session non valide.";
        }

        ?>
  <button onclick="window.location.href='home.php'">back</button>
    </div>
</body>
</html>
