<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Contacto';
$errors = [];
$serviceOptions = ['Página web corporativa','Catálogo o tienda en línea','Sistema web personalizado','Landing page','Mantenimiento web','Hosting y dominio','Otro'];

if (request_is_post()) {
    verify_csrf();
    $nombre = clean_text($_POST['nombre'] ?? '', 120);
    $email = mb_strtolower(clean_text($_POST['email'] ?? '', 180));
    $telefono = clean_text($_POST['telefono'] ?? '', 30);
    $empresa = clean_text($_POST['empresa'] ?? '', 150);
    $tipoServicio = clean_text($_POST['tipo_servicio'] ?? '', 120);
    $presupuesto = clean_text($_POST['presupuesto'] ?? '', 80);
    $mensaje = clean_multiline($_POST['mensaje'] ?? '', 3000);
    if (mb_strlen($nombre) < 3) $errors[] = 'Escribe tu nombre completo.';
    if (!valid_email($email)) $errors[] = 'Escribe un correo válido.';
    if (mb_strlen($mensaje) < 10) $errors[] = 'Describe brevemente tu proyecto.';
    if ($tipoServicio !== '' && !in_array($tipoServicio, $serviceOptions, true)) $errors[] = 'Selecciona un tipo de servicio válido.';
    if (!$errors) {
        db()->prepare('INSERT INTO contacto (nombre,email,telefono,empresa,tipo_servicio,presupuesto,mensaje,estado) VALUES (?,?,?,?,?,?,?,"nuevo")')
            ->execute([$nombre,$email,$telefono ?: null,$empresa ?: null,$tipoServicio ?: null,$presupuesto ?: null,$mensaje]);
        clear_old_input();
        flash('success', 'Tu mensaje fue enviado. El equipo de RootCode lo revisará.');
        redirect('contacto.php');
    }
    preserve_old_input($_POST);
}
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><span class="section-kicker">Contacto</span><h1>Cuéntanos sobre tu proyecto</h1><p>También puedes crear una cuenta para administrar solicitudes y mensajes internos.</p></div></section>
<section class="section"><div class="container contact-layout"><div class="contact-info"><img src="<?= asset('img/team-tech.svg') ?>" alt="Equipo de RootCode"><h2>Estamos para orientarte</h2><p>Completa el formulario con información básica. El administrador podrá revisar el mensaje desde su panel.</p><a class="btn btn-outline" href="<?= url('auth/registro.php') ?>">Crear cuenta</a></div><form class="form-card" method="post" data-validate><?= csrf_field() ?>
<?php if ($errors): ?><div class="error-list"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="form-grid"><div class="field"><label for="nombre">Nombre</label><input id="nombre" name="nombre" value="<?= old('nombre') ?>" maxlength="120" required></div><div class="field"><label for="email">Correo</label><input id="email" name="email" type="email" value="<?= old('email') ?>" maxlength="180" required></div><div class="field"><label for="telefono">Teléfono</label><input id="telefono" name="telefono" value="<?= old('telefono') ?>" maxlength="30"></div><div class="field"><label for="empresa">Empresa</label><input id="empresa" name="empresa" value="<?= old('empresa') ?>" maxlength="150"></div><div class="field"><label for="tipo_servicio">Servicio</label><select id="tipo_servicio" name="tipo_servicio"><option value="">Selecciona</option><?php foreach ($serviceOptions as $option): ?><option <?= (($_SESSION['_old']['tipo_servicio'] ?? '') === $option) ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div><div class="field"><label for="presupuesto">Presupuesto estimado</label><input id="presupuesto" name="presupuesto" value="<?= old('presupuesto') ?>" maxlength="80"></div><div class="field full"><label for="mensaje">Descripción</label><textarea id="mensaje" name="mensaje" maxlength="3000" required><?= old('mensaje') ?></textarea></div></div><button class="btn" type="submit">Enviar mensaje</button></form></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
