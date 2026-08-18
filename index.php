<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Desarrollo web y soluciones digitales';
$projects = db()->query('SELECT * FROM portafolio WHERE activo = 1 ORDER BY destacado DESC, created_at DESC LIMIT 9')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="home-hero">
    <div class="container hero-grid">
        <div>
            <span class="hero-label">Desarrollo web para negocios</span>
            <h1>Ideas digitales que ayudan a crecer.</h1>
            <p>En RootCode creamos páginas web, sistemas y soluciones sencillas para emprendedores y negocios locales.</p>
            <div class="hero-actions">
                <a class="btn" href="<?= is_client() ? url('cliente/solicitudes.php?action=new') : url('auth/registro.php') ?>">Nuevo proyecto</a>
                <a class="btn btn-outline-light" href="<?= url('portafolio.php') ?>">Explorar proyectos</a>
            </div>
        </div>
        <img class="hero-image" src="<?= asset('img/android-studio-seguimiento.png') ?>" alt="Proyecto móvil desarrollado en Android Studio" width="1917" height="1020" fetchpriority="high">
    </div>
</section>

<section class="section impact-section">
    <div class="container">
        <div class="section-heading center">
            <span class="section-kicker">RootCode en números</span>
            <h2>Soluciones claras para cada proyecto</h2>
        </div>
        <div class="impact-grid">
            <div class="impact-main"><strong>100%</strong><span>diseño adaptable</span></div>
            <div class="impact-item"><strong>2</strong><span>paneles independientes</span></div>
            <div class="impact-item"><strong>24/7</strong><span>solicitudes disponibles</span></div>
            <div class="impact-item"><strong>6</strong><span>estados de seguimiento</span></div>
            <div class="impact-item"><strong>1</strong><span>base de datos compartida</span></div>
        </div>
    </div>
</section>

<section class="section section-white">
    <div class="container split-section">
        <div>
            <span class="section-kicker">Cómo trabajamos</span>
            <h2>Un proceso sencillo y visible</h2>
            <p>El cliente registra su idea, el administrador revisa la información y ambos mantienen la comunicación desde sus paneles.</p>
            <a class="btn btn-dark" href="<?= url('servicios.php') ?>">Conocer servicios</a>
        </div>
        <div class="steps-table">
            <div><strong>01</strong><span>Registro de la solicitud</span></div>
            <div><strong>02</strong><span>Revisión del proyecto</span></div>
            <div><strong>03</strong><span>Mensajes y seguimiento</span></div>
            <div><strong>04</strong><span>Desarrollo y entrega</span></div>
        </div>
    </div>
</section>

<section class="section portfolio-slider-section">
    <div class="container">
        <div class="section-heading center">
            <span class="section-kicker">Portafolio</span>
            <h2>Proyectos y ejemplos de trabajo</h2>
            <p>El carrusel muestra tres proyectos en computadora y uno en dispositivos móviles.</p>
        </div>
        <div class="content-slider" data-slider>
            <button class="slider-arrow prev" type="button" data-slider-prev aria-label="Anterior">←</button>
            <div class="slider-viewport">
                <div class="slider-track" data-slider-track>
                    <?php foreach ($projects as $project): ?>
                    <article class="slider-card">
                        <img src="<?= $project['imagen'] ? url($project['imagen']) : asset('img/service-web.svg') ?>" alt="<?= e($project['titulo']) ?>">
                        <div class="slider-card-body">
                            <span class="card-category"><?= e($project['categoria'] ?: 'Proyecto web') ?></span>
                            <h3><?= e($project['titulo']) ?></h3>
                            <p><?= e($project['resumen']) ?></p>
                            <a class="btn btn-small" href="<?= url('portafolio.php') ?>">Ver proyecto</a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="slider-arrow next" type="button" data-slider-next aria-label="Siguiente">→</button>
            <div class="slider-dots" data-slider-dots aria-label="Posición del carrusel"></div>
        </div>
    </div>
</section>

<section class="join-section">
    <div class="container join-content">
        <div><h2>¿Tienes una idea para tu negocio?</h2><p>Crea tu cuenta y registra una solicitud para comenzar.</p></div>
        <a class="btn btn-light" href="<?= url('auth/registro.php') ?>">Registrarme</a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
