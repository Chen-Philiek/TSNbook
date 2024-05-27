// Fonction pour charger la conversation avec un ami spécifique
function loadConversation(friendName, friendId) {
    var conversationContainer = document.getElementById('conversationContainer');
    conversationContainer.innerHTML = 'Conversation avec ' + friendName;
    console.log('Loading conversation with friendId:', friendId);

    // Vérifier si une conversation existe déjà avec cet ami
    checkConversationExistence(friendId);
}

// Fonction pour vérifier l'existence d'une conversation avec l'ami spécifié
function checkConversationExistence(friendId) {
    console.log('Checking conversation existence for friendId:', friendId);
    // Vérifier l'existence de la conversation en utilisant une requête AJAX
    var xhrCheckConversation = new XMLHttpRequest();
    xhrCheckConversation.onreadystatechange = function() {
        if (xhrCheckConversation.readyState === XMLHttpRequest.DONE) {
            if (xhrCheckConversation.status === 200) {
                var response = JSON.parse(xhrCheckConversation.responseText);
                console.log('checkConversationExistence response:', response);
                if (response.success) {
                    // Conversation existante, charger les messages
                    var conversationId = response.conversation_id;
                    console.log('Existing conversation found with conversationId:', conversationId);
                    loadMessages(friendId, conversationId);
                } else {
                    // Conversation non existante, créer une nouvelle conversation
                    console.log('No existing conversation found. Creating a new one.');
                    createConversation(friendId);
                }
            } else {
                // Erreur lors de la vérification de la conversation
                console.error('Erreur lors de la vérification de la conversation');
            }
        }
    };
    xhrCheckConversation.open('POST', 'create_conversation.php');
    xhrCheckConversation.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhrCheckConversation.send('friend_id=' + encodeURIComponent(friendId));
}

// Fonction pour créer une nouvelle conversation avec l'ami spécifié
function createConversation(friendId) {
    console.log('Creating a new conversation with friendId:', friendId);
    // Créer une nouvelle conversation en utilisant une requête AJAX
    var xhrCreateConversation = new XMLHttpRequest();
    xhrCreateConversation.onreadystatechange = function() {
        if (xhrCreateConversation.readyState === XMLHttpRequest.DONE) {
            if (xhrCreateConversation.status === 200) {
                var response = JSON.parse(xhrCreateConversation.responseText);
                console.log('createConversation response:', response);
                if (response.success) {
                    // Conversation créée avec succès, charger les messages
                    var conversationId = response.conversation_id;
                    console.log('New conversation created with conversationId:', conversationId);
                    loadMessages(friendId, conversationId);
                } else {
                    // Erreur lors de la création de la conversation
                    console.error('Erreur lors de la création de la conversation:', response.message);
                }
            } else {
                // Erreur lors de la création de la conversation
                console.error('Erreur lors de la création de la conversation');
            }
        }
    };
    xhrCreateConversation.open('POST', 'create_conversation.php');
    xhrCreateConversation.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhrCreateConversation.send('friend_id=' + encodeURIComponent(friendId));
}

// Fonction pour charger les messages de la conversation avec l'ami sélectionné
function loadMessages(friendId, conversationId) {
    console.log('Loading messages for friendId:', friendId, 'and conversationId:', conversationId);
    // Charger la conversation avec l'ami sélectionné en utilisant une requête AJAX
    var xhrLoadMessages = new XMLHttpRequest();
    xhrLoadMessages.onreadystatechange = function() {
        if (xhrLoadMessages.readyState === XMLHttpRequest.DONE) {
            if (xhrLoadMessages.status === 200) {
                // Réponse reçue avec succès, afficher les messages dans la zone de conversation
                var messagesContainer = document.getElementById('messagesContainer');
                messagesContainer.innerHTML = xhrLoadMessages.responseText;
                // Afficher le formulaire pour envoyer des messages
                var sendMessageForm = document.getElementById('sendMessageForm');
                sendMessageForm.style.display = 'block';
                // Mettre à jour les attributs du formulaire avec les IDs
                sendMessageForm.setAttribute('data-friend-id', friendId);
                sendMessageForm.setAttribute('data-conversation-id', conversationId);
            } else {
                // Erreur lors du chargement de la conversation
                console.error('Erreur lors du chargement de la conversation');
            }
        }
    };
    xhrLoadMessages.open('POST', 'load_messages.php');
    xhrLoadMessages.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhrLoadMessages.send('friend_id=' + encodeURIComponent(friendId) + '&conversation_id=' + encodeURIComponent(conversationId));
}

// Script JavaScript pour gérer le clic sur les liens d'amis
var friendLinks = document.querySelectorAll('.friend-link');
friendLinks.forEach(function(link) {
    link.addEventListener('click', function(event) {
        event.preventDefault();
        var friendId = this.getAttribute('data-friend-id');
        var friendName = this.textContent;
        console.log('Friend link clicked with friendId:', friendId, 'and friendName:', friendName);

        // Charger la conversation avec l'ami sélectionné
        loadConversation(friendName, friendId);
    });
});

// Récupérer le formulaire d'envoi de message
const sendMessageForm = document.getElementById('sendMessageForm');

// Ajouter un gestionnaire d'événement pour le formulaire
sendMessageForm.addEventListener('submit', function(event) {
    event.preventDefault(); // Empêcher le formulaire de se soumettre normalement

    // Récupérer le contenu du message
    const messageContent = document.querySelector('.messageInput').value;
    const friendId = sendMessageForm.getAttribute('data-friend-id');
    const conversationId = sendMessageForm.getAttribute('data-conversation-id');
    console.log('Sending message with content:', messageContent, 'friendId:', friendId, 'conversationId:', conversationId);

    // Envoyer le message
    sendMessage(messageContent, friendId, conversationId);
});

// Fonction pour envoyer un message
function sendMessage(messageContent, friendId, conversationId) {
    console.log('Preparing to send message:', messageContent, 'to friendId:', friendId, 'in conversationId:', conversationId);
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Message envoyé avec succès
                console.log('Message envoyé avec succès');
                // Réinitialiser le champ de saisie après l'envoi
                document.querySelector('.messageInput').value = '';
                // Recharger les messages de la conversation actuelle après l'envoi
                loadMessages(friendId, conversationId);
            } else {
                // Erreur lors de l'envoi du message
                console.error('Erreur lors de l\'envoi du message');
            }
        }
    };
    xhr.open('POST', 'send_message.php');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    const params = 'message_content=' + encodeURIComponent(messageContent) +
                   '&friend_id=' + encodeURIComponent(friendId) +
                   '&conversation_id=' + encodeURIComponent(conversationId);
    xhr.send(params);
}
