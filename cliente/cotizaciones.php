<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('cliente');
$pageTitle = 'Mis cotizaciones';
$userId = (int)user()['id'];
$errors = [];
$action = clean_text($_GET['action'] ?? 'list', 20);
$services = db()->query('SELECT id, nombre FROM servicios WHERE activo = 1 ORDER BY nombre')->fetchAll();
$current = null;

$loadOwned = function (int $id) use ($userId): array {
    $stmt = db()->prepare('SELECT c.*, s.nombre AS servicio FROM cotizaciones c LEFT JOIN servicios s ON s.id = c.servicio_id WHERE c.id = ? AND c.usuario_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch();
    if (!$row) abort_forbidden('Intento de acceso a cotización ajena.');
    return $row;
};
if (isset($_GET['t'])) {
    $id = decode_id($_GET['t']);
    if (!$id) abort_forbidden('Token de cotización alterado.');
    $current = $loadOwned($id);
}

if (request_is_post()) {
    verify_csrf();
    $formAction = clean_text($_POST['form_action'] ?? '', 20);
    if (in_array($formAction, ['create','update'], true)) {
        $serviceId = filter_input(INPUT_POST, 'servicio_id', FILTER_VALIDATE_INT) ?: null;
        $title = clean_text($_POST['titulo'] ?? '', 160);
        $range = clean_text($_POST['rango_presupuesto'] ?? '', 100);
        $details = clean_multiline($_POST['detalles'] ?? '', 4000);
        if (mb_strlen($title) < 4) $errors[] = 'Escribe un título.';
        if (mb_strlen($details) < 15) $errors[] = 'Agrega más detalles para preparar la cotización.';
        if (!$errors) {
            if ($formAction === 'create') {
                db()->prepare('INSERT INTO cotizaciones (usuario_id, servicio_id, titulo, rango_presupuesto, detalles, estado) VALUES (?, ?, ?, ?, ?, "pendiente")')->execute([$userId, $serviceId, $title, $range ?: null, $details]);
                flash('success', 'Solicitud de cotización registrada.');
            } else {
                $id = decode_id($_POST['token_id'] ?? '');
                if (!$id) abort_forbidden('Token alterado al modificar cotización.');
                $owned = $loadOwned($id);
                if ($owned['estado'] !== 'pendiente') abort_forbidden('Intento de editar cotización no pendiente.');
                db()->prepare('UPDATE cotizaciones SET servicio_id=?, titulo=?, rango_presupuesto=?, detalles=?, updated_at=NOW() WHERE id=? AND usuario_id=? AND estado="pendiente"')->execute([$serviceId, $title, $range ?: null, $details, $id, $userId]);
                flash('success', 'Cotización actualizada.');
            }
            redirect('cliente/cotizaciones.php');
        }
        preserve_old_input($_POST);
        $action = $formAction === 'create' ? 'new' : 'edit';
    }
    if ($formAction === 'delete') {
        $id = decode_id($_POST['token_id'] ?? '');
        if (!$id) abort_forbidden('Token alterado al eliminar cotización.');
        $owned = $loadOwned($id);
        if ($owned['estado'] !== 'pendiente') abort_forbidden('Intento de eliminar cotización no pendiente.');
        db()->prepare('DELETE FROM cotizaciones WHERE id=? AND usuario_id=? AND estado="pendiente"')->execute([$id,$userId]);
        flash('success', 'Cotización eliminada.');
        redirect('cliente/cotizaciones.php');
    }
}
if ($action === 'list') {
    $stmt = db()->prepare('SELECT c.*, s.nombre AS servicio FROM cotizaciones c LEFT JOIN servicios s ON s.id=c.servicio_id WHERE c.usuario_id=? ORDER BY c.created_at DESC');
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
}
require dirname(__DIR__) . '/includes/client_header.php';
?>
<?php if (in_array($action,['new','edit'],true)): ?>
<div class="panel-heading"><div><h1><?= $action==='new'?'Solicitar cotización':'Editar cotización' ?></h1><p>Proporciona información suficiente para estimar el proyecto.</p></div><a class="btn btn-light" href="<?= url('cliente/cotizaciones.php') ?>">Cancelar</a></div><form class="form-card" method="post"><?= csrf_field() ?><input type="hidden" name="form_action" value="<?= $action==='new'?'create':'update' ?>"><?php if ($current): ?><input type="hidden" name="token_id" value="<?= e(encode_id((int)$current['id'])) ?>"><?php endif; ?><?php if($errors):?><div class="error-list"><ul><?php foreach($errors as $error):?><li><?=e($error)?></li><?php endforeach;?></ul></div><?php endif; ?><div class="form-grid"><div class="field full"><label>Título</label><input name="titulo" value="<?= old('titulo',$current['titulo']??'') ?>" required></div><div class="field"><label>Servicio</label><select name="servicio_id"><option value="">Personalizado</option><?php $sel=(string)($_SESSION['_old']['servicio_id']??$current['servicio_id']??'');foreach($services as $s):?><option value="<?= (int)$s['id'] ?>" <?=$sel===(string)$s['id']?'selected':''?>><?=e($s['nombre'])?></option><?php endforeach;?></select></div><div class="field"><label>Rango de presupuesto</label><input name="rango_presupuesto" value="<?= old('rango_presupuesto',$current['rango_presupuesto']??'') ?>" placeholder="$5,000 a $10,000"></div><div class="field full"><label>Detalles</label><textarea name="detalles" required><?= old('detalles',$current['detalles']??'') ?></textarea></div></div><button class="btn" type="submit">Guardar</button></form>
<?php elseif ($action==='view'&&$current): ?>
<div class="panel-heading"><div><h1><?=e($current['titulo'])?></h1><p>Propuesta y estado de la cotización.</p></div><a class="btn btn-light" href="<?=url('cliente/cotizaciones.php')?>">Volver</a></div><div class="card"><div class="detail-grid"><div class="detail-item"><strong>Servicio</strong><?=e($current['servicio']??'Personalizado')?></div><div class="detail-item"><strong>Estado</strong><span class="badge badge-<?=status_class($current['estado'])?>"><?=e(ucfirst($current['estado']))?></span></div><div class="detail-item"><strong>Presupuesto indicado</strong><?=e($current['rango_presupuesto']??'Por definir')?></div><div class="detail-item"><strong>Monto propuesto</strong><?=$current['monto_propuesto']!==null?format_money($current['monto_propuesto']):'Pendiente de propuesta'?></div><div class="detail-item" style="grid-column:1/-1"><strong>Detalles</strong><?=nl2br(e($current['detalles']))?></div><?php if($current['propuesta_admin']):?><div class="detail-item" style="grid-column:1/-1"><strong>Propuesta de RootCode</strong><?=nl2br(e($current['propuesta_admin']))?></div><?php endif;?></div><?php if($current['estado']==='pendiente'):?><div class="actions"><a class="btn btn-outline" href="<?=url('cliente/cotizaciones.php?action=edit&t='.encode_id((int)$current['id']))?>">Editar</a><form method="post"><?=csrf_field()?><input type="hidden" name="form_action" value="delete"><input type="hidden" name="token_id" value="<?=e(encode_id((int)$current['id']))?>"><button class="btn btn-danger" data-confirm="¿Eliminar esta cotización?">Eliminar</button></form></div><?php endif;?></div>
<?php else: ?>
<div class="panel-heading"><div><h1>Mis cotizaciones</h1><p>Solicita y consulta propuestas económicas.</p></div><a class="btn" href="<?=url('cliente/cotizaciones.php?action=new')?>">Nueva cotización</a></div><div class="table-card"><div class="table-responsive"><table><thead><tr><th>Título</th><th>Servicio</th><th>Propuesta</th><th>Estado</th><th>Fecha</th><th></th></tr></thead><tbody><?php if(!$items):?><tr><td colspan="6" class="empty-state">No hay cotizaciones.</td></tr><?php endif;?><?php foreach($items as $item):?><tr><td><?=e($item['titulo'])?></td><td><?=e($item['servicio']??'Personalizado')?></td><td><?=$item['monto_propuesto']!==null?format_money($item['monto_propuesto']):'Pendiente'?></td><td><span class="badge badge-<?=status_class($item['estado'])?>"><?=e(ucfirst($item['estado']))?></span></td><td><?=format_date($item['created_at'])?></td><td><a class="btn btn-small btn-outline" href="<?=url('cliente/cotizaciones.php?action=view&t='.encode_id((int)$item['id']))?>">Ver</a></td></tr><?php endforeach;?></tbody></table></div></div>
<?php endif; ?>
<?php clear_old_input();require dirname(__DIR__) . '/includes/client_footer.php'; ?>
