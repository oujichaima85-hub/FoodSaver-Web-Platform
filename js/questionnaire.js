/**
 * ============================================
 * FoodSaver - questionnaire.js
 * Validation JavaScript du formulaire
 * ============================================
 * 
 * Ce fichier contient:
 * - Validation du nom
 * - Validation de l'email
 * - Validation des fonctionnalités (checkboxes)
 * - Gestion de la soumission du formulaire
 */

// ============================================
// VALIDATION DU FORMULAIRE
// ============================================

/**
 * Valide le nom (minimum 3 caractères)
 * @returns {boolean} true si valide, false sinon
 */
function validerNom() {
    const nom = document.getElementById("nom").value.trim();
    const erreurNom = document.getElementById("erreurNom");
    
    // Vérifier que le nom n'est pas vide
    if (nom === "") {
        erreurNom.innerHTML = "❌ Le nom est obligatoire.";
        erreurNom.style.display = "block";
        return false;
    }
    
    // Vérifier que le nom a au moins 3 caractères
    if (nom.length < 3) {
        erreurNom.innerHTML = "❌ Le nom doit contenir au moins 3 caractères.";
        erreurNom.style.display = "block";
        return false;
    }
    
    // Vérifier que le nom ne contient que des lettres et espaces
    if (!/^[a-zA-ZÀ-ÿ\s'-]+$/.test(nom)) {
        erreurNom.innerHTML = "❌ Le nom ne doit contenir que des lettres.";
        erreurNom.style.display = "block";
        return false;
    }
    
    // Si valide, masquer le message d'erreur
    erreurNom.innerHTML = "";
    erreurNom.style.display = "none";
    return true;
}

/**
 * Valide l'adresse email
 * @returns {boolean} true si valide, false sinon
 */
function validerEmail() {
    const email = document.getElementById("email").value.trim();
    const erreurEmail = document.getElementById("erreurEmail");
    
    // Regex pour valider un email
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    // Vérifier que l'email n'est pas vide
    if (email === "") {
        erreurEmail.innerHTML = "❌ L'email est obligatoire.";
        erreurEmail.style.display = "block";
        return false;
    }
    
    // Vérifier que l'email est au format correct
    if (!regexEmail.test(email)) {
        erreurEmail.innerHTML = "❌ Veuillez entrer une adresse email valide (ex: exemple@email.com).";
        erreurEmail.style.display = "block";
        return false;
    }
    
    // Si valide, masquer le message d'erreur
    erreurEmail.innerHTML = "";
    erreurEmail.style.display = "none";
    return true;
}

/**
 * Valide que au moins une fonctionnalité est sélectionnée
 * @returns {boolean} true si au moins une option est cochée, false sinon
 */
function validerFonctionnalites() {
    // Récupérer tous les checkboxes pour les fonctionnalités
    const checkboxes = document.querySelectorAll('input[name="fonctionnalites"]');
    const erreurFonctionnalites = document.getElementById("erreurFonctionnalites");
    
    // Vérifier si au moins une checkbox est cochée
    let uneSelectionnee = false;
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            uneSelectionnee = true;
        }
    });
    
    // Si aucune n'est sélectionnée, afficher l'erreur
    if (!uneSelectionnee) {
        erreurFonctionnalites.innerHTML = "❌ Veuillez sélectionner au moins une fonctionnalité.";
        erreurFonctionnalites.style.display = "block";
        return false;
    }
    
    // Si valide, masquer le message d'erreur
    erreurFonctionnalites.innerHTML = "";
    erreurFonctionnalites.style.display = "none";
    return true;
}

/**
 * Valide l'ensemble du formulaire
 * @returns {boolean} true si tous les champs sont valides, false sinon
 */
function validerFormulaire() {
    // Valider chaque champ obligatoire
    const nomValide = validerNom();
    const emailValide = validerEmail();
    //const fonctionnalitesValides = validerFonctionnalites();
    
    // Retourner true seulement si tous les champs sont valides
    return nomValide && emailValide //&& fonctionnalitesValides;
}

// ============================================
// GESTION DES ÉVÉNEMENTS
// ============================================

/**
 * Initialisation des événements au chargement de la page
 */
document.addEventListener("DOMContentLoaded", function() {
    
    // Récupérer le formulaire
    const formulaire = document.getElementById("formulaireQuestionnaire");
    
    // Validation en temps réel du nom
    document.getElementById("nom").addEventListener("blur", validerNom);
    document.getElementById("nom").addEventListener("input", validerNom);
    
    // Validation en temps réel de l'email
    document.getElementById("email").addEventListener("blur", validerEmail);
    document.getElementById("email").addEventListener("input", validerEmail);
    
    // Validation en temps réel des fonctionnalités
    const checkboxes = document.querySelectorAll('input[name="fonctionnalites"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", validerFonctionnalites);
    });
    
    // Gestion de la soumission du formulaire
    formulaire.addEventListener("submit", function(e) {
        // Empêcher l'envoi par défaut du formulaire
        const valide = validerFormulaire();

        if (!valide) {
            e.preventDefault(); 
        }
        // Valider le formulaire
        if (validerFormulaire()) {
            // Si valide : soumettre vers PHP (laisser le formulaire s'envoyer)
            formulaire.submit();
        } else {
            // Si non valide, afficher un message d'erreur
            const messageDiv = document.getElementById("messageFormulaire");
            messageDiv.innerHTML = `
                <div style="
                    background-color: #f8d7da;
                    color: #721c24;
                    padding: 1rem;
                    border-radius: 12px;
                    margin-bottom: 1rem;
                    border: 1px solid #f5c6cb;
                    font-weight: 600;
                ">
                    ❌ Veuillez corriger les erreurs avant de soumettre.
                </div>
            `;
        }
    });
});

// ============================================
// FIN DU FICHIER QUESTIONNAIRE.JS
// ============================================