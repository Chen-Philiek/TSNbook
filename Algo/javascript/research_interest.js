
document.addEventListener("DOMContentLoaded", function() {
    const interestInput = document.getElementById('interestInput');
    const interestSuggestions = document.getElementById('interestSuggestions');

    interestInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        if (searchTerm.length === 0) {
            interestSuggestions.innerHTML = '';
            return;
        }

        // Effectuer une requête AJAX pour récupérer les suggestions d'intérêts
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    const suggestions = JSON.parse(xhr.responseText);
                    displaySuggestions(suggestions);
                } else {
                    console.error('Une erreur est survenue lors de la recherche des suggestions d\'intérêts.');
                }
            }
        };
        xhr.open('GET', `get_interest_suggestions.php?searchTerm=${encodeURIComponent(searchTerm)}`, true);
        xhr.send();
    });

    function displaySuggestions(suggestions) {
        if (suggestions.length === 0) {
            interestSuggestions.innerHTML = 'Aucun résultat trouvé.';
        } else {
            let html = '<ul>';
            suggestions.forEach(suggestion => {
                html += `<li>${suggestion}</li>`;
            });
            html += '</ul>';
            interestSuggestions.innerHTML = html;
        }
    }
});

// Récupérer la modal pour les intérêts
var interestsModal = document.getElementById("myModal");

// Récupérer le bouton qui ouvre la modal des intérêts
var interestsBtn = document.getElementById("editInterestsButton");

// Récupérer l'élément span qui ferme la modal des intérêts
var interestsClose = interestsModal.getElementsByClassName("closee")[0];

// Quand l'utilisateur clique sur le bouton pour les intérêts, ouvrir la modal
interestsBtn.onclick = function() {
    interestsModal.style.display = "block";
}

// Quand l'utilisateur clique sur <span> (x) pour les intérêts, fermer la modal
interestsClose.onclick = function() {
    interestsModal.style.display = "none";
}

// Quand l'utilisateur clique en dehors de la modal pour les intérêts, fermer celle-ci
window.onclick = function(event) {
    if (event.target == interestsModal) {
        interestsModal.style.display = "none";
    }
}
