<?php
session_start();
require_once 'config.php';


// Vérifier si le paramètre group_id est défini dans la requête POST
if (isset($_POST['group_id'])) {
    $groupId = $_POST['group_id'];

    try {
        // Commencer une transaction
        $bdd->beginTransaction();

        // Écrire la requête SQL pour supprimer les membres du groupe de la table group_members
        $deleteGroupMembersQuery = "DELETE FROM group_members WHERE group_id = :group_id";
        $deleteGroupMembersStmt = $bdd->prepare($deleteGroupMembersQuery);
        $deleteGroupMembersStmt->bindParam(':group_id', $groupId);

        // Exécuter la requête de suppression des membres du groupe
        $deleteGroupMembersStmt->execute();

        // Ensuite, écrire la requête SQL pour supprimer le groupe de la table groups
        $deleteGroupQuery = "DELETE FROM groups WHERE group_id = :group_id";
        $deleteGroupStmt = $bdd->prepare($deleteGroupQuery);
        $deleteGroupStmt->bindParam(':group_id', $groupId);

        // Exécuter la requête de suppression du groupe
        $deleteGroupStmt->execute();

        // Valider la transaction
        $bdd->commit();

        // Retourner une réponse JSON indiquant que la suppression a réussi
        echo json_encode(['success' => true]);
        exit();
    } catch (PDOException $e) {
        // En cas d'erreur, annuler la transaction et retourner une réponse JSON indiquant une erreur
        $bdd->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
    }
} else {
    // Retourner une réponse JSON indiquant une erreur si le paramètre group_id n'est pas défini
    echo json_encode(['success' => false, 'error' => 'Paramètre group_id manquant']);
    exit();
}
?>
