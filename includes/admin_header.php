<?php
require_role('administrador');
$pageTitle = $pageTitle ?? 'Administración';
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$currentSection = $currentSection ?? ''; 
$flashes = get_flashes();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | RootCode Admin</title>
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
</head>
<body class="panel-body">
<header class="panel-topbar">
    <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="Abrir menú">☰</button>
    <strong><?= e($pageTitle) ?></strong>
    <div class="topbar-user"><span><?= e(user()['nombre']) ?></span><a href="<?= url('auth/logout.php') ?>">Salir</a></div>
</header>
<div class="panel-shell">
    <aside class="panel-sidebar" data-sidebar>
        <div class="sidebar-head">
            <span class="profile-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 8a7 7 0 0 0-14 0h14Z"/></svg>
            </span>
            <div><strong>RootCode Admin</strong><small>Panel administrativo</small></div>
        </div>
        <nav>
            <a class="<?= $currentSection === 'panel' || $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="<?= url('admin/dashboard.php') ?>">Dashboard</a>
            <a class="<?= $currentSection === 'solicitudes' || $currentPage === 'solicitudes.php' ? 'active' : '' ?>" href="<?= url('admin/solicitudes.php') ?>">Solicitudes</a>
            <a class="<?= $currentSection === 'mensajes' || $currentPage === 'mensajes.php' ? 'active' : '' ?>" href="<?= url('admin/mensajes.php') ?>">Mensajes</a>
            <a class="<?= $currentSection === 'gestion-portafolio' || $currentPage === 'portafolio.php' ? 'active' : '' ?>" href="<?= url('admin/portafolio.php') ?>">Portafolio</a>
            <a class="<?= $currentSection === 'gestion-contactos' || $currentPage === 'contactos.php' ? 'active' : '' ?>" href="<?= url('admin/contactos.php') ?>">Contactos</a>
            <a href="<?= url('index.php') ?>">Sitio público</a>
            <a class="sidebar-logout" href="<?= url('auth/logout.php') ?>">Cerrar sesión</a>
        </nav>
    </aside>
    <main class="panel-content">
        <?php foreach ($flashes as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>
