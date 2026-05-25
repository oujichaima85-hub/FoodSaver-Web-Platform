<?php
// ════════════════════════════════════════════════════════
//  avis.php — Traitement du formulaire d'avis
//  Reçoit les données POST, les insère en BDD,
//  puis redirige vers guide.php
// ════════════════════════════════════════════════════════

// __DIR__ = dossier du fichier actuel (chemin absolu)
// Évite les problèmes de chemin relatif selon l'endroit d'exécution
require_once __DIR__ . '/connexion.php';

// Récupère la connexion PDO
$pdo = getConnexion();

// Force l'encodage UTF-8 pour les caractères spéciaux (é, à, ç, emojis...)
// Évite les problèmes d'affichage en base de données
$pdo->exec("SET NAMES 'utf8mb4'");

// ════════════════════════════════════════════════════════
//  VÉRIFICATION DE LA MÉTHODE HTTP
//  Ce fichier ne doit être exécuté QUE si le formulaire
//  a été soumis en POST (pas en accès direct via l'URL)
// ════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── RÉCUPÉRATION ET NETTOYAGE DES DONNÉES ────────────
    // ?? '' : si la clé n'existe pas dans $_POST → chaîne vide (évite les erreurs)
    // trim() : supprime les espaces au début et à la fin

    $nom         = trim($_POST['nom']         ?? '');  // Nom de l'utilisateur
    $email       = trim($_POST['email']       ?? '');  // Adresse email
    $aliment     = trim($_POST['aliment']     ?? '');  // Nom de l'aliment (optionnel)
    $message     = trim($_POST['message']     ?? '');  // Contenu du message (optionnel)
    $typeDemande = trim($_POST['typeDemande'] ?? '');  // remerciement / signalement / proposition

    // ── VALIDATION DES CHAMPS OBLIGATOIRES ───────────────
    // Vérifie que les 3 champs indispensables ne sont pas vides
    // empty() retourne true si la valeur est "" , null, 0, false...
    if (empty($nom) || empty($email) || empty($typeDemande)) {
        // die() arrête l'exécution et affiche le message d'erreur
        die("❌ Champs obligatoires manquants.");
    }

    // ── INSERTION EN BASE DE DONNÉES ─────────────────────
    // prepare() : prépare la requête avec des marqueurs "?"
    // Avantage : protège contre les injections SQL
    // Les "?" seront remplacés par les vraies valeurs lors de execute()
    $stmt = $pdo->prepare("
        INSERT INTO avis (nom, email, aliment, message, type_demande)
        VALUES (?, ?, ?, ?, ?)
    ");

    // execute() : exécute la requête en remplaçant les "?" dans l'ordre du tableau
    // [0] → nom      remplace le 1er ?
    // [1] → email    remplace le 2ème ?
    // [2] → aliment  remplace le 3ème ?
    // [3] → message  remplace le 4ème ?
    // [4] → type     remplace le 5ème ?
    $stmt->execute([$nom, $email, $aliment, $message, $typeDemande]);

    // ── REDIRECTION APRÈS SUCCÈS ──────────────────────────
    // header('Location: ...') : redirige le navigateur vers une autre page
    // ?avis=ok  : paramètre GET détecté par le JavaScript de guide.php
    //             pour afficher le message de confirmation vert ✅
    // #formulaireAvis : ancre HTML → fait défiler la page jusqu'au formulaire
    header('Location: guide.php?avis=ok#formulaireAvis');

    // exit : OBLIGATOIRE après header()
    // Sans exit, le code PHP continuerait à s'exécuter après la redirection
    exit;
}
?>