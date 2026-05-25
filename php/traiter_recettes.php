<?php
session_start();
require_once __DIR__ . '/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getConnexion();
    

    $nom        = trim($_POST['nom']                ?? '');
    $email      = trim($_POST['email']              ?? '');
    $type       = trim($_POST['typeDemande']        ?? '');
    $titre      = trim($_POST['titreRecette']       ?? '');
    $desc       = trim($_POST['descriptionRecette'] ?? '');
    $categorie  = trim($_POST['categorie']          ?? '');
    $temps      = trim($_POST['temps']              ?? '');
    $difficulte = trim($_POST['difficulte']         ?? '');
    $photo      = trim($_POST['photo']              ?? '');
    $message    = trim($_POST['messageAvis']        ?? '');

    $erreurs = [];

    if (empty($nom) || strlen($nom) < 2)
        $erreurs[] = 'Le nom est obligatoire (min 2 caractères).';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $erreurs[] = 'Email invalide.';
    if (empty($type))
        $erreurs[] = 'Choisissez un type de demande.';

    if (empty($erreurs)) {
        try {
            // CAS 1 : Ajout recette → insérer dans recettes
            if ($type === 'ajout' && !empty($titre)) {
                $stmt = $pdo->prepare("
                    INSERT INTO recettes (nom, categorie, temps_preparation, difficulte, description, photo)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $titre,
                    $categorie  ?: 'Plat principal',
                    $temps      ?: 30,
                    $difficulte ?: 'Moyen',
                    $desc,
                    $photo      ?: 'salade.jpg'
                ]);
            }

            // CAS 1, 2 & 3 : Tout insérer dans propositions
            $stmt = $pdo->prepare("
                INSERT INTO propositions (nom, email, type_demande, titre, categorie, temps, difficulte, description, photo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nom,
                $email,
                $type,
                $titre      ?: null,
                $categorie  ?: null,
                $temps      ?: null,
                $difficulte ?: null,
                $desc       ?: $message,
                $photo      ?: null
            ]);

            $_SESSION['succes'] = true;

        } catch (PDOException $e) {
            $_SESSION['erreurs'] = ['Erreur BD : ' . $e->getMessage()];
        }
    } else {
        $_SESSION['erreurs'] = $erreurs;
    }

    header('Location:recettes.php');
    exit;
}

header('Location:recettes.php');
exit;
?>