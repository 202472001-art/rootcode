<?php
require_once __DIR__ . '/includes/bootstrap.php';
$section = clean_text($_GET['section'] ?? '', 40);
$role = user()['role'] ?? '';
$routes = [
    'panel' => ['administrador' => 'admin/dashboard.php', 'cliente' => 'cliente/dashboard.php'],
    'solicitudes' => ['administrador' => 'admin/solicitudes.php', 'cliente' => 'cliente/solicitudes.php'],
    'mensajes' => ['administrador' => 'admin/mensajes.php', 'cliente' => 'cliente/mensajes.php'],
    'perfil' => ['cliente' => 'cliente/perfil.php'],
    'gestion-portafolio' => ['administrador' => 'admin/portafolio.php'],
    'gestion-contactos' => ['administrador' => 'admin/contactos.php'],
];
if (!isset($routes[$section])) { http_response_code(404); exit('Página no encontrada.'); }
if (!$role || !isset($routes[$section][$role])) {
    if (!$role) redirect('auth/login.php');
    abort_forbidden('Ruta no autorizada para este rol.');
}
$currentSection = $section;
require __DIR__ . '/' . $routes[$section][$role];
