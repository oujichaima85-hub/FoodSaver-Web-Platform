<?php
ob_start();
require_once 'connexion.php';
$pdo = getConnexion();

$message = '';

try {
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['supprimer']) &&
        is_numeric($_POST['supprimer'])
    ) {
        $id   = (int)$_POST['supprimer'];
        $stmt = $pdo->prepare("DELETE FROM aliments WHERE id = ?");
        $stmt->execute([$id]);
        $message = '<div class="msg-succes">✅ Aliment supprimé avec succès !</div>';
    } else {
        $message = '<div class="msg-erreur">❌ Identifiant invalide ou manquant.</div>';
    }
} catch (PDOException $e) {
    $message = '<div class="msg-erreur">❌ Erreur BD : ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FoodSaver – Suppression</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/logo.jpg">
    <link rel="stylesheet" href="../css/extern.css">
    <link rel="stylesheet" href="../css/guide.css">
    <style>
        main { padding-top:var(--navbar-height,80px); max-width:700px; margin:0 auto; padding-left:5%; padding-right:5%; }
        .msg-erreur { background:#f8d7da;color:#721c24;padding:1rem 1.5rem;border-radius:14px;margin-bottom:1.5rem;border-left:5px solid #e74c3c;font-weight:600; }
        .msg-succes { background:#d4edda;color:#155724;padding:1rem 1.5rem;border-radius:14px;margin-bottom:1.5rem;border-left:5px solid #35ab47;font-weight:600; }
        .btn-retour {
            display:inline-block; padding:.75rem 2rem; margin-top:1rem;
            background:linear-gradient(145deg,#35ab47,#1e5a2b);
            color:#fff; border-radius:50px; text-decoration:none;
            font-weight:600; font-size:1rem;
            box-shadow:0 4px 15px rgba(53,171,71,.3); transition:.3s;
        }
        .btn-retour:hover { transform:translateY(-2px); }
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
                <li><a href="guide.php" class="active"><span class="nav-icon">📖</span>Tutoriel culinaire</a></li>
                <li><a href="recettes.php"><span class="nav-icon">🥘</span>Cuisine savoureuse</a></li>
                <div class="sidebar-sep"></div>
                <li><a href="../contact.html"><span class="nav-icon">✉️</span>Contactez-nous</a></li>
                <li><a href="../questionnaire.html"><span class="nav-icon">📋</span>Questionnaire</a></li>
                <li><a href="../funpage.html"><span class="nav-icon">🎮</span>Fun Page</a></li>
            </ul>
        </nav>
    </aside>
    <header>
        <nav class="navbar">
            <div class="site-name">
                <img src="../images/logo.jpg" alt="FoodSaver logo">
                <a href="../index.html">FoodSaver</a>
            </div>
            <div class="navbar-right">
                <label class="hamburger-btn" for="sidebar-toggle">
                    <span></span><span></span><span></span>
                </label>
            </div>
        </nav>
    </header>

    <main>
        <h1>🗑️ Suppression d'un aliment</h1>
        <?= $message ?>
        <a href="guide.php" class="btn-retour">⬅ Retour au guide</a>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-logo">FoodSaver</div>
            <p class="footer-copy">© 2026 FoodSaver. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>