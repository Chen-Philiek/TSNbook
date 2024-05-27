document.addEventListener('DOMContentLoaded', function() {
    const showNotificationButtons = document.querySelectorAll('.show-notification-btn');
    showNotificationButtons.forEach(button => {
        button.addEventListener('click', () => {
            const notification = button.parentElement;
            alert(notification.querySelector('p').textContent);
        });
    });

    const deleteNotificationButtons = document.querySelectorAll('.delete-notification-btn');
    deleteNotificationButtons.forEach(button => {
        button.addEventListener('click', () => {
            const notificationId = button.getAttribute('data-notification-id');
            if (confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
                // Envoyer une demande de suppression de notification via AJAX
                const xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            // Notification supprimée avec succès, actualiser la page ou effectuer d'autres actions si nécessaire
                            button.parentElement.remove(); // Supprimer la notification de l'interface utilisateur
                        } else {
                            // Gérer les erreurs de suppression
                            console.error('Erreur lors de la suppression de la notification :', xhr.status);
                        }
                    }
                };
                xhr.open('POST', 'delete_notification.php');
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.send(`notification_id=${notificationId}`);
            }
        });
    });
});