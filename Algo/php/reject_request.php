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
        <h2>Reject friend request</h2>

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
                    // Mettez à jour le statut de la demande pour marquer qu'elle a été rejetée
                    $updateRequestQuery = "UPDATE friend_requests SET status = 'rejected' WHERE request_id = :requestId";
                    $updateRequestStmt = $bdd->prepare($updateRequestQuery);
                    $updateRequestStmt->bindParam(':requestId', $requestId, PDO::PARAM_INT);
                    
                    if ($updateRequestStmt->execute()) {
                        echo "The friend request is successfully rejected.";
                     
                    } else {
                        echo "Une erreur est survenue lors du rejet de la demande d'amis.";
                    }
                } else {
                    echo "La demande d'amis n'existe pas ou n'est pas en attente.";
                }
            } else {
                echo "Paramètres invalides.";
            }
        } else {
            echo "Session non valide.";
        }
        ?>
    <button onclick="window.location.href='home.php'">Back</button>
    </div>
</body>
</html>
