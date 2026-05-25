<?php
/**
 * FoodSaver - Aliment.php
 * Classe PHP représentant un aliment de la base de données
 * Correspond à l'objet Aliment utilisé dans app.js
 */

class Aliment {

    // ════════════════════════════════════════════════════
    //  ATTRIBUTS PRIVÉS
    //  Accessibles uniquement depuis l'intérieur de la classe
    //  (via getters/setters)
    // ════════════════════════════════════════════════════

    private int    $id;                      // Identifiant unique en base de données
    private string $nom;                     // Nom de l'aliment (ex: "Pomme")
    private string $type;                    // Catégorie (ex: "Fruit")
    private string $methodeConservation;     // Description de la conservation
    private int    $dureeConservationJours;  // Durée en jours (ex: 7)
    private string $photo;                   // Nom du fichier image
    private string $dateAjout;              // Date d'insertion en BDD

    // Liste des types autorisés → utilisée pour la validation
    const TYPES_VALIDES = [
        'Légume',
        'Fruit',
        'Produit laitier',
        'Céréale',
        'Plat préparé',
        'Produit frais'
    ];

    // ════════════════════════════════════════════════════
    //  CONSTRUCTEUR
    //  Appelé lors de : new Aliment("Pomme", "Fruit", ...)
    //  Les paramètres avec "=" ont des valeurs par défaut
    // ════════════════════════════════════════════════════
    public function __construct(
        string $nom,                          // Obligatoire
        string $type,                         // Obligatoire
        string $methodeConservation,          // Obligatoire
        int    $dureeConservationJours = 1,   // Optionnel → 1 jour par défaut
        string $photo     = 'default.jpg',    // Optionnel → image par défaut
        int    $id        = 0,                // Optionnel → 0 si nouvel aliment
        string $dateAjout = ''                // Optionnel → vide = date du jour
    ) {
        // Affecte chaque paramètre à son attribut correspondant
        $this->id                     = $id;
        $this->nom                    = $nom;
        $this->type                   = $type;
        $this->methodeConservation    = $methodeConservation;
        $this->dureeConservationJours = $dureeConservationJours;
        $this->photo                  = $photo;

        // Si dateAjout est vide → utilise la date et heure actuelles
        // Sinon → utilise la valeur fournie
        $this->dateAjout = $dateAjout ?: date('Y-m-d H:i:s');
    }

    // ════════════════════════════════════════════════════
    //  GETTERS
    //  Permettent de lire les attributs privés depuis
    //  l'extérieur de la classe (lecture seule)
    // ════════════════════════════════════════════════════

    public function getId():   int    { return $this->id; }
    public function getNom():  string { return $this->nom; }
    public function getType(): string { return $this->type; }
    public function getMethodeConservation():    string { return $this->methodeConservation; }
    public function getDureeConservationJours(): int    { return $this->dureeConservationJours; }
    public function getPhoto():     string { return $this->photo; }
    public function getDateAjout(): string { return $this->dateAjout; }

    // ════════════════════════════════════════════════════
    //  SETTERS AVEC VALIDATION
    //  Permettent de modifier les attributs privés
    //  avec vérification des données avant modification
    //  → Lance une exception si la valeur est invalide
    // ════════════════════════════════════════════════════

    public function setNom(string $nom): void {
        // trim() supprime les espaces au début/fin
        // strlen() compte les caractères
        if (strlen(trim($nom)) < 2)
            throw new InvalidArgumentException('Nom trop court.');
        $this->nom = trim($nom);
    }

    public function setType(string $type): void {
        // in_array() vérifie que le type est dans la liste autorisée
        if (!in_array($type, self::TYPES_VALIDES))
            throw new InvalidArgumentException("Type invalide.");
        $this->type = $type;
    }

    public function setMethodeConservation(string $m): void {
        // La méthode doit avoir au moins 5 caractères
        if (strlen(trim($m)) < 5)
            throw new InvalidArgumentException('Méthode trop courte.');
        $this->methodeConservation = trim($m);
    }

    public function setDureeConservationJours(int $d): void {
        // La durée doit être d'au moins 1 jour
        if ($d < 1)
            throw new InvalidArgumentException('Durée invalide.');
        $this->dureeConservationJours = $d;
    }

    public function setPhoto(string $p): void {
        // Si $p est vide → utilise l'image par défaut
        $this->photo = $p ?: 'default.jpg';
    }

    // ════════════════════════════════════════════════════
    //  COULEUR SELON LE TYPE
    //  Retourne une couleur hexadécimale selon la catégorie
    //  Utilisée pour colorier le badge du type dans le tableau
    // ════════════════════════════════════════════════════
    public function getCouleurType(): string {
        // match() = switch() moderne (strict, plus concis)
        return match($this->type) {
            'Légume'          => '#35ab47',  // Vert
            'Fruit'           => '#ff9800',  // Orange
            'Produit laitier' => '#2196f3',  // Bleu
            'Céréale'         => '#795548',  // Marron
            'Plat préparé'    => '#9c27b0',  // Violet
            'Produit frais'   => '#00bcd4',  // Cyan
            default           => '#607d8b'   // Gris (type inconnu)
        };
    }

    // ════════════════════════════════════════════════════
    //  GÉNÉRATION D'UNE LIGNE HTML DU TABLEAU
    //  Retourne le HTML complet d'une ligne <tr>
    //  avec les boutons Modifier et Supprimer
    // ════════════════════════════════════════════════════
    public function toTableRow(): string {

        // Récupère la couleur correspondant au type
        $couleur = $this->getCouleurType();
        $id      = $this->id;

        // Construit l'URL de la page de modification avec l'id en paramètre GET
        // Ex: modifier_aliment.php?id=5
        $urlModifier = "modifier_aliment.php?id={$id}";

        // Double sécurité contre les injections :
        // 1. htmlspecialchars() → convertit < > & " en entités HTML
        // 2. addslashes() → échappe les apostrophes pour le JS (confirm dialog)
        //    Ex: "Pomme d'api" → "Pomme d\'api" (sinon le JS planterait)
        $nomEchap = addslashes(htmlspecialchars($this->nom, ENT_QUOTES));

        // Génère et retourne le HTML de la ligne
        return "
        <tr>
            <!-- Colonne : Nom de l'aliment en gras -->
            <td><strong>" . htmlspecialchars($this->nom) . "</strong></td>

            <!-- Colonne : Badge coloré selon le type -->
            <td>
                <span class='type-badge' style='
                    background:{$couleur};       /* Couleur dynamique selon le type */
                    display:inline-block;
                    padding:.2rem .75rem;
                    border-radius:20px;          /* Badge arrondi */
                    color:#fff;
                    font-size:.78rem;
                    font-weight:600;'>
                    " . htmlspecialchars($this->type) . "
                </span>
            </td>

            <!-- Colonne : Description de la méthode de conservation -->
            <td>" . htmlspecialchars($this->methodeConservation) . "</td>

            <!-- Colonne : Durée en jours -->
            <td><strong>" . $this->dureeConservationJours . "</strong> jour(s)</td>

            <!-- Colonne : Boutons d'action -->
            <td style='white-space:nowrap;'> <!-- nowrap : boutons sur une seule ligne -->

                <!-- Bouton Modifier : lien vers modifier_aliment.php?id=X -->
                <a href='{$urlModifier}'
                   class='btn-table-edit'
                   style='background:#ff9800; color:#fff; padding:.3rem .9rem;
                          border-radius:20px; text-decoration:none;
                          font-size:.82rem; margin-right:.3rem; display:inline-block;'>
                    ✏️ Modifier
                </a>

                <!-- Formulaire de suppression (méthode POST pour sécurité) -->
                <form method='POST' action='supprimer_aliment.php'
                      style='display:inline; margin:0; padding:0;'
                      onsubmit=\"return confirm('Supprimer « {$nomEchap} » ?')\">
                <!-- onsubmit : demande confirmation JS avant d'envoyer le formulaire -->

                    <!-- Champ caché : envoie l'id de l'aliment à supprimer -->
                    <input type='hidden' name='supprimer' value='{$id}'>

                    <!-- Bouton de soumission du formulaire -->
                    <button type='submit' class='btn-table-del'
                        style='background:#e74c3c; color:#fff; padding:.3rem .9rem;
                               border-radius:20px; border:none;
                               font-size:.82rem; cursor:pointer; display:inline-block;'>
                        🗑️ Supprimer
                    </button>
                </form>
            </td>
        </tr>";
    }

    // ════════════════════════════════════════════════════
    //  INSTANCIATION DEPUIS UN TABLEAU BASE DE DONNÉES
    //  Méthode statique : appelée sans créer d'objet
    //  Ex: Aliment::fromArray($row)
    //  Convertit une ligne SQL en objet Aliment
    // ════════════════════════════════════════════════════
    public static function fromArray(array $row): self {
        return new self(
            $row['nom'],                              // Nom de l'aliment
            $row['type'],                             // Type/catégorie
            $row['methode_conservation'],             // Méthode de conservation
            (int)$row['duree_conservation_jours'],    // Cast en int (sécurité)
            $row['photo'] ?? 'default.jpg',           // ?? = si null → 'default.jpg'
            (int)$row['id'],                          // Cast en int (sécurité)
            $row['date_ajout'] ?? ''                  // ?? = si null → chaîne vide
        );
    }
}

// ════════════════════════════════════════════════════════
//  FONCTION GLOBALE D'AFFICHAGE DU TABLEAU
//  Fonction indépendante (hors classe)
//  Prend un tableau d'objets Aliment et génère le HTML complet
// ════════════════════════════════════════════════════════
function afficherTableauAliments(array $aliments): void {

    // Si le tableau est vide → affiche un message et arrête la fonction
    if (empty($aliments)) {
        echo '<p style="text-align:center; color:#888; padding:2rem;">
                  Aucun aliment trouvé.
              </p>';
        return; // Arrête l'exécution de la fonction ici
    }

    // Génère l'entête du tableau HTML
    echo '<div class="table-container">
          <table id="tableauAliments" border="1" width="100%" cellpadding="10">
            <thead>
                <tr>
                    <th>🥘 Aliment</th>
                    <th>📂 Type</th>
                    <th>🧊 Conservation</th>
                    <th>⏱️ Durée</th>
                    <th>⚙️ Actions</th>
                </tr>
            </thead>
            <tbody>';

    // Parcourt chaque objet Aliment du tableau
    foreach ($aliments as $a) {

        // Filtre : n'affiche que les aliments avec une durée valide (≥ 1 jour)
        if ($a->getDureeConservationJours() >= 1) {

            // Appelle toTableRow() pour générer la ligne <tr> de cet aliment
            echo $a->toTableRow();
        }
    }

    // Ferme le tableau HTML
    echo '</tbody></table></div>';
}
?>