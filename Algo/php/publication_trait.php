<?php
session_start();
require_once 'config.php';

// Vérifier si des données ont été envoyées via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données envoyées depuis le formulaire
    $description = $_POST['description']; // Récupérer la description
    $privacy = $_POST['privacy']; // Récupérer le niveau de confidentialité

    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        
        // Vérifier s'il y a un fichier média envoyé
        if(isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
            // Récupérer les informations sur le fichier média
            $file_name = $_FILES['media']['name'];
            $file_tmp_name = $_FILES['media']['tmp_name'];
            $file_type = $_FILES['media']['type'];

            // Déplacer le fichier média vers un emplacement permanent sur le serveur
            $upload_dir = "C:/xampp/htdocs/Algo projet/uploads/"; // Répertoire où stocker les fichiers téléchargés
            $file_path = $upload_dir . $file_name;
            move_uploaded_file($file_tmp_name, $file_path);
        } else {
            // Si aucun fichier média n'a été envoyé, définissez les variables du fichier sur null
            $file_name = null;
            $file_tmp_name = null;
            $file_type = null;
            $file_path = null;
        }

        // Insérer les données dans la base de données
        $stmt = $bdd->prepare("INSERT INTO publications (user_id, description, file_path, file_name, file_type, privacy, created_at) VALUES (:user_id, :description, :file_path, :file_name, :file_type, :privacy, NOW())");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':description', $description); // Liaison de la description
        $stmt->bindParam(':file_path', $file_path);
        $stmt->bindParam(':file_name', $file_name);
        $stmt->bindParam(':file_type', $file_type);
        $stmt->bindParam(':privacy', $privacy); // Liaison du niveau de confidentialité
        
        try {
            $stmt->execute();
            // Envoyer une réponse HTTP 200 OK si l'insertion a réussi
            http_response_code(200);
            echo "Publication réussie !";
        } catch(PDOException $e) {
            // En cas d'erreur lors de l'insertion dans la base de données, afficher l'erreur
            http_response_code(500);
            echo "Erreur lors de la publication : " . $e->getMessage();
        }
    } else {
        // Si la session utilisateur n'est pas définie, rediriger vers la page de connexion
        header("Location: login.php");
        exit;
    }
} else {
    // Si la requête n'est pas de type POST, afficher un message d'erreur
    http_response_code(405);
    echo "Méthode non autorisée.";
}




// Maintenant, $filteredPublications contient les publications à afficher dans le fil d'actualité de l'utilisateur
// Vous pouvez utiliser ces données pour afficher les publications dans votre interface utilisateur
?>
