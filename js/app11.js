/**
 * ============================================
 * FoodSaver - app.js  (version avec Modifier)
 * ============================================
 */

// ── 1. CONSTRUCTEUR ──────────────────────────
/*function Aliment(nom, type, methodeConservation) {
    this.nom = nom;
    this.type = type;
    this.methodeConservation = methodeConservation;
}

// ── 2. DONNÉES INITIALES ─────────────────────
let aliments = [
    new Aliment("Poulet cuit",      "Plat préparé",   "Conserver au réfrigérateur dans une boîte hermétique pendant 2-3 jours maximum."),
    new Aliment("Riz cuit",         "Céréale",         "Mettre au frais rapidement après cuisson et consommer sous 48 heures."),
    new Aliment("Pâtes cuites",     "Plat préparé",   "Conserver dans un récipient fermé au réfrigérateur 3 jours."),
    new Aliment("Lait",             "Produit laitier", "Garder au frais entre 2-4°C et bien refermer après usage."),
    new Aliment("Fromage",          "Produit laitier", "Envelopper dans du papier alimentaire et conserver au réfrigérateur."),
    new Aliment("Tomates",          "Légume",          "Conserver à température ambiante loin du soleil."),
    new Aliment("Carottes",         "Légume",          "Mettre au réfrigérateur dans un sac perforé."),
    new Aliment("Pommes",           "Fruit",           "Conserver au frais, séparées des autres fruits."),
    new Aliment("Bananes",          "Fruit",           "Garder à température ambiante, loin de l'humidité."),
    new Aliment("Pommes de terre",  "Légume",          "Stocker dans un endroit sec et sombre."),
    new Aliment("Pain",             "Céréale",         "Conserver dans un sac en tissu ou congeler pour longue durée."),
    new Aliment("Poisson frais",    "Produit frais",   "Conserver au réfrigérateur et consommer sous 24 heures."),
    new Aliment("Yaourt",           "Produit laitier", "Conserver au froid et vérifier la date de péremption."),
    new Aliment("Salade verte",     "Légume",          "Laver, sécher et conserver dans un récipient fermé au frais."),
    new Aliment("Fraises",          "Fruit",           "Ne pas laver avant stockage, garder au frais.")
];

// ── 3. AFFICHAGE DU TABLEAU ───────────────────
function afficherTableau(listeAliments) {
    var corps = document.getElementById("corpsTableau");
    if (!corps) return;
    corps.innerHTML = "";

    if (listeAliments.length === 0) {
        corps.innerHTML = "<tr><td colspan='4' style='text-align:center;color:#999;padding:1.5rem;'>Aucun aliment trouvé.</td></tr>";
        return;
    }

    listeAliments.forEach(function(aliment) {
        var indexReel = aliments.indexOf(aliment);
        var ligne = document.createElement("tr");
        ligne.innerHTML =
            "<td><strong>" + aliment.nom + "</strong></td>" +
            "<td>" + aliment.type + "</td>" +
            "<td>" + aliment.methodeConservation + "</td>" +
            "<td style='white-space:nowrap;'>" +
                "<button class='btn-edit' onclick='ouvrirModale(" + indexReel + ")' " +
                    "style='background:#ff9800;color:#fff;border:none;padding:.3rem .9rem;border-radius:20px;font-size:.82rem;cursor:pointer;margin-right:.4rem;'>"+
                    "✏️ Modifier</button>" +
                "<button class='btn-delete' onclick='supprimerAliment(" + indexReel + ")' " +
                    "style='background:#e74c3c;color:#fff;border:none;padding:.3rem .9rem;border-radius:20px;font-size:.82rem;cursor:pointer;'>"+
                    "🗑️ Supprimer</button>" +
            "</td>";
        corps.appendChild(ligne);
    });
}
*//*
// ── 4. FILTRAGE ───────────────────────────────
function filtrerParType(typeChoisi) {
    if (typeChoisi === "") {
        afficherTableau(aliments);
    } else {
        afficherTableau(aliments.filter(function(a) { return a.type === typeChoisi; }));
    }
}

// ── 5. AJOUT ──────────────────────────────────
function ajouterAliment() {
    var nom     = document.getElementById("nomAliment").value.trim();
    var type    = document.getElementById("typeAliment").value;
    var methode = document.getElementById("methodeCon").value.trim();

    if (!nom || !type || !methode) {
        alert("❌ Veuillez remplir tous les champs!");
        return;
    }
    aliments.push(new Aliment(nom, type, methode));
    afficherTableau(aliments);
    document.getElementById("formulaireAliment").reset();
    alert("✅ Aliment ajouté avec succès!");
}

// ── 6. SUPPRESSION ────────────────────────────
function supprimerAliment(index) {
    if (confirm("Êtes-vous sûr de vouloir supprimer cet aliment?")) {
        aliments.splice(index, 1);
        afficherTableau(aliments);
        alert("✅ Aliment supprimé avec succès!");
    }
}*/

// ── 7. MODALE MODIFICATION ────────────────────

/*function ouvrirModale(index) {
    var a = aliments[index];
    if (!a) return;

    document.getElementById("modaleIndex").value   = index;
    document.getElementById("modaleNom").value     = a.nom;
    document.getElementById("modaleType").value    = a.type;
    document.getElementById("modaleMethode").value = a.methodeConservation;

    document.getElementById("errModaleNom").textContent     = "";
    document.getElementById("errModaleType").textContent    = "";
    document.getElementById("errModaleMethode").textContent = "";

    document.getElementById("modaleModifier").style.display = "flex";
}

function fermerModale() {
    document.getElementById("modaleModifier").style.display = "none";
}

function sauvegarderModification() {
    var index   = parseInt(document.getElementById("modaleIndex").value);
    var nom     = document.getElementById("modaleNom").value.trim();
    var type    = document.getElementById("modaleType").value;
    var methode = document.getElementById("modaleMethode").value.trim();
    var ok      = true;

    if (nom.length < 2) {
        document.getElementById("errModaleNom").textContent = "❌ Minimum 2 caractères.";
        ok = false;
    } else {
        document.getElementById("errModaleNom").textContent = "";
    }

    if (!type) {
        document.getElementById("errModaleType").textContent = "❌ Choisissez un type.";
        ok = false;
    } else {
        document.getElementById("errModaleType").textContent = "";
    }

    if (methode.length < 5) {
        document.getElementById("errModaleMethode").textContent = "❌ Minimum 5 caractères.";
        ok = false;
    } else {
        document.getElementById("errModaleMethode").textContent = "";
    }

    if (!ok) return;

    aliments[index].nom                 = nom;
    aliments[index].type                = type;
    aliments[index].methodeConservation = methode;

    fermerModale();
    afficherTableau(aliments);
    alert("✅ Aliment modifié avec succès!");
}*/

// ── 8. RECHERCHE ──────────────────────────────
function rechercherAliment(termes) {
    var t   = termes.toLowerCase().trim();
    var msg = document.getElementById("resultatsRecherche");

    if (t === "") {
        afficherTableau(aliments);
        if (msg) msg.innerHTML = "";
        return;
    }

    var resultats = aliments.filter(function(a) {
        return a.nom.toLowerCase().includes(t) || a.type.toLowerCase().includes(t);
    });

    afficherTableau(resultats);
    if (msg) {
        msg.innerHTML = resultats.length > 0
            ? "<p style='color:#35ab47;font-weight:bold;'>✅ " + resultats.length + " résultat(s) trouvé(s)</p>"
            : "<p style='color:#e74c3c;font-weight:bold;'>❌ Aucun aliment ne correspond à votre recherche.</p>";
    }
}

// ── 9. INITIALISATION ─────────────────────────
document.addEventListener("DOMContentLoaded", function() {

    //afficherTableau(aliments);

    document.getElementById("btnAfficherTout").addEventListener("click",     function() { filtrerParType(""); });
    document.getElementById("btnAfficherLegumes").addEventListener("click",  function() { filtrerParType("Légume"); });
    document.getElementById("btnAfficherFruits").addEventListener("click",   function() { filtrerParType("Fruit"); });
    document.getElementById("btnAfficherLaitiers").addEventListener("click", function() { filtrerParType("Produit laitier"); });

    /*document.getElementById("formulaireAliment").addEventListener("submit", function(e) {
        e.preventDefault();
        ajouterAliment();
    });
*/
    document.getElementById("btnRechercher").addEventListener("click", function() {
        rechercherAliment(document.getElementById("champRecherche").value);
    });
    document.getElementById("champRecherche").addEventListener("input", function() {
        rechercherAliment(this.value);
    });

});
