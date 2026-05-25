<?php
require_once __DIR__ . '/connexion.php';
$pdo = getConnexion();

class ReponseQuestionnaire {

    private string $nom;
    private string $email;
    private string $satisfaction;
    private array  $fonctionnalites;
    private string $commentaires;
    private string $source;
    private string $dateReponse;

    const SATISFACTIONS_VALIDES   = ['très-satisfait','satisfait','neutre','insatisfait'];
    const SOURCES_VALIDES         = ['moteur-recherche','reseaux-sociaux','ami','publicite','autre'];
    const FONCTIONNALITES_VALIDES = ['recettes','conservation','astuces','communaute'];

    public function __construct(
        string $nom, string $email, string $satisfaction,
        array  $fonctionnalites, string $commentaires,
        string $source, string $dateReponse = ''
    ) {
        $this->nom             = $nom;
        $this->email           = $email;
        $this->satisfaction    = $satisfaction;
        $this->fonctionnalites = $fonctionnalites;
        $this->commentaires    = $commentaires;
        $this->source          = $source;
        $this->dateReponse     = $dateReponse ?: date('d/m/Y H:i');
    }

    public function getNom()            : string { return $this->nom; }
    public function getEmail()          : string { return $this->email; }
    public function getSatisfaction()   : string { return $this->satisfaction; }
    public function getFonctionnalites(): array  { return $this->fonctionnalites; }
    public function getCommentaires()   : string { return $this->commentaires; }
    public function getSource()         : string { return $this->source; }
    public function getDateReponse()    : string { return $this->dateReponse; }

    public function setNom(string $v): void {
        if (strlen(trim($v)) < 3) throw new InvalidArgumentException('Nom trop court.');
        $this->nom = trim($v);
    }
    public function setEmail(string $v): void {
        if (!filter_var($v, FILTER_VALIDATE_EMAIL))
            throw new InvalidArgumentException('Email invalide.');
        $this->email = $v;
    }
    public function setSatisfaction(string $v): void {
        if (!in_array($v, self::SATISFACTIONS_VALIDES))
            throw new InvalidArgumentException('Satisfaction invalide.');
        $this->satisfaction = $v;
    }
    public function setFonctionnalites(array $v): void {
        $v = array_filter($v, fn($x) => in_array($x, self::FONCTIONNALITES_VALIDES));
        if (empty($v)) throw new InvalidArgumentException('Sélectionnez au moins une fonctionnalité.');
        $this->fonctionnalites = array_values($v);
    }
    public function setSource(string $v): void {
        if (!in_array($v, self::SOURCES_VALIDES))
            throw new InvalidArgumentException('Source invalide.');
        $this->source = $v;
    }

    public function getEmojiSatisfaction(): string {
        return match($this->satisfaction) {
            'très-satisfait' => '😄', 'satisfait'  => '🙂',
            'neutre'         => '😐', 'insatisfait' => '😞',
            default => '❓'
        };
    }

    public function getCouleurSatisfaction(): string {
        return match($this->satisfaction) {
            'très-satisfait' => '#35ab47', 'satisfait'   => '#3498db',
            'neutre'         => '#ff9800', 'insatisfait' => '#e74c3c',
            default => '#607d8b'
        };
    }

    public function getFonctionnalitesLabel(): string {
        $labels = [
            'recettes'     => '🥘 Recettes',
            'conservation' => '🧊 Conservation',
            'astuces'      => '💡 Astuces',
            'communaute'   => '👥 Communauté',
        ];
        return implode(', ', array_map(fn($f) => $labels[$f] ?? $f, $this->fonctionnalites));
    }
}

function afficherTableauReponses(array $reponses): void {
    if (empty($reponses)) {
        echo '<p style="text-align:center;color:#888;padding:2rem;">Aucune réponse.</p>';
        return;
    }
    echo '<div style="overflow-x:auto;">';
    echo '<table class="result-table">';
    echo '<thead><tr>
            <th>👤 Nom</th>
            <th>⭐ Satisfaction</th>
            <th>✨ Fonctionnalités</th>
            <th>🔍 Source</th>
            <th>📅 Date</th>
          </tr></thead><tbody>';

    foreach ($reponses as $r) {
        $bg = $r->getSatisfaction() === 'très-satisfait' ? '#f0fff4' : '#fff';
        $c  = $r->getCouleurSatisfaction();
        echo "<tr style='background:{$bg};border-bottom:1px solid #dde5da;'>";
        echo "<td><strong>" . htmlspecialchars($r->getNom()) . "</strong><br>
                  <span style='font-size:.78rem;color:#999'>" . htmlspecialchars($r->getEmail()) . "</span></td>";
        echo "<td><span style='background:{$c};color:#fff;padding:.2rem .7rem;
                  border-radius:20px;font-size:.8rem;font-weight:700;'>"
              . $r->getEmojiSatisfaction() . " " . htmlspecialchars($r->getSatisfaction()) . "</span></td>";
        echo "<td style='font-size:.85rem;'>" . htmlspecialchars($r->getFonctionnalitesLabel()) . "</td>";
        echo "<td style='font-size:.85rem;color:#666;'>" . htmlspecialchars($r->getSource()) . "</td>";
        echo "<td style='font-size:.8rem;color:#aaa;'>" . htmlspecialchars($r->getDateReponse()) . "</td>";
        echo "</tr>";
    }
    echo '</tbody></table></div>';
    echo '<p style="color:#888;font-size:.82rem;margin-top:.5rem;">' . count($reponses) . ' réponse(s) affichée(s).</p>';
}

$nom             = trim($_POST['nom']          ?? '');
$email           = trim($_POST['email']        ?? '');
$satisfaction    = trim($_POST['satisfaction'] ?? '');
$fonctionnalites = array_values(array_filter(
    $_POST['fonctionnalites'] ?? [],
    fn($v) => in_array($v, ReponseQuestionnaire::FONCTIONNALITES_VALIDES)
));
$commentaires    = trim($_POST['commentaires'] ?? '');
$source          = trim($_POST['source']       ?? '');

$erreurs = [];

if (empty($nom) || strlen($nom) < 3)
    $erreurs['nom'] = 'Le nom doit contenir au moins 3 caractères.';
elseif (!preg_match('/^[\p{L}\s\'\-]+$/u', $nom))
    $erreurs['nom'] = 'Le nom ne doit contenir que des lettres.';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
    $erreurs['email'] = 'Adresse e-mail invalide.';

if (!in_array($satisfaction, ReponseQuestionnaire::SATISFACTIONS_VALIDES))
    $erreurs['satisfaction'] = 'Veuillez sélectionner votre niveau de satisfaction.';

if (empty($fonctionnalites))
    $erreurs['fonctionnalites'] = 'Sélectionnez au moins une fonctionnalité.';

if (!in_array($source, ReponseQuestionnaire::SOURCES_VALIDES))
    $erreurs['source'] = 'Veuillez indiquer comment vous avez découvert FoodSaver.';

if (empty($erreurs)) {
    $stmt = $pdo->prepare("
        INSERT INTO questionnaires (nom, email, satisfaction, fonctionnalites, commentaires, source)
        VALUES (:nom, :email, :satisfaction, :fonctionnalites, :commentaires, :source)
    ");
    $stmt->execute([
        ':nom'             => $nom,
        ':email'           => $email,
        ':satisfaction'    => $satisfaction,
        ':fonctionnalites' => implode(',', array_values($fonctionnalites)),
        ':commentaires'    => $commentaires,
        ':source'          => $source,
    ]);
    $succes = true;
} else {
    $succes = false;
}

$reponsesExistantes = [];
try {
    $rows = $pdo->query("SELECT * FROM questionnaires ORDER BY date_reponse DESC")->fetchAll();
    foreach ($rows as $row) {
        $reponsesExistantes[] = new ReponseQuestionnaire(
            $row['nom'],
            $row['email'],
            $row['satisfaction'],
            explode(',', $row['fonctionnalites']),
            $row['commentaires'],
            $row['source'],
            (new DateTime($row['date_reponse']))->format('d/m/Y H:i')
        );
    }
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FoodSaver – Questionnaire</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/logo.jpg">
    <link rel="stylesheet" href="../css/extern.css">
    <link rel="stylesheet" href="../css/questionnaire.css">
    <style>
        .alert { padding:1rem 1.4rem; border-radius:12px; margin-bottom:1.2rem; font-weight:600; font-size:.95rem; }
        .alert-success { background:#dcfce7; color:#15803d; border-left:5px solid #22c55e; }
        .alert-danger  { background:#fee2e2; color:#b91c1c; border-left:5px solid #dc2626; }
        .alert-danger li { font-weight:400; margin:.3rem 0 .3rem 1.2rem; }
        .error-message {
            display:block; color:#dc2626; font-size:.83rem; margin-top:.25rem;
            padding:.3rem .7rem; background:#fef2f2;
            border-left:3px solid #dc2626; border-radius:0 6px 6px 0;
        }
        .result-table { width:100%; border-collapse:collapse; }
        .result-table th,
        .result-table td { padding:.75rem 1rem; text-align:left; border-bottom:1px solid #f0f0f0; font-size:.92rem; vertical-align:top; }
        .result-table th { background:#2d6a4f; color:#fff; font-weight:600; }
        .result-table tr:last-child td { border-bottom:none; }
        .result-table tr:nth-child(even) td { background:#f9fdf9; }
        .result-section {
            background:#fff; border-radius:20px; padding:2rem; margin-top:2rem;
            box-shadow:0 4px 20px rgba(0,0,0,.08); border:2px solid #e8f5e9;
        }
        .result-section h2 { font-size:1.3rem; color:#1b4332; margin-bottom:1.2rem; padding-bottom:.6rem; border-bottom:2px solid #e8f5e9; }
        .btn-retour {
            display:inline-block; padding:.75rem 1.8rem; border-radius:50px;
            font-weight:700; text-decoration:none; transition:all .25s;
            margin:.4rem .4rem 0 0; font-size:.93rem;
        }
        .btn-retour.primary { background:#2d6a4f; color:#fff; box-shadow:0 4px 12px rgba(45,106,79,.3); }
        .btn-retour.primary:hover { background:#1b4332; transform:translateY(-2px); }
        .btn-retour.outline { background:#fff; color:#2d6a4f; border:2px solid #2d6a4f; }
        .btn-retour.outline:hover { background:#f0faf4; transform:translateY(-2px); }
        .badge-fonc {
            display:inline-block; background:#d8f3dc; color:#1b4332;
            padding:.2rem .7rem; border-radius:20px; font-size:.8rem; font-weight:700; margin:.1rem;
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
                <li><a href="../guide.php"><span class="nav-icon">📖</span>Tutoriel culinaire</a></li>
                <li><a href="../recettes.php"><span class="nav-icon">🥘</span>Cuisine savoureuse</a></li>
                <div class="sidebar-sep"></div>
                <li><a href="../contact.html"><span class="nav-icon">✉️</span>Contactez-nous</a></li>
                <li><a href="../questionnaire.html" class="active"><span class="nav-icon">📋</span>Questionnaire</a></li>
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
        <section class="questionnaire-hero">
            <h1>Questionnaire de Satisfaction</h1>
            <p>Voici le résultat du traitement de votre questionnaire.</p>
        </section>

        <?php if (!$succes) : ?>
        <div class="alert alert-danger">
            ⚠️ Veuillez corriger les erreurs suivantes :
            <ul>
                <?php foreach ($erreurs as $champ => $msg) : ?>
                    <li><strong><?= htmlspecialchars(ucfirst($champ)) ?> :</strong>
                        <?= htmlspecialchars($msg) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="result-section">
            <h2>📋 Données saisies</h2>
            <table class="result-table">
                <thead>
                    <tr><th>Champ</th><th>Valeur saisie</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    <?php
                    $champsVerif = [
                        'nom'             => ['label' => '👤 Nom',             'valeur' => $nom],
                        'email'           => ['label' => '📧 Email',           'valeur' => $email],
                        'satisfaction'    => ['label' => '⭐ Satisfaction',    'valeur' => $satisfaction],
                        'fonctionnalites' => ['label' => '✨ Fonctionnalités', 'valeur' => implode(', ', $fonctionnalites)],
                        'source'          => ['label' => '🔍 Source',          'valeur' => $source],
                    ];
                    foreach ($champsVerif as $cle => $info) :
                        $aErreur = isset($erreurs[$cle]);
                        $icone   = $aErreur ? '❌' : '✅';
                        $couleur = $aErreur ? '#b91c1c' : '#15803d';
                        $valAff  = !empty($info['valeur'])
                                   ? htmlspecialchars($info['valeur'])
                                   : '<em style="color:#999;">vide</em>';
                    ?>
                    <tr>
                        <td><strong><?= $info['label'] ?></strong></td>
                        <td><?= $valAff ?></td>
                        <td style="color:<?= $couleur ?>;font-weight:700;">
                            <?= $icone ?>
                            <?= $aErreur ? htmlspecialchars($erreurs[$cle]) : 'Valide' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td><strong>💬 Commentaires</strong></td>
                        <td><?= !empty($commentaires) ? htmlspecialchars($commentaires) : '<em style="color:#999;">vide</em>' ?></td>
                        <td style="color:#15803d;font-weight:700;">✅ Optionnel</td>
                    </tr>
                </tbody>
            </table>
            <div style="margin-top:1.5rem;">
                <a href="../questionnaire.html" class="btn-retour primary">✏️ Corriger le formulaire</a>
                <a href="../index.html"         class="btn-retour outline">🏠 Accueil</a>
            </div>
        </div>

        <?php else : ?>
        <div class="alert alert-success">
            ✅ Merci <strong><?= htmlspecialchars($nom) ?></strong> !
            Votre questionnaire a bien été soumis.
        </div>

        <div class="result-section">
            <h2>📋 Récapitulatif de vos réponses</h2>
            <table class="result-table">
                <thead><tr><th>Question</th><th>Réponse</th></tr></thead>
                <tbody>
                    <tr><td>👤 Nom complet</td><td><?= htmlspecialchars($nom) ?></td></tr>
                    <tr><td>📧 Email</td><td><?= htmlspecialchars($email) ?></td></tr>
                    <tr>
                        <td>⭐ Satisfaction</td>
                        <td>
                            <?php
                            $obj = new ReponseQuestionnaire($nom, $email, $satisfaction, array_values($fonctionnalites), $commentaires, $source);
                            $c   = $obj->getCouleurSatisfaction();
                            ?>
                            <span style="background:<?= $c ?>;color:#fff;padding:.3rem .9rem;border-radius:20px;font-weight:700;font-size:.9rem;">
                                <?= $obj->getEmojiSatisfaction() ?>
                                <?= htmlspecialchars(ucfirst(str_replace('-', ' ', $satisfaction))) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>✨ Fonctionnalités</td>
                        <td>
                            <?php foreach ($fonctionnalites as $f) : ?>
                                <span class="badge-fonc"><?= htmlspecialchars($f) ?></span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr><td>🔍 Source</td><td><?= htmlspecialchars(str_replace('-', ' ', $source)) ?></td></tr>
                    <?php if (!empty($commentaires)) : ?>
                    <tr><td>💬 Commentaires</td><td><?= nl2br(htmlspecialchars($commentaires)) ?></td></tr>
                    <?php endif; ?>
                    <tr><td>📅 Date</td><td><?= date('d/m/Y à H:i:s') ?></td></tr>
                </tbody>
            </table>
        </div>

        <div class="result-section">
            <h2>📊 Toutes les réponses (<?= count($reponsesExistantes) ?>)</h2>
            <?php afficherTableauReponses($reponsesExistantes); ?>
        </div>

        <?php
        $aliments = [];
        try {
            if (in_array('conservation', $fonctionnalites)) {
                $stmt = $pdo->prepare("SELECT * FROM aliments ORDER BY nom LIMIT 6");
                $stmt->execute([]);
            } else {
                $stmt = $pdo->query("SELECT * FROM aliments ORDER BY nom LIMIT 6");
            }
            $aliments = $stmt->fetchAll();
        } catch (PDOException $e) {}

        if (!empty($aliments)) : ?>
        <div class="result-section">
            <h2>🧊 Aliments recommandés</h2>
            <div style="overflow-x:auto;">
                <table class="result-table">
                    <thead>
                        <tr><th>🥘 Aliment</th><th>📂 Type</th><th>🧊 Conservation</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aliments as $row) : ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['nom']) ?></strong></td>
                            <td><?= htmlspecialchars($row['type']) ?></td>
                            <td><?= htmlspecialchars($row['methode_conservation']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top:1.5rem;">
            <a href="../questionnaire.html" class="btn-retour outline">📋 Répondre à nouveau</a>
            <a href="../index.html"         class="btn-retour primary">🏠 Retour à l'accueil</a>
        </div>

        <?php endif; ?>
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
</body>
</html>