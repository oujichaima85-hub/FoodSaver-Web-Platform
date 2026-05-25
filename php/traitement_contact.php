<?php
require_once __DIR__ . '/connexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../contact.html');
    exit;
}

$nom     = trim($_POST['nom']     ?? '');
$email   = trim($_POST['email']   ?? '');
$sujet   = trim($_POST['sujet']   ?? '');
$message = trim($_POST['message'] ?? '');

$erreurs = [];

if (empty($nom)) {
    $erreurs[] = 'Le nom est obligatoire.';
} elseif (strlen($nom) < 2 || strlen($nom) > 100) {
    $erreurs[] = 'Le nom doit contenir entre 2 et 100 caractères.';
} elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $nom)) {
    $erreurs[] = 'Le nom ne doit contenir que des lettres.';
}

if (empty($email)) {
    $erreurs[] = "L'adresse e-mail est obligatoire.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = "Format d'e-mail invalide (ex: nom@domaine.com).";
}

if (empty($sujet)) {
    $erreurs[] = 'Le sujet est obligatoire.';
} elseif (strlen($sujet) < 3 || strlen($sujet) > 200) {
    $erreurs[] = 'Le sujet doit contenir entre 3 et 200 caractères.';
}

if (empty($message)) {
    $erreurs[] = 'Le message est obligatoire.';
} elseif (strlen($message) < 10) {
    $erreurs[] = 'Le message doit contenir au moins 10 caractères.';
}

$insere   = false;
$idInsere = 0;
$erreurBD = '';

if (empty($erreurs)) {
    try {
        $pdo  = getConnexion();
        $stmt = $pdo->prepare(
            "INSERT INTO contacts (nom, email, sujet, message)
             VALUES (:nom, :email, :sujet, :message)"
        );
        $stmt->execute([
            ':nom'     => $nom,
            ':email'   => $email,
            ':sujet'   => $sujet,
            ':message' => $message,
        ]);
        $idInsere = $pdo->lastInsertId();
        $insere   = true;
    } catch (PDOException $e) {
        $erreurBD = $e->getMessage();
    }
}

$derniers = [];
if ($insere) {
    try {
        $pdo      = getConnexion();
        $derniers = $pdo->query(
            "SELECT nom, email, sujet, message, date_envoi
             FROM contacts
             ORDER BY date_envoi DESC
             LIMIT 5"
        )->fetchAll();
    } catch (PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FoodSaver – Confirmation Contact</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/logo.jpg">
    <link rel="stylesheet" href="../css/extern.css">
    <link rel="stylesheet" href="../css/contact.css">
    <style>
        .result-wrapper { max-width:900px; margin:2rem auto; padding:0 5%; }
        .result-card {
            background:#fff; border-radius:20px;
            box-shadow:0 4px 20px rgba(0,0,0,.08);
            padding:2rem; margin-bottom:1.5rem;
            border:2px solid #e8f5e9;
        }
        .result-card h2 {
            font-size:1.3rem; font-weight:700;
            margin-bottom:1.2rem; padding-bottom:.6rem;
            border-bottom:2px solid #e8f5e9; color:#1b4332;
        }
        .alert { padding:1rem 1.4rem; border-radius:12px; margin-bottom:1.2rem; font-weight:600; font-size:.95rem; }
        .alert-success { background:#dcfce7; color:#15803d; border-left:5px solid #22c55e; }
        .alert-danger  { background:#fee2e2; color:#b91c1c; border-left:5px solid #dc2626; }
        .alert-warning { background:#fff7ed; color:#9a3412; border-left:5px solid #f97316; }
        .alert li { margin:.3rem 0 .3rem 1rem; font-weight:400; }
        .result-table { width:100%; border-collapse:collapse; font-size:.92rem; }
        .result-table th,
        .result-table td { padding:.75rem 1rem; text-align:left; border-bottom:1px solid #f0f0f0; vertical-align:top; }
        .result-table th { background:#2d6a4f; color:#fff; font-weight:600; }
        .result-table tr:last-child td { border-bottom:none; }
        .result-table tr:nth-child(even) td { background:#f9fdf9; }
        .msg-cell { max-width:400px; word-break:break-word; }
        .btn-retour {
            display:inline-block; padding:.75rem 1.8rem; border-radius:50px;
            font-weight:700; font-size:.93rem; text-decoration:none;
            transition:all .25s; margin:.4rem .4rem 0 0;
        }
        .btn-retour.primary { background:#2d6a4f; color:#fff; box-shadow:0 4px 12px rgba(45,106,79,.3); }
        .btn-retour.primary:hover { background:#1b4332; transform:translateY(-2px); }
        .btn-retour.outline { background:#fff; color:#2d6a4f; border:2px solid #2d6a4f; }
        .btn-retour.outline:hover { background:#f0faf4; transform:translateY(-2px); }
    </style>
</head>
<body>
    <div class="orb"></div>
    <input type="checkbox" id="sidebar-toggle">
    <label class="sidebar-overlay" for="sidebar-toggle" aria-hidden="true"></label>

    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="../index.html" class="sidebar-logo">
                <img src="../images/logo.jpg" alt="FoodSaver"><span>FoodSaver</span>
            </a>
            <label class="sidebar-close" for="sidebar-toggle">
                <span></span><span></span><span></span>
            </label>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="../index.html"><span class="nav-icon">🏠</span>Vitrine</a></li>
                <li><a href="../about.html"><span class="nav-icon">🌿</span>Qui sommes-nous</a></li>
                <div class="sidebar-sep"></div>
                <li><a href="../guide.php"><span class="nav-icon">📖</span>Tutoriel culinaire</a></li>
                <li><a href="../recettes.php"><span class="nav-icon">🥘</span>Cuisine savoureuse</a></li>
                <div class="sidebar-sep"></div>
                <li><a href="../contact.html" class="active"><span class="nav-icon">✉️</span>Contactez-nous</a></li>
                <li><a href="../questionnaire.html"><span class="nav-icon">📋</span>Questionnaire</a></li>
                <li><a href="../funpage.html"><span class="nav-icon">🎮</span>Fun Page</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer">© 2026 FoodSaver · Tous droits réservés</div>
    </aside>

    <header>
        <nav class="navbar">
            <div class="site-name">
                <img src="../images/logo.jpg" alt="FoodSaver logo">
                <a href="../index.html">FoodSaver</a>
            </div>
            <div class="navbar-right">
                <span class="nav-hint">Menu</span>
                <label class="hamburger-btn" for="sidebar-toggle">
                    <span></span><span></span><span></span>
                </label>
            </div>
        </nav>
    </header>

    <div class="badges-bar">
        <div class="badge"><span class="badge-icon">🌿</span> Zéro Gaspillage</div>
        <div class="badge"><span class="badge-icon">🥘</span> Recettes Fraîches</div>
        <div class="badge"><span class="badge-icon">❄️</span> Conseils de Conservation</div>
    </div>

    <main>
        <section class="contact-hero">
            <h1>📨 Confirmation de Contact</h1>
            <p>Voici le résultat du traitement de votre message.</p>
        </section>

        <div class="result-wrapper">

            <?php if (!empty($erreurs)): ?>
            <div class="result-card">
                <h2>❌ Erreurs détectées</h2>
                <div class="alert alert-danger">
                    ⚠️ Votre message n'a pas pu être envoyé :
                    <ul>
                        <?php foreach ($erreurs as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="../contact.html" class="btn-retour primary">✏️ Corriger le formulaire</a>
                <a href="../index.html"   class="btn-retour outline">🏠 Accueil</a>
            </div>

            <?php elseif (!$insere && !empty($erreurBD)): ?>
            <div class="result-card">
                <h2>⚠️ Erreur base de données</h2>
                <div class="alert alert-warning">
                    Votre message n'a pas pu être enregistré.<br>
                    <small><?= htmlspecialchars($erreurBD) ?></small>
                </div>
                <a href="../contact.html" class="btn-retour outline">↩️ Réessayer</a>
            </div>

            <?php else: ?>
            <div class="alert alert-success">
                ✅ Message envoyé et enregistré avec succès !
                <?php if ($idInsere): ?>
                    (Référence : <strong>#<?= $idInsere ?></strong>)
                <?php endif; ?>
            </div>

            <div class="result-card">
                <h2>📋 Récapitulatif de votre message</h2>
                <table class="result-table">
                    <thead><tr><th>Champ</th><th>Valeur</th></tr></thead>
                    <tbody>
                        <tr><td>👤 Nom</td><td><?= htmlspecialchars($nom) ?></td></tr>
                        <tr><td>📧 Email</td><td><?= htmlspecialchars($email) ?></td></tr>
                        <tr><td>📌 Sujet</td><td><?= htmlspecialchars($sujet) ?></td></tr>
                        <tr><td>💬 Message</td><td class="msg-cell"><?= nl2br(htmlspecialchars($message)) ?></td></tr>
                        <tr><td>📅 Date</td><td><?= date('d/m/Y à H:i:s') ?></td></tr>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($derniers)): ?>
            <div class="result-card">
                <h2>📬 Les 5 derniers messages reçus</h2>
                <div style="overflow-x:auto;">
                    <table class="result-table">
                        <thead>
                            <tr>
                                <th>👤 Nom</th>
                                <th>📧 Email</th>
                                <th>📌 Sujet</th>
                                <th>💬 Message</th>
                                <th>📅 Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($derniers as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['nom']) ?></strong></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['sujet']) ?></td>
                                <td class="msg-cell">
                                    <?= htmlspecialchars(mb_substr($row['message'], 0, 60))
                                        . (mb_strlen($row['message']) > 60 ? '…' : '') ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['date_envoi'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <div>
                <a href="../contact.html" class="btn-retour outline">✉️ Envoyer un autre message</a>
                <a href="../index.html"   class="btn-retour primary">🏠 Retour à l'accueil</a>
            </div>

            <?php endif; ?>

        </div>
    </main>

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