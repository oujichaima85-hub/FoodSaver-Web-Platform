/**
 * ============================================
 * FoodSaver - contact.js
 * Bannière dynamique et galerie d'images
 * ============================================
 * 
 * Ce fichier contient:
 * - Bannière animée avec date et heure
 * - Galerie d'images avec rotation automatique
 * - Contrôles manuels pour la galerie
 */

// ============================================
// 1. BANNIÈRE ANIMÉE AVEC DATE ET HEURE
// ============================================

/**
 * Met à jour la bannière avec la date et l'heure actuelle
 */
function mettreAJourBanniere() {
    // Créer une date actuelle
    const maintenant = new Date();
    
    // Formater la date en français
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    const dateFormatee = maintenant.toLocaleDateString('fr-FR', options);
    
    // Formater l'heure
    const heure = maintenant.getHours().toString().padStart(2, '0');
    const minute = maintenant.getMinutes().toString().padStart(2, '0');
    const seconde = maintenant.getSeconds().toString().padStart(2, '0');
    const heureFormatee = `${heure}:${minute}:${seconde}`;
    
    // Créer le message
    const message = `🌿 Bienvenue au site web FoodSaver! Aujourd'hui ${dateFormatee}, et l'heure actuelle est ${heureFormatee} ⏰`;
    
    // Afficher le message dans la bannière
    document.getElementById("texteBanniere").innerHTML = message;
}

/**
 * Initialise la bannière animée au chargement
 */
function initialiserBanniere() {
    // Mise à jour initiale
    mettreAJourBanniere();
    
    // Mettre à jour chaque seconde
    setInterval(mettreAJourBanniere, 1000);
}

// ============================================
// 2. GALERIE D'IMAGES
// ============================================

/**
 * Tableau des images de la galerie
 * Utilisées pour le carousel automatique
 */
const imagesGalerie = [
    { src: "./images/burger.jpg", nom: "Burger au bœuf maison" },
    { src: "./images/salade.jpg", nom: "Salade Méditerranéenne" },
    { src: "./images/pizza.jpg", nom: "Pizza Margherita" },
    { src: "./images/gratin.jpg", nom: "Gratin dauphinois" },
    { src: "./images/soupe.jpg", nom: "Soupe de légumes d'hiver" }
];

// Variable globale pour l'index de l'image actuelle
let indexGalerieActuel = 0;
let timerGalerie = null;

/**
 * Affiche l'image à l'index spécifié
 * @param {number} index - Index de l'image à afficher
 */
function afficherImageGalerie(index) {
    // Vérifier les limites
    if (index < 0) {
        indexGalerieActuel = imagesGalerie.length - 1;
    } else if (index >= imagesGalerie.length) {
        indexGalerieActuel = 0;
    } else {
        indexGalerieActuel = index;
    }
    
    // Obtenir l'image actuelle
    const imageActuelle = imagesGalerie[indexGalerieActuel];
    
    // Mettre à jour l'image
    document.getElementById("galerieImage").src = imageActuelle.src;
    document.getElementById("galerieImage").alt = imageActuelle.nom;
    
    // Mettre à jour le compteur
    document.getElementById("compteurGalerie").textContent = 
        `${indexGalerieActuel + 1} / ${imagesGalerie.length}`;
}

/**
 * Passe à l'image suivante
 */
function prochainImage() {
    // Arrêter la rotation automatique et la redémarrer
    clearInterval(timerGalerie);
    
    afficherImageGalerie(indexGalerieActuel + 1);
    
    // Redémarrer la rotation automatique
    demarrerRotationAutomatique();
}

/**
 * Passe à l'image précédente
 */
function imagePrecedente() {
    // Arrêter la rotation automatique et la redémarrer
    clearInterval(timerGalerie);
    
    afficherImageGalerie(indexGalerieActuel - 1);
    
    // Redémarrer la rotation automatique
    demarrerRotationAutomatique();
}

/**
 * Démarre la rotation automatique des images (chaque 3 secondes)
 */
function demarrerRotationAutomatique() {
    // Chaque 3 secondes, passer à l'image suivante
    timerGalerie = setInterval(function() {
        afficherImageGalerie(indexGalerieActuel + 1);
    }, 3000); // 3000 ms = 3 secondes
}

// ============================================
// 3. INITIALISATION AU CHARGEMENT
// ============================================

/**
 * Initialise tous les composants au chargement de la page
 */
document.addEventListener("DOMContentLoaded", function() {
    
    // ===== BANNIÈRE =====
    // Initialiser la bannière avec la date et l'heure
    initialiserBanniere();
    
    // ===== GALERIE =====
    // Afficher la première image
    afficherImageGalerie(0);
    
    // Démarrer la rotation automatique
    demarrerRotationAutomatique();
    
    // Ajouter les event listeners pour les boutons de contrôle
    document.getElementById("btnSuivant").addEventListener("click", prochainImage);
    document.getElementById("btnPrecedent").addEventListener("click", imagePrecedente);
    
    // Ajouter la possibilité de naviguer avec les flèches du clavier
    document.addEventListener("keydown", function(event) {
        if (event.key === "ArrowRight") {
            prochainImage();
        } else if (event.key === "ArrowLeft") {
            imagePrecedente();
        }
    });
    
    // Afficher un message dans la console
    console.log("✅ Contact.js chargé avec succès!");
    console.log("📸 Galerie avec " + imagesGalerie.length + " images");
    console.log("🎯 Utilisez les flèches pour naviguer dans la galerie");
});

// ============================================
// FIN DU FICHIER CONTACT.JS
// ============================================