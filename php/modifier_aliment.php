<?php
require_once __DIR__ . '/connexion.php';
require_once __DIR__ . '/Aliment.php';
$pdo = getConnexion();

$message        = '';
$erreurs        = [];
$alimentEdition = null;

try {
    $pdo = getConnexion();
    $pdo->exec("SET NAMES 'utf8mb4'");

    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id   = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM aliments WHERE id = ?");
        $stmt->execute([$id]);
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($ligne) {
            $alimentEdition = Aliment::fromArray($ligne);
        } else {
            $message = '<div class="msg-erreur">❌ Aliment introuvable.</div>';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {
        $id      = intval($_POST['id']     ?? 0);
        $nom     = trim($_POST['nom']      ?? '');
        $type    = trim($_POST['type']     ?? '');
        $methode = trim($_POST['methode']  ?? '');
        $duree   = intval($_POST['duree']  ?? 0);

        if (empty($nom) || strlen($nom) < 2)
            $erreurs[] = 'Le nom doit contenir au moins 2 caractères.';
        if (!in_array($type, Aliment::TYPES_VALIDES))
            $erreurs[] = 'Type invalide.';
        if (empty($methode) || strlen($methode) < 5)
            $erreurs[] = 'La méthode est trop courte (min 5 car.).';
        if ($duree < 1 || $duree > 3650)
            $erreurs[] = 'La durée doit être entre 1 et 3650 jours.';

        if (empty($erreurs) && $id > 0) {
            $stmt = $pdo->prepare(
                "UPDATE aliments
                    SET nom = :nom,
                        type = :type,
                        methode_conservation = :methode,
                        duree_conservation_jours = :duree
                  WHERE id = :id"
            );
            $stmt->execute([':nom'=>$nom,':type'=>$type,':methode'=>$methode,':duree'=>$duree,':id'=>$id]);
            header('Location:guide.php');
            exit;
        } else {
            if ($id > 0) {
                $stmt = $pdo->prepare("SELECT * FROM aliments WHERE id = ?");
                $stmt->execute([$id]);
                $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($ligne) $alimentEdition = Aliment::fromArray($ligne);
            }
        }
    }

} catch (PDOException $e) {
    $message = '<div class="msg-erreur">❌ Erreur BD : ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FoodSaver – Modifier un aliment</title>
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
            border:2px solid #dde5da; border-top:4px solid #ff9800;
        }
        h2 { color:#1e5a2b; font-size:1.4rem; margin-bottom:1.2rem; border-left:5px solid #ff9800; padding-left:.8rem; }
        .form-card label { display:block; font-weight:600; font-size:.9rem; color:#3a4a3c; margin-bottom:.3rem; margin-top:1rem; }
        .form-card label:first-of-type { margin-top:0; }
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
        .form-card textarea:focus { outline:none; border-color:#ff9800; background:#fff; }
        .err-inline { color:#e74c3c; font-size:.82rem; display:none; margin-top:.25rem; font-weight:600; }
        .btn-submit {
            display:inline-block; padding:.75rem 2rem; margin-top:1.2rem;
            background:linear-gradient(145deg,#ff9800,#e65100);
            color:#fff; border:none; border-radius:50px;
            font-size:1rem; font-weight:600; cursor:pointer;
            box-shadow:0 4px 15px rgba(255,152,0,.3); transition:all .3s;
        }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(255,152,0,.4); }
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
        <h1>✏️ Modifier un aliment</h1>

        <?= $message ?>

        <?php if (!empty($erreurs)): ?>
            <div class="msg-validation">❌ <?= implode('<br>', array_map('htmlspecialchars', $erreurs)) ?></div>
        <?php endif; ?>

        <?php if ($alimentEdition): ?>
        <div class="form-card">
            <h2>✏️ Modifier : <?= htmlspecialchars($alimentEdition->getNom()) ?></h2>
            <form id="formModifier" method="POST" action="modifier_aliment.php" novalidate>
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="id"     value="<?= $alimentEdition->getId() ?>">

                <label>🥘 Nom <span style="color:red">*</span></label>
                <input type="text" id="nomMod" name="nom"
                       value="<?= htmlspecialchars($alimentEdition->getNom()) ?>" required>
                <span class="err-inline" id="errNomMod"></span>

                <label>📂 Type <span style="color:red">*</span></label>
                <select id="typeMod" name="type" required>
                    <?php foreach (Aliment::TYPES_VALIDES as $t): ?>
                        <option value="<?= htmlspecialchars($t) ?>"
                            <?= $alimentEdition->getType() === $t ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>🧊 Méthode de conservation <span style="color:red">*</span></label>
                <textarea id="methodeMod" name="methode" rows="3" required><?= htmlspecialchars($alimentEdition->getMethodeConservation()) ?></textarea>
                <span class="err-inline" id="errMethodeMod"></span>

                <label>⏱️ Durée (jours) <span style="color:red">*</span></label>
                <input type="number" id="dureeMod" name="duree"
                       value="<?= $alimentEdition->getDureeConservationJours() ?>" min="1" max="3650" required>
                <span class="err-inline" id="errDureeMod"></span>

                <button type="submit" class="btn-submit">💾 Sauvegarder</button>
                <a href="guide.php" class="btn-retour">Annuler</a>
            </form>
        </div>
        <?php elseif (empty($message)): ?>
            <p>Aucun aliment sélectionné. <a href="guide.php" style="color:#35ab47;font-weight:600;">Retour à la liste</a></p>
        <?php else: ?>
            <a href="guide.php" class="btn-retour" style="margin-left:0;">⬅ Retour à la gestion</a>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-logo">FoodSaver</div>
            <p class="footer-copy">© 2026 FoodSaver. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
    function validerModification() {
        let ok = true;
        const nom = document.getElementById("nomMod");
        const errNom = document.getElementById("errNomMod");
        if (nom && nom.value.trim().length < 2) {
            errNom.textContent = "❌ Le nom doit contenir au moins 2 caractères.";
            errNom.style.display = "block"; ok = false;
        } else if (nom) { errNom.style.display = "none"; }

        const methode = document.getElementById("methodeMod");
        const errMethode = document.getElementById("errMethodeMod");
        if (methode && methode.value.trim().length < 5) {
            errMethode.textContent = "❌ La méthode doit contenir au moins 5 caractères.";
            errMethode.style.display = "block"; ok = false;
        } else if (methode) { errMethode.style.display = "none"; }

        const duree = document.getElementById("dureeMod");
        const errDuree = document.getElementById("errDureeMod");
        if (duree) {
            const d = parseInt(duree.value);
            if (isNaN(d) || d < 1 || d > 3650) {
                errDuree.textContent = "❌ La durée doit être entre 1 et 3650 jours.";
                errDuree.style.display = "block"; ok = false;
            } else { errDuree.style.display = "none"; }
        }
        return ok;
    }

    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("formModifier");
        if (form) {
            form.addEventListener("submit", function (e) { if (!validerModification()) e.preventDefault(); });
            document.getElementById("nomMod")?.addEventListener("input", validerModification);
            document.getElementById("methodeMod")?.addEventListener("input", validerModification);
            document.getElementById("dureeMod")?.addEventListener("input", validerModification);
        }
    });
    </script>
</body>
</html>