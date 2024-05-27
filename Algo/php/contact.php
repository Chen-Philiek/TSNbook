<?php
// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Votre adresse email
$recipient_email = "chenphiliek2002@gmail.com";

// Vérifie si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupère les données du formulaire
    $name = $_POST['contact_name'];
    $email = $_POST['contact_email'];
    $message = $_POST['contact_message'];

    // Prépare la requête d'insertion
    $sql = "INSERT INTO mails (name, email, message) VALUES ('$name', '$email', '$message')";

    // Exécute la requête d'insertion
    $insert_result = $bdd->query($sql);

    if ($insert_result === TRUE) {
        // Envoie un email de notification
        $subject = "Nouveau message de contact de $name";
        $email_content = "Nom: $name\n";
        $email_content .= "Email: $email\n";
        $email_content .= "Message:\n$message\n";

        // Envoi de l'email
        if (sendEmail($recipient_email, $subject, $email_content, $email)) {
            echo "Votre message a été envoyé avec succès.";
        } else {
            echo "Erreur lors de l'envoi de l'email. Veuillez réessayer plus tard.";
        }
    } else {
        echo "Une erreur s'est produite lors de l'enregistrement de votre message dans la base de données. Veuillez réessayer plus tard.";
    }
} else {
    // Redirection vers la page de contact si le formulaire n'a pas été soumis directement
    header("Location: Accueil.html");
}

function sendEmail($to, $subject, $message, $from) {
    // En-têtes de l'email
    $headers = "From: $from" . "\r\n";
    $headers .= "Reply-To: $from" . "\r\n";
    $headers .= "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/plain;charset=UTF-8" . "\r\n";

    // Envoi de l'email
    if (mail($to, $subject, $message, $headers)) {
        return true; // Succès de l'envoi de l'email
    } else {
        // Affiche les informations de débogage en cas d'échec de l'envoi
        echo "Erreur lors de l'envoi de l'email : " . error_get_last()['message'];
        return false; // Échec de l'envoi de l'email
    }
}
?>
