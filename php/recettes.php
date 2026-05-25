<?php
session_start();
require_once __DIR__ . '/connexion.php';

$succes  = $_SESSION['succes']  ?? false;
$erreurs = $_SESSION['erreurs'] ?? [];
unset($_SESSION['succes'], $_SESSION['erreurs']);

try {
    $pdo      = getConnexion();
    $stmt     = $pdo->query("SELECT * FROM recettes ORDER BY categorie, nom");
    $recettes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recettes = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FoodSaver - Cuisine Savoureuse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/logo.jpg">
    <link rel="stylesheet" href="../css/extern.css">
    <link rel="stylesheet" href="../css/recettes.css">
    <style>
        .alert-success {
            background:#d4edda;color:#155724;padding:1rem 1.5rem;
            border-radius:12px;margin-bottom:1rem;
            border-left:4px solid #35ab47;font-weight:600;
        }
        .alert-error {
            background:#f8d7da;color:#721c24;padding:1rem 1.5rem;
            border-radius:12px;margin-bottom:1rem;
            border-left:4px solid #e74c3c;font-weight:600;
        }
        .diff-badge {
            display:inline-block;padding:.2rem .7rem;border-radius:20px;
            color:#fff;font-size:.75rem;font-weight:600;margin:0 1rem .5rem;
        }
        #champsAjout, #champsCorrection, #champsAvis {
            display: none;
            flex-direction: column;
            gap: 1.2rem;
        }
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
            <label class="sidebar-close" for="sidebar-toggle" aria-label="Fermer le menu">
                <span></span><span></span><span></span>
            </label>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="../index.html"><span class="nav-icon">🏠</span>Vitrine</a></li>
                <li><a href="../about.html"><span class="nav-icon">🌿</span>Qui sommes-nous</a></li>
                <div class="sidebar-sep"></div>
                <li><a href="guide.php"><span class="nav-icon">📖</span>Tutoriel culinaire</a></li>
                <li><a href="recettes.php" class="active"><span class="nav-icon">🥘</span>Cuisine savoureuse</a></li>
                <div class="sidebar-sep"></div>
                <li><a href="../contact.html"><span class="nav-icon">✉️</span>Contactez-nous</a></li>
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
                <label class="hamburger-btn" for="sidebar-toggle" aria-label="Ouvrir le menu">
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
        <h1 class="title">Nos <?= count($recettes) ?> Recettes Anti-Gaspillage De Ce Mois</h1>

        <!-- Grille recettes depuis BD -->
        <section class="grid-container">
            <?php
            $diffColors = ['Facile'=>'#35ab47','Moyen'=>'#ff9800','Difficile'=>'#e74c3c'];
            foreach ($recettes as $r):
                $dc = $diffColors[$r['difficulte']] ?? '#607d8b';
            ?>
            <div class="card">
                <img src="../images/<?= htmlspecialchars($r['photo']) ?>"
                     alt="<?= htmlspecialchars($r['nom']) ?>"
                     onerror="this.src='../images/salade.jpg'">
                <h3><?= htmlspecialchars($r['nom']) ?></h3>
                <span class="diff-badge" style="background:<?= $dc ?>;">
                    <?= htmlspecialchars($r['difficulte']) ?> · <?= (int)$r['temps_preparation'] ?> min
                </span>
                <p class="description"><?= htmlspecialchars($r['description']) ?></p>
            </div>
            <?php endforeach; ?>
        </section>

        <!-- Formulaire -->
        <section class="form-section">
            <h2>Proposer ou Modifier une Recette</h2>

            <?php if ($succes): ?>
                <div class="alert-success">✅ Votre demande a été reçue ! Merci 🌿</div>
            <?php endif; ?>
            <?php if (!empty($erreurs)): ?>
                <div class="alert-error">❌ <?= implode('<br>', array_map('htmlspecialchars', $erreurs)) ?></div>
            <?php endif; ?>

            <form action="traiter_recettes.php" method="POST" id="formRecette">

                <label>Votre Nom :</label>
                <input type="text" name="nom" required>

                <label>Votre Email :</label>
                <input type="email" name="email" required>

                <label>Type de demande :</label>
                <select name="typeDemande" id="typeDemande" required>
                    <option value="">-- Choisir --</option>
                    <option value="ajout">Ajouter une recette</option>
                    <option value="correction">Corriger une recette</option>
                    <option value="avis">Donner un avis</option>
                </select>

                <!-- AJOUT -->
                <div id="champsAjout">
                    <label>Titre de la recette :</label>
                    <input type="text" name="titreRecette" id="titreAjout">

                    <label>Catégorie :</label>
                    <select name="categorie">
                        <option value="Plat principal">Plat principal</option>
                        <option value="Entrée">Entrée</option>
                        <option value="Soupe">Soupe</option>
                        <option value="Dessert">Dessert</option>
                        <option value="Boisson">Boisson</option>
                    </select>

                    <label>Temps de préparation (minutes) :</label>
                    <input type="number" name="temps" min="1" max="300">

                    <label>Difficulté :</label>
                    <select name="difficulte">
                        <option value="Facile">Facile</option>
                        <option value="Moyen">Moyen</option>
                        <option value="Difficile">Difficile</option>
                    </select>

                    <label>Description :</label>
                    <textarea name="descriptionRecette" id="descAjout" rows="4"></textarea>

                    <label>Nom de la photo (ex: salade.jpg) :</label>
                    <input type="text" name="photo" placeholder="salade.jpg">
                </div>

                <!-- CORRECTION -->
                <div id="champsCorrection">
                    <label>Titre de la recette à corriger :</label>
                    <input type="text" name="titreCorrection" id="titreCorrection">

                    <label>Description de la correction :</label>
                    <textarea name="descriptionCorrection" id="descCorrection" rows="4"
                              placeholder="Décrivez l'erreur à corriger..."></textarea>
                </div>

                <!-- AVIS -->
                <div id="champsAvis">
                    <label>Votre avis :</label>
                    <textarea name="messageAvis" rows="4"
                              placeholder="Partagez votre avis..."></textarea>
                </div>

                <button type="submit">Envoyer</button>
            </form>
        </section>

        <div class="retour">
            <a href="../index.html">⬅ Retour à la page d'accueil</a>
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

    <script>
    document.addEventListener("DOMContentLoaded", function () {

        // --- Fade-out du message de succès après 3 secondes ---
        var succes = document.querySelector(".alert-success");
        if (succes) {
            setTimeout(function () {
                succes.style.transition = "opacity 1s";
                succes.style.opacity = "0";
                setTimeout(function () { succes.remove(); }, 1000);
            }, 3000);
        }

        // --- Affichage conditionnel des champs selon le type de demande ---
        var selectType = document.getElementById("typeDemande");

        function toggleChamps() {
            document.getElementById("champsAjout").style.display      = "none";
            document.getElementById("champsCorrection").style.display = "none";
            document.getElementById("champsAvis").style.display       = "none";

            var val = selectType.value;
            if (val === "ajout")
                document.getElementById("champsAjout").style.display = "flex";
            else if (val === "correction")
                document.getElementById("champsCorrection").style.display = "flex";
            else if (val === "avis")
                document.getElementById("champsAvis").style.display = "flex";
        }

        selectType.addEventListener("change", toggleChamps);

        // --- Validation côté client avant soumission ---
        document.getElementById("formRecette").addEventListener("submit", function (e) {
            var val = selectType.value;

            if (val === "ajout") {
                var titre = document.getElementById("titreAjout").value.trim();
                var desc  = document.getElementById("descAjout").value.trim();
                if (!titre || !desc) {
                    e.preventDefault();
                    alert("Veuillez remplir le titre et la description de la recette.");
                }
            } else if (val === "correction") {
                var titreC = document.getElementById("titreCorrection").value.trim();
                var descC  = document.getElementById("descCorrection").value.trim();
                if (!titreC || !descC) {
                    e.preventDefault();
                    alert("Veuillez remplir le titre et la description de la correction.");
                }
            } else if (val === "avis") {
                var avis = document.querySelector("[name='messageAvis']").value.trim();
                if (!avis) {
                    e.preventDefault();
                    alert("Veuillez saisir votre avis.");
                }
            }
        });
    });
    </script>
</body>
</html>