<?php

declare(strict_types=1);

function url(string $path = ''): string
{
    $base = rtrim(APP_BASE_URL, '/');
    $path = ltrim($path, '/');

    // Las rutas visibles no muestran las carpetas internas admin/cliente.
    $parts = explode('?', $path, 2);
    $cleanPath = $parts[0];
    $query = isset($parts[1]) ? '?' . $parts[1] : '';
    $routes = [
        'index.php' => '',
        'servicios.php' => 'servicios',
        'portafolio.php' => 'portafolio',
        'contacto.php' => 'contacto',
        'auth/login.php' => 'iniciar-sesion',
        'auth/registro.php' => 'crear-cuenta',
        'auth/recuperar.php' => 'recuperar',
        'auth/restablecer.php' => 'restablecer',
        'auth/logout.php' => 'salir',
        'admin/dashboard.php' => 'panel',
        'cliente/dashboard.php' => 'panel',
        'admin/solicitudes.php' => 'solicitudes',
        'cliente/solicitudes.php' => 'solicitudes',
        'admin/mensajes.php' => 'mensajes',
        'cliente/mensajes.php' => 'mensajes',
        'admin/portafolio.php' => 'gestion-portafolio',
        'admin/contactos.php' => 'gestion-contactos',
        'cliente/perfil.php' => 'perfil',
    ];
    if (array_key_exists($cleanPath, $routes)) {
        $cleanPath = $routes[$cleanPath];
    }
    return $base . ($cleanPath !== '' ? '/' . $cleanPath : '/') . $query;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $items = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $items;
}

function preserve_old_input(array $input): void
{
    unset($input['password'], $input['password_confirmation'], $input['csrf_token']);
    $_SESSION['_old'] = $input;
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['_csrf_token'] ?? '', $token)) {
        log_security_event('csrf_failure', 'Token CSRF inválido.');
        http_response_code(419);
        exit('La sesión del formulario expiró. Regresa e inténtalo nuevamente.');
    }
}

function request_is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function client_ip(): string
{
    return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45);
}

function clean_text(?string $value, int $max = 1000): string
{
    $value = trim((string)$value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? '';
    return mb_substr($value, 0, $max);
}

function clean_multiline(?string $value, int $max = 5000): string
{
    $value = trim((string)$value);
    $value = preg_replace("/\r\n?|\n/", "\n", $value) ?? '';
    return mb_substr($value, 0, $max);
}

function valid_email(?string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function format_money(float|string|null $amount): string
{
    return '$' . number_format((float)$amount, 2) . ' MXN';
}

function format_date(?string $date, bool $withTime = true): string
{
    if (!$date) {
        return '—';
    }
    try {
        $zone = new DateTimeZone(APP_TIMEZONE);
        $value = new DateTimeImmutable($date, $zone);
        return $value->setTimezone($zone)->format($withTime ? 'd/m/Y H:i' : 'd/m/Y');
    } catch (Throwable $exception) {
        return '—';
    }
}

function status_class(string $status): string
{
    return match (mb_strtolower($status)) {
        'aceptada', 'activo', 'finalizada', 'respondido', 'pagada' => 'success',
        'rechazada', 'inactivo', 'cancelada' => 'danger',
        'en revisión', 'en revision', 'en desarrollo', 'borrador', 'leído', 'leido', 'enviado' => 'info',
        default => 'warning',
    };
}

function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = preg_replace('/[^a-zA-Z0-9]+/', '-', $text) ?? '';
    return strtolower(trim($text, '-')) ?: bin2hex(random_bytes(4));
}

function encode_id(int $id): string
{
    $payload = (string)$id;
    $signature = hash_hmac('sha256', $payload, APP_KEY);
    return rtrim(strtr(base64_encode($payload . '.' . $signature), '+/', '-_'), '=');
}

function decode_id(?string $token): ?int
{
    if (!$token) {
        return null;
    }
    $decoded = base64_decode(strtr($token, '-_', '+/'), true);
    if (!$decoded || !str_contains($decoded, '.')) {
        return null;
    }
    [$id, $signature] = explode('.', $decoded, 2);
    if (!ctype_digit($id) || !hash_equals(hash_hmac('sha256', $id, APP_KEY), $signature)) {
        return null;
    }
    return (int)$id;
}

function log_security_event(string $event, string $details = '', ?int $userId = null): void
{
    try {
        $stmt = db()->prepare('INSERT INTO security_logs (user_id, event_type, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId ?? ($_SESSION['user']['id'] ?? null),
            mb_substr($event, 0, 80),
            mb_substr($details, 0, 1000),
            client_ip(),
            mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    } catch (Throwable $exception) {
        error_log('Security log error: ' . $exception->getMessage());
    }
}

function abort_forbidden(string $reason = 'Acceso no autorizado.'): never
{
    log_security_event('forbidden_access', $reason);
    http_response_code(403);
    exit('No tienes permiso para acceder a este recurso.');
}

function upload_image(array $file, string $directory = 'portfolio'): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo completar la carga del archivo.');
    }
    if (($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('La imagen supera el límite de 5 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Solo se permiten imágenes JPG, PNG o WEBP.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $relative = 'uploads/' . trim($directory, '/') . '/' . $filename;
    $target = dirname(__DIR__) . '/' . $relative;
    if (!is_dir(dirname($target))) {
        mkdir(dirname($target), 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('No se pudo guardar la imagen.');
    }
    return $relative;
}

function delete_uploaded_file(?string $relativePath): void
{
    if (!$relativePath || !str_starts_with($relativePath, 'uploads/')) {
        return;
    }
    $full = dirname(__DIR__) . '/' . $relativePath;
    if (is_file($full)) {
        unlink($full);
    }
}

function smtp_send_mail(string $to, string $subject, string $body): bool
{
    if (!defined('SMTP_ENABLED') || !SMTP_ENABLED || SMTP_USER === '' || SMTP_PASS === '' || str_contains(SMTP_PASS, 'COLOCA_AQUI')) {
        return false;
    }

    $remote = (SMTP_ENCRYPTION === 'ssl' ? 'ssl://' : '') . SMTP_HOST . ':' . SMTP_PORT;
    $socket = @stream_socket_client($remote, $errorNumber, $errorMessage, 15, STREAM_CLIENT_CONNECT);
    if (!$socket) {
        error_log("SMTP connection error {$errorNumber}: {$errorMessage}");
        return false;
    }
    stream_set_timeout($socket, 15);

    $read = static function () use ($socket): string {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') break;
        }
        return $response;
    };
    $command = static function (string $text, array $accepted) use ($socket, $read): bool {
        fwrite($socket, $text . "\r\n");
        $response = $read();
        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $accepted, true)) {
            error_log('SMTP error after ' . strtok($text, ' ') . ': ' . trim($response));
            return false;
        }
        return true;
    };

    $banner = $read();
    if ((int)substr($banner, 0, 3) !== 220) { fclose($socket); return false; }
    $hostName = $_SERVER['SERVER_NAME'] ?? 'rootcode.local';
    if (!$command('EHLO ' . $hostName, [250])) { fclose($socket); return false; }

    if (SMTP_ENCRYPTION === 'tls') {
        if (!$command('STARTTLS', [220])) { fclose($socket); return false; }
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($socket); return false; }
        if (!$command('EHLO ' . $hostName, [250])) { fclose($socket); return false; }
    }

    if (!$command('AUTH LOGIN', [334]) ||
        !$command(base64_encode(SMTP_USER), [334]) ||
        !$command(base64_encode(SMTP_PASS), [235]) ||
        !$command('MAIL FROM:<' . SMTP_USER . '>', [250]) ||
        !$command('RCPT TO:<' . $to . '>', [250, 251]) ||
        !$command('DATA', [354])) {
        fclose($socket);
        return false;
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = [
        'From: ' . SMTP_FROM_NAME . ' <' . SMTP_USER . '>',
        'To: <' . $to . '>',
        'Subject: ' . $encodedSubject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'Date: ' . date(DATE_RFC2822),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $hostName . '>',
    ];
    $safeBody = preg_replace('/(?m)^\./', '..', str_replace(["\r\n", "\r"], "\n", $body));
    $data = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n", "\r\n", $safeBody) . "\r\n.";
    $ok = $command($data, [250]);
    $command('QUIT', [221]);
    fclose($socket);
    return $ok;
}

function send_rootcode_mail(string $to, string $subject, string $body): bool
{
    if (smtp_send_mail($to, $subject, $body)) {
        return true;
    }
    // Respaldo para entornos donde mail() esté configurado correctamente.
    $headers = [
        'From: RootCode <' . SUPPORT_EMAIL . '>',
        'Reply-To: ' . SUPPORT_EMAIL,
        'Content-Type: text/plain; charset=UTF-8',
    ];
    return @mail($to, $subject, $body, implode("\r\n", $headers));
}

function pagination(int $page, int $perPage, int $total): array
{
    $pages = max(1, (int)ceil($total / $perPage));
    $page = max(1, min($page, $pages));
    return ['page' => $page, 'pages' => $pages, 'offset' => ($page - 1) * $perPage];
}
