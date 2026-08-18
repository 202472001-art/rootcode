<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('administrador');

$pageTitle = 'Dashboard';
$metrics = db()->query("SELECT
    (SELECT COUNT(*) FROM solicitudes) solicitudes,
    (SELECT COUNT(*) FROM solicitudes WHERE estado = 'pendiente') pendientes,
    (SELECT COUNT(*) FROM mensajes WHERE destinatario_id = " . (int) user()['id'] . " AND leido = 0) mensajes,
    (SELECT COUNT(*) FROM contacto WHERE estado = 'nuevo') contactos")->fetch();

$recent = db()->query('SELECT s.*, u.nombre cliente
    FROM solicitudes s
    JOIN usuarios u ON u.id = s.usuario_id
    ORDER BY s.created_at DESC
    LIMIT 8')->fetchAll();

require dirname(__DIR__) . '/includes/admin_header.php';
?>
<div class="panel-heading">
    <div>
        <h1>Panel administrativo</h1>
    </div>
</div>

<div class="metric-grid">
    <div class="metric-card"><span>Solicitudes</span><strong><?= (int) $metrics['solicitudes'] ?></strong></div>
    <div class="metric-card"><span>Pendientes</span><strong><?= (int) $metrics['pendientes'] ?></strong></div>
    <div class="metric-card"><span>Mensajes nuevos</span><strong><?= (int) $metrics['mensajes'] ?></strong></div>
    <div class="metric-card"><span>Contactos nuevos</span><strong><?= (int) $metrics['contactos'] ?></strong></div>
</div>

<?php if ((int) $metrics['contactos'] > 0): ?>
    <div class="alert alert-info">
        Hay <?= (int) $metrics['contactos'] ?> contactos nuevos.
        <a href="<?= url('admin/contactos.php') ?>">Revisarlos</a>
    </div>
<?php endif; ?>

<div class="table-card">
    <div class="table-toolbar">
        <h3>Solicitudes recientes</h3>
        <a class="btn btn-small" href="<?= url('admin/solicitudes.php') ?>">Gestionar</a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Proyecto</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$recent): ?>
                    <tr><td colspan="6" class="empty-state">No hay solicitudes.</td></tr>
                <?php endif; ?>
                <?php foreach ($recent as $item): ?>
                    <tr>
                        <td><?= e($item['cliente']) ?></td>
                        <td><?= e($item['titulo']) ?></td>
                        <td><?= e($item['tipo_pagina']) ?></td>
                        <td><span class="badge badge-<?= status_class($item['estado']) ?>"><?= e(ucfirst($item['estado'])) ?></span></td>
                        <td><?= format_date($item['created_at']) ?></td>
                        <td><a class="btn btn-small btn-outline" href="<?= url('admin/solicitudes.php?action=view&t=' . encode_id((int) $item['id'])) ?>">Abrir</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
