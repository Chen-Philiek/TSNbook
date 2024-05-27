document.addEventListener('DOMContentLoaded', function () {
    const likeButtons = document.querySelectorAll('.like-btn');

    likeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const publicationId = button.getAttribute('data-id');

            // Envoi de la demande de like ou d'unlike au serveur via AJAX
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        // Mettre à jour l'interface utilisateur après l'ajout ou la suppression du like
                        const likeCountContainer = button.nextElementSibling.nextElementSibling;
                        likeCountContainer.textContent = xhr.responseText;
                        if (button.classList.contains('liked')) {
                            button.classList.remove('liked');
                        } else {
                            button.classList.add('liked');
                        }
                    } else {
                        // Gérer les erreurs de requête
                        console.error('Erreur lors du like ou du unlike :', xhr.status);
                    }
                }
            };

            // Déterminer si l'utilisateur aime déjà la publication ou non
            const isLiked = button.classList.contains('liked');
            if (isLiked) {
                // Si l'utilisateur aime déjà la publication, envoie une requête pour un-unlike
                xhr.open('POST', 'unlike.php');
            } else {
                // Sinon, envoie une requête pour un-like
                xhr.open('POST', 'like.php');
            }
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.send(`publication_id=${publicationId}`);
            updateLikeCount(publicationId, button);
        });
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
            
            if (confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')) {
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
                    alert('Publication réussie !!!');
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