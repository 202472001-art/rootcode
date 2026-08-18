<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('cliente');
$pageTitle = 'Solicitudes';
$userId = (int)user()['id'];
$errors = [];
$action = clean_text($_GET['action'] ?? 'list', 20);
$currentItem = null;
$typeOptions = ['Página web corporativa','Catálogo o tienda en línea','Sistema web personalizado','Landing page','Mantenimiento web','Hosting y dominio','Proyecto personalizado'];
$loadOwned = function (int $id) use ($userId): array {
    $stmt = db()->prepare('SELECT * FROM solicitudes WHERE id=? AND usuario_id=? LIMIT 1');
    $stmt->execute([$id,$userId]);
    $item = $stmt->fetch();
    if (!$item) abort_forbidden('Intento de acceso a una solicitud ajena o inexistente.');
    return $item;
};
if (isset($_GET['t'])) {
    $id = decode_id($_GET['t']);
    if (!$id) abort_forbidden('Identificador de solicitud alterado.');
    $currentItem = $loadOwned($id);
}
if (request_is_post()) {
    verify_csrf();
    $formAction = clean_text($_POST['form_action'] ?? '', 20);
    if (in_array($formAction, ['create','update'], true)) {
        $title = clean_text($_POST['titulo'] ?? '', 160);
        $type = clean_text($_POST['tipo_pagina'] ?? '', 120);
        $budgetRaw = trim((string)($_POST['presupuesto'] ?? ''));
        $budget = $budgetRaw === '' ? null : filter_var($budgetRaw, FILTER_VALIDATE_FLOAT);
        $description = clean_multiline($_POST['descripcion'] ?? '', 5000);
        $date = clean_text($_POST['fecha_deseada'] ?? '', 10) ?: null;
        if (mb_strlen($title) < 3) $errors[] = 'Escribe un nombre para el proyecto.';
        if (!in_array($type, $typeOptions, true)) $errors[] = 'Selecciona un tipo de proyecto.';
        if ($budgetRaw !== '' && ($budget === false || $budget < 0)) $errors[] = 'El presupuesto no es válido.';
        if (mb_strlen($description) < 10) $errors[] = 'Describe el proyecto con al menos 10 caracteres.';
        if (!$errors) {
            if ($formAction === 'create') {
                db()->prepare('INSERT INTO solicitudes(usuario_id,titulo,tipo_pagina,presupuesto,descripcion,fecha_deseada,estado) VALUES(?,?,?,?,?,?,"pendiente")')
                    ->execute([$userId,$title,$type,$budget,$description,$date]);
                flash('success', 'Solicitud creada correctamente.');
            } else {
                $id = decode_id($_POST['token_id'] ?? '');
                if (!$id) abort_forbidden('Identificador alterado al editar.');
                $owned = $loadOwned($id);
                if ($owned['estado'] !== 'pendiente') abort_forbidden('Solo pueden editarse solicitudes pendientes.');
                db()->prepare('UPDATE solicitudes SET titulo=?,tipo_pagina=?,presupuesto=?,descripcion=?,fecha_deseada=?,updated_at=NOW() WHERE id=? AND usuario_id=? AND estado="pendiente"')
                    ->execute([$title,$type,$budget,$description,$date,$id,$userId]);
                flash('success', 'Solicitud actualizada.');
            }
            clear_old_input();
            redirect('cliente/solicitudes.php');
        }
        preserve_old_input($_POST);
        $action = $formAction === 'create' ? 'new' : 'edit';
    }
    if ($formAction === 'delete') {
        $id = decode_id($_POST['token_id'] ?? '');
        if (!$id) abort_forbidden('Identificador alterado al eliminar.');
        $owned = $loadOwned($id);
        if ($owned['estado'] !== 'pendiente') abort_forbidden('Solo pueden eliminarse solicitudes pendientes.');
        db()->prepare('DELETE FROM solicitudes WHERE id=? AND usuario_id=? AND estado="pendiente"')->execute([$id,$userId]);
        flash('success', 'Solicitud eliminada.');
        redirect('cliente/solicitudes.php');
    }
}
if ($action === 'list') {
    $stmt = db()->prepare('SELECT * FROM solicitudes WHERE usuario_id=? ORDER BY created_at DESC');
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
}
require dirname(__DIR__) . '/includes/client_header.php';
?>
<?php if (in_array($action, ['new','edit'], true)): ?>
<div class="panel-heading"><div><h1><?= $action === 'new' ? 'Nueva solicitud' : 'Editar solicitud' ?></h1><p>Completa la información principal del proyecto.</p></div><a class="btn btn-light" href="<?= url('cliente/solicitudes.php') ?>">Cancelar</a></div>
<form class="form-card panel-form" method="post" data-validate><?= csrf_field() ?><input type="hidden" name="form_action" value="<?= $action === 'new' ? 'create' : 'update' ?>"><?php if ($currentItem): ?><input type="hidden" name="token_id" value="<?= e(encode_id((int)$currentItem['id'])) ?>"><?php endif; ?>
<?php if ($errors): ?><div class="error-list"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="form-grid"><div class="field full"><label for="titulo">Nombre del proyecto</label><input id="titulo" name="titulo" maxlength="160" value="<?= old('titulo', $currentItem['titulo'] ?? '') ?>" required></div><div class="field"><label for="tipo_pagina">Tipo de página o sistema</label><select id="tipo_pagina" name="tipo_pagina" required><option value="">Selecciona</option><?php $selectedType = $_SESSION['_old']['tipo_pagina'] ?? $currentItem['tipo_pagina'] ?? urldecode($_GET['type'] ?? ''); foreach ($typeOptions as $option): ?><option <?= $selectedType === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div><div class="field"><label for="presupuesto">Presupuesto estimado (MXN)</label><input id="presupuesto" name="presupuesto" type="number" min="0" step="0.01" value="<?= old('presupuesto', isset($currentItem['presupuesto']) ? (string)$currentItem['presupuesto'] : '') ?>"></div><div class="field"><label for="fecha_deseada">Fecha deseada</label><input id="fecha_deseada" name="fecha_deseada" type="date" value="<?= old('fecha_deseada', $currentItem['fecha_deseada'] ?? '') ?>"></div><div class="field full"><label for="descripcion">Descripción del proyecto</label><textarea id="descripcion" name="descripcion" maxlength="5000" required><?= old('descripcion', $currentItem['descripcion'] ?? '') ?></textarea></div></div><button class="btn" type="submit">Guardar solicitud</button></form>
<?php elseif ($action === 'view' && $currentItem): ?>
<div class="panel-heading"><div><h1><?= e($currentItem['titulo']) ?></h1><p>Detalle de tu solicitud.</p></div><a class="btn btn-light" href="<?= url('cliente/solicitudes.php') ?>">Volver</a></div>
<div class="detail-card"><div class="detail-grid"><div class="detail-item"><strong>Tipo</strong><?= e($currentItem['tipo_pagina']) ?></div><div class="detail-item"><strong>Estado</strong><span class="badge badge-<?= status_class($currentItem['estado']) ?>"><?= e($currentItem['estado']) ?></span></div><div class="detail-item"><strong>Presupuesto</strong><?= $currentItem['presupuesto'] !== null ? format_money($currentItem['presupuesto']) : 'No indicado' ?></div><div class="detail-item"><strong>Fecha deseada</strong><?= format_date($currentItem['fecha_deseada'], false) ?></div><div class="detail-item full"><strong>Descripción</strong><?= nl2br(e($currentItem['descripcion'])) ?></div><div class="detail-item full"><strong>Notas del administrador</strong><?= nl2br(e($currentItem['notas_admin'] ?: 'Todavía no hay notas.')) ?></div></div><div class="actions"><a class="btn" href="<?= url('cliente/mensajes.php?solicitud=' . encode_id((int)$currentItem['id'])) ?>">Enviar mensaje</a><?php if ($currentItem['estado'] === 'pendiente'): ?><a class="btn btn-outline" href="<?= url('cliente/solicitudes.php?action=edit&t=' . encode_id((int)$currentItem['id'])) ?>">Editar</a><form method="post"><?= csrf_field() ?><input type="hidden" name="form_action" value="delete"><input type="hidden" name="token_id" value="<?= e(encode_id((int)$currentItem['id'])) ?>"><button class="btn btn-danger" data-confirm="¿Eliminar esta solicitud?">Eliminar</button></form><?php endif; ?></div></div>
<?php else: ?>
<div class="panel-heading"><div><h1>Mis solicitudes</h1></div><a class="btn" href="<?= url('cliente/solicitudes.php?action=new') ?>">Nueva solicitud</a></div>
<div class="table-card"><div class="table-responsive"><table><thead><tr><th>Proyecto</th><th>Tipo</th><th>Presupuesto</th><th>Estado</th><th>Fecha</th><th></th></tr></thead><tbody><?php if (!$items): ?><tr><td colspan="6" class="empty-state">No has creado solicitudes.</td></tr><?php endif; ?><?php foreach ($items as $item): ?><tr><td><?= e($item['titulo']) ?></td><td><?= e($item['tipo_pagina']) ?></td><td><?= $item['presupuesto'] !== null ? format_money($item['presupuesto']) : '—' ?></td><td><span class="badge badge-<?= status_class($item['estado']) ?>"><?= e(ucfirst($item['estado'])) ?></span></td><td><?= format_date($item['created_at']) ?></td><td><a class="btn btn-small btn-outline" href="<?= url('cliente/solicitudes.php?action=view&t=' . encode_id((int)$item['id'])) ?>">Ver</a></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php endif; ?>
<?php clear_old_input(); require dirname(__DIR__) . '/includes/client_footer.php'; ?>
