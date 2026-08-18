<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('administrador');
$pageTitle = 'Contactos';
$statuses = ['nuevo','en revisión','respondido','cerrado'];
if (request_is_post()) {
    verify_csrf();
    $formAction = clean_text($_POST['form_action'] ?? '', 20);
    $id = decode_id($_POST['token_id'] ?? '');
    if (!$id) abort_forbidden('Token de contacto alterado.');
    if ($formAction === 'status') {
        $status = clean_text($_POST['estado'] ?? '', 30);
        if (!in_array($status, $statuses, true)) abort_forbidden('Estado de contacto inválido.');
        db()->prepare('UPDATE contacto SET estado=?,updated_at=NOW() WHERE id=?')->execute([$status,$id]);
        flash('success', 'Estado actualizado.');
    } elseif ($formAction === 'delete') {
        db()->prepare('DELETE FROM contacto WHERE id=?')->execute([$id]);
        flash('success', 'Contacto eliminado.');
    }
    redirect('admin/contactos.php');
}
$items = db()->query('SELECT * FROM contacto ORDER BY created_at DESC')->fetchAll();
require dirname(__DIR__) . '/includes/admin_header.php';
?>
<div class="panel-heading"><div><h1>Contactos</h1><p>Mensajes recibidos desde el formulario público.</p></div></div>
<div class="table-card"><div class="table-responsive"><table><thead><tr><th>Persona</th><th>Servicio</th><th>Mensaje</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead><tbody><?php if (!$items): ?><tr><td colspan="6" class="empty-state">No hay contactos.</td></tr><?php endif; ?><?php foreach ($items as $item): ?><tr><td><strong><?= e($item['nombre']) ?></strong><br><a href="mailto:<?= e($item['email']) ?>"><?= e($item['email']) ?></a><br><small><?= e($item['telefono'] ?: '') ?></small></td><td><?= e($item['tipo_servicio'] ?: 'No indicado') ?><br><small><?= e($item['presupuesto'] ?: '') ?></small></td><td><?= nl2br(e(mb_strimwidth($item['mensaje'], 0, 180, '…'))) ?></td><td><form class="inline-form" method="post"><?= csrf_field() ?><input type="hidden" name="form_action" value="status"><input type="hidden" name="token_id" value="<?= e(encode_id((int)$item['id'])) ?>"><select name="estado"><?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>" <?= $item['estado'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select><button class="btn btn-small">Guardar</button></form></td><td><?= format_date($item['created_at']) ?></td><td><form method="post"><?= csrf_field() ?><input type="hidden" name="form_action" value="delete"><input type="hidden" name="token_id" value="<?= e(encode_id((int)$item['id'])) ?>"><button class="btn btn-small btn-danger" data-confirm="¿Eliminar este contacto?">Eliminar</button></form></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
