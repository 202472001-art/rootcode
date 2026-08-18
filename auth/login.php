<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_guest();
$pageTitle = 'Iniciar sesión';
$showChatbot = false;
$errors = [];

if (request_is_post()) {
    verify_csrf();
    $email = mb_strtolower(clean_text($_POST['email'] ?? '', 180));
    $password = (string)($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    if (!valid_email($email) || $password === '') {
        $errors[] = 'Escribe tu correo y contraseña.';
    } elseif (too_many_login_attempts($email)) {
        $errors[] = 'Se detectaron varios intentos. Espera 15 minutos antes de volver a intentar.';
        log_security_event('login_rate_limited', 'Correo: ' . $email);
    } else {
        $account = find_user_by_email($email);
        $valid = $account && $account['estado'] === 'activo' && password_verify($password, $account['password_hash']);
        record_login_attempt($email, (bool)$valid);
        if ($valid) {
            login_user($account, $remember);
            clear_old_input();
            redirect($account['role_name'] === 'administrador' ? 'admin/dashboard.php' : 'cliente/dashboard.php');
        }
        $errors[] = 'Credenciales incorrectas o cuenta inactiva.';
        log_security_event('login_failed', 'Correo: ' . $email);
    }
    preserve_old_input(['email' => $email]);
}
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="auth-page"><form class="auth-card" method="post" data-validate><?= csrf_field() ?><span class="eyebrow">Acceso seguro</span><h1>Iniciar sesión</h1><p>Ingresa a tu panel de RootCode.</p><?php if ($errors): ?><div class="error-list"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><div class="field"><label for="email">Correo</label><input id="email" name="email" type="email" value="<?= old('email') ?>" required autocomplete="email"></div><div class="field"><label for="password">Contraseña</label><div class="password-wrap"><input id="password" name="password" type="password" required autocomplete="current-password"><button type="button" data-password-toggle="#password">Mostrar</button></div></div><label><input type="checkbox" name="remember" value="1" style="width:auto"> Recordar sesión</label><button class="btn" type="submit" style="width:100%;margin-top:18px">Ingresar</button><div class="auth-links"><a href="<?= url('auth/registro.php') ?>">Crear cuenta</a><a href="<?= url('auth/recuperar.php') ?>">Olvidé mi contraseña</a></div></form></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
