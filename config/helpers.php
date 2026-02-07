<?php
declare(strict_types=1);

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    // si on reçoit "/pages/login.php"
    if (str_starts_with($path, '/')) {
        $path = BASE_URL . $path;
    }

    header('Location: ' . $path);
    exit;
}


function flash_set(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_get_all(): array {
    $msgs = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $msgs;
}

// ---- CSRF ----
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_verify(?string $token): bool {
    return isset($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

// ---- Dates ----
function dt(string $s): DateTimeImmutable {
    return new DateTimeImmutable($s);
}

function overlap(DateTimeImmutable $aStart, DateTimeImmutable $aEnd, DateTimeImmutable $bStart, DateTimeImmutable $bEnd): bool {
    return ($aStart < $bEnd) && ($bStart < $aEnd);
}
