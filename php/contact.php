<?php
/**
 * ════════════════════════════════════════════════════════
 * FoodSaver - contact.php
 * Reçoit les données POST du formulaire contact.html
 * Étapes :
 *  1. Récupération et nettoyage des données
 *  2. Validation PHP côté serveur
 *  3. Insertion en BDD si tout est valide
 *  4. Affichage du résultat (succès / erreurs)
 * ════════════════════════════════════════════════════════
 */

// Inclut le fichier de connexion avec chemin absolu
require_once __DIR__ . '/connexion.php';

// Récupère l'objet PDO (connexion à la base de données)
$pdo = getConnexion();


// ════════════════════════════════════════════════════════
//  ÉTAPE 1 — RÉCUPÉRATION ET NETTOYAGE DES DONNÉES
//  ?? '' : si la clé n'existe pas dans $_POST → chaîne vide
//  trim() : supprime les espaces inutiles avant/après
// ════════════════════════════════════════════════════════

$nom     = trim($_POST['nom']     ?? '');  // Nom de l'expéditeur
$email   = trim($_POST['email']   ?? '');  // Adresse email
$sujet   = trim($_POST['sujet']   ?? '');  // Sujet du message
$message = trim($_POST['message'] ?? '');  // Corps du message


// ════════════════════════════════════════════════════════
//  ÉTAPE 2 — VALIDATION PHP (2ème ligne de défense)
//  La 1ère ligne étant la validation JavaScript côté client
//  PHP revalide car le JS peut être contourné par l'utilisateur
//  $erreurs = tableau associatif : clé = champ, valeur = message
// ════════════════════════════════════════════════════════

$erreurs = [];  // Tableau vide au départ, rempli si erreurs trouvées

// ── Validation du Nom ──────────────────────────────────
if (empty($nom)) {
    // Champ vide
    $erreurs['nom'] = 'Le nom est obligatoire.';
} elseif (strlen($nom) < 3 || strlen($nom) > 100) {
    // Trop court ou trop long
    $erreurs['nom'] = 'Le nom doit contenir entre 3 et 100 caractères.';
} elseif (!preg_match('/^[\p{L}\s\'\-]+$/u', $nom)) {
    // preg_match : vérifie le format avec une expression régulière
    // \p{L} : toute lettre unicode (é, à, ñ...)
    // \s    : espaces
    // \'    : apostrophes (ex: O'Brien)
    // \-    : tirets (ex: Marie-Claire)
    // /u    : mode unicode
    $erreurs['nom'] = 'Le nom ne doit contenir que des lettres.';
}

// ── Validation de l'Email ──────────────────────────────
if (empty($email)) {
    $erreurs['email'] = "L'e-mail est obligatoire.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // filter_var() : fonction PHP native de validation
    // FILTER_VALIDATE_EMAIL : vérifie le format nom@domaine.com
    $erreurs['email'] = "Format e-mail invalide (ex: nom@domaine.com).";
}

// ── Validation du Sujet ────────────────────────────────
if (empty($sujet)) {
    $erreurs['sujet'] = 'Le sujet est obligatoire.';
} elseif (strlen($sujet) < 5 || strlen($sujet) > 200) {
    $erreurs['sujet'] = 'Le sujet doit contenir entre 5 et 200 caractères.';
}

// ── Validation du Message ──────────────────────────────
if (empty($message)) {
    $erreurs['message'] = 'Le message est obligatoire.';
} elseif (strlen($message) < 5) {
    $erreurs['message'] = 'Le message doit contenir au moins 5 caractères.';
} elseif (strlen($message) > 2000) {
    $erreurs['message'] = 'Le message ne peut pas dépasser 2000 caractères.';
}


// ════════════════════════════════════════════════════════
//  ÉTAPE 3 — INSERTION EN BASE DE DONNÉES
//  Exécutée UNIQUEMENT si $erreurs est vide (aucune erreur)
// ════════════════════════════════════════════════════════

$insere   = false;  // Booléen : insertion réussie ?
$idInsere = 0;      // ID de la ligne insérée (0 si échec)
$erreurBD = '';     // Message d'erreur BDD si exception

if (empty($erreurs)) {  // Pas d'erreurs de validation → on insère

    try {
        // prepare() avec paramètres NOMMÉS (:nom, :email...)
        // Plus lisible que les ? anonymes
        // Protège contre les injections SQL
        $stmt = $pdo->prepare(
            "INSERT INTO contacts (nom, email, sujet, message)
             VALUES (:nom, :email, :sujet, :message)"
        );

        // execute() remplace chaque :parametre par sa valeur réelle
        // La clé du tableau DOIT correspondre au nom du paramètre
        $stmt->execute([
            ':nom'     => $nom,      // :nom     ← $nom
            ':email'   => $email,    // :email   ← $email
            ':sujet'   => $sujet,    // :sujet   ← $sujet
            ':message' => $message,  // :message ← $message
        ]);

        // lastInsertId() : récupère l'ID auto-incrémenté de la ligne insérée
        // Utile pour afficher une référence à l'utilisateur (#5, #6...)
        $idInsere = $pdo->lastInsertId();

        // Marque l'insertion comme réussie
        $insere = true;

    } catch (PDOException $e) {
        // Si une erreur BDD survient (table inexistante, connexion coupée...)
        // On capture le message d'erreur sans planter la page
        $erreurBD = $e->getMessage();
    }
}


// ════════════════════════════════════════════════════════
//  ÉTAPE 4 — RÉCUPÉRATION DES 5 DERNIERS MESSAGES
//  Uniquement si l'insertion a réussi (pour afficher le tableau)
// ════════════════════════════════════════════════════════

$derniers = [];  // Tableau vide par défaut

if ($insere) {  // On ne charge que si l'insertion a réussi
    try {
        // query() simple (pas de paramètres → pas besoin de prepare)
        // fetchAll() sans argument → retourne FETCH_BOTH par défaut
        $derniers = $pdo->query(
            "SELECT nom, email, sujet, message, date_envoi
             FROM contacts
             ORDER BY date_envoi DESC   -- Du plus récent au plus ancien
             LIMIT 5"                   
        )->fetchAll();
    } catch (PDOException $e) {
        // Silencieux : si la table n'existe pas, $derniers reste []
        // La page s'affiche quand même sans le tableau
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FoodSaver – Confirmation Contact</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon"       type="image/png" href="../images/logo.jpg">
    <link rel="stylesheet" href="../css/extern.css">
    <link rel="stylesheet" href="../css/contact.css">

    <style>
        /* ════════════════════════════════════════════════
           STYLES SPÉCIFIQUES À CONTACT.PHP
           ════════════════════════════════════════════════ */

        /* Conteneur centré, largeur max 900px */
        .result-wrapper {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 5%;
        }

        /* Carte blanche avec ombre (utilisée pour chaque section) */
        .result-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            padding: 2rem;
            margin-bottom: 1.5rem;
            border: 2px solid #e8f5e9;   /* Bordure verte très claire */
        }
        .result-card h2 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
            padding-bottom: .6rem;
            border-bottom: 2px solid #e8f5e9;
            color: #1b4332;
        }

        /* ── Alertes colorées ──────────────────────────── */
        .alert {
            padding: 1rem 1.4rem;
            border-radius: 12px;
            margin-bottom: 1.2rem;
            font-weight: 600;
            font-size: .95rem;
        }
        /* Verte  → succès */
        .alert-success {
            background: #dcfce7;
            color: #15803d;
            border-left: 5px solid #22c55e;
        }
        /* Rouge  → erreur de validation */
        .alert-danger {
            background: #fee2e2;
            color: #b91c1c;
            border-left: 5px solid #dc2626;
        }
        /* Orange → erreur base de données */
        .alert-warning {
            background: #fff7ed;
            color: #9a3412;
            border-left: 5px solid #f97316;
        }
        /* Items de liste dans les alertes */
        .alert li { margin: .3rem 0 .3rem 1rem; font-weight: 400; }

        /* ── Tableau de résultats ──────────────────────── */
        .result-table {
            width: 100%;
            border-collapse: collapse;   /* Fusionne les bordures */
            font-size: .92rem;
        }
        .result-table th,
        .result-table td {
            padding: .75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;         /* Aligne en haut (pour messages longs) */
        }
        /* En-têtes : fond vert foncé, texte blanc */
        .result-table th {
            background: #2d6a4f;
            color: #fff;
            font-weight: 600;
            white-space: nowrap;         /* Empêche le retour à la ligne */
        }
        /* Supprime la bordure de la dernière ligne */
        .result-table tr:last-child td { border-bottom: none; }
        /* Lignes alternées légèrement colorées (zébrage) */
        .result-table tr:nth-child(even) td { background: #f9fdf9; }
        /* Colonne label (Nom, Email...) : largeur fixe, texte vert */
        .result-table .label-col {
            color: #4f6452;
            font-weight: 600;
            width: 160px;
            white-space: nowrap;
        }
        /* Cellule message : gère les longs textes sans débordement */
        .msg-cell { max-width: 400px; word-break: break-word; }

        /* ── Badge de champ (ex: "nom" "email") ─────────── */
        .badge-field {
            display: inline-block;
            background: #d8f3dc;
            color: #1b4332;
            padding: .15rem .6rem;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 700;
            margin-right: .4rem;
        }

        /* ── Boutons de retour ───────────────────────────── */
        .btn-retour {
            display: inline-block;
            padding: .75rem 1.8rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: .93rem;
            text-decoration: none;
            transition: all .25s;
            margin: .4rem .4rem 0 0;
        }
        /* Bouton plein (vert foncé) */
        .btn-retour.primary {
            background: #2d6a4f;
            color: #fff;
            box-shadow: 0 4px 12px rgba(45,106,79,.3);
        }
        .btn-retour.primary:hover { background: #1b4332; transform: translateY(-2px); }
        /* Bouton contour (blanc avec bordure verte) */
        .btn-retour.outline {
            background: #fff;
            color: #2d6a4f;
            border: 2px solid #2d6a4f;
        }
        .btn-retour.outline:hover { background: #f0faf4; transform: translateY(-2px); }
    </style>
</head>

<body>
    <!-- Décoration graphique d'arrière-plan -->
    <div class="orb"></div>

    <!-- Checkbox cachée : contrôle l'ouverture/fermeture du sidebar -->
    <input type="checkbox" id="sidebar-toggle">
    <!-- Fond semi-transparent pour fermer le menu en cliquant dessus -->
    <label class="sidebar-overlay" for="sidebar-toggle" aria-hidden="true"></label>

    <!-- ════════════════════════════════════════════════
         SIDEBAR DE NAVIGATION
         ════════════════════════════════════════════════ -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="../index.html" class="sidebar-logo">
                <img src="../images/logo.jpg" alt="FoodSaver">
                <span>FoodSaver</span>
            </a>
            <!-- Bouton fermeture du sidebar -->
            <label class="sidebar-close" for="sidebar-toggle" aria-label="Fermer le menu">
                <span></span><span></span><span></span>
            </label>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="../index.html"><span class="nav-icon">🏠</span>Vitrine</a></li>
                <li><a href="../about.html"><span class="nav-icon">🌿</span>Qui sommes-nous</a></li>
                <div class="sidebar-sep"></div>  <!-- Séparateur visuel -->
                <li><a href="guide.php"><span class="nav-icon">📖</span>Tutoriel culinaire</a></li>
                <li><a href="recettes.php"><span class="nav-icon">🥘</span>Cuisine savoureuse</a></li>
                <div class="sidebar-sep"></div>
                <!-- "active" = page actuelle (style différent) -->
                <li><a href="../contact.html" class="active"><span class="nav-icon">✉️</span>Contactez-nous</a></li>
                <li><a href="../questionnaire.html"><span class="nav-icon">📋</span>Questionnaire</a></li>
                <li><a href="../funpage.html"><span class="nav-icon">🎮</span>Fun Page</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer">© 2026 FoodSaver · Tous droits réservés</div>
    </aside>

    <!-- ════════════════════════════════════════════════
         HEADER & NAVBAR
         ════════════════════════════════════════════════ -->
    <header>
        <nav class="navbar">
            <div class="site-name">
                <img src="../images/logo.jpg" alt="FoodSaver logo">
                <a href="../index.html">FoodSaver</a>
            </div>
            <div class="navbar-right">
                <span class="nav-hint">Menu</span>
                <!-- Bouton hamburger : ouvre le sidebar -->
                <label class="hamburger-btn" for="sidebar-toggle" aria-label="Ouvrir le menu">
                    <span></span><span></span><span></span>
                </label>
            </div>
        </nav>
    </header>

    <!-- Badges décoratifs -->
    <div class="badges-bar">
        <div class="badge"><span class="badge-icon">🌿</span> Zéro Gaspillage</div>
        <div class="badge"><span class="badge-icon">🥘</span> Recettes Fraîches</div>
        <div class="badge"><span class="badge-icon">❄️</span> Conseils de Conservation</div>
    </div>

    <!-- ════════════════════════════════════════════════
         CONTENU PRINCIPAL
         3 cas possibles selon le résultat du traitement PHP
         ════════════════════════════════════════════════ -->
    <main>
        <section class="contact-hero">
            <h1>📨 Confirmation de Contact</h1>
            <p>Voici le résultat du traitement de votre message.</p>
        </section>

        <div class="result-wrapper">

            <?php if (!empty($erreurs)) : ?>
            <!-- ══════════════════════════════════════════
                 CAS 1 : ERREURS DE VALIDATION
                 $erreurs n'est pas vide → affiche les erreurs
                 ══════════════════════════════════════════ -->
            <div class="result-card">
                <h2>❌ Erreurs détectées</h2>

                <!-- Alerte rouge listant toutes les erreurs -->
                <div class="alert alert-danger">
                    ⚠️ Votre message n'a pas pu être envoyé :
                    <ul>
                        <?php foreach ($erreurs as $champ => $msg) : ?>
                            <li>
                                <!-- ucfirst() : met en majuscule la 1ère lettre du champ -->
                                <span class="badge-field">
                                    <?= htmlspecialchars(ucfirst($champ)) ?>
                                </span>
                                <?= htmlspecialchars($msg) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Tableau récapitulatif des données saisies avec statut par champ -->
                <h2 style="margin-top:1.5rem;">📋 Données saisies</h2>
                <table class="result-table">
                    <thead>
                        <tr><th>Champ</th><th>Valeur saisie</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        // Tableau de configuration des champs à afficher
                        $champs = [
                            'nom'     => ['label' => '👤 Nom',     'valeur' => $nom],
                            'email'   => ['label' => '📧 Email',   'valeur' => $email],
                            'sujet'   => ['label' => '📌 Sujet',   'valeur' => $sujet],
                            'message' => ['label' => '💬 Message', 'valeur' => $message],
                        ];

                        foreach ($champs as $cle => $info) :
                            // isset() : vérifie si ce champ a une erreur
                            $aErreur = isset($erreurs[$cle]);
                            // Icône et couleur selon présence d'erreur
                            $icone   = $aErreur ? '❌' : '✅';
                            $couleur = $aErreur ? '#b91c1c' : '#15803d';  // Rouge ou vert
                            // Si vide → affiche "vide" en gris, sinon la vraie valeur
                            $valAff  = !empty($info['valeur'])
                                       ? htmlspecialchars($info['valeur'])
                                       : '<em style="color:#999;">vide</em>';
                        ?>
                        <tr>
                            <td class="label-col"><?= $info['label'] ?></td>
                            <td class="msg-cell"><?= $valAff ?></td>
                            <td style="color:<?= $couleur ?>; font-weight:700;">
                                <?= $icone ?>
                                <!-- Si erreur → message d'erreur, sinon "Valide" -->
                                <?= $aErreur ? htmlspecialchars($erreurs[$cle]) : 'Valide' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Boutons pour corriger ou retourner à l'accueil -->
                <div style="margin-top:1.5rem;">
                    <a href="../contact.html" class="btn-retour primary">✏️ Corriger le formulaire</a>
                    <a href="../index.html"   class="btn-retour outline">🏠 Accueil</a>
                </div>
            </div>


            <?php elseif (!$insere && !empty($erreurBD)) : ?>
            <!-- ══════════════════════════════════════════
                 CAS 2 : ERREUR BASE DE DONNÉES
                 Données valides MAIS insertion échouée
                 (connexion coupée, table inexistante...)
                 ══════════════════════════════════════════ -->
            <div class="result-card">
                <h2>⚠️ Erreur base de données</h2>
                <div class="alert alert-warning">
                    Votre message a été validé mais n'a pas pu être enregistré.<br>
                    <!-- Affiche le message technique de l'exception PDO -->
                    <small><?= htmlspecialchars($erreurBD) ?></small>
                </div>
                <a href="../contact.html" class="btn-retour outline">↩️ Réessayer</a>
            </div>


            <?php else : ?>
            <!-- ══════════════════════════════════════════
                 CAS 3 : SUCCÈS COMPLET
                 Données valides + insertion réussie
                 ══════════════════════════════════════════ -->

            <!-- Alerte verte de confirmation -->
            <div class="alert alert-success">
                ✅ Message envoyé et enregistré avec succès !
                <?php if ($idInsere) : ?>
                    <!-- Affiche le numéro de référence si disponible -->
                    (Référence : <strong>#<?= $idInsere ?></strong>)
                <?php endif; ?>
            </div>

            <!-- Tableau 1 : Récapitulatif du message envoyé par l'utilisateur -->
            <div class="result-card">
                <h2>📋 Récapitulatif de votre message</h2>
                <table class="result-table">
                    <thead>
                        <tr><th>Champ</th><th>Valeur</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="label-col">👤 Nom</td>
                            <!-- htmlspecialchars() : protège contre les injections XSS -->
                            <td><?= htmlspecialchars($nom) ?></td>
                        </tr>
                        <tr>
                            <td class="label-col">📧 Email</td>
                            <td><?= htmlspecialchars($email) ?></td>
                        </tr>
                        <tr>
                            <td class="label-col">📌 Sujet</td>
                            <td><?= htmlspecialchars($sujet) ?></td>
                        </tr>
                        <tr>
                            <td class="label-col">💬 Message</td>
                            <!-- nl2br() : convertit les \n en <br> pour conserver les sauts de ligne -->
                            <td class="msg-cell"><?= nl2br(htmlspecialchars($message)) ?></td>
                        </tr>
                        <tr>
                            <td class="label-col">📅 Date d'envoi</td>
                            <!-- date() : formate la date/heure actuelle -->
                            <td><?= date('d/m/Y à H:i:s') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tableau 2 : Les 5 derniers messages (affiché si $derniers non vide) -->
            <?php if (!empty($derniers)) : ?>
            <div class="result-card">
                <h2>📬 Les 5 derniers messages reçus</h2>
                <!-- overflow-x:auto : scroll horizontal sur petit écran -->
                <div style="overflow-x:auto;">
                    <table class="result-table">
                        <thead>
                            <tr>
                                <th>👤 Nom</th>
                                <th>📧 Email</th>
                                <th>📌 Sujet</th>
                                <th>💬 Message (extrait)</th>
                                <th>📅 Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($derniers as $row) : ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['nom']) ?></strong></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['sujet']) ?></td>
                                <td class="msg-cell">
                                    <?php
                                    // mb_substr() : extrait les 60 premiers caractères
                                    // (mb_ = supporte les caractères multioctets : é, à...)
                                    // mb_strlen() : compte les caractères (multioctets)
                                    // Si message > 60 chars → ajoute "…" à la fin
                                    echo htmlspecialchars(mb_substr($row['message'], 0, 60))
                                         . (mb_strlen($row['message']) > 60 ? '…' : '');
                                    ?>
                                </td>
                                <td style="white-space:nowrap;">
                                    <?php
                                    // strtotime() : convertit la date SQL en timestamp Unix
                                    // date()      : reformate le timestamp en "jj/mm/aaaa hh:mm"
                                    echo date('d/m/Y H:i', strtotime($row['date_envoi']));
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Boutons de retour (succès) -->
            <div>
                <a href="../contact.html" class="btn-retour outline">✉️ Envoyer un autre message</a>
                <a href="../index.html"   class="btn-retour primary">🏠 Retour à l'accueil</a>
            </div>

            <?php endif; ?>
            <!-- Fin des 3 cas (if / elseif / else) -->

        </div><!-- fin .result-wrapper -->
    </main>

    <!-- ════════════════════════════════════════════════
         FOOTER
         ════════════════════════════════════════════════ -->
    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-logo">FoodSaver</div>
            <div class="footer-divider"></div>
            <div class="footer-contact">
                <span>📞 +216 12 345 678 &nbsp;|&nbsp; +216 98 765 432</span>
                <span>✉ <a href="mailto:contact@foodsaver.tn">contact@foodsaver.tn</a></span>
            </div>
            <div class="footer-badge">⭐ Certifié Qualité Internationale – ISO 9001</div>
            <p class="footer-copy">© 2026 FoodSaver. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>