<?php
declare(strict_types=1);

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return current_user() !== null;
}

function require_login(): void {
    if (!is_logged_in()) {
        flash_set('error', 'Veuillez vous connecter.');
        redirect('/pages/login.php');
    }
}

function require_role(string $role): void {
    require_login();
    $u = current_user();
    if (($u['role'] ?? null) !== $role) {
        flash_set('error', 'Accès refusé.');
        redirect('/index.php');
    }
}

function auth_login(PDO $pdo, string $email, string $password): bool {
    $stmt = $pdo->prepare("
        SELECT id_user, email, firstname, lastname, role, password_hash
        FROM `user_account`
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute([':email' => $email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u) return false;
    if (!password_verify($password, $u['password_hash'])) return false;

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id_user'   => (int)$u['id_user'],
        'email'     => (string)$u['email'],
        'firstname' => (string)$u['firstname'],
        'lastname'  => (string)$u['lastname'],
        'role'      => (string)$u['role'], // CLIENT / ADMIN
    ];

    return true;
}

function auth_logout(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"], $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
