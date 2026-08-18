<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('administrador');
$pageTitle = 'Solicitudes';
$statuses = ['pendiente','en revisión','aceptada','rechazada','en desarrollo','finalizada'];
$action = clean_text($_GET['action'] ?? 'list', 20);
$errors = [];
$current = null;
$load = function (int $id): array {
    $stmt = db()->prepare('SELECT s.*,u.nombre cliente,u.email,u.telefono,u.empresa FROM solicitudes s JOIN usuarios u ON u.id=s.usuario_id WHERE s.id=? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { http_response_code(404); exit('Solicitud no encontrada.'); }
    return $row;
};
if (isset($_GET['t'])) {
    $id = decode_id($_GET['t']);
    if (!$id) abort_forbidden('Token de solicitud alterado.');
    $current = $load($id);
}
if (request_is_post()) {
    verify_csrf();
    $formAction = clean_text($_POST['form_action'] ?? '', 20);
    $id = decode_id($_POST['token_id'] ?? '');
    if (!$id) abort_forbidden('Token de solicitud alterado.');
    $item = $load($id);
    if ($formAction === 'update') {
        $status = clean_text($_POST['estado'] ?? '', 30);
        $notes = clean_multiline($_POST['notas_admin'] ?? '', 5000);
        if (!in_array($status, $statuses, true)) $errors[] = 'Estado inválido.';
        if (!$errors) {
            db()->prepare('UPDATE solicitudes SET estado=?,notas_admin=?,updated_at=NOW() WHERE id=?')->execute([$status,$notes ?: null,$id]);
            flash('success', 'Solicitud actualizada.');
            redirect('admin/solicitudes.php?action=view&t=' . encode_id($id));
        }
        $current = array_merge($item, ['estado'=>$status,'notas_admin'=>$notes]);
        $action = 'view';
    }
    if ($formAction === 'delete') {
        db()->prepare('DELETE FROM solicitudes WHERE id=?')->execute([$id]);
        flash('success', 'Solicitud eliminada.');
        redirect('admin/solicitudes.php');
    }
}
if ($action === 'list') {
    $items = db()->query('SELECT s.*,u.nombre cliente FROM solicitudes s JOIN usuarios u ON u.id=s.usuario_id ORDER BY s.created_at DESC')->fetchAll();
}
require dirname(__DIR__) . '/includes/admin_header.php';
?>
<?php if ($action === 'view' && $current): ?>
<div class="panel-heading"><div><h1><?= e($current['titulo']) ?></h1><p><?= e($current['cliente'] . ' · ' . $current['email']) ?></p></div><a class="btn btn-light" href="<?= url('admin/solicitudes.php') ?>">Volver</a></div>
<div class="grid-2"><div class="detail-card"><div class="detail-grid"><div class="detail-item"><strong>Tipo de proyecto</strong><?= e($current['tipo_pagina']) ?></div><div class="detail-item"><strong>Presupuesto</strong><?= $current['presupuesto'] !== null ? format_money($current['presupuesto']) : 'No indicado' ?></div><div class="detail-item"><strong>Teléfono</strong><?= e($current['telefono'] ?: '—') ?></div><div class="detail-item"><strong>Empresa</strong><?= e($current['empresa'] ?: '—') ?></div><div class="detail-item full"><strong>Descripción</strong><?= nl2br(e($current['descripcion'])) ?></div></div><div class="actions"><a class="btn btn-outline" href="<?= url('admin/mensajes.php?cliente=' . encode_id((int)$current['usuario_id']) . '&solicitud=' . encode_id((int)$current['id'])) ?>">Abrir conversación</a></div></div>
<form class="card panel-card" method="post"><?= csrf_field() ?><input type="hidden" name="form_action" value="update"><input type="hidden" name="token_id" value="<?= e(encode_id((int)$current['id'])) ?>"><h2>Actualizar seguimiento</h2><?php if ($errors): ?><div class="error-list"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><div class="field"><label>Estado</label><select name="estado"><?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>" <?= $current['estado'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></div><div class="field"><label>Notas para el cliente</label><textarea name="notas_admin"><?= e($current['notas_admin'] ?? '') ?></textarea></div><button class="btn">Guardar cambios</button></form></div>
<?php else: ?>
<div class="panel-heading"><div><h1>Solicitudes</h1></div></div>
<div class="table-card"><div class="table-responsive"><table><thead><tr><th>Cliente</th><th>Proyecto</th><th>Tipo</th><th>Presupuesto</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead><tbody><?php if (!$items): ?><tr><td colspan="7" class="empty-state">No hay solicitudes.</td></tr><?php endif; ?><?php foreach ($items as $item): ?><tr><td><?= e($item['cliente']) ?></td><td><?= e($item['titulo']) ?></td><td><?= e($item['tipo_pagina']) ?></td><td><?= $item['presupuesto'] !== null ? format_money($item['presupuesto']) : '—' ?></td><td><span class="badge badge-<?= status_class($item['estado']) ?>"><?= e(ucfirst($item['estado'])) ?></span></td><td><?= format_date($item['created_at']) ?></td><td class="table-actions"><a class="btn btn-small btn-outline" href="<?= url('admin/solicitudes.php?action=view&t=' . encode_id((int)$item['id'])) ?>">Abrir</a><form method="post"><?= csrf_field() ?><input type="hidden" name="form_action" value="delete"><input type="hidden" name="token_id" value="<?= e(encode_id((int)$item['id'])) ?>"><button class="btn btn-small btn-danger" data-confirm="¿Eliminar esta solicitud?">Eliminar</button></form></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
