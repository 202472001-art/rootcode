<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_guest();
$pageTitle = 'Restablecer contraseña';
$showChatbot = false;
$errors = [];
$token = clean_text($_GET['token'] ?? $_POST['token'] ?? '', 128);
$hash = hash('sha256', $token);
$stmt = db()->prepare('SELECT pr.*, u.email FROM password_resets pr JOIN usuarios u ON u.id = pr.user_id WHERE pr.token_hash = ? AND pr.used_at IS NULL AND pr.expires_at > NOW() LIMIT 1');
$stmt->execute([$hash]);
$reset = $stmt->fetch();

if (!$reset) {
    flash('danger', 'El enlace es inválido o ya expiró.');
    redirect('auth/recuperar.php');
}

if (request_is_post()) {
    verify_csrf();
    $password = (string)($_POST['password'] ?? '');
    $confirmation = (string)($_POST['password_confirmation'] ?? '');
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) $errors[] = 'La contraseña no cumple los requisitos.';
    if ($password !== $confirmation) $errors[] = 'Las contraseñas no coinciden.';
    if (!$errors) {
        db()->beginTransaction();
        db()->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $reset['user_id']]);
        db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([$reset['id']]);
        db()->prepare('DELETE FROM remember_tokens WHERE user_id = ?')->execute([$reset['user_id']]);
        db()->commit();
        flash('success', 'Contraseña actualizada. Inicia sesión nuevamente.');
        redirect('auth/login.php');
    }
}
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="auth-page"><form class="auth-card" method="post"><?= csrf_field() ?><input type="hidden" name="token" value="<?= e($token) ?>"><span class="eyebrow">Seguridad</span><h1>Define tu contraseña</h1><?php if ($errors): ?><div class="error-list"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><div class="field"><label for="password">Nueva contraseña</label><input id="password" name="password" type="password" required></div><div class="field"><label for="password_confirmation">Confirmar contraseña</label><input id="password_confirmation" name="password_confirmation" type="password" required></div><button class="btn" type="submit" style="width:100%;margin-top:18px">Actualizar contraseña</button></form></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
