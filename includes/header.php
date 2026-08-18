<?php
/** @var string $pageTitle */
$pageTitle = $pageTitle ?? APP_NAME;
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$flashes = get_flashes();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="RootCode desarrolla páginas web, sistemas y soluciones digitales para negocios.">
    <title><?= e($pageTitle) ?> | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
</head>
<body>
<header class="site-header">
    <div class="public-nav">
        <a class="public-brand" href="<?= url('index.php') ?>" aria-label="RootCode inicio">
            <span class="logo-boxes"><i>R</i><i>O</i><i>O</i><i>T</i></span><strong>CODE</strong>
        </a>
        <button class="nav-toggle" type="button" aria-label="Abrir menú" aria-expanded="false">Menú</button>
        <nav class="main-nav" aria-label="Navegación principal">
            <div class="nav-links">
                <a class="<?= $currentPage === 'index.php' ? 'active' : '' ?>" href="<?= url('index.php') ?>">Inicio</a>
                <a class="<?= $currentPage === 'servicios.php' ? 'active' : '' ?>" href="<?= url('servicios.php') ?>">Servicios</a>
                <a class="<?= $currentPage === 'portafolio.php' ? 'active' : '' ?>" href="<?= url('portafolio.php') ?>">Portafolio</a>
                <a class="<?= $currentPage === 'contacto.php' ? 'active' : '' ?>" href="<?= url('contacto.php') ?>">Contacto</a>
            </div>
            <div class="nav-actions">
                <?php if (is_client()): ?>
                    <a class="nav-button outline" href="<?= url('cliente/dashboard.php') ?>">Mi panel</a>
                <?php elseif (is_admin()): ?>
                    <a class="nav-button outline" href="<?= url('admin/dashboard.php') ?>">Administración</a>
                <?php else: ?>
                    <a class="nav-button outline" href="<?= url('auth/login.php') ?>">Iniciar sesión</a>
                    <a class="nav-button light" href="<?= url('auth/registro.php') ?>">Crear una cuenta</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
<?php foreach ($flashes as $flash): ?>
<div class="container alert alert-<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
<?php endforeach; ?>
<main>
