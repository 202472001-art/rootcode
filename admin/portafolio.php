<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('administrador');
$pageTitle = 'Portafolio';
$action = clean_text($_GET['action'] ?? 'list', 20);
$errors = [];
$current = null;
$load = function (int $id): array {
    $stmt = db()->prepare('SELECT * FROM portafolio WHERE id=?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { http_response_code(404); exit('Proyecto no encontrado.'); }
    return $row;
};
if (isset($_GET['t'])) {
    $id = decode_id($_GET['t']);
    if (!$id) abort_forbidden('Token de portafolio alterado.');
    $current = $load($id);
}
if (request_is_post()) {
    verify_csrf();
    $formAction = clean_text($_POST['form_action'] ?? '', 20);
    if (in_array($formAction, ['create','update'], true)) {
        $title = clean_text($_POST['titulo'] ?? '', 160);
        $category = clean_text($_POST['categoria'] ?? '', 100);
        $summary = clean_text($_POST['resumen'] ?? '', 300);
        $description = clean_multiline($_POST['descripcion'] ?? '', 5000);
        $featured = isset($_POST['destacado']) ? 1 : 0;
        $active = isset($_POST['activo']) ? 1 : 0;
        if (mb_strlen($title) < 3) $errors[] = 'Escribe un título válido.';
        if (mb_strlen($summary) < 10) $errors[] = 'Agrega un resumen de al menos 10 caracteres.';
        $id = $formAction === 'update' ? decode_id($_POST['token_id'] ?? '') : null;
        if ($formAction === 'update' && !$id) abort_forbidden('Token alterado.');
        try { $newImage = upload_image($_FILES['imagen'] ?? [], 'portfolio'); } catch (RuntimeException $ex) { $errors[] = $ex->getMessage(); $newImage = null; }
        if (!$errors) {
            if ($formAction === 'create') {
                db()->prepare('INSERT INTO portafolio(categoria,titulo,slug,resumen,descripcion,imagen,destacado,activo) VALUES(?,?,?,?,?,?,?,?)')
                    ->execute([$category ?: null,$title,slugify($title) . '-' . bin2hex(random_bytes(2)),$summary,$description ?: null,$newImage,$featured,$active]);
                flash('success', 'Proyecto agregado.');
            } else {
                $old = $load($id);
                $image = $newImage ?: $old['imagen'];
                db()->prepare('UPDATE portafolio SET categoria=?,titulo=?,resumen=?,descripcion=?,imagen=?,destacado=?,activo=?,updated_at=NOW() WHERE id=?')
                    ->execute([$category ?: null,$title,$summary,$description ?: null,$image,$featured,$active,$id]);
                if ($newImage) delete_uploaded_file($old['imagen']);
                flash('success', 'Proyecto actualizado.');
            }
            redirect('admin/portafolio.php');
        }
        preserve_old_input($_POST);
        $action = $formAction === 'create' ? 'new' : 'edit';
    }
    if ($formAction === 'delete') {
        $id = decode_id($_POST['token_id'] ?? '');
        if (!$id) abort_forbidden('Token alterado.');
        $old = $load($id);
        db()->prepare('DELETE FROM portafolio WHERE id=?')->execute([$id]);
        delete_uploaded_file($old['imagen']);
        flash('success', 'Proyecto eliminado.');
        redirect('admin/portafolio.php');
    }
}
if ($action === 'list') $items = db()->query('SELECT * FROM portafolio ORDER BY created_at DESC')->fetchAll();
require dirname(__DIR__) . '/includes/admin_header.php';
?>
<?php if (in_array($action, ['new','edit'], true)): ?>
<div class="panel-heading"><div><h1><?= $action === 'new' ? 'Agregar proyecto' : 'Editar proyecto' ?></h1><p>Información utilizada en el portafolio público.</p></div><a class="btn btn-light" href="<?= url('admin/portafolio.php') ?>">Cancelar</a></div>
<form class="form-card panel-form" method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="form_action" value="<?= $action === 'new' ? 'create' : 'update' ?>"><?php if ($current): ?><input type="hidden" name="token_id" value="<?= e(encode_id((int)$current['id'])) ?>"><?php endif; ?><?php if ($errors): ?><div class="error-list"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><div class="form-grid"><div class="field full"><label>Título</label><input name="titulo" value="<?= old('titulo', $current['titulo'] ?? '') ?>" required></div><div class="field"><label>Categoría escrita</label><input name="categoria" maxlength="100" value="<?= old('categoria', $current['categoria'] ?? '') ?>" placeholder="Ej. Sistema web"></div><div class="field full"><label>Resumen</label><input name="resumen" maxlength="300" value="<?= old('resumen', $current['resumen'] ?? '') ?>" required></div><div class="field full"><label>Descripción</label><textarea name="descripcion"><?= old('descripcion', $current['descripcion'] ?? '') ?></textarea></div><div class="field full"><label>Imagen JPG, PNG o WEBP (máximo 5 MB)</label><input type="file" name="imagen" accept="image/jpeg,image/png,image/webp"><?php if ($current && $current['imagen']): ?><img src="<?= url($current['imagen']) ?>" alt="Vista actual" class="preview-image"><?php endif; ?></div></div><label class="check-line"><input type="checkbox" name="destacado" <?= ($current['destacado'] ?? 0) ? 'checked' : '' ?>> Destacado</label><label class="check-line"><input type="checkbox" name="activo" <?= !$current || $current['activo'] ? 'checked' : '' ?>> Activo</label><div class="actions"><button class="btn">Guardar proyecto</button></div></form>
<?php else: ?>
<div class="panel-heading"><div><h1>Portafolio</h1></div><a class="btn" href="<?= url('admin/portafolio.php?action=new') ?>">Agregar proyecto</a></div>
<div class="table-card"><div class="table-responsive"><table><thead><tr><th>Proyecto</th><th>Categoría</th><th>Destacado</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead><tbody><?php if (!$items): ?><tr><td colspan="6" class="empty-state">No hay proyectos.</td></tr><?php endif; ?><?php foreach ($items as $item): ?><tr><td><strong><?= e($item['titulo']) ?></strong><br><small><?= e($item['resumen']) ?></small></td><td><?= e($item['categoria'] ?: '—') ?></td><td><?= $item['destacado'] ? 'Sí' : 'No' ?></td><td><?= $item['activo'] ? 'Activo' : 'Inactivo' ?></td><td><?= format_date($item['created_at']) ?></td><td class="table-actions"><a class="btn btn-small btn-light" href="<?= url('admin/portafolio.php?action=edit&t=' . encode_id((int)$item['id'])) ?>">Editar</a><form method="post"><?= csrf_field() ?><input type="hidden" name="form_action" value="delete"><input type="hidden" name="token_id" value="<?= e(encode_id((int)$item['id'])) ?>"><button class="btn btn-small btn-danger" data-confirm="¿Eliminar este proyecto?">Eliminar</button></form></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php endif; ?>
<?php clear_old_input(); require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
