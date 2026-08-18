<?php

declare(strict_types=1);

function user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_authenticated(): bool
{
    return user() !== null;
}

function is_admin(): bool
{
    return (user()['role'] ?? null) === 'administrador';
}

function is_client(): bool
{
    return (user()['role'] ?? null) === 'cliente';
}

function login_user(array $account, bool $remember = false): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$account['id'],
        'role_id' => (int)$account['role_id'],
        'role' => $account['role_name'],
        'nombre' => $account['nombre'],
        'email' => $account['email'],
    ];
    $_SESSION['last_activity'] = time();

    db()->prepare('UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?')->execute([$account['id']]);

    if ($remember) {
        issue_remember_token((int)$account['id']);
    }
}

function logout_user(bool $deleteAllRememberTokens = false): void
{
    if ($deleteAllRememberTokens && is_authenticated()) {
        db()->prepare('DELETE FROM remember_tokens WHERE user_id = ?')->execute([user()['id']]);
    }

    if (!empty($_COOKIE['rootcode_remember'])) {
        [$selector] = explode(':', $_COOKIE['rootcode_remember'], 2) + [''];
        if ($selector !== '') {
            db()->prepare('DELETE FROM remember_tokens WHERE selector = ?')->execute([$selector]);
        }
        setcookie('rootcode_remember', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function issue_remember_token(int $userId): void
{
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $hash = hash('sha256', $validator);
    $expires = (new DateTimeImmutable('+' . REMEMBER_DAYS . ' days'))->format('Y-m-d H:i:s');

    db()->prepare('INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)')
        ->execute([$userId, $selector, $hash, $expires]);

    setcookie('rootcode_remember', $selector . ':' . $validator, [
        'expires' => strtotime($expires),
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function attempt_remember_login(): void
{
    if (is_authenticated() || empty($_COOKIE['rootcode_remember'])) {
        return;
    }

    [$selector, $validator] = explode(':', $_COOKIE['rootcode_remember'], 2) + ['', ''];
    if ($selector === '' || $validator === '') {
        return;
    }

    $stmt = db()->prepare('SELECT rt.*, u.*, r.nombre AS role_name FROM remember_tokens rt JOIN usuarios u ON u.id = rt.user_id JOIN roles r ON r.id = u.role_id WHERE rt.selector = ? AND rt.expires_at > NOW() AND u.estado = "activo" LIMIT 1');
    $stmt->execute([$selector]);
    $record = $stmt->fetch();

    if (!$record || !hash_equals($record['token_hash'], hash('sha256', $validator))) {
        db()->prepare('DELETE FROM remember_tokens WHERE selector = ?')->execute([$selector]);
        return;
    }

    db()->prepare('DELETE FROM remember_tokens WHERE selector = ?')->execute([$selector]);
    login_user($record, true);
}

function enforce_session_timeout(): void
{
    if (!is_authenticated()) {
        return;
    }

    $last = (int)($_SESSION['last_activity'] ?? time());
    if (time() - $last > SESSION_TIMEOUT) {
        log_security_event('session_expired', 'La sesión expiró por inactividad.');
        logout_user();
        session_start();
        flash('warning', 'Tu sesión expiró por inactividad.');
        redirect('auth/login.php');
    }
    $_SESSION['last_activity'] = time();
}

function require_guest(): void
{
    if (is_authenticated()) {
        redirect(is_admin() ? 'admin/dashboard.php' : 'cliente/dashboard.php');
    }
}

function require_auth(): void
{
    if (!is_authenticated()) {
        flash('warning', 'Inicia sesión para continuar.');
        redirect('auth/login.php');
    }
}

function require_role(string $role): void
{
    require_auth();
    if ((user()['role'] ?? '') !== $role) {
        $attemptedRole = user()['role'] ?? 'desconocido';
        log_security_event('role_mismatch', "Rol {$attemptedRole} intentó acceder a {$role}.");
        logout_user();
        session_start();
        flash('danger', 'Acceso denegado. La sesión fue cerrada por seguridad.');
        redirect('auth/login.php');
    }
}

function find_user_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT u.*, r.nombre AS role_name FROM usuarios u JOIN roles r ON r.id = u.role_id WHERE u.email = ? LIMIT 1');
    $stmt->execute([mb_strtolower($email)]);
    return $stmt->fetch() ?: null;
}

function too_many_login_attempts(string $email): bool
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE email = ? AND ip_address = ? AND successful = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
    $stmt->execute([$email, client_ip()]);
    return (int)$stmt->fetchColumn() >= 5;
}

function record_login_attempt(string $email, bool $successful): void
{
    db()->prepare('INSERT INTO login_attempts (email, ip_address, successful) VALUES (?, ?, ?)')
        ->execute([$email, client_ip(), $successful ? 1 : 0]);
}
