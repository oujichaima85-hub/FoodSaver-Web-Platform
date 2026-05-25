<?php
/**
 * ============================================
 * FoodSaver - connexion.php
 * Connexion à la base de données via PDO
 * ============================================
 */
define('DB_HOST',    'localhost');
define('DB_NAME',    'foodsaver_db');
define('DB_USER',    'root');       // Modifier selon votre config
define('DB_PASS',    '');           // Modifier selon votre config
define('DB_CHARSET', 'utf8mb4');

/**
 * Crée et retourne une connexion PDO.
 * @return PDO
 */
function getConnexion(): PDO {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        die('
        <div style="font-family:sans-serif;color:#721c24;background:#f8d7da;
                    border:1px solid #f5c6cb;padding:1.5rem;border-radius:12px;margin:2rem auto;max-width:600px;">
            <h2>❌ Erreur de connexion à la base de données</h2>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
            <p>Vérifiez vos paramètres dans <code>connexion.php</code>.</p>
        </div>');
    }
}
?>
