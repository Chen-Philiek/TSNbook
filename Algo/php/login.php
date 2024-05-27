<?php
include 'config.php';

// Récupération des données du formulaire
$username = $_POST['username'];
$password = $_POST['password'];

// Vérification des informations d'identification
$sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Connexion réussie. Redirection vers la page d'accueil.";
    // Redirection vers la page d'accueil après une connexion réussie
    header('Location: home.php');
} else {
    echo "Nom d'utilisateur ou mot de passe incorrect.";
}

$conn->close();
?>
