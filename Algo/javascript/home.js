// Récupérer la modal pour les données personnelles
var profileModal = document.getElementById("welcomeModal");

// Récupérer le bouton qui ouvre la modal des données personnelles
var profileBtn = document.getElementById("editProfileButton");

// Récupérer l'élément span qui ferme la modal des données personnelles
var profileClose = document.getElementsByClassName("close")[0];

// Quand l'utilisateur clique sur le bouton pour les données personnelles, ouvrir la modal
profileBtn.onclick = function() {
    profileModal.style.display = "block";
}

// Quand l'utilisateur clique sur <span> (x) pour les données personnelles, fermer la modal
profileClose.onclick = function() {
    profileModal.style.display = "none";
}

// Quand l'utilisateur clique en dehors de la modal pour les données personnelles, fermer celle-ci
window.onclick = function(event) {
    if (event.target == profileModal) {
        profileModal.style.display = "none";
    }
}

 // Script pour la suppression de publication
 const deleteButtons = document.querySelectorAll('.deleteInterest');

 deleteButtons.forEach(button => {
     button.addEventListener('click', () => {
         const publicationId = button.getAttribute('data-user-id');
         
         if (confirm('Êtes-vous sûr de vouloir supprimer cet intérêt ?')) {
             // Envoi de la demande de suppression au serveur via AJAX
             const xhr = new XMLHttpRequest();
             xhr.onreadystatechange = function() {
                 if (xhr.readyState === XMLHttpRequest.DONE) {
                     if (xhr.status === 200) {
                         // Actualiser la page après la suppression réussie
                         window.location.reload();
                     } else {
                         // Afficher un message d'erreur en cas d'échec de la suppression
                         alert('Erreur lors de la suppression.');
                     }
                 }
             };
             xhr.open('POST', 'delete_interest.php');
             xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
             xhr.send(`interest_id=${interestId}`);
         }
     });
 });


