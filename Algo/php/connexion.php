<?php 
session_start(); // Démarrage de la session
require_once 'config.php'; // On inclut la connexion à la base de données

if (!empty($_POST['username']) && !empty($_POST['password'])) { // Si les champs username et password existent et ne sont pas vides
    // Patch XSS
    $username = htmlspecialchars($_POST['username']); 
    $password = $_POST['password'];
  
    // On vérifie si l'utilisateur est inscrit dans la table "users" en utilisant son username
    $check = $bdd->prepare('SELECT full_name, email, gender, age, telephone, password, token FROM users WHERE username = ?');
    $check->bindParam(1, $username); // Lier la valeur de $username au paramètre de substitution
    $check->execute();
    $data = $check->fetch();
    $row = $check->rowCount();
    
    // Si le nombre de résultats est supérieur à 0, alors l'utilisateur existe
    if ($row > 0) {
        // Si le mot de passe est correct
        if (password_verify($password, $data['password'])) {
            $_SESSION['user'] = $data['token']; // Attribuer le token à la session de l'utilisateur
            header('Location: home.php');
            exit();
        } else {
            header('Location: index.php?login_err=password');
            exit();
        }
    } else {
        header('Location: index.php?login_err=already');
        exit();
    }
} else {
    header('Location: home.php');
    exit();
}
?>
