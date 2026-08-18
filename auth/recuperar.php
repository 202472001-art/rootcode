<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_guest();
$pageTitle = 'Recuperar contraseña';
$showChatbot = false;
$errors = [];
$developmentLink = null;

if (request_is_post()) {
    verify_csrf();
    $email = mb_strtolower(clean_text($_POST['email'] ?? '', 180));
    if (!valid_email($email)) {
        $errors[] = 'Escribe un correo válido.';
    } else {
        $account = find_user_by_email($email);
        if ($account) {
            $rawToken = bin2hex(random_bytes(32));
            $hash = hash('sha256', $rawToken);
            db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL')->execute([$account['id']]);
            db()->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))')->execute([$account['id'], $hash]);
            $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('auth/restablecer.php?token=' . urlencode($rawToken));
            $subject = 'Restablecer contraseña de RootCode';
            $body = "Hola {$account['nombre']},\n\nUsa este enlace durante los próximos 30 minutos:\n{$link}\n\nSi no solicitaste el cambio, ignora este mensaje.";
            $sent = send_rootcode_mail($account['email'], $subject, $body);
            if (!$sent) {
                error_log('No fue posible enviar el correo de recuperación a ' . $account['email']);
                if (APP_ENV === 'development') $errors[] = 'No se pudo enviar el correo. Configura SMTP_PASS en config/app.php.';
            }
            if (APP_ENV === 'development') $developmentLink = $link;
        }
        flash('success', 'Si el correo existe, se generó un enlace para restablecer la contraseña.');
    }
}
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="auth-page"><form class="auth-card" method="post"><?= csrf_field() ?><span class="eyebrow">Recuperación</span><h1>Nueva contraseña</h1><p>Te enviaremos un enlace temporal.</p><?php if ($errors): ?><div class="error-list"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><div class="field"><label for="email">Correo</label><input id="email" name="email" type="email" required></div><button class="btn" type="submit" style="width:100%;margin-top:18px">Generar enlace</button><?php if ($developmentLink): ?><div class="alert alert-info">Modo local: <a href="<?= e($developmentLink) ?>">abrir enlace de recuperación</a></div><?php endif; ?><div class="auth-links"><a href="<?= url('auth/login.php') ?>">Volver al login</a></div></form></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
