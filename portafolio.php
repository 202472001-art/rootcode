<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Portafolio';
$projects = db()->query('SELECT * FROM portafolio WHERE activo = 1 ORDER BY destacado DESC, created_at DESC')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><span class="section-kicker">Portafolio</span><h1>Ejemplos de soluciones digitales</h1><p>Proyectos académicos y demostrativos administrados desde el panel RootCode.</p></div></section>
<section class="section section-white"><div class="container portfolio-grid">
<?php if (!$projects): ?><div class="empty-state">Todavía no hay proyectos publicados.</div><?php endif; ?>
<?php foreach ($projects as $project): ?>
<article class="portfolio-card-public"><img src="<?= $project['imagen'] ? url($project['imagen']) : asset('img/service-web.svg') ?>" alt="<?= e($project['titulo']) ?>"><div><span class="card-category"><?= e($project['categoria'] ?: 'Proyecto') ?></span><h2><?= e($project['titulo']) ?></h2><p><?= e($project['resumen']) ?></p></div></article>
<?php endforeach; ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
