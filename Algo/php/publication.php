<?php
session_start();
require_once 'config.php'; // Assurez-vous que le fichier de configuration est inclus
require_once 'functions.php';

// Récupérer les publications depuis la base de données à chaque chargement de la page
$stmt = $bdd->query("SELECT * FROM publications ORDER BY created_at DESC");
$stmt = $bdd->query("SELECT publications.*, users.full_name FROM publications INNER JOIN users ON publications.user_id = users.user_id ORDER BY publications.created_at DESC");
$publications = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Récupérer l'ID de l'utilisateur à partir du token s'il est connecté
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
        $_SESSION['user_id'] = $userId; // Stocker l'ID de l'utilisateur dans la session
    }
}

// Récupérer l'ID de l'utilisateur connecté
$userId = $_SESSION['user_id'];



// Filtrer les publications en fonction du niveau de confidentialité
$filteredPublications = [];
// Utilisation de la fonction pour vérifier si l'utilisateur est ami avec l'auteur de la publication
foreach ($publications as $publication) {
    // Vérifier le niveau de confidentialité de la publication
    if ($publication['privacy'] === 'public'|| $publication['user_id'] === $userId) {
        // Si la publication est publique, l'ajouter directement aux publications filtrées
        $filteredPublications[] = $publication;
    } elseif ($publication['privacy'] === 'friends' && areFriends($userId, $publication['user_id'], $bdd)) {
        // Si la publication est réservée aux amis uniquement, vérifier si l'utilisateur est ami avec l'auteur de la publication
        if (areFriends($userId, $publication['user_id'], $bdd)) {
            // Si l'utilisateur est ami avec l'auteur de la publication, l'ajouter aux publications filtrées
            $filteredPublications[] = $publication;
        }
    } elseif ($publication['privacy'] === 'private') {
        // Si la publication est privée, vérifier si l'utilisateur est l'auteur de la publication
        if ($publication['user_id'] === $userId) {
            // Si l'utilisateur est l'auteur de la publication, l'ajouter aux publications filtrées
            $filteredPublications[] = $publication;
        } else {
            // Si la publication est privée et que l'utilisateur n'est pas l'auteur, vérifier si l'utilisateur est ami avec l'auteur
            if (areFriends($userId, $publication['user_id'], $bdd)) {
                // Si l'utilisateur est ami avec l'auteur de la publication, l'ajouter aux publications filtrées
                $filteredPublications[] = $publication;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />

    <title>Publication</title>
    <link rel="stylesheet" href="../css/publication.css">
</head>
<header class="header">
     
<h1 class="d-inline-block text-uppercase">TSNbook</h1>
    <div class="logo">
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
    <div class="container">
        <h2>New post</h2>
        <form id="postForm" enctype="multipart/form-data">
            <input type="file" id="media" name="media" accept="image/*, video/*" onchange="previewMedia()">
            <label for="media">Add a video or image</label>
            
            <!-- Champ de sélection pour le niveau de confidentialité -->
            <label for="privacy">Privacy :</label>
            <select id="privacy" name="privacy">
                <option value="public">Public</option>
                <option value="friends">Friend only</option>
                <option value="private">Private</option>
            </select>

            <textarea id="description" name="description" placeholder="Description of the post"></textarea>
            <button type="submit">Publish</button>
        </form>

        <div id="preview"></div>
    </div>

    <div id="posts" class="container">
    <!-- Afficher les publications à partir de la variable $filteredPublications -->
    <?php foreach ($filteredPublications as $publication): ?>
        <div class="post">
            <!-- Afficher le nom du publicateur -->
            <p>Publisher: <?php echo $publication['full_name']; ?></p>
            <!-- Afficher les détails de la publication (description, média, date, etc.) -->
            <p>Description: <?php echo $publication['description']; ?></p>
            <?php if ($publication['file_type'] === 'image/png'): ?>
                <img src="<?php echo $publication['file_path']; ?>" alt="Image de la publication">
            <?php elseif ($publication['file_type'] === 'video/mp4'): ?>
                <video controls>
                    <source src="<?php echo $publication['file_path']; ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php endif; ?>
            <p>Date of publication: <?php echo $publication['created_at']; ?></p>
            
           <!-- Div pour afficher les commentaires -->
            <div class="comments-container">
                <?php
                // Récupérer les commentaires pour cette publication
                $publicationId = $publication['id'];
                $getCommentsQuery = "SELECT comments.*, users.full_name FROM comments INNER JOIN users ON comments.user_id = users.user_id WHERE publication_id = :publicationId ORDER BY comments.created_at DESC";
                $getCommentsStmt = $bdd->prepare($getCommentsQuery);
                $getCommentsStmt->bindParam(':publicationId', $publicationId, PDO::PARAM_INT);
                $getCommentsStmt->execute();
                $comments = $getCommentsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Afficher les commentaires
                foreach ($comments as $comment) {
                    echo "<div class='comment'>";
                    echo "<p class='comment-date'>Publié le {$comment['created_at']} - <strong>{$comment['full_name']}</strong></p>";
                    echo "<p> {$comment['comment']} </p> ";
                    echo "</div>";
                }
                ?>
            </div>
            <!-- Div pour afficher les likes -->
            <div class="like-container">
                <!-- Afficher l'image de like avec le nombre de likes comme légende -->
                <img src="../img/like.png" alt="Like" class="like-image">
                <span class="like-count">
                    <?php
                    // Récupérer l'identifiant de la publication
                    $publicationId = $publication['id'];
                    
                    // Requête pour compter le nombre de likes pour cette publication
                    $likeCountQuery = "SELECT COUNT(*) AS like_count FROM likes WHERE publication_id = :publicationId";
                    $likeCountStmt = $bdd->prepare($likeCountQuery);
                    $likeCountStmt->bindParam(':publicationId', $publicationId, PDO::PARAM_INT);
                    $likeCountStmt->execute();
                    $likeCount = $likeCountStmt->fetchColumn();
                    
                    // Afficher le nombre de likes
                    echo $likeCount;
                    ?>
                </span>
            </div>



           <!-- Bouton de like et zone de commentaire -->
            <div class="like-comment-container">
                <button class="like-btn" data-id="<?php echo $publication['id']; ?>">Like</button>
                <div class="comment-section">
                    <input type="text" class="comment-input" placeholder="Add a comment...">
                    <button class="comment-btn" data-id="<?php echo $publication['id']; ?>">Comment</button>
                </div>
            </div>

            <!-- Bouton de suppression -->
            <?php if ($publication['user_id'] == $_SESSION['user_id']): ?>
                <button class="delete-btn" data-id="<?php echo $publication['id']; ?>">Delete</button>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
    <script>
    // Script pour les actions de like et de commentaire
    const likeButtons = document.querySelectorAll('.like-btn');

    likeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const publicationId = button.getAttribute('data-id');

            // Envoi de la demande de like au serveur via AJAX
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        // Actualiser l'interface utilisateur ou effectuer d'autres actions si nécessaire
                        console.log('Like effectué avec succès');
                        window.location.reload();
                    } else {
                        // Gérer les erreurs de requête
                        console.error('Erreur lors du like :', xhr.status);
                    }
                }
            };
            xhr.open('POST', 'like.php');
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.send(`publication_id=${publicationId}`);
            updateLikeCount(publicationId, button);
        });
        // Fonction pour mettre à jour le nombre de likes affiché
        function updateLikeCount(publicationId, button) {
            const likeCountContainer = button.nextElementSibling.nextElementSibling;
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        // Mettre à jour le contenu de l'élément avec le nombre de likes
                        likeCountContainer.textContent = xhr.responseText;
                    } else {
                        // Gérer les erreurs de requête
                        console.error('Erreur lors de la récupération du nombre de likes :', xhr.status);
                    }
                }
            };
            xhr.open('POST', 'likes.php');
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.send(`publication_id=${publicationId}`);
        }
            
       // Récupérer la zone de commentaire associée
        const commentSection = button.nextElementSibling;

        // Ajouter un gestionnaire d'événement pour le bouton "Commenter"
        const commentButton = commentSection.querySelector('.comment-btn');
        commentButton.addEventListener('click', () => {
            const publicationId = button.getAttribute('data-id');
            const commentInput = commentSection.querySelector('.comment-input');

            // Envoi de la demande de commentaire au serveur via AJAX
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        // Réinitialiser la valeur de l'entrée de commentaire
                        commentInput.value = '';
                        // Actualiser l'interface utilisateur ou effectuer d'autres actions si nécessaire
                        console.log('Commentaire ajouté avec succès');
                        window.location.reload();
                    } else {
                        // Gérer les erreurs de requête
                        console.error('Erreur lors de l\'ajout du commentaire :', xhr.status);
                    }
                }
            };
            xhr.open('POST', 'comment.php');
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.send(`publication_id=${publicationId}&comment=${encodeURIComponent(commentInput.value)}`);
        });

    });


  




        // Script pour la suppression de publication
        const deleteButtons = document.querySelectorAll('.delete-btn');

        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                const publicationId = button.getAttribute('data-id');
                
                if (confirm('Are you sure to delete this post ?')) {
                    // Envoi de la demande de suppression au serveur via AJAX
                    const xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status === 200) {
                                // Actualiser la page après la suppression réussie
                                window.location.reload();
                            } else {
                                // Afficher un message d'erreur en cas d'échec de la suppression
                                alert('Erreur lors de la suppression de la publication.');
                            }
                        }
                    };
                    xhr.open('POST', 'delete_publication.php');
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.send(`publication_id=${publicationId}`);
                }
            });
        });

        function previewMedia() {
            const mediaFile = document.getElementById('media').files[0];
            const preview = document.getElementById('preview');

            if (mediaFile) {
                preview.innerHTML = ''; // Supprimer le contenu précédent de l'aperçu

                const mediaType = mediaFile.type.split('/')[0];
                if (mediaType === 'image') {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(mediaFile);
                    preview.appendChild(img);
                } else if (mediaType === 'video') {
                    const video = document.createElement('video');
                    video.src = URL.createObjectURL(mediaFile);
                    video.controls = true;
                    preview.appendChild(video);
                }
            }
        }

        const postForm = document.getElementById('postForm');
        const postsContainer = document.getElementById('posts');

        postForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Empêcher la soumission par défaut du formulaire

            const formData = new FormData(this); // Créer un objet FormData avec les données du formulaire

            const xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    console.log(xhr.responseText);
                    if (xhr.status === 200) {
                        // La publication a été envoyée avec succès
                        // Afficher la publication dans la section des publications
                        const post = document.createElement('div');
                        post.classList.add('post');

                        // Créer un élément pour afficher la description
                        const description = document.createElement('p');
                        description.textContent = formData.get('description');
                        post.appendChild(description);

                        const mediaType = formData.get('media').type.split('/')[0];
                        if (mediaType === 'image') {
                            const img = document.createElement('img');
                            img.src = URL.createObjectURL(formData.get('media'));
                            post.appendChild(img);
                        } else if (mediaType === 'video') {
                            const video = document.createElement('video');
                            video.src = URL.createObjectURL(formData.get('media'));
                            video.controls = true;
                            post.appendChild(video);
                        }
                        const date = new Date();
                        const dateTimeString = date.toLocaleString();
                        const dateTimeNode = document.createTextNode(`Publié le ${dateTimeString}`);
                        const br = document.createElement('br');
                        post.appendChild(br);
                        post.appendChild(dateTimeNode);
                        postsContainer.prepend(post); // Ajouter la nouvelle publication au début de la liste des publications
                        postForm.reset(); // Réinitialiser le formulaire
                        document.getElementById('preview').innerHTML = ''; // Réinitialiser la zone de prévisualisation
                        alert('Publication done !');
                    } else {
                        // Il y a eu une erreur lors de l'envoi de la publication
                        alert('Erreur lors de la publication. Veuillez réessayer.');
                    }
                }
            };

            // Envoyer les données du formulaire via POST à l'URL spécifiée (à remplacer par votre URL de traitement côté serveur)
            xhr.open('POST', 'publication_trait.php');
            xhr.send(formData);
        });
    </script>
   
    <script src="../javascript/logout.js"></script>
    

</body>
</html>
