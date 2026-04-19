<?php
// UniSmart Pay - Fonctions Utilitaires

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function csrf_token(): string {
    if (empty($_SESSION[CSRF_SESSION_KEY])) {
        $_SESSION[CSRF_SESSION_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_SESSION_KEY];
}

function require_csrf_token(string $token): void {
    $sessionToken = $_SESSION[CSRF_SESSION_KEY] ?? '';
    if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
        exit('CSRF token invalide');
    }
}

function is_hex_sha256(string $hash): bool {
    return (bool)preg_match('/^[a-f0-9]{64}$/i', $hash);
}

function verify_password(string $plainPassword, string $storedHash): bool {
    $storedHash = trim($storedHash);

    // If it's a PHP password_hash() (bcrypt/argon2)
    if (str_starts_with($storedHash, '$2y$') || str_starts_with($storedHash, '$argon2')) {
        return password_verify($plainPassword, $storedHash);
    }

    // If it's a SHA256 hex (as inserted by SHA2(...,256) in MySQL)
    if (is_hex_sha256($storedHash)) {
        $computed = hash('sha256', $plainPassword);
        return hash_equals(strtolower($storedHash), strtolower($computed));
    }

    return false;
}

function require_student_login(): void {
    if (empty($_SESSION['etudiant_id'])) {
        header('Location: ' . BASE_URL . '/etudiant/login.php');
        exit();
    }

    if (!empty($_SESSION['login_time']) && (time() - (int)$_SESSION['login_time'] > SESSION_DURATION)) {
        session_destroy();
        header('Location: ' . BASE_URL . '/etudiant/login.php?expired=1');
        exit();
    }

    $_SESSION['login_time'] = time();
}

function require_admin_login(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit();
    }

    if (!empty($_SESSION['login_time']) && (time() - (int)$_SESSION['login_time'] > SESSION_DURATION)) {
        session_destroy();
        header('Location: ' . BASE_URL . '/admin/login.php?expired=1');
        exit();
    }

    $_SESSION['login_time'] = time();
}

function log_security(?int $idUser, string $typeUser, string $action, string $details, string $statut = 'SUCCES'): void {
    try {
        $pdo = db();
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $pdo->prepare(
            'INSERT INTO logs_securite (id_user, type_user, action, details, ip_address, statut) VALUES (:id_user, :type_user, :action, :details, :ip, :statut)'
        );
        $stmt->execute([
            ':id_user' => $idUser,
            ':type_user' => $typeUser,
            ':action' => $action,
            ':details' => $details,
            ':ip' => $ip,
            ':statut' => $statut,
        ]);
    } catch (Throwable $e) {
        // Avoid breaking the app for logging failures.
        error_log('log_security error: ' . $e->getMessage());
    }
}

function generate_reference(): string {
    return date('YmdHis') . random_int(1000, 9999);
}

function mask_uid(string $uid): string {
    // Example: AA:BB:CC:DD -> AA:BB:**:**
    $parts = explode(':', $uid);
    if (count($parts) >= 4) {
        $parts[2] = '**';
        $parts[3] = '**';
        return implode(':', $parts);
    }
    if (strlen($uid) <= 4) {
        return str_repeat('*', strlen($uid));
    }
    return substr($uid, 0, 4) . str_repeat('*', max(0, strlen($uid) - 4));
}
