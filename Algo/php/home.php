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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['interests'])) {
    $interests = htmlspecialchars($_POST['interests']);

    // Séparer les intérêts en un tableau
    $interestsArray = explode(',', $interests);

    // Parcourir les intérêts et les ajouter à la table interests si nécessaire
    foreach ($interestsArray as $interest) {
        $checkInterestQuery = "SELECT * FROM interests WHERE interest_label = :interest";
        $checkInterestStmt = $bdd->prepare($checkInterestQuery);
        $checkInterestStmt->bindParam(':interest', $interest);
        $checkInterestStmt->execute();
        $existingInterest = $checkInterestStmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingInterest) {
            // Ajouter l'intérêt à la table interests s'il n'existe pas déjà
            $addInterestQuery = "INSERT INTO interests (interest_label) VALUES (:interest)";
            $addInterestStmt = $bdd->prepare($addInterestQuery);
            $addInterestStmt->bindParam(':interest', $interest);
            $addInterestStmt->execute();
        }
    }

    // Récupérer les intérêts existants de l'utilisateur
    $existingInterestsQuery = "SELECT interests FROM users WHERE token = :token";
    $existingInterestsStmt = $bdd->prepare($existingInterestsQuery);
    $existingInterestsStmt->bindParam(':token', $token);
    $existingInterestsStmt->execute();
    $existingInterests = $existingInterestsStmt->fetchColumn();

    // Concaténer les nouveaux intérêts avec les anciens
    $updatedInterests = $existingInterests . ',' . $interests;

    // Mettre à jour les intérêts de l'utilisateur dans la table users
    $updateQuery = "UPDATE users SET interests = :interests WHERE token = :token";
    $updateStmt = $bdd->prepare($updateQuery);
    $updateStmt->bindParam(':interests', $updatedInterests);
    $updateStmt->bindParam(':token', $token);
    $updateStmt->execute();

    // Mettre à jour la table user_interests
    foreach ($interestsArray as $interest) {
        $getInterestIdQuery = "SELECT interest_id FROM interests WHERE interest_label = :interest";
        $getInterestIdStmt = $bdd->prepare($getInterestIdQuery);
        $getInterestIdStmt->bindParam(':interest', $interest);
        $getInterestIdStmt->execute();
        $interestId = $getInterestIdStmt->fetchColumn();

        // Insérer ou mettre à jour les relations utilisateur-intérêt dans la table user_interests
        $updateUserInterestsQuery = "REPLACE INTO user_interests (user_id, interest_id) VALUES (:user_id, :interest_id)";
        $updateUserInterestsStmt = $bdd->prepare($updateUserInterestsQuery);
        $updateUserInterestsStmt->bindParam(':user_id', $userInfo['user_id']);
        $updateUserInterestsStmt->bindParam(':interest_id', $interestId);
        $updateUserInterestsStmt->execute();
    }
}

function getCommonInterestRecommendations($userId, $bdd) {
    $query = "SELECT u.user_id, u.full_name, GROUP_CONCAT(i.interest_label SEPARATOR ', ') AS common_interests
              FROM users u
              INNER JOIN user_interests ui ON u.user_id = ui.user_id
              INNER JOIN interests i ON ui.interest_id = i.interest_id
              WHERE u.user_id != :userId
              AND ui.interest_id IN (
                  SELECT interest_id FROM user_interests WHERE user_id = :userId
              )
              GROUP BY u.user_id";
    
    $stmt = $bdd->prepare($query);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $recommendations;
}
// Récupérer les recommandations d'amis avec intérêts communs
$recommandations = getCommonInterestRecommendations($userInfo['user_id'], $bdd);

$stmt = $bdd->query("SELECT * FROM messages ORDER BY datetime DESC");
$stmt = $bdd->query("SELECT messages.*, users.full_name FROM messages INNER JOIN users ON messages.sender_id = users.user_id ORDER BY messages.datetime DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />

    <title>Home</title>
    <link rel="stylesheet" href="../css/global1.css">
</head>
<header class="header">
     <!-- page header -->
     <div class="container3" id="home">
          <div class="col-12 text-center">
            <div class="tm-page-header">
              <i class="fas fa-4x fa-chart-bar mr-4"></i>
              <h1 class="d-inline-block text-uppercase">TSNbook</h1>
            </div>
          </div>
        </div>
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
            <li><a href="../php/friends.php" id="friend">My relations</a></li>
            <li><a href="../php/Graphes.php" id="graphes">Graphs</a></li>
            <li><a href="../php/home.php">My profil</a></li>
            <li><a href="../html/connexion.html" id="logout">Log out</a></li>
            
        </ul>
    </nav>
</header>
<body>
<div class="container-wrapper">
        <div class="container-column">
            <!-- Affichage des recommandations d'amis avec intérêts communs -->
            <div class="container" id="friendOfFriendContainer">
                    <h3>Friend of friend :</h3>
                    <div id="friendOfFriendList">
                        <!-- Liste des utilisateurs friend of friend affichée ici -->
                        <?php
                        // Récupérer les recommandations d'amis avec intérêts communs pour l'utilisateur connecté
                        $friendOfFriendRecommendations = getFriendOfFriendUsers($userInfo['user_id'], $bdd);

                        // Afficher les recommandations
                        foreach ($friendOfFriendRecommendations as $recommendation) {
                            echo "<div class='userContainer'>" . $recommendation['username'] . "</div>";
                        }
                        ?>
                    </div>
                    <button id="refreshButton">Refresh</button>
            </div>
            <!-- Affichage des recommandations d'amis avec intérêts communs -->
            <div class="container" id="homeContainer">
                    <h3>Recommendations of friends with same interest :</h3>
                    <ul>
                        <?php foreach ($recommandations as $recommandation) : ?>
                            
                                <?php echo $recommandation['full_name']."<br>"; ?> 
                            
                                (Common interest:
                                <?php 
                                    // Récupérer les intérêts communs pour cette recommandation
                                    $commonInterests = getCommonInterests($userInfo['user_id'], $recommandation['user_id'], $bdd);
                                    // Afficher chaque intérêt commun séparément
                                    echo implode(', ', $commonInterests);
                                ?>)
                                <br>
                                <br>
                                <?php 
                                // Vérifier si l'utilisateur est déjà ami avec la recommandation
                                if (areFriends($userInfo['user_id'], $recommandation['user_id'], $bdd)) {
                                    echo "<form method='post' action='view_profile.php'>";
                                    echo "<input type='hidden' name='user_id' value='{$recommandation['user_id']}'>";
                                    echo "<button type='submit'>View profil</button>";
                                    echo "</form>";
                                } else {
                                    echo "<form method='post' action='addfriend1.php'>";
                                    echo "<input type='hidden' name='friend_id' value='{$recommandation['user_id']}'>";
                                    echo "<button type='submit'>Add friend</button>";
                                    echo "</form>";
                                }
                                ?>
                            
                        <?php endforeach; ?>
                    </ul>
            </div>
        </div>
        <div class="container-column">
            <div class="container" id="homeContainer">
                    <div id="editProfileHeader">
                        <button type="button" id="editProfileButton"><img src="../img/Modifier.png" alt="Modifier"></button>
                    </div>
                    <h1>Welcome to your personal account space !</h1>
                    <p>Full name: <?php echo $userInfo['full_name']; ?></p>
                    <p>Email: <?php echo $userInfo['email']; ?></p>
                    <p>Gender: <?php echo $userInfo['gender']; ?></p>
                    <p>Age: <?php echo $userInfo['age']; ?></p>
                    <p>Phone number: <?php echo $userInfo['telephone']; ?></p>
            </div>

            <div class="container" id="homeContainer">
                    <form method="post" action="home.php" id="interestsForm">
                    <button type="button" id="editInterestsButton"><img src="../img/Modifier.png" alt="Modifier"></button>
                    <div id="interestsHeader">
                        <h2>Interests:</h2>
                    </div>
                        <div class="form-group">
                            <label for="interests">Interests:</label>
                            <input type="text" id="interestInput" name="interests" placeholder="Recherchez vos intérêts">
                            <div id="interestSuggestions"></div>
                        </div>
                        <button type="submit">Update interests</button>
                    </form>
            </div>

            
        </div>
        <div class="container-column">
            <div class="container" id="researchContainer">
                <form method="post" action="research_users.php">
                    <label for="searchUser">Search Users :</label>
                    <input type="text" id="searchUser" name="searchUser" placeholder="Enter the name of the user">
                    <br>
                    <br>
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="container" id="messageContainer">
                <h1>Messages</h1>
                <h2>List of friend</h2>
                <ul id="friendsList">
                    <!-- Liste des amis avec des liens pour commencer une discussion -->
                    <?php foreach ($friends as $friend) : ?>
                        <li>
                            <a href="#" class="friend-link" data-friend-id="<?php echo $friend['user_id']; ?>">
                                <?php echo $friend['full_name']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <!-- Zone de conversation -->
                <div id="conversationContainer">
                    <!-- La conversation sera affichée ici -->
                </div>
                <!-- Formulaire pour envoyer des messages -->
                <form id="sendMessageForm">
                    <input type="text" class="messageInput" name="message" placeholder="Tapez votre message">
                    <button type="submit" class="message-btn">Send</button>
                </form>
            </div>
        </div>

  
</div>
       

        <!-- La fenêtre modale -->
        <div id="welcomeModal" class="modal">
            <div class="modal-content1">
                <span class="close">&times;</span>
                <h2>Modify your personal informations</h2>
                <form id="editProfileForm" method="post" action="update_profile.php">
                    <label for="fullName">Full name:</label>
                    <input type="text" id="fullName" name="fullName" placeholder="Your full name" value="<?php echo $userInfo['full_name']; ?>">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Your email" value="<?php echo $userInfo['email']; ?>">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="male" <?php if($userInfo['gender'] == 'male') echo 'selected'; ?>>Male</option>
                        <option value="female" <?php if($userInfo['gender'] == 'female') echo 'selected'; ?>>Female</option>
                        <option value="other" <?php if($userInfo['gender'] == 'other') echo 'selected'; ?>>Other</option>
                    </select>
                    <label for="age">Age:</label>
                    <input type="number" id="age" name="age" placeholder="Your age" value="<?php echo $userInfo['age']; ?>">
                    <label for="telephone">Phone number:</label>
                    <input type="tel" id="telephone" name="telephone" placeholder="Your phone number" value="<?php echo $userInfo['telephone']; ?>">
                    <button type="submit">Register</button>
                </form>
            </div>
        </div>

        
        

       <!-- La fenêtre modale -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="closee">&times;</span>
                <h2>Modify your interests</h2>
                <form id="editInterestsForm" method="post" action="home.php">
                    <label for="existingInterests">Existing interests:</label>
                    <ul>
                    <?php
                    // Récupérer les intérêts existants de l'utilisateur depuis la base de données
                    $token = $_SESSION['user'];
                    $getInterestsQuery = "SELECT i.interest_label, ui.user_id FROM interests i INNER JOIN user_interests ui ON i.interest_id = ui.interest_id WHERE ui.user_id = (SELECT user_id FROM users WHERE token = :token)";
                    $getInterestsStmt = $bdd->prepare($getInterestsQuery);
                    $getInterestsStmt->bindParam(':token', $token);
                    $getInterestsStmt->execute();
                    $userInterests = $getInterestsStmt->fetchAll(PDO::FETCH_ASSOC);

                    // Afficher les intérêts existants dans une liste
                    foreach ($userInterests as $interest) {
                        echo $interest['interest_label'] . "<button class='deleteInterest' data-user-id='" . $interest['user_id'] . "'>Supprimer</button></li>";
                    }
                    ?>
                    </ul>
                    <label for="interests">New interests:</label>
                    <input type="text" id="interestInput" name="interests" placeholder="Enter your new interest ">
                    <button type="submit">Register</button>
                </form>
            </div>
        </div>


        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Fonction pour rafraîchir la liste des utilisateurs friend of friend
    function refreshFriendOfFriendList() {
        $.ajax({
            url: 'functions.php', // L'url vers votre script PHP qui rafraîchit la liste
            method: 'POST',
            data: {userId: <?php echo $loggedInUserId; ?>}, // Envoie de l'ID de l'utilisateur connecté
            success: function(response) {
                // Afficher une liste aléatoire de 4 amis d'amis
                var randomUsers = getRandomUsers(response, 4);
                $('#friendOfFriendList').html(randomUsers);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    // Fonction pour obtenir une liste aléatoire d'utilisateurs à partir d'une liste donnée
    function getRandomUsers(users, count) {
        var shuffled = users.slice(0), i = users.length, temp, index;
        while (i--) {
            index = Math.floor((i + 1) * Math.random());
            temp = shuffled[index];
            shuffled[index] = shuffled[i];
            shuffled[i] = temp;
        }
        return shuffled.slice(0, count);
    }

    // Rafraîchissement de la liste lorsque le bouton est cliqué
    $('#refreshButton').click(function() {
        refreshFriendOfFriendList();
    });

    // Chargement initial de la liste
    refreshFriendOfFriendList();

    </script>
    <script src="../javascript/logout.js"></script>
    <script src="../javascript/research_interest.js"></script>
    <script src="../javascript/friendtchat.js"></script>
    <script src="../javascript/home.js"></script>
    <!-- Ajoutez d'autres informations du compte personnel ici si nécessaire -->


</body>
</html>
