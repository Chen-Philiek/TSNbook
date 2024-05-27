<?php
session_start();
require_once 'config.php'; // Assurez-vous que le fichier de configuration est inclus
require_once 'functions.php';

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

// Gérer la création de groupes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['group_name'])) {
    $groupName = htmlspecialchars($_POST['group_name']);
    $creatorId = $userInfo['user_id'];
    // $members = $_POST['members'];

    // Créer le groupe
    $createGroupQuery = "INSERT INTO groups (group_name, creator_id) VALUES (:group_name, :creator_id)";
    $createGroupStmt = $bdd->prepare($createGroupQuery);
    $createGroupStmt->bindParam(':group_name', $groupName);
    $createGroupStmt->bindParam(':creator_id', $creatorId);
    $createGroupStmt->execute();

    // Récupérer l'ID du groupe nouvellement créé
    $groupId = $bdd->lastInsertId();

    // // Associer les membres sélectionnés au groupe
    // foreach ($members as $memberId) {
    //     $addGroupMemberQuery = "INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)";
    //     $addGroupMemberStmt = $bdd->prepare($addGroupMemberQuery);
    //     $addGroupMemberStmt->bindParam(':group_id', $groupId);
    //     $addGroupMemberStmt->bindParam(':user_id', $memberId);
    //     $addGroupMemberStmt->execute();
    // }
}

// Récupérer les groupes auxquels l'utilisateur est associé (en tant que créateur ou membre)
$getGroupsQuery = "
    SELECT g.group_id, g.group_name 
    FROM groups g 
    LEFT JOIN group_members gm ON g.group_id = gm.group_id 
    WHERE g.creator_id = :user_id OR gm.user_id = :user_id
    GROUP BY g.group_id, g.group_name";
$getGroupsStmt = $bdd->prepare($getGroupsQuery);
$getGroupsStmt->bindParam(':user_id', $userInfo['user_id']);
$getGroupsStmt->execute();
$groups = $getGroupsStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les amis de l'utilisateur
$getFriendsQuery = "SELECT u.user_id, u.full_name FROM friendships f JOIN users u ON f.friend_id = u.user_id WHERE f.user_id = :user_id";
$getFriendsStmt = $bdd->prepare($getFriendsQuery);
$getFriendsStmt->bindParam(':user_id', $userInfo['user_id']);
$getFriendsStmt->execute();
$friends = $getFriendsStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les commentaires pour chaque groupe
$groupComments = [];
$getCommentsQuery = "
    SELECT mg.group_id, mg.message, mg.created_at, u.full_name 
    FROM messagesgroups mg 
    JOIN users u ON mg.user_id = u.user_id 
    WHERE mg.group_id = :group_id 
    ORDER BY mg.created_at ASC";
$getCommentsStmt = $bdd->prepare($getCommentsQuery);

foreach ($groups as $group) {
    $getCommentsStmt->bindParam(':group_id', $group['group_id']);
    $getCommentsStmt->execute();
    $groupComments[$group['group_id']] = $getCommentsStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Groupes</title>
    <link rel="stylesheet" href="../css/publication.css">
    <style>
        /* CSS pour le formulaire de création de groupe */
        #createGroupForm {
            margin-top: 20px;
        }
        #groupListContainer {
            margin-top: 20px;
        }
        #groupListContainer ul {
            list-style: none;
            padding: 0;
        }
        #groupListContainer ul li {
            margin-bottom: 10px;
        }
        input[type="text"],
        select {
           
            padding: 10px;
            margin: auto;
            border-radius: 5px;
     
            justify-content: center;
            border: 1px solid #ccc;
        }
        .form-group input{
            padding: 10px;
            margin: auto;
            border-radius: 5px;
            width: 90%;
            border: 1px solid #ccc;
        }
       
        label {
            display: block;
            font-weight: bold;
            text-align: center;
        }
        .comments-list {
            max-height: 100px; /* Limitez la hauteur du div pour éviter qu'il ne devienne trop grand */
            overflow-y: auto; /* Ajoutez une barre de défilement verticale si nécessaire */
            margin-bottom: 10px; /* Ajoutez un espace en bas du div des commentaires */
            border: 1px solid;
            padding: 20px 10%; /* Ajouter un padding de 10px en haut et en bas, et de 10px à gauche et à droite */

        }
                /* Styles pour la modale */
        .modal {
            display: none; /* Masquer la modale par défaut */
            position: fixed; /* Rester en place */
            z-index: 1; /* Rester au-dessus */
            left: 0;
            top: 0;
            width: 100%; /* Largeur pleine */
            height: 100%; /* Hauteur pleine */
            overflow: auto; /* Activer le défilement si nécessaire */
            background-color: rgb(0,0,0); /* Couleur de fond */
            background-color: rgba(0,0,0,0.4); /* Couleur de fond avec opacité */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% du haut et centré */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Peut être ajusté pour s'adapter */
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .member-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
        }

        .delete-member-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
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
            <li>
                <div class="notification-box">
                    <a href="../php/notifications.php" class="notification-button">Notifications</a>
                </div>
            </li>
     
            <li><a href="../php/publication.php" id="publish">Publications</a></li>
            <li><a href="../php/friends.php" id="friend">My relations</a></li>
            <li><a href="../php/Graphes.php" id="graphes">Graphs</a></li>
            <li><a href="../php/home.php">My profil</a></li>
            <li><a href="../html/connexion.html" id="logout">Log out</a></li>
        </ul>
    </nav>
</header>
<div class="container-wrapper">
    <div class="container-column">
        <div class="container" id="groupFormContainer">
            <h3>Create a team conversation</h3>
            <form id="createGroupForm" method="post" action="groups.php">
                <div class="form-group">
                    <label for="group_name">Name of the group:</label>
                    <input type="text" id="group_name" name="group_name" required>
                </div>
                <!-- <div class="form-group">
                    <label for="friendSelect">Ajouter des amis:</label>
                    <select id="friendSelect">
                        <?php foreach ($friends as $friend) : ?>
                            <option value="<?php echo $friend['user_id']; ?>"><?php echo $friend['full_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="addFriendButton">Ajouter</button>
                </div>
                <div class="form-group">
                    <h4>Amis ajoutés:</h4>
                    <ul id="selectedFriendsList"></ul>
                </div> -->
                <input type="hidden" name="friends[]" id="friendsInput">
                <button type="submit">Create</button>
            </form>
        </div>
        <div class="container" id="groupListContainer">
            <h3>My groups</h3>
            <?php foreach ($groups as $group): ?>
                <div class="group-container" data-group-id="<?php echo $group['group_id']; ?>">
                    <h4><?php echo htmlspecialchars($group['group_name']); ?></h4>
                    
                    <div class="add-member-section">
                        <!-- Bouton pour afficher les membres du groupe -->
                    <button class="view-members-btn" data-group-id="<?php echo $group['group_id']; ?>">...</button>
                        <select class="friend-select">
                            <?php foreach ($friends as $friend) : ?>
                                <option value="<?php echo $friend['user_id']; ?>"><?php echo $friend['full_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="add-member-btn">Add members</button>
                        
                    </div>
                    <div class="comments-list">
                        <ul>
                            <?php foreach ($groupComments[$group['group_id']] as $comment): ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($comment['full_name']); ?>:</strong> 
                                    <?php echo htmlspecialchars($comment['message']); ?>
                                    <br>
                                    <small><?php echo htmlspecialchars($comment['created_at']); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="comment-section">
                        <input type="text" class="comment-input" placeholder="Send a message...">
                        <button class="comment-btn">Send</button>
                        <!-- Bouton de suppression du groupe -->
                        <button class="delete-group-btn" data-group-id="<?php echo $group['group_id']; ?>">Delete the group</button>
                    </div>
                   
                </div>
            <?php endforeach; ?>
        </div>

<!-- Modale pour afficher les membres -->
<div id="memberModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Team members</h2>
        <ul id="memberList"></ul>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

$(document).ready(function() {
    var modal = $('#memberModal');
    var span = $('.close');

    $('.view-members-btn').click(function() {
        var groupId = $(this).data('group-id');
        $.ajax({
            type: 'POST',
            url: 'get_group_members.php', // Fichier pour récupérer les membres
            data: { group_id: groupId },
            success: function(response) {
                try {
                    var members = JSON.parse(response);
                    var memberList = $('#memberList');
                    memberList.empty();
                    members.forEach(function(member) {
                        var listItem = $('<li class="member-item"></li>');
                        listItem.text(member.full_name);
                        var deleteButton = $('<button class="delete-member-btn" data-member-id="' + member.user_id + '">Supprimer</button>');
                        listItem.append(deleteButton);
                        memberList.append(listItem);
                    });
                    modal.show();
                } catch (e) {
                    console.error('Erreur lors du traitement de la réponse JSON: ', e);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur lors de la récupération des membres :', xhr.status);
            }
        });
    });

    span.click(function() {
        modal.hide();
    });

    $(window).click(function(event) {
        if (event.target == modal[0]) {
            modal.hide();
        }
    });

    $(document).on('click', '.delete-member-btn', function() {
        var memberId = $(this).data('member-id');
        var groupId = $('.view-members-btn').data('group-id');
        var listItem = $(this).parent();

        $.ajax({
            type: 'POST',
            url: 'delete_member.php', // Fichier pour supprimer le membre
            data: {
                group_id: groupId,
                user_id: memberId
            },
            success: function(response) {
                try {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.success) {
                        listItem.remove();
                        console.log('Membre supprimé avec succès');
                    } else {
                        console.error('Erreur: ' + jsonResponse.error);
                    }
                } catch (e) {
                    console.error('Erreur lors du traitement de la réponse JSON: ', e);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur lors de la suppression du membre :', xhr.status);
            }
        });
    });
});


 

    $(document).ready(function() {
        $('#addFriendButton').click(function() {
            var selectedFriendId = $('#friendSelect').val();
            var selectedFriendText = $('#friendSelect option:selected').text();
            $('#selectedFriendsList').append('<li data-id="' + selectedFriendId + '">' + selectedFriendText + '</li>');
            var selectedFriends = $('#selectedFriendsList li').map(function() {
                return $(this).data('id');
            }).get();
            $('#friendsInput').val(JSON.stringify(selectedFriends));
        });

        $('.comment-btn').click(function() {
            var groupId = $(this).closest('.group-container').data('group-id');
            var commentInput = $(this).siblings('.comment-input');
            var commentText = commentInput.val();

            $.ajax({
                type: 'POST',
                url: 'conv_group.php',
                data: {
                    group_id: groupId,
                    message: commentText
                },
                success: function(response) {
                    try {
                        var jsonResponse = JSON.parse(response);
                        if (jsonResponse.success) {
                            commentInput.val('');
                            console.log('Commentaire ajouté avec succès');
                            window.location.reload();
                        } else {
                            console.error('Erreur: ' + jsonResponse.error);
                        }
                    } catch (e) {
                        console.error('Erreur lors du traitement de la réponse JSON: ', e);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur lors de l\'ajout du commentaire :', xhr.status);
                }
            });
        });

        $('.add-member-btn').click(function() {
            var groupId = $(this).closest('.group-container').data('group-id');
            var friendSelect = $(this).siblings('.friend-select');
            var userId = friendSelect.val();

            $.ajax({
                type: 'POST',
                url: 'add_members.php',
                data: {
                    group_id: groupId,
                    user_id: userId
                },
                success: function(response) {
                    try {
                        var jsonResponse = JSON.parse(response);
                        if (jsonResponse.success) {
                            console.log('Membre ajouté avec succès');
                        } else {
                            console.error('Erreur: ' + jsonResponse.error);
                        }
                    } catch (e) {
                        console.error('Erreur lors du traitement de la réponse JSON: ', e);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur lors de l\'ajout du membre :', xhr.status);
                }
            });
        });
    });
    $('.delete-group-btn').click(function() {
        var groupId = $(this).data('group-id');
        var confirmation = confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?');
        if (confirmation) {
            $.ajax({
                type: 'POST',
                url: 'delete_group.php', // Remplacez 'delete_group.php' par le fichier de suppression de groupe
                data: {
                    group_id: groupId
                },
                success: function(response) {
                    try {
                        var jsonResponse = JSON.parse(response);
                        if (jsonResponse.success) {
                            // Supprimer le groupe de l'interface utilisateur
                            $('.group-container[data-group-id="' + groupId + '"]').remove();
                            console.log('Groupe supprimé avec succès');
                            window.location.reload();
                        } else {
                            console.error('Erreur: ' + jsonResponse.error);
                        }
                    } catch (e) {
                        console.error('Erreur lors du traitement de la réponse JSON: ', e);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur lors de la suppression du groupe :', xhr.status);
                }
            });
        }
    });
</script>

<script src="../javascript/logout.js"></script>
    
</body>
</html>
