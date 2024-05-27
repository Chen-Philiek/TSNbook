<?php
require_once 'config.php';

if (!empty($_POST['email'])) {
    $email = htmlspecialchars($_POST['email']);

    // Vérifiez si l'email existe dans la base de données
    $check = $bdd->prepare('SELECT token FROM users WHERE email = ?');
    $check->bindParam(1, $email);
    $check->execute();
    $data = $check->fetch();
    $row = $check->rowCount();

    if ($row > 0) {
        $token = bin2hex(random_bytes(50));
        
        // Mettre à jour le token dans la base de données
        $update = $bdd->prepare('UPDATE users SET token = ? WHERE email = ?');
        $update->bindParam(1, $token);
        $update->bindParam(2, $email);
        $update->execute();

        // Envoyer un email de réinitialisation
        $resetLink = "http://localhost/Algo%20projet/php/reset_password.php?token=" . $token;
        $message = "Pour réinitialiser votre mot de passe, veuillez cliquer sur le lien suivant : " . $resetLink;
        mail($email, 'Réinitialisation du mot de passe', $message);

        echo "Un email de réinitialisation a été envoyé à votre adresse.";
    } else {
        echo "Aucun compte n'est associé à cet email.";
    }
} else {
    echo "Veuillez fournir un email.";
}
?>
