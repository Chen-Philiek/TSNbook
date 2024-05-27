<?php
require_once 'config.php'; // On inclut la connexion à la base de données

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $telephone = $_POST['telephone'];
    $username = $_POST['signupUsername'];
    $password = $_POST['signupPassword'];

    // Hacher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Génération d'un token unique
    $token = bin2hex(openssl_random_pseudo_bytes(64));

    // Vérifier si l'utilisateur existe déjà dans la base de données
    $checkUserQuery = "SELECT * FROM users WHERE username = :username";
    $checkUserStmt = $bdd->prepare($checkUserQuery);
    $checkUserStmt->bindParam(':username', $username);
    $checkUserStmt->execute();
    $checkUserResult = $checkUserStmt->fetch(PDO::FETCH_ASSOC);

    if ($checkUserResult) {
        echo "User name already used, please choose another one.";
    } else {
        // Insérer les données dans la base de données
        $insertUserQuery = "INSERT INTO users (full_name, email, gender, age, telephone, username, password, token) VALUES (:fullName, :email, :gender, :age, :telephone, :username, :password, :token)";
        $insertUserStmt = $bdd->prepare($insertUserQuery);
        $insertUserStmt->bindParam(':fullName', $fullName);
        $insertUserStmt->bindParam(':email', $email);
        $insertUserStmt->bindParam(':gender', $gender);
        $insertUserStmt->bindParam(':age', $age);
        $insertUserStmt->bindParam(':telephone', $telephone);
        $insertUserStmt->bindParam(':username', $username);
        $insertUserStmt->bindParam(':password', $hashed_password);
        $insertUserStmt->bindParam(':token', $token);

        // Exécuter la requête d'insertion
        if ($insertUserStmt->execute()) {
            echo "Account created successfully.";
            echo '<button onclick="window.location.href=\'../html/connexion.html\'">Back to login</button>';
        } else {
            echo "Erreur lors de la création du compte : " . $insertUserStmt->errorInfo()[2];
        }
    }
}
?>
