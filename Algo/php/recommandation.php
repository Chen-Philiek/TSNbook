<?php
session_start();
require_once 'config.php';

// Fonction pour recommander des amis à un utilisateur donné
function recommander_amis($utilisateur_actuel, $bdd) {
    // Récupérer les données des utilisateurs depuis la base de données
    $query = "SELECT * FROM users WHERE id != :current_user_id";
    $stmt = $bdd->prepare($query);
    $stmt->bindParam(':current_user_id', $utilisateur_actuel['id']);
    $stmt->execute();
    $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Implémenter la logique de recommandation d'amis
    $recommandations_amis = [];
    foreach ($utilisateurs as $utilisateur) {
        // Calcul du score basé sur les intérêts communs
        $score = calculer_score($utilisateur_actuel['interests'], $utilisateur['interests']);

        // Ajouter l'utilisateur avec son score aux recommandations
        $recommandations_amis[] = [
            'id' => $utilisateur['id'],
            'full_name' => $utilisateur['full_name'],
            'score' => $score
        ];
    }

    // Trier les recommandations par score de similarité
    usort($recommandations_amis, function($a, $b) {
        return $b['score'] - $a['score'];
    });

    // Retourner les meilleures recommandations
    return array_slice($recommandations_amis, 0, 5);
}

// Fonction pour calculer le score basé sur les intérêts communs
function calculer_score($interests1, $interests2) {
    // Convertir les listes d'intérêts en tableaux
    $interests_array1 = explode(',', $interests1);
    $interests_array2 = explode(',', $interests2);

    // Calculer le nombre d'intérêts communs
    $common_interests_count = count(array_intersect($interests_array1, $interests_array2));

    // Retourner le score calculé
    return $common_interests_count;
}

// Récupérer les informations de l'utilisateur actuel à partir de la session
if (isset($_SESSION['user'])) {
    $token = $_SESSION['user'];
    $getInfoQuery = "SELECT * FROM users WHERE token = :token";
    $getInfoStmt = $bdd->prepare($getInfoQuery);
    $getInfoStmt->bindParam(':token', $token);
    $getInfoStmt->execute();
    $utilisateur_actuel = $getInfoStmt->fetch(PDO::FETCH_ASSOC);
}

// Vérifier si l'utilisateur est connecté
if (!$utilisateur_actuel) {
    header('Location: connexion.php');
    exit();
}

// Appel de la fonction pour recommander des amis
$recommandations = recommander_amis($utilisateur_actuel, $bdd);
?>
