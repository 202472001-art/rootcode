<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Servicios';
$services = [
    ['categoria' => 'Desarrollo web', 'nombre' => 'Página web corporativa', 'descripcion' => 'Sitio para presentar una empresa, sus servicios, ubicación y medios de contacto.', 'imagen' => 'service-web.svg'],
    ['categoria' => 'Ventas', 'nombre' => 'Catálogo o tienda en línea', 'descripcion' => 'Productos organizados, información de compra y herramientas para comenzar a vender.', 'imagen' => 'hero-tech.svg'],
    ['categoria' => 'Sistemas', 'nombre' => 'Sistema web personalizado', 'descripcion' => 'Usuarios, formularios, registros y procesos adaptados a una necesidad específica.', 'imagen' => 'team-tech.svg'],
    ['categoria' => 'Promoción', 'nombre' => 'Landing page', 'descripcion' => 'Página de una sola sección para campañas, productos o captación de clientes.', 'imagen' => 'service-web.svg'],
    ['categoria' => 'Soporte', 'nombre' => 'Mantenimiento web', 'descripcion' => 'Respaldos, correcciones, cambios de contenido y revisión general del sitio.', 'imagen' => 'team-tech.svg'],
    ['categoria' => 'Infraestructura', 'nombre' => 'Hosting y dominio', 'descripcion' => 'Orientación para publicar el sitio con dominio, SSL y alojamiento compatible.', 'imagen' => 'hero-tech.svg'],
];
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><span class="section-kicker">Servicios RootCode</span><h1>Opciones para iniciar o mejorar tu presencia digital</h1><p>Los servicios se muestran como contenido fijo para mantener una base de datos más sencilla.</p></div></section>
<section class="section section-white"><div class="container service-grid">
<?php foreach ($services as $service): ?>
<article class="service-block"><img src="<?= asset('img/' . $service['imagen']) ?>" alt="<?= e($service['nombre']) ?>"><div><span class="card-category"><?= e($service['categoria']) ?></span><h2><?= e($service['nombre']) ?></h2><p><?= e($service['descripcion']) ?></p><a class="btn btn-small" href="<?= is_client() ? url('cliente/solicitudes.php?action=new&type=' . urlencode($service['nombre'])) : url('auth/registro.php') ?>">Solicitar información</a></div></article>
<?php endforeach; ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
