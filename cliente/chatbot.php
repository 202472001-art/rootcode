<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('cliente');
$pageTitle = 'Chatbot';
require dirname(__DIR__) . '/includes/client_header.php';
?>
<div class="panel-heading"><div><h1>Asistente de preguntas frecuentes</h1><p>Información rápida sin utilizar servicios externos de inteligencia artificial.</p></div></div>
<div class="chatbot-info-grid"><div class="card"><h2>Temas disponibles</h2><ul><li>Servicios y proceso de solicitud.</li><li>Tiempos aproximados.</li><li>Estado de solicitudes.</li><li>Hosting, dominio y mantenimiento.</li><li>Contacto, pagos y soporte.</li></ul><button class="btn" type="button" data-open-chatbot>Abrir chatbot</button></div><div class="card"><h2>¿No encuentras la respuesta?</h2><p>Envía un mensaje interno para que el administrador revise tu caso.</p><a class="btn btn-outline" href="<?= url('cliente/mensajes.php') ?>">Contactar al administrador</a></div></div>
<?php require dirname(__DIR__) . '/includes/client_footer.php'; ?>
