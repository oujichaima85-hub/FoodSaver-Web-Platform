<?php
// ════════════════════════════════════════════════════════
//  INITIALISATION & RÉCUPÉRATION DES DONNÉES
// ════════════════════════════════════════════════════════

// Inclut le fichier de connexion à la base de données (une seule fois)
require_once 'connexion.php';

// Inclut la classe Aliment qui représente un aliment en objet PHP
require_once 'Aliment.php';

// Crée la connexion PDO via la fonction définie dans connexion.php
$pdo = getConnexion();

// Exécute la requête SQL :
// - Récupère tous les aliments de la table
// - Les trie par type puis par nom (ordre alphabétique)
$stmt = $pdo->query("SELECT * FROM aliments ORDER BY type, nom");

// Récupère toutes les lignes sous forme de tableaux associatifs
// Exemple : ["id" => 1, "nom" => "Pomme", "type" => "Fruit", ...]
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Transforme chaque tableau associatif en objet Aliment
// grâce à la méthode statique fromArray()
$aliments = array_map(fn($l) => Aliment::fromArray($l), $lignes);
?>

<!DOCTYPE html>
<html lang="fr"><!-- Langue française pour l'accessibilité et le SEO -->

<head>
    <!-- Encodage UTF-8 pour supporter les caractères spéciaux (é, à, ç...) -->
    <meta charset="UTF-8">

    <!-- Titre affiché dans l'onglet du navigateur -->
    <title>FoodSaver - Tutoriel Culinaire</title>

    <!-- Rend la page responsive sur mobile et tablette -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Icône affichée dans l'onglet du navigateur (favicon) -->
    <link rel="icon" type="image/png" href="../images/logo.jpg">

    <!-- Feuille de style globale du site -->
    <link rel="stylesheet" href="../css/extern.css">

    <!-- Feuille de style spécifique à cette page -->
    <link rel="stylesheet" href="../css/guide.css">

    <style>
        /* ════════════════════════════════════════════════
           STYLES DE LA MODALE DE MODIFICATION
           ════════════════════════════════════════════════ */

        /* Conteneur principal de la modale (fond sombre) */
        #modaleModifier {
            display: none;               /* Cachée par défaut */
            position: fixed;             /* Reste fixe même en scrollant */
            inset: 0;                    /* Couvre tout l'écran (top/right/bottom/left = 0) */
            background: rgba(0,0,0,.55); /* Fond noir semi-transparent */
            z-index: 9999;               /* Au-dessus de tous les autres éléments */
            align-items: center;         /* Centre le contenu verticalement */
            justify-content: center;     /* Centre le contenu horizontalement */
        }

        /* Boîte blanche au centre de la modale */
        .modale-boite {
            background: #fff;
            border-radius: 24px;                      /* Coins très arrondis */
            padding: 2rem 2.5rem;                     /* Espacement intérieur */
            width: min(500px, 92vw);                  /* Max 500px, sinon 92% de l'écran */
            box-shadow: 0 20px 60px rgba(0,0,0,.25); /* Ombre portée */
            border-top: 5px solid #ff9800;            /* Barre orange décorative en haut */
            position: relative;                       /* Pour positionner le bouton ✕ */
        }

        /* Titre de la modale */
        .modale-boite h2 {
            color: #1e5a2b;
            font-size: 1.3rem;
            margin-bottom: 1.2rem;
            border-left: 5px solid #ff9800; /* Barre orange à gauche du titre */
            padding-left: .75rem;
        }

        /* Étiquettes des champs */
        .modale-boite label {
            display: block;        /* Chaque label sur sa propre ligne */
            font-weight: 600;
            font-size: .9rem;
            color: #3a4a3c;
            margin-top: 1rem;
            margin-bottom: .3rem;
        }

        /* Style commun pour tous les champs de saisie */
        .modale-boite input[type=text],
        .modale-boite select,
        .modale-boite textarea {
            width: 100%;
            padding: .65rem .9rem;
            border: 2px solid #dde5da;
            border-radius: 10px;
            font-size: .95rem;
            font-family: inherit;       /* Utilise la même police que le reste */
            background: #f8faf8;
            box-sizing: border-box;     /* Le padding ne déborde pas la largeur */
            transition: border-color .2s; /* Animation douce au focus */
        }

        /* Style quand un champ est actif (cliqué) */
        .modale-boite input:focus,
        .modale-boite select:focus,
        .modale-boite textarea:focus {
            outline: none;              /* Supprime le contour bleu par défaut */
            border-color: #ff9800;      /* Bordure orange */
            background: #fff;
        }

        /* Zone d'affichage des erreurs de validation */
        .err-modale {
            color: #e74c3c;             /* Rouge */
            font-size: .82rem;
            font-weight: 600;
            display: block;
            min-height: 1rem;           /* Garde l'espace même quand vide (évite les sauts) */
            margin-top: .2rem;
        }

        /* Conteneur des boutons d'action de la modale */
        .modale-actions {
            display: flex;
            gap: .8rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;            /* Les boutons passent à la ligne sur petit écran */
        }

        /* Bouton principal "Sauvegarder" */
        .btn-sauvegarder {
            flex: 1;                    /* Prend tout l'espace disponible */
            padding: .75rem 1.5rem;
            background: linear-gradient(145deg,#ff9800,#e65100); /* Dégradé orange */
            color: #fff;
            border: none;
            border-radius: 50px;        /* Bouton complètement arrondi */
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255,152,0,.3);
            transition: all .3s;
        }

        /* Effet au survol : légère élévation */
        .btn-sauvegarder:hover {
            transform: translateY(-2px);
        }

        /* Bouton secondaire "Annuler" */
        .btn-annuler-modale {
            padding: .7rem 1.5rem;
            background: #f5f5f5;
            color: #555;
            border: 2px solid #dde;
            border-radius: 50px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }

        /* Assombrit le bouton Annuler au survol */
        .btn-annuler-modale:hover {
            background: #ddd;
        }

        /* Bouton ✕ de fermeture en haut à droite */
        .modale-fermer {
            position: absolute;   /* Positionné par rapport à .modale-boite */
            top: 1rem;
            right: 1.2rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #aaa;
            transition: color .2s;
        }

        /* Passe en rouge au survol */
        .modale-fermer:hover {
            color: #e74c3c;
        }
    </style>
</head>

<body>
    <!-- Élément décoratif graphique (bulle/orbe d'arrière-plan) -->
    <div class="orb"></div>

    <!-- ════════════════════════════════════════════════════
         SYSTÈME DE MENU SIDEBAR (sans JavaScript)
         Fonctionne grâce à une checkbox cachée :
         - cochée = menu ouvert
         - décochée = menu fermé
         ════════════════════════════════════════════════════ -->

    <!-- Checkbox invisible qui contrôle l'état du menu -->
    <input type="checkbox" id="sidebar-toggle">

    <!-- Fond semi-transparent : cliquer dessus ferme le menu -->
    <label class="sidebar-overlay" for="sidebar-toggle" aria-hidden="true"></label>

    <!-- ── BARRE LATÉRALE DE NAVIGATION ── -->
    <aside class="sidebar">

        <!-- En-tête du sidebar : logo + bouton fermeture -->
        <div class="sidebar-header">
            <a href="../index.html" class="sidebar-logo">
                <img src="../images/logo.jpg" alt="FoodSaver">
                <span>FoodSaver</span>
            </a>
            <!-- Label lié à la checkbox → décoche = ferme le menu -->
            <label class="sidebar-close" for="sidebar-toggle" aria-label="Fermer le menu">
                <!-- 3 spans = icône hamburger animée en croix -->
                <span></span><span></span><span></span>
            </label>
        </div>

        <!-- Liens de navigation -->
        <nav class="sidebar-nav">
            <ul>
                <li><a href="../index.html"><span class="nav-icon">🏠</span>Vitrine</a></li>
                <li><a href="../about.html"><span class="nav-icon">🌿</span>Qui sommes-nous</a></li>
                <!-- "active" = indique la page courante (style différent) -->
                <li><a href="guide.php" class="active"><span class="nav-icon">📖</span>Tutoriel culinaire</a></li>
                <li><a href="recettes.php"><span class="nav-icon">🥘</span>Cuisine savoureuse</a></li>
                <li><a href="../contact.html"><span class="nav-icon">✉️</span>Contactez-nous</a></li>
                <li><a href="../questionnaire.html"><span class="nav-icon">📋</span>Questionnaire</a></li>
                <li><a href="../funpage.html"><span class="nav-icon">🎮</span>Fun Page</a></li>
            </ul>
        </nav>

        <div class="sidebar-footer">© 2026 FoodSaver · Tous droits réservés</div>
    </aside>

    <!-- ════════════════════════════════════════════════════
         HEADER & BARRE DE NAVIGATION PRINCIPALE
         ════════════════════════════════════════════════════ -->
    <header>
        <nav class="navbar">
            <!-- Logo + nom du site (lien vers l'accueil) -->
            <div class="site-name">
                <img src="../images/logo.jpg" alt="FoodSaver logo">
                <a href="../index.html">FoodSaver</a>
            </div>

            <!-- Bouton hamburger pour ouvrir le sidebar -->
            <div class="navbar-right">
                <span class="nav-hint">Menu</span>
                <!-- Cliquer sur ce label coche/décoche la checkbox sidebar-toggle -->
                <label class="hamburger-btn" for="sidebar-toggle" aria-label="Ouvrir le menu">
                    <span></span><span></span><span></span>
                </label>
            </div>
        </nav>
    </header>

    <!-- ════════════════════════════════════════════════════
         BADGES DÉCORATIFS
         ════════════════════════════════════════════════════ -->
    <div class="badges-bar">
        <div class="badge"><span class="badge-icon">🌿</span> Zéro Gaspillage</div>
        <div class="badge"><span class="badge-icon">🥘</span> Recettes Fraîches</div>
        <div class="badge"><span class="badge-icon">❄️</span> Conseils de Conservation</div>
    </div>

    <!-- ════════════════════════════════════════════════════
         MODALE DE MODIFICATION D'UN ALIMENT
         Cachée par défaut, affichée par JavaScript
         quand l'utilisateur clique sur "Modifier"
         ════════════════════════════════════════════════════ -->
    <div id="modaleModifier">
        <div class="modale-boite">

            <!-- Bouton ✕ : appelle fermerModale() définie dans app.js -->
            <button class="modale-fermer" onclick="fermerModale()" title="Fermer">✕</button>

            <h2>✏️ Modifier l'aliment</h2>

            <!-- Champ caché : stocke l'identifiant de l'aliment à modifier -->
            <!-- Invisible pour l'utilisateur mais utilisé par JavaScript -->
            <input type="hidden" id="modaleIndex">

            <!-- Champ Nom -->
            <label>🥘 Nom <span style="color:red">*</span></label>
            <input type="text" id="modaleNom" placeholder="Ex: Carottes">
            <!-- Zone d'erreur : remplie par JS si le champ est vide -->
            <span class="err-modale" id="errModaleNom"></span>

            <!-- Champ Type -->
            <label>📂 Type <span style="color:red">*</span></label>
            <select id="modaleType">
                <option value="">-- Choisir --</option>
                <option value="Légume">Légume</option>
                <option value="Fruit">Fruit</option>
                <option value="Produit laitier">Produit laitier</option>
                <option value="Produit frais">Produit frais</option>
                <option value="Céréale">Céréale</option>
                <option value="Plat préparé">Plat préparé</option>
            </select>
            <span class="err-modale" id="errModaleType"></span>

            <!-- Champ Méthode de conservation -->
            <label>🧊 Méthode de conservation <span style="color:red">*</span></label>
            <textarea id="modaleMethode" rows="3" placeholder="Ex: Conserver au réfrigérateur..."></textarea>
            <span class="err-modale" id="errModaleMethode"></span>

            <!-- Boutons d'action -->
            <div class="modale-actions">
                <!-- Valide et envoie les modifications -->
                <button class="btn-sauvegarder" onclick="sauvegarderModification()">💾 Sauvegarder</button>
                <!-- Annule et ferme la modale sans rien changer -->
                <button class="btn-annuler-modale" onclick="fermerModale()">Annuler</button>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════
         CONTENU PRINCIPAL DE LA PAGE
         ════════════════════════════════════════════════════ -->
    <main>
        <h1>Guide de Conservation Anti-Gaspillage</h1>

        <!-- ── BOUTONS DE FILTRAGE ── -->
        <!-- Ces boutons filtrent le tableau via JavaScript (app.js) -->
        <div style="display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap;">
            <button id="btnAfficherTout"     class="btn-action">📊 Afficher tout</button>
            <button id="btnAfficherLegumes"  class="btn-action">🥬 Légumes seulement</button>
            <button id="btnAfficherFruits"   class="btn-action">🍎 Fruits seulement</button>
            <button id="btnAfficherLaitiers" class="btn-action">🥛 Produits laitiers</button>
        </div>

        <!-- ── TABLEAU DES ALIMENTS ── -->
        <div class="table-container">
            <table id="tableauAliments" border="1" width="100%" cellpadding="10">
                <thead>
                    <tr>
                        <th>Aliment</th>
                        <th>Type</th>
                        <th>Méthode de Conservation</th>
                        <th>période de conservation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aliments as $aliment): ?>
                        <!-- Pour chaque objet Aliment, génère une ligne <tr> HTML -->
                        <!-- toTableRow() est une méthode de la classe Aliment -->
                        <?= $aliment->toTableRow() ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <br><br>

        <!-- ── FORMULAIRE D'AJOUT D'UN ALIMENT ── -->
        <!-- method="POST" : données envoyées de façon sécurisée -->
        <!-- action="ajouter_aliment.php" : fichier PHP qui traite l'ajout -->
        <h2>Ajouter un nouvel Aliment</h2>
        <form id="formulaireAliment" action="ajouter_aliment.php" method="POST">

            <label>Nom de l'aliment :</label><br>
            <!-- name="nom" : clé utilisée dans $_POST['nom'] côté PHP -->
            <input type="text" id="nomAliment" name="nom" required><br><br>

            <label>Type :</label><br>
            <select id="typeAliment" name="type" required>
                <option value="">-- Choisir --</option>
                <option value="Légume">Légume</option>
                <option value="Fruit">Fruit</option>
                <option value="Produit laitier">Produit laitier</option>
                <option value="Produit frais">Produit frais</option>
                <option value="Céréale">Céréale</option>
                <option value="Plat préparé">Plat préparé</option>
            </select><br><br>

            <label>Méthode de Conservation :</label><br>
            <textarea id="methodeCon" name="methode" rows="3" required></textarea><br><br>

            <!-- Soumet le formulaire vers ajouter_aliment.php -->
            <button type="submit" class="btn-submit">✅ Ajouter l'aliment</button>
        </form>

        <br><br>

        <!-- ── BARRE DE RECHERCHE ── -->
        <!-- La recherche est gérée en JavaScript, sans rechargement de page -->
        <h2>Rechercher un Aliment</h2>
        <input type="text" id="champRecherche"
               placeholder="Tapez le nom d'un aliment..."
               style="padding:.8rem 1rem; border:2px solid #dde5da; border-radius:12px;
                      width:100%; max-width:400px; font-size:1rem; margin-bottom:1rem;">
        <!-- Déclenche la recherche via JavaScript -->
        <button id="btnRechercher" class="btn-action">🔍 Rechercher</button>
        <!-- Conteneur vide : les résultats y seront injectés par JavaScript -->
        <div id="resultatsRecherche" style="margin-top:1rem;"></div>

        <br><br>

        <!-- ── FORMULAIRE D'AVIS ── -->
        <!-- Permet d'envoyer un avis ou de proposer un nouvel aliment -->
        <h2 id="formulaireAvis-titre">Donnez votre Avis ou Proposez un Aliment</h2>
        <form action="avis.php" method="POST" id="formulaireAvis">

            <label>Nom :</label><br>
            <input type="text" name="nom" required><br><br>

            <label>Email :</label><br>
            <!-- type="email" : validation automatique du format email -->
            <input type="email" name="email" required><br><br>

            <label>Nom de l'aliment :</label><br>
            <!-- Optionnel : pas de "required" -->
            <input type="text" name="aliment"><br><br>

            <label>Votre message :</label><br>
            <textarea name="message" rows="5"></textarea><br><br>

            <!-- Type de demande : détermine le traitement côté PHP -->
            <select name="typeDemande" required>
                <option value="">-- Choisir --</option>
                <option value="remerciement">Remercier la méthode</option>
                <option value="signalement">Signaler une erreur</option>
                <option value="proposition">Proposer un nouvel aliment</option>
            </select><br><br>

            <button type="submit">Envoyer</button>
        </form>

        <br><br>
        <div><a href="../index.html">⬅ Retour à la page d'accueil</a></div>
    </main>

    <!-- ════════════════════════════════════════════════════
         PIED DE PAGE
         ════════════════════════════════════════════════════ -->
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

    <!-- Fichier JS principal : gère les filtres, la recherche et la modale -->
    <script src="../js/app.js"></script>

    <script>
        // ════════════════════════════════════════════════
        //  NOTIFICATION DE CONFIRMATION APRÈS ENVOI D'AVIS
        // ════════════════════════════════════════════════

        // Attend que tout le HTML soit chargé avant d'exécuter le code
        document.addEventListener("DOMContentLoaded", function() {

            // Analyse les paramètres de l'URL
            // Exemple : guide.php?avis=ok → get('avis') retourne "ok"
            // Cela signifie que avis.php a traité le formulaire et redirigé ici
            if (new URLSearchParams(window.location.search).get('avis') === 'ok') {

                // Cherche le formulaire d'avis dans la page
                var formulaire = document.querySelector("#formulaireAvis");

                // Vérifie que le formulaire existe bien (sécurité)
                if (formulaire) {

                    // Crée une nouvelle div en mémoire (pas encore dans la page)
                    var div = document.createElement("div");

                    // Définit le texte du message de succès
                    div.textContent = "✅ Message envoyé avec succès ! Merci 🌿";

                    // Applique les styles CSS directement sur l'élément
                    div.style.cssText =
                        "background:#d4edda;"           +  /* Fond vert clair       */
                        "color:#155724;"                +  /* Texte vert foncé      */
                        "padding:1rem 1.5rem;"          +  /* Espacement intérieur  */
                        "border-radius:12px;"           +  /* Coins arrondis        */
                        "border-left:5px solid #35ab47;"+  /* Barre verte à gauche  */
                        "font-weight:600;"              +  /* Texte en gras         */
                        "margin-top:1rem;"              +  /* Marge au-dessus       */
                        "transition:opacity 1s;";          /* Animation de disparition */

                    // Insère la div juste AVANT le formulaire dans le DOM
                    // Résultat : [✅ Message] puis [Formulaire]
                    formulaire.before(div);

                    // Après 2 secondes (2000ms) → déclenche la disparition
                    setTimeout(function() {

                        // Rend la div invisible (mais elle est encore dans le DOM)
                        // La transition CSS "opacity 1s" rend la disparition progressive
                        div.style.opacity = "0";

                        // Après 1 seconde supplémentaire (le temps de la transition)
                        // supprime définitivement la div du DOM
                        setTimeout(function() {
                            div.remove();
                        }, 1000);

                    }, 2000);
                }
            }
        });
    </script>
</body>
</html>