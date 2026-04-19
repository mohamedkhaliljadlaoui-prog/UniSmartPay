<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$etudiantId = $_SESSION['etudiant_id'] ?? null;

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();

if ($etudiantId) {
    log_security((int)$etudiantId, 'etudiant', 'LOGOUT', 'Déconnexion étudiant', 'SUCCES');
}

header('Location: ' . BASE_URL . '/etudiant/login.php');
exit();
