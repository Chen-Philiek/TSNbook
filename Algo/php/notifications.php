<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Notifications</title>
    <link rel="stylesheet" href="../css/publication.css">
    <!-- Ajoutez le lien vers votre fichier CSS pour les notifications -->
    <style>
        /* Styles pour les boutons Accepter et Rejeter */
        .notif{
            margin-top: 15px;
        }
        .accept-btn, .reject-btn {
            padding: 8px 16px;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-transform: uppercase;
            margin-right: 8px;
            transition: background-color 0.3s;
            
        }

        .accept-btn {
            background-color: #007bff;
            color: #fff;
        }

        .reject-btn {
            background-color: #dc3545;
            color: #fff;
        }

        .accept-btn:hover, .reject-btn:hover {
            background-color: rgba(0, 123, 255, 0.8);
        }

         /* Styles pour centrer les sections */
         .notification-container {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            height: 50vh; /* Ajustez la hauteur selon vos besoins */
            overflow-y: auto; /* Activer la barre de défilement verticale */
            flex-wrap: wrap; /* Permettre le passage à la ligne si nécessaire */
        }

        /* Styles pour le titre */
        .notification-title {
            text-align: center;
            margin-top: 20px;
            background-color: #007bff;
            color: #fff;
            margin-bottom: 20px; /* Espacement entre le titre et les sections */
            width: 100%; /* Assurer que le titre occupe toute la largeur */
        }

        /* Styles pour les sections des notifications */
        .notification-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px; /* Espacement entre les sections */
            flex: 1; /* Les sections occupent un espace égal */
            border: 3px solid #2c3e50 ;
            max-height: 40vh; /* Hauteur maximale de la section */
            overflow-y: auto; /* Activer la barre de défilement verticale si nécessaire */
        }

        /* Styles pour les listes de notifications */
        .notification-list {
            list-style: none;
            padding: 0;
            text-align: left;
           
        }
    </style>
</head>
<body>
    <header class="header">
   
                <h1 class="d-inline-block text-uppercase">TSNbook</h1>
             
     
        <div class="logo">
            <i class="fas fa-bars"></i> <!-- Icône pour le menu déroulant -->
        </div>
        <nav class="navbar">
            <ul>
            <li><a href="../php/groups.php" id="groups">Groups</a></li> <!-- Lien ajouté -->

                <li><a href="../php/publication.php" id="publish">Publications</a></li>
                <li><a href="../php/friends.php" id="friend">My relations</a></li>
                <li><a href="../php/Graphes.php" id="graphes">Graphs</a></li>
                <li><a href="../php/home.php">My profil</a></li>
                <li><a href="../html/connexion.html" id="logout">Log out</a></li>
            </ul>
        </nav>
    </header>
    <h1 class="notification-title">My notifications</h1>
    <div class="notification-container">
        <div class="notification-section">
            <h3>Friends requests</h3>
            <ul class="notification-list">
                <!-- Contenu de la notification -->
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

                        // Récupérez les demandes d'amis en attente pour cet utilisateur
                        $getPendingRequestsQuery = "SELECT * FROM friend_requests WHERE to_user_id = :userId AND status = 'pending'";
                        $getPendingRequestsStmt = $bdd->prepare($getPendingRequestsQuery);
                        $getPendingRequestsStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                        $getPendingRequestsStmt->execute();
                        $pendingRequests = $getPendingRequestsStmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($pendingRequests as $request) {
                            $fromUserId = $request['from_user_id'];
                            // Récupérez le nom de l'utilisateur qui a envoyé la demande
                            $getUserNameQuery = "SELECT full_name FROM users WHERE user_id = :fromUserId";
                            $getUserNameStmt = $bdd->prepare($getUserNameQuery);
                            $getUserNameStmt->bindParam(':fromUserId', $fromUserId, PDO::PARAM_INT);
                            $getUserNameStmt->execute();
                            $userName = $getUserNameStmt->fetchColumn();

                            echo "<li class='notif'>You have received a friend request from $userName <a class='accept-btn'href='accept_request.php?request_id={$request['request_id']}'>Accept</a> | <a class='reject-btn'href='reject_request.php?request_id={$request['request_id']}'>Refuse</a></li>";
                        }
                    }
                }
                ?>
            </ul>
        </div>
        <div class="notification-section">
            <h3>Other notifications</h3>
            <ul class="notification-list">
            <?php
            if (isset($_SESSION['user'])) {
                $token = $_SESSION['user'];
                
                // Récupérer l'ID de l'utilisateur connecté
                $userIdQuery = "SELECT user_id FROM users WHERE token = :token";
                $userIdStmt = $bdd->prepare($userIdQuery);
                $userIdStmt->bindParam(':token', $token, PDO::PARAM_STR);
                $userIdStmt->execute();
                $userData = $userIdStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userData) {
                    $userId = $userData['user_id'];
                    
                    // Récupérer les notifications de l'utilisateur connecté
                    $getNotificationsQuery = "
                        SELECT notifications.*, users.full_name AS commenter_name
                        FROM notifications
                        INNER JOIN users ON notifications.user_id = users.user_id
                        INNER JOIN publications ON notifications.publication_id = publications.id
                        WHERE publications.user_id = :userId
                        AND notifications.user_id != :userId
                        ORDER BY notifications.created_at DESC";
                    $getNotificationsStmt = $bdd->prepare($getNotificationsQuery);
                    $getNotificationsStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                    $getNotificationsStmt->execute();
                    $notifications = $getNotificationsStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Afficher les notifications de l'utilisateur connecté
                    foreach ($notifications as $notification) {
                        echo "<div class='notification'>";
                        echo "<p>Notification: You have received a notification on the post {$notification['publication_id']} from {$notification['commenter_name']}</p>";
                        echo "<button class='delete-notification-btn' data-notification-id='{$notification['id']}'>Delete</button>";
                        echo "</div>";
                    }
                }
            }
            ?>

            
                <!-- Contenu des autres notifications -->
                <!-- Utilisez PHP pour générer dynamiquement le contenu -->
            </ul>
        </div>
    </div>
    <script src="../javascript/notif.js"></script>
    <script src="../javascript/logout.js"></script>
</body>

</html>
