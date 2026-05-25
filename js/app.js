// ── FILTRAGE PAR TYPE ─────────────────────────
function filtrerParType(type) {
    var lignes = document.querySelectorAll("#tableauAliments tbody tr");
    lignes.forEach(function(ligne) {
        ligne.style.display = "";
        if (type !== "") {
            var typeLigne = ligne.cells[1] ? ligne.cells[1].textContent.trim() : "";
            if (typeLigne !== type) {
                ligne.style.display = "none";
            }
        }
    });
    document.getElementById("resultatsRecherche").innerHTML = "";
    document.getElementById("champRecherche").value = "";
}

// ── RECHERCHE ─────────────────────────────────
function rechercherAliment(termes) {
    var t      = termes.toLowerCase().trim();
    var msg    = document.getElementById("resultatsRecherche");
    var lignes = document.querySelectorAll("#tableauAliments tbody tr");
    var count  = 0;

    lignes.forEach(function(ligne) {
        ligne.style.display = "";
        if (t !== "") {
            var nom   = ligne.cells[0] ? ligne.cells[0].textContent.toLowerCase() : "";
            var type  = ligne.cells[1] ? ligne.cells[1].textContent.toLowerCase() : "";
            var match = nom.includes(t) || type.includes(t);
            ligne.style.display = match ? "" : "none";
            if (match) count++;
        }
    });

    if (t === "") {
        msg.innerHTML = "";
    } else {
        msg.innerHTML = count > 0
            ? "<p style='color:#35ab47;font-weight:bold;'>✅ " + count + " résultat(s) trouvé(s)</p>"
            : "<p style='color:#e74c3c;font-weight:bold;'>❌ Aucun aliment trouvé.</p>";
    }
}

// ── MODALE ────────────────────────────────────
function fermerModale() {
    document.getElementById("modaleModifier").style.display = "none";
}

// ── INITIALISATION ────────────────────────────
document.addEventListener("DOMContentLoaded", function() {

    document.getElementById("btnAfficherTout").addEventListener("click", function() {
        filtrerParType("");
    });
    document.getElementById("btnAfficherLegumes").addEventListener("click", function() {
        filtrerParType("Légume");
    });
    document.getElementById("btnAfficherFruits").addEventListener("click", function() {
        filtrerParType("Fruit");
    });
    document.getElementById("btnAfficherLaitiers").addEventListener("click", function() {
        filtrerParType("Produit laitier");
    });

    document.getElementById("btnRechercher").addEventListener("click", function() {
        rechercherAliment(document.getElementById("champRecherche").value);
    });
    document.getElementById("champRecherche").addEventListener("input", function() {
        rechercherAliment(this.value);
    });

});