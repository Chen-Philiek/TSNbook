<?php
// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Vérifier si le terme de recherche est présent dans la requête
if (isset($_GET['searchTerm'])) {
    // Nettoyer et récupérer le terme de recherche
    $searchTerm = trim($_GET['searchTerm']);

    // Préparer la requête SQL pour rechercher les intérêts correspondant au terme de recherche
    $searchQuery = "SELECT interest_label FROM interests WHERE interest_label LIKE :searchTerm";
    $searchStmt = $bdd->prepare($searchQuery);
    $searchStmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $searchStmt->execute();
    $suggestions = $searchStmt->fetchAll(PDO::FETCH_COLUMN);

    // Renvoyer les suggestions au format JSON
    echo json_encode($suggestions);
} else {
    // Si le terme de recherche n'est pas présent, renvoyer une réponse vide
    echo json_encode([]);
}

?>
