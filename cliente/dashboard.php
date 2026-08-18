<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('cliente');

$pageTitle = 'Inicio';
$userId = (int) user()['id'];

$metricsStmt = db()->prepare('SELECT
    (SELECT COUNT(*) FROM solicitudes WHERE usuario_id = ?) solicitudes,
    (SELECT COUNT(*) FROM solicitudes WHERE usuario_id = ? AND estado IN ("aceptada", "en desarrollo")) activas,
    (SELECT COUNT(*) FROM mensajes WHERE destinatario_id = ? AND leido = 0) no_leidos');
$metricsStmt->execute([$userId, $userId, $userId]);
$metrics = $metricsStmt->fetch();

$recentStmt = db()->prepare('SELECT * FROM solicitudes WHERE usuario_id = ? ORDER BY created_at DESC LIMIT 5');
$recentStmt->execute([$userId]);
$recent = $recentStmt->fetchAll();

require dirname(__DIR__) . '/includes/client_header.php';
?>
<div class="panel-heading">
    <div>
        <h1>Hola, <?= e(user()['nombre']) ?></h1>
    </div>
    <a class="btn" href="<?= url('cliente/solicitudes.php?action=new') ?>">Nueva solicitud</a>
</div>

<div class="metric-grid three">
    <div class="metric-card">
        <span>Solicitudes</span>
        <strong><?= (int) $metrics['solicitudes'] ?></strong>
    </div>
    <div class="metric-card">
        <span>Proyectos activos</span>
        <strong><?= (int) $metrics['activas'] ?></strong>
    </div>
    <div class="metric-card">
        <span>Mensajes nuevos</span>
        <strong><?= (int) $metrics['no_leidos'] ?></strong>
    </div>
</div>

<div class="table-card">
    <div class="table-toolbar">
        <h3>Solicitudes recientes</h3>
        <a class="btn btn-small btn-light" href="<?= url('cliente/solicitudes.php') ?>">Ver todas</a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Proyecto</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$recent): ?>
                    <tr><td colspan="5" class="empty-state">Todavía no has creado solicitudes.</td></tr>
                <?php endif; ?>
                <?php foreach ($recent as $item): ?>
                    <tr>
                        <td><?= e($item['titulo']) ?></td>
                        <td><?= e($item['tipo_pagina']) ?></td>
                        <td><span class="badge badge-<?= status_class($item['estado']) ?>"><?= e(ucfirst($item['estado'])) ?></span></td>
                        <td><?= format_date($item['created_at']) ?></td>
                        <td><a class="btn btn-small btn-outline" href="<?= url('cliente/solicitudes.php?action=view&t=' . encode_id((int) $item['id'])) ?>">Consultar</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__) . '/includes/client_footer.php'; ?>
