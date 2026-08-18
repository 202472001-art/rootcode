<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_guest();
$pageTitle = 'Crear cuenta';
$showChatbot = false;
$errors = [];

if (request_is_post()) {
    verify_csrf();
    $nombre = clean_text($_POST['nombre'] ?? '', 120);
    $email = mb_strtolower(clean_text($_POST['email'] ?? '', 180));
    $telefono = clean_text($_POST['telefono'] ?? '', 30);
    $empresa = clean_text($_POST['empresa'] ?? '', 150);
    $password = (string)($_POST['password'] ?? '');
    $confirmation = (string)($_POST['password_confirmation'] ?? '');

    if (mb_strlen($nombre) < 3) $errors[] = 'El nombre debe tener al menos 3 caracteres.';
    if (!valid_email($email)) $errors[] = 'Escribe un correo válido.';
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) $errors[] = 'La contraseña debe tener al menos 8 caracteres, mayúscula, minúscula y número.';
    if ($password !== $confirmation) $errors[] = 'Las contraseñas no coinciden.';
    if (find_user_by_email($email)) $errors[] = 'Ya existe una cuenta con ese correo.';

    if (!$errors) {
        $roleId = (int)db()->query("SELECT id FROM roles WHERE nombre = 'cliente' LIMIT 1")->fetchColumn();
        $stmt = db()->prepare('INSERT INTO usuarios (role_id, nombre, email, password_hash, telefono, empresa, estado) VALUES (?, ?, ?, ?, ?, ?, "activo")');
        $stmt->execute([$roleId, $nombre, $email, password_hash($password, PASSWORD_DEFAULT), $telefono ?: null, $empresa ?: null]);
        clear_old_input();
        flash('success', 'Cuenta creada correctamente. Ya puedes iniciar sesión.');
        redirect('auth/login.php');
    }
    preserve_old_input($_POST);
}
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="auth-page"><form class="auth-card" method="post" data-validate><?= csrf_field() ?><span class="eyebrow">Nuevo cliente</span><h1>Crear cuenta</h1><p>Administra tus solicitudes y mensajes en un espacio privado.</p><?php if ($errors): ?><div class="error-list"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><div class="field"><label for="nombre">Nombre completo</label><input id="nombre" name="nombre" value="<?= old('nombre') ?>" required></div><div class="field"><label for="email">Correo</label><input id="email" name="email" type="email" value="<?= old('email') ?>" required></div><div class="field"><label for="telefono">Teléfono</label><input id="telefono" name="telefono" value="<?= old('telefono') ?>"></div><div class="field"><label for="empresa">Empresa</label><input id="empresa" name="empresa" value="<?= old('empresa') ?>"></div><div class="field"><label for="password">Contraseña</label><input id="password" name="password" type="password" required></div><div class="field"><label for="password_confirmation">Confirmar contraseña</label><input id="password_confirmation" name="password_confirmation" type="password" required></div><p class="help">Mínimo 8 caracteres, con mayúscula, minúscula y número.</p><button class="btn" type="submit" style="width:100%">Crear cuenta</button><div class="auth-links"><a href="<?= url('auth/login.php') ?>">Ya tengo cuenta</a></div></form></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
