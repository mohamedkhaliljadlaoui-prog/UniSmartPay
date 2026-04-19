<?php
// UniSmart Pay - Connexion PDO MySQL

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $port = (int)(defined('DB_PORT') ? DB_PORT : 3306);
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', DB_HOST, $port, DB_NAME, DB_CHARSET);

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        $message = 'Connexion MySQL impossible. Vérifiez que MySQL (XAMPP) est démarré et que DB_HOST/DB_PORT/DB_USER/DB_PASS sont corrects.';
        if (defined('APP_ENV') && APP_ENV === 'dev') {
            $message .= ' Détail: ' . $e->getMessage();
        }
        throw new RuntimeException($message, 0, $e);
    }

    return $pdo;
}
