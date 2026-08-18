<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('administrador');
$pageTitle = 'Mensajes';
$adminId = (int)user()['id'];
$errors = [];
$clients = db()->query("SELECT u.id,u.nombre,u.email FROM usuarios u JOIN roles r ON r.id=u.role_id WHERE r.nombre='cliente' AND u.estado='activo' ORDER BY u.nombre")->fetchAll();
$selectedClientId = isset($_GET['cliente']) ? decode_id($_GET['cliente']) : null;
$selectedRequestId = isset($_GET['solicitud']) ? decode_id($_GET['solicitud']) : null;
if (($selectedClientId === null && isset($_GET['cliente'])) || ($selectedRequestId === null && isset($_GET['solicitud']))) abort_forbidden('Token de conversación alterado.');
if (request_is_post()) {
    verify_csrf();
    $clientId = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
    $requestId = filter_input(INPUT_POST, 'solicitud_id', FILTER_VALIDATE_INT) ?: null;
    $subject = clean_text($_POST['asunto'] ?? '', 180);
    $message = clean_multiline($_POST['mensaje'] ?? '', 4000);
    $checkClient = db()->prepare("SELECT u.id FROM usuarios u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND r.nombre='cliente' AND u.estado='activo'");
    $checkClient->execute([$clientId]);
    if (!$checkClient->fetch()) $errors[] = 'Selecciona un cliente válido.';
    if ($requestId) {
        $check = db()->prepare('SELECT id FROM solicitudes WHERE id=? AND usuario_id=?');
        $check->execute([$requestId,$clientId]);
        if (!$check->fetch()) $errors[] = 'La solicitud no pertenece al cliente seleccionado.';
    }
    if (mb_strlen($subject) < 3) $errors[] = 'Escribe un asunto.';
    if (mb_strlen($message) < 2) $errors[] = 'El mensaje no puede estar vacío.';
    if (!$errors) {
        db()->prepare('INSERT INTO mensajes(remitente_id,destinatario_id,solicitud_id,asunto,mensaje,estado,leido) VALUES(?,?,?,?,?,"enviado",0)')
            ->execute([$adminId,$clientId,$requestId,$subject,$message]);
        $sql = 'UPDATE mensajes SET estado="respondido" WHERE remitente_id=? AND destinatario_id=? AND estado IN ("enviado","leído")';
        $params = [$clientId,$adminId];
        if ($requestId) { $sql .= ' AND solicitud_id=?'; $params[] = $requestId; }
        else { $sql .= ' AND solicitud_id IS NULL'; }
        db()->prepare($sql)->execute($params);
        flash('success', 'Respuesta enviada al cliente.');
        redirect('admin/mensajes.php?cliente=' . encode_id((int)$clientId) . ($requestId ? '&solicitud=' . encode_id((int)$requestId) : ''));
    }
    $selectedClientId = $clientId ?: null;
    $selectedRequestId = $requestId;
}
$conversationRows = db()->query("SELECT
CASE WHEN m.remitente_id={$adminId} THEN m.destinatario_id ELSE m.remitente_id END cliente_id,
m.solicitud_id,MAX(m.created_at) ultima_fecha,
SUM(CASE WHEN m.destinatario_id={$adminId} AND m.leido=0 THEN 1 ELSE 0 END) no_leidos
FROM mensajes m
WHERE m.remitente_id={$adminId} OR m.destinatario_id={$adminId}
GROUP BY CASE WHEN m.remitente_id={$adminId} THEN m.destinatario_id ELSE m.remitente_id END,m.solicitud_id
ORDER BY ultima_fecha DESC")->fetchAll();
$conversations = [];
foreach ($conversationRows as $row) {
    $stmt = db()->prepare('SELECT u.nombre,u.email,s.titulo solicitud FROM usuarios u LEFT JOIN solicitudes s ON s.id=? WHERE u.id=?');
    $stmt->execute([$row['solicitud_id'],$row['cliente_id']]);
    $info = $stmt->fetch();
    if ($info) $conversations[] = array_merge($row,$info);
}
$requests = [];
$thread = [];
if ($selectedClientId) {
    $check = db()->prepare("SELECT u.id,u.nombre,u.email FROM usuarios u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND r.nombre='cliente'");
    $check->execute([$selectedClientId]);
    $selectedClient = $check->fetch();
    if (!$selectedClient) abort_forbidden('Cliente de conversación inválido.');
    $rs = db()->prepare('SELECT id,titulo FROM solicitudes WHERE usuario_id=? ORDER BY created_at DESC');
    $rs->execute([$selectedClientId]);
    $requests = $rs->fetchAll();
    if ($selectedRequestId) {
        $validRequest = false;
        foreach ($requests as $request) {
            if ((int)$request['id'] === $selectedRequestId) { $validRequest = true; break; }
        }
        if (!$validRequest) abort_forbidden('La solicitud no pertenece al cliente seleccionado.');
    }
    $sql = 'SELECT m.*,r.nombre remitente FROM mensajes m JOIN usuarios r ON r.id=m.remitente_id WHERE ((m.remitente_id=? AND m.destinatario_id=?) OR (m.remitente_id=? AND m.destinatario_id=?))';
    $params = [$adminId,$selectedClientId,$selectedClientId,$adminId];
    if ($selectedRequestId) { $sql .= ' AND m.solicitud_id=?'; $params[] = $selectedRequestId; }
    else { $sql .= ' AND m.solicitud_id IS NULL'; }
    $sql .= ' ORDER BY m.created_at ASC';
    $update = 'UPDATE mensajes SET leido=1,leido_at=NOW(),estado=IF(estado="enviado","leído",estado) WHERE remitente_id=? AND destinatario_id=? AND leido=0';
    $up = [$selectedClientId,$adminId];
    if ($selectedRequestId) { $update .= ' AND solicitud_id=?'; $up[] = $selectedRequestId; }
    else { $update .= ' AND solicitud_id IS NULL'; }
    db()->prepare($update)->execute($up);
    $ts = db()->prepare($sql); $ts->execute($params); $thread = $ts->fetchAll();
}
require dirname(__DIR__) . '/includes/admin_header.php';
?>
<div class="panel-heading"><div><h1>Mensajes</h1></div></div>
<div class="admin-messages-layout"><aside class="conversation-sidebar"><h2>Conversaciones</h2><?php if (!$conversations): ?><p class="help">Todavía no hay conversaciones.</p><?php endif; ?><?php foreach ($conversations as $conversation): ?><a class="conversation-link <?= $selectedClientId === (int)$conversation['cliente_id'] && $selectedRequestId === ($conversation['solicitud_id'] ? (int)$conversation['solicitud_id'] : null) ? 'active' : '' ?>" href="<?= url('admin/mensajes.php?cliente=' . encode_id((int)$conversation['cliente_id']) . ($conversation['solicitud_id'] ? '&solicitud=' . encode_id((int)$conversation['solicitud_id']) : '')) ?>"><strong><?= e($conversation['nombre']) ?></strong><span><?= e($conversation['solicitud'] ?: 'Conversación general') ?></span><?php if ((int)$conversation['no_leidos'] > 0): ?><b><?= (int)$conversation['no_leidos'] ?></b><?php endif; ?></a><?php endforeach; ?><hr><h3>Nueva conversación</h3><?php foreach ($clients as $client): ?><a class="conversation-link" href="<?= url('admin/mensajes.php?cliente=' . encode_id((int)$client['id'])) ?>"><strong><?= e($client['nombre']) ?></strong><span><?= e($client['email']) ?></span></a><?php endforeach; ?></aside>
<section class="conversation-panel"><?php if (!$selectedClientId): ?><div class="empty-state">Selecciona un cliente para leer o enviar mensajes.</div><?php else: ?><div class="conversation-title"><h2><?= e($selectedClient['nombre']) ?></h2><span><?= $selectedRequestId ? 'Conversación de solicitud' : 'Conversación general' ?></span></div><div class="thread-tabs"><a class="<?= $selectedRequestId === null ? 'active' : '' ?>" href="<?= url('admin/mensajes.php?cliente=' . encode_id((int)$selectedClientId)) ?>">General</a><?php foreach ($requests as $request): ?><a class="<?= $selectedRequestId === (int)$request['id'] ? 'active' : '' ?>" href="<?= url('admin/mensajes.php?cliente=' . encode_id((int)$selectedClientId) . '&solicitud=' . encode_id((int)$request['id'])) ?>"><?= e($request['titulo']) ?></a><?php endforeach; ?></div><div class="thread-messages"><?php if (!$thread): ?><div class="empty-state">No hay mensajes en esta conversación.</div><?php endif; ?><?php foreach ($thread as $item): ?><?php $mine = (int)$item['remitente_id'] === $adminId; ?><article class="message-row <?= $mine ? 'mine' : 'other' ?>"><div class="message-meta"><strong><?= $mine ? 'Administrador' : e($item['remitente']) ?></strong><span><?= format_date($item['created_at']) ?></span></div><h3><?= e($item['asunto']) ?></h3><p><?= nl2br(e($item['mensaje'])) ?></p><small>Estado: <?= e(ucfirst($item['estado'])) ?></small></article><?php endforeach; ?></div><form class="message-compose compact" method="post" data-validate><?= csrf_field() ?><input type="hidden" name="cliente_id" value="<?= (int)$selectedClientId ?>"><?php if ($errors): ?><div class="error-list"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><div class="field"><label>Solicitud relacionada</label><select name="solicitud_id"><option value="">Conversación general</option><?php foreach ($requests as $request): ?><option value="<?= (int)$request['id'] ?>" <?= $selectedRequestId === (int)$request['id'] ? 'selected' : '' ?>><?= e($request['titulo']) ?></option><?php endforeach; ?></select><small class="help">Para cambiar de hilo usa la lista de conversaciones o abre la solicitud.</small></div><div class="field"><label>Asunto</label><input name="asunto" maxlength="180" value="Respuesta de RootCode" required></div><div class="field"><label>Respuesta</label><textarea name="mensaje" maxlength="4000" required></textarea></div><button class="btn">Enviar respuesta</button></form><?php endif; ?></section></div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
