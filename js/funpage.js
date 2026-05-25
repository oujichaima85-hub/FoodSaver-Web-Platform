/**
 * ============================================================
 * FoodSaver - funpage.js
 * Jeu : "Sauve la Bouffe! 🥕"
 * Quiz interactif de conservation des aliments
 * ============================================================
 */

// ============================================================
// 1. BASE DE DONNÉES DES ALIMENTS (tableau d'objets)
// ============================================================

const alimentsJeu = [
    { id: 1,  emoji: "🍗", nom: "Poulet cuit",          conservation: "frigo",       duree: "2-3 jours",    astuce: "Toujours dans une boîte hermétique au fond du frigo." },
    { id: 2,  emoji: "🍅", nom: "Tomates",              conservation: "ambiant",     duree: "4-7 jours",    astuce: "Le froid dénature leur goût et leur texture !" },
    { id: 3,  emoji: "🧀", nom: "Fromage",              conservation: "frigo",       duree: "7-14 jours",   astuce: "Enveloppez dans du papier alimentaire, pas du plastique." },
    { id: 4,  emoji: "🍌", nom: "Bananes",              conservation: "ambiant",     duree: "3-5 jours",    astuce: "Le frigo les fait noircir rapidement." },
    { id: 5,  emoji: "🥩", nom: "Viande crue",          conservation: "congelateur", duree: "3-6 mois",     astuce: "Congeler immédiatement si pas utilisée dans 2 jours." },
    { id: 6,  emoji: "🍞", nom: "Pain",                 conservation: "placard",     duree: "3-5 jours",    astuce: "Le frigo le rend rassis plus vite. Congeler si longue durée." },
    { id: 7,  emoji: "🥛", nom: "Lait",                 conservation: "frigo",       duree: "5-7 jours",    astuce: "Toujours au fond du frigo, jamais dans la porte." },
    { id: 8,  emoji: "🥕", nom: "Carottes",             conservation: "frigo",       duree: "2-3 semaines", astuce: "Dans un sac perforé pour garder l'humidité." },
    { id: 9,  emoji: "🧄", nom: "Ail",                  conservation: "placard",     duree: "2-3 mois",     astuce: "Endroit frais, sec et bien ventilé." },
    { id: 10, emoji: "🍓", nom: "Fraises",              conservation: "frigo",       duree: "2-3 jours",    astuce: "Ne pas laver avant de les mettre au frigo." },
    { id: 11, emoji: "🥚", nom: "Œufs",                 conservation: "frigo",       duree: "3-4 semaines", astuce: "Toujours dans la boîte d'origine, pointe vers le bas." },
    { id: 12, emoji: "🍋", nom: "Citrons",              conservation: "ambiant",     duree: "1-2 semaines", astuce: "Coupés ? Alors au frigo dans un film alimentaire." },
    { id: 13, emoji: "🫙", nom: "Confiture ouverte",    conservation: "frigo",       duree: "1-3 mois",     astuce: "Bien refermer le couvercle après chaque usage." },
    { id: 14, emoji: "🥜", nom: "Noix",                 conservation: "placard",     duree: "3-6 mois",     astuce: "À l'abri de la lumière et de l'humidité." },
    { id: 15, emoji: "🫐", nom: "Myrtilles surgelées",  conservation: "congelateur", duree: "10-12 mois",   astuce: "Ne jamais recongeler après décongélation." },
    { id: 16, emoji: "🧅", nom: "Oignons",              conservation: "placard",     duree: "2-3 mois",     astuce: "Séparés des pommes de terre pour éviter la germination." },
    { id: 17, emoji: "🍦", nom: "Glace",                conservation: "congelateur", duree: "2-3 mois",     astuce: "Film plastique au contact pour éviter les cristaux de glace." },
    { id: 18, emoji: "🥑", nom: "Avocat mûr",           conservation: "frigo",       duree: "3-4 jours",    astuce: "Non mûr : laisser à température ambiante d'abord." },
];

// Zones de conservation disponibles
const zones = [
    { id: "frigo",       emoji: "❄️",  label: "Réfrigérateur",  couleur: "#2563eb" },
    { id: "congelateur", emoji: "🧊",  label: "Congélateur",    couleur: "#4f46e5" },
    { id: "placard",     emoji: "🗄️", label: "Placard",        couleur: "#92400e" },
    { id: "ambiant",     emoji: "🌡️", label: "Temp. ambiante", couleur: "#b45309" },
];

// ============================================================
// 2. CLASSE GestionnaireJeu (ES6)
// ============================================================

class GestionnaireJeu {
    constructor() {
        this.score         = 0;
        this.vies          = 3;
        this.questionIndex = 0;
        this.questions     = [];
        this.timer         = null;
        this.tempsTotal    = 15;
        this.tempsRestant  = this.tempsTotal;
        this.streak        = 0;
        this.meilleurScore = parseInt(localStorage.getItem('fs_best') || '0');
    }

    // Fisher-Yates shuffle
    melangerAliments() {
        const copie = [...alimentsJeu];
        for (let i = copie.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [copie[i], copie[j]] = [copie[j], copie[i]];
        }
        this.questions = copie;
    }

    questionActuelle() { return this.questions[this.questionIndex] || null; }

    verifierReponse(zoneChoisie) {
        return this.questionActuelle()?.conservation === zoneChoisie;
    }

    calculerPoints() {
        const bonus       = Math.ceil(this.tempsRestant / this.tempsTotal * 50);
        const streakBonus = this.streak >= 3 ? 20 : 0;
        return 50 + bonus + streakBonus;
    }

    mettreAJourBestScore() {
        if (this.score > this.meilleurScore) {
            this.meilleurScore = this.score;
            localStorage.setItem('fs_best', this.score);
        }
    }

    reset() {
        this.score         = 0;
        this.vies          = 3;
        this.questionIndex = 0;
        this.streak        = 0;
        this.tempsRestant  = this.tempsTotal;
        clearInterval(this.timer);
    }
}

// ============================================================
// 3. INSTANCE & DOM
// ============================================================

const jeu = new GestionnaireJeu();

const ecranAccueil   = document.getElementById('ecran-accueil');
const ecranJeu       = document.getElementById('ecran-jeu');
const ecranFin       = document.getElementById('ecran-fin');
const btnCommencer   = document.getElementById('btn-commencer');
const btnRejouer     = document.getElementById('btn-rejouer');
const btnMenu        = document.getElementById('btn-menu');
const emojiAliment   = document.getElementById('emoji-aliment');
const nomAliment     = document.getElementById('nom-aliment');
const zonesContainer = document.getElementById('zones-container');
const feedbackBox    = document.getElementById('feedback-box');
const feedbackTexte  = document.getElementById('feedback-texte');
const feedbackAstuce = document.getElementById('feedback-astuce');
const scoreAffiche   = document.getElementById('score-affiche');
const viesAffiche    = document.getElementById('vies-affiche');
const progressBar    = document.getElementById('progress-bar');
const questionNum    = document.getElementById('question-num');
const timerBar       = document.getElementById('timer-bar');
const timerNum       = document.getElementById('timer-num');
const streakAffiche  = document.getElementById('streak-affiche');
const scoreFinale    = document.getElementById('score-finale');
const bestScoreEl    = document.getElementById('best-score');
const totalQEl       = document.getElementById('total-questions');
const messageFinale  = document.getElementById('message-finale');

// ============================================================
// 4. UTILITAIRES UI
// ============================================================

function afficherEcran(ecran) {
    [ecranAccueil, ecranJeu, ecranFin].forEach(e => e.classList.remove('actif'));
    ecran.classList.add('actif');
}

function mettreAJourVies() {
    viesAffiche.innerHTML = '';
    for (let i = 0; i < 3; i++) {
        const s = document.createElement('span');
        s.className = 'coeur';
        s.textContent = i < jeu.vies ? '❤️' : '🖤';
        viesAffiche.appendChild(s);
    }
}

function mettreAJourStats() {
    scoreAffiche.textContent = jeu.score;
    questionNum.textContent  = `${jeu.questionIndex + 1} / ${jeu.questions.length}`;
    progressBar.style.width  = (jeu.questionIndex / jeu.questions.length * 100) + '%';
    streakAffiche.textContent = jeu.streak >= 2 ? `🔥 ×${jeu.streak}` : '';
    mettreAJourVies();
}

// ============================================================
// 5. TIMER
// ============================================================

function demarrerTimer() {
    jeu.tempsRestant = jeu.tempsTotal;
    timerNum.textContent = jeu.tempsRestant;
    timerBar.style.width = '100%';
    timerBar.style.background = '#22c55e';
    clearInterval(jeu.timer);

    jeu.timer = setInterval(() => {
        jeu.tempsRestant--;
        timerNum.textContent = jeu.tempsRestant;
        const pct = jeu.tempsRestant / jeu.tempsTotal * 100;
        timerBar.style.width = pct + '%';
        if (pct > 50)      timerBar.style.background = '#22c55e';
        else if (pct > 25) timerBar.style.background = '#f59e0b';
        else               timerBar.style.background = '#ef4444';

        if (jeu.tempsRestant <= 0) {
            clearInterval(jeu.timer);
            tempsEcoule();
        }
    }, 1000);
}

function tempsEcoule() {
    jeu.vies--;
    jeu.streak = 0;
    if (jeu.vies <= 0) { terminerJeu(); return; }
    afficherFeedback(false, "⏰ Temps écoulé !", jeu.questionActuelle().astuce);
    setTimeout(passerQuestion, 2200);
}

// ============================================================
// 6. LOGIQUE DE JEU
// ============================================================

function afficherQuestion() {
    const q = jeu.questionActuelle();
    if (!q) { terminerJeu(); return; }

    feedbackBox.classList.remove('visible');
    zonesContainer.classList.remove('bloque');
    mettreAJourStats();

    emojiAliment.textContent = q.emoji;
    nomAliment.textContent   = q.nom;
    // Relancer l'animation pop
    emojiAliment.style.animation = 'none';
    void emojiAliment.offsetWidth;
    emojiAliment.style.animation = '';

    demarrerTimer();
}

function afficherFeedback(correct, titre, astuce) {
    feedbackBox.className   = 'feedback-box visible ' + (correct ? 'correct' : 'erreur');
    feedbackTexte.textContent = titre;
    feedbackAstuce.textContent = '💡 ' + astuce;
}

function passerQuestion() {
    jeu.questionIndex++;
    zonesContainer.querySelectorAll('.btn-zone').forEach(b =>
        b.classList.remove('bonne-reponse', 'mauvaise-reponse', 'zone-correcte')
    );
    if (jeu.questionIndex >= jeu.questions.length) terminerJeu();
    else afficherQuestion();
}

function terminerJeu() {
    clearInterval(jeu.timer);
    jeu.mettreAJourBestScore();
    scoreFinale.textContent  = jeu.score;
    bestScoreEl.textContent  = jeu.meilleurScore;
    totalQEl.textContent     = `${jeu.questionIndex} réponses données sur ${jeu.questions.length}`;
    if (jeu.score >= 800)      messageFinale.textContent = "🏆 Expert Anti-Gaspillage ! Vous maîtrisez la conservation !";
    else if (jeu.score >= 500) messageFinale.textContent = "🌿 Très bien ! Quelques astuces à perfectionner encore.";
    else if (jeu.score >= 200) messageFinale.textContent = "🥕 Bon début ! Consultez le Tutoriel Culinaire pour progresser.";
    else                       messageFinale.textContent = "📖 Entraînez-vous encore — lisez notre Guide Anti-Gaspillage !";
    afficherEcran(ecranFin);
}

// ============================================================
// 7. CRÉATION DES BOUTONS ZONES + EVENT BUBBLING
// ============================================================

function creerBoutonsZones() {
    zonesContainer.innerHTML = '';
    zones.forEach(zone => {
        const btn = document.createElement('button');
        btn.className    = 'btn-zone';
        btn.dataset.zone = zone.id;
        btn.style.setProperty('--zone-color', zone.couleur);
        btn.innerHTML = `<span class="zone-emoji">${zone.emoji}</span>
                         <span class="zone-label">${zone.label}</span>`;

        // Niveau 3 — le bouton émet le click
        btn.addEventListener('click', function(e) {
            if (zonesContainer.classList.contains('bloque')) return;
            const correct = jeu.verifierReponse(this.dataset.zone);
            clearInterval(jeu.timer);
            zonesContainer.classList.add('bloque');

            this.classList.add(correct ? 'bonne-reponse' : 'mauvaise-reponse');
            if (!correct) {
                zonesContainer
                    .querySelector(`[data-zone="${jeu.questionActuelle().conservation}"]`)
                    ?.classList.add('zone-correcte');
            }

            if (correct) {
                jeu.streak++;
                const pts = jeu.calculerPoints();
                jeu.score += pts;
                const smsg = jeu.streak >= 3 ? ` 🔥 Combo ×${jeu.streak} ! +${pts} pts` : ` +${pts} pts`;
                afficherFeedback(true, "✅ Bravo ! " + zones.find(z => z.id === this.dataset.zone).label + smsg, jeu.questionActuelle().astuce);
            } else {
                jeu.streak = 0;
                jeu.vies--;
                mettreAJourVies();
                if (jeu.vies <= 0) {
                    afficherFeedback(false, "❌ Plus de vies ! La bonne réponse : " + zones.find(z => z.id === jeu.questionActuelle().conservation).label, jeu.questionActuelle().astuce);
                    setTimeout(terminerJeu, 2200);
                    return;
                }
                afficherFeedback(false, `❌ Faux ! C'était : ${zones.find(z => z.id === jeu.questionActuelle().conservation).label}`, jeu.questionActuelle().astuce);
            }
            setTimeout(passerQuestion, 2200);
        });

        zonesContainer.appendChild(btn);
    });

    // Niveau 2 — le container reçoit l'événement par BUBBLING
    zonesContainer.addEventListener('click', function(e) {
        if (!e.target.closest('.btn-zone')) return;
        // Flash visuel pour démontrer le bubbling
        this.classList.add('bubble-flash');
        setTimeout(() => this.classList.remove('bubble-flash'), 400);
    });
}

// ============================================================
// 8. CONTRÔLES PRINCIPAUX
// ============================================================

btnCommencer.addEventListener('click', () => {
    jeu.reset();
    jeu.melangerAliments();
    creerBoutonsZones();
    afficherEcran(ecranJeu);
    afficherQuestion();
});

btnRejouer.addEventListener('click', () => {
    jeu.reset();
    jeu.melangerAliments();
    creerBoutonsZones();
    afficherEcran(ecranJeu);
    afficherQuestion();
});

btnMenu.addEventListener('click', () => {
    clearInterval(jeu.timer);
    afficherEcran(ecranAccueil);
});

// Initialisation — meilleur score sur l'accueil
const msa = document.getElementById('meilleur-score-accueil');
if (msa) msa.textContent = jeu.meilleurScore > 0 ? `🏆 Record : ${jeu.meilleurScore} pts` : '';
