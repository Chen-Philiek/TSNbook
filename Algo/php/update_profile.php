<?php
// Démarrer la session
session_start();

require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    // Rediriger vers la page de connexion ou afficher un message d'erreur
    header("Location: login.php");
    exit();
}

// Récupérer les informations de l'utilisateur actuel depuis la session
$userToken = $_SESSION['user'];

// Effectuer une requête pour obtenir les informations de l'utilisateur à partir du token
$userQuery = "SELECT * FROM users WHERE token = :token";
$userStmt = $bdd->prepare($userQuery);
$userStmt->bindParam(':token', $userToken);
$userStmt->execute();
$userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous que les données sont valides (vérifiez les validations appropriées ici)

    // Récupérer les données du formulaire
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $telephone = $_POST['telephone'];

    // Effectuer la mise à jour dans la base de données
    // Exemple de requête SQL (assurez-vous d'adapter cela à votre structure de base de données)
    $updateQuery = "UPDATE users SET full_name = :fullName, email = :email, gender = :gender, age = :age, telephone = :telephone WHERE user_id = :userId";

    // Préparer la requête
    $stmt = $bdd->prepare($updateQuery);

    // Liaison des paramètres
    $stmt->bindParam(':fullName', $fullName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':age', $age);
    $stmt->bindParam(':telephone', $telephone);
    $stmt->bindParam(':userId', $userInfo['user_id']);

    // Exécution de la requête
    if ($stmt->execute()) {
        // Redirection vers la page de compte personnel ou afficher un message de succès
        header("Location: home.php");
        exit();
    } else {
        // Gérer les erreurs de mise à jour
        echo "Erreur lors de la mise à jour des données.";
    }
}
?>
