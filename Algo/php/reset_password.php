<?php
require_once 'config.php';

if (!empty($_POST['newPassword']) && !empty($_POST['token'])) {
    $newPassword = password_hash($_POST['newPassword'], PASSWORD_BCRYPT);
    $token = htmlspecialchars($_POST['token']);

    // Vérifiez si le token est valide
    $check = $bdd->prepare('SELECT id FROM users WHERE token = ?');
    $check->bindParam(1, $token);
    $check->execute();
    $row = $check->rowCount();

    if ($row > 0) {
        // Mettre à jour le mot de passe et supprimer le token
        $update = $bdd->prepare('UPDATE users SET password = ?, token = NULL WHERE token = ?');
        $update->bindParam(1, $newPassword);
        $update->bindParam(2, $token);
        $update->execute();

        echo "Votre mot de passe a été réinitialisé avec succès.";
    } else {
        echo "Le lien de réinitialisation est invalide.";
    }
} else {
    echo "Veuillez fournir un nouveau mot de passe.";
}
?>
