<?php
require_once __DIR__ . '/connexion.php';
require_once __DIR__ . '/Aliment.php';
$pdo = getConnexion();

$message = '';
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom']     ?? '');
    $type    = trim($_POST['type']    ?? '');
    $methode = trim($_POST['methode'] ?? '');
    $duree   = intval($_POST['duree'] ?? 0);

    if (empty($nom) || strlen($nom) < 2)
        $erreurs[] = 'Le nom doit contenir au moins 2 caractères.';
    if (!in_array($type, Aliment::TYPES_VALIDES))
        $erreurs[] = 'Type invalide.';
    if (empty($methode) || strlen($methode) < 5)
        $erreurs[] = 'La méthode de conservation est trop courte (min 5 car.).';
    if ($duree < 1 || $duree > 3650)
        $erreurs[] = 'La durée doit être entre 1 et 3650 jours.';

    if (empty($erreurs)) {
        try {
            $pdo->exec("SET NAMES 'utf8mb4'");
            $stmt = $pdo->prepare(
                "INSERT INTO aliments (nom, type, methode_conservation, duree_conservation_jours)
                 VALUES (:nom, :type, :methode, :duree)"
            );
            $stmt->execute([':nom'=>$nom,':type'=>$type,':methode'=>$methode,':duree'=>$duree]);
            header('Location: guide.php');
            exit;
        } catch (PDOException $e) {
            $message = '<div class="msg-erreur">❌ Erreur BD : ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FoodSaver – Ajouter un aliment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/logo.jpg">
    <link rel="stylesheet" href="../css/extern.css">
    <link rel="stylesheet" href="../css/guide.css">
    <style>
        main { padding-top:var(--navbar-height,80px); max-width:700px; margin:0 auto; padding-left:5%; padding-right:5%; }
        h1   { font-size:clamp(1.6rem,4vw,2.2rem); color:#1e5a2b; margin:2rem 0 1.5rem; }
        .msg-succes    { background:#d4edda;color:#155724;padding:1rem 1.5rem;border-radius:14px;margin-bottom:1.5rem;border-left:5px solid #35ab47;font-weight:600; }
        .msg-erreur    { background:#f8d7da;color:#721c24;padding:1rem 1.5rem;border-radius:14px;margin-bottom:1.5rem;border-left:5px solid #e74c3c;font-weight:600; }
        .msg-validation{ background:#fff3cd;color:#856404;padding:1rem 1.5rem;border-radius:14px;margin-bottom:1.5rem;border-left:5px solid #ff9800; }
        .form-card {
            background:#fff; border-radius:24px; padding:2rem;
            box-shadow:0 10px 30px rgba(0,0,0,.08);
            border:2px solid #dde5da; border-top:4px solid #35ab47;
        }
        h2 { color:#1e5a2b; font-size:1.4rem; margin-bottom:1.2rem; border-left:5px solid #e79530; padding-left:.8rem; }
        .form-card label { display:block; font-weight:600; font-size:.9rem; color:#3a4a3c; margin-bottom:.3rem; margin-top:1rem; }
        .form-card input[type=text],
        .form-card input[type=number],
        .form-card select,
        .form-card textarea {
            width:100%; padding:.65rem .9rem;
            border:2px solid #dde5da; border-radius:10px;
            font-size:.95rem; font-family:inherit;
            background:#f8faf8; box-sizing:border-box;
            transition:border-color .2s;
        }
        .form-card input:focus,
        .form-card select:focus,
        .form-card textarea:focus { outline:none; border-color:#35ab47; background:#fff; }
        .err-inline { color:#e74c3c; font-size:.82rem; display:none; margin-top:.25rem; font-weight:600; }
        .btn-submit {
            display:inline-block; padding:.75rem 2rem; margin-top:1.2rem;
            background:linear-gradient(145deg,#35ab47,#1e5a2b);
            color:#fff; border:none; border-radius:50px;
            font-size:1rem; font-weight:600; cursor:pointer;
            box-shadow:0 4px 15px rgba(53,171,71,.3); transition:all .3s;
        }
        .btn-submit:hover { transform:translateY(-2px); }
        .btn-retour {
            display:inline-block; padding:.65rem 1.5rem; margin-top:1.2rem; margin-left:.8rem;
            background:#f5f5f5; color:#555; border:2px solid #dde; border-radius:50px;
            font-size:.9rem; font-weight:600; text-decoration:none; transition:.2s;
        }
        .btn-retour:hover { background:#ddd; }
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
        <h1>➕ Ajouter un aliment</h1>
        <?= $message ?>
        <?php if (!empty($erreurs)): ?>
            <div class="msg-validation">❌ <?= implode('<br>', array_map('htmlspecialchars', $erreurs)) ?></div>
        <?php endif; ?>
        <div class="form-card">
            <h2>➕ Nouvel aliment</h2>
            <form id="formAjouter" method="POST" action="ajouter_aliment.php" novalidate>
                <label>🥘 Nom <span style="color:red">*</span></label>
                <input type="text" id="nomAj" name="nom"
                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                       placeholder="Ex: Carottes" required>
                <span class="err-inline" id="errNomAj"></span>

                <label>📂 Type <span style="color:red">*</span></label>
                <select id="typeAj" name="type" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach (Aliment::TYPES_VALIDES as $t): ?>
                        <option value="<?= htmlspecialchars($t) ?>"
                            <?= (($_POST['type'] ?? '') === $t) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>🧊 Méthode de conservation <span style="color:red">*</span></label>
                <textarea id="methodeAj" name="methode" rows="3"
                          placeholder="Ex: Conserver au réfrigérateur..."><?= htmlspecialchars($_POST['methode'] ?? '') ?></textarea>
                <span class="err-inline" id="errMethodeAj"></span>

                <label>⏱️ Durée de conservation (jours) <span style="color:red">*</span></label>
                <input type="number" id="dureeAj" name="duree"
                       value="<?= htmlspecialchars($_POST['duree'] ?? '1') ?>"
                       min="1" max="3650" required>
                <span class="err-inline" id="errDureeAj"></span>

                <button type="submit" class="btn-submit">✅ Ajouter l'aliment</button>
                <a href="guide.php" class="btn-retour">⬅ Retour</a>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-logo">FoodSaver</div>
            <p class="footer-copy">© 2026 FoodSaver. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
    function validerAjout() {
        let ok = true;
        const nom = document.getElementById("nomAj");
        const errNom = document.getElementById("errNomAj");
        if (nom.value.trim().length < 2) {
            errNom.textContent = "❌ Le nom doit contenir au moins 2 caractères.";
            errNom.style.display = "block"; ok = false;
        } else { errNom.style.display = "none"; }

        const methode = document.getElementById("methodeAj");
        const errMethode = document.getElementById("errMethodeAj");
        if (methode.value.trim().length < 5) {
            errMethode.textContent = "❌ La méthode doit contenir au moins 5 caractères.";
            errMethode.style.display = "block"; ok = false;
        } else { errMethode.style.display = "none"; }

        const duree = document.getElementById("dureeAj");
        const errDuree = document.getElementById("errDureeAj");
        const d = parseInt(duree.value);
        if (isNaN(d) || d < 1 || d > 3650) {
            errDuree.textContent = "❌ La durée doit être entre 1 et 3650 jours.";
            errDuree.style.display = "block"; ok = false;
        } else { errDuree.style.display = "none"; }

        return ok;
    }

    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("formAjouter");
        form.addEventListener("submit", function (e) { if (!validerAjout()) e.preventDefault(); });
        document.getElementById("nomAj").addEventListener("input", validerAjout);
        document.getElementById("methodeAj").addEventListener("input", validerAjout);
        document.getElementById("dureeAj").addEventListener("input", validerAjout);
    });
    </script>
</body>
</html>