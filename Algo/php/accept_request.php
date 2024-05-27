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
        <h2>Accept friend request</h2>
        <?php
        session_start();
        require_once 'config.php';

        if (isset($_SESSION['user'])) {
            if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['request_id'])) {
                $requestId = $_GET['request_id'];
                
                // Vérifiez d'abord si la demande existe et est en attente
                $checkRequestQuery = "SELECT * FROM friend_requests WHERE request_id = :requestId AND status = 'pending'";
                $checkRequestStmt = $bdd->prepare($checkRequestQuery);
                $checkRequestStmt->bindParam(':requestId', $requestId, PDO::PARAM_INT);
                $checkRequestStmt->execute();
                $request = $checkRequestStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($request) {
                    // Mettez à jour le statut de la demande pour marquer qu'elle a été acceptée
                    $updateRequestQuery = "UPDATE friend_requests SET status = 'accepted' WHERE request_id = :requestId";
                    $updateRequestStmt = $bdd->prepare($updateRequestQuery);
                    $updateRequestStmt->bindParam(':requestId', $requestId, PDO::PARAM_INT);
                    
                    if ($updateRequestStmt->execute()) {
                        // Insérez une nouvelle relation d'amitié dans la table "friendships"
                        $insertFriendshipQuery = "INSERT INTO friendships (user_id, friend_id) VALUES (:userId, :friendId)";
                        $insertFriendshipStmt = $bdd->prepare($insertFriendshipQuery);
                        $insertFriendshipStmt->bindParam(':userId', $request['to_user_id'], PDO::PARAM_INT);
                        $insertFriendshipStmt->bindParam(':friendId', $request['from_user_id'], PDO::PARAM_INT);
                        
                        if ($insertFriendshipStmt->execute()) {
                            echo "<p>The friend request is successfully accepted .</p>";
                        } else {
                            echo "<p>Une erreur est survenue lors de l'acceptation de la demande d'amis.</p>";
                        }
                    } else {
                        echo "<p>error!.</p>";
                    }
                } else {
                    echo "<p>La demande d'amis n'existe pas ou n'est pas en attente.</p>";
                }
            } else {
                echo "<p>Paramètres invalides.</p>";
            }
        } else {
            echo "<p>Session non valide.</p>";
        }
        ?>
        <button onclick="window.location.href='home.php'">Back</button>
    </div>
</body>
</html>
