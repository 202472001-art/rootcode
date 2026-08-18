<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('cliente');
$pageTitle = 'Mensajes';
$userId = (int)user()['id'];
$errors = [];
$preselectedRequestId = isset($_GET['solicitud']) ? decode_id($_GET['solicitud']) : null;
if (isset($_GET['solicitud']) && !$preselectedRequestId) abort_forbidden('Token de solicitud alterado.');
$adminStmt = db()->query("SELECT u.id,u.nombre FROM usuarios u JOIN roles r ON r.id=u.role_id WHERE r.nombre='administrador' AND u.estado='activo' ORDER BY u.id LIMIT 1");
$admin = $adminStmt->fetch();
if (!$admin) { http_response_code(503); exit('No existe un administrador activo para recibir mensajes.'); }
$requestsStmt = db()->prepare('SELECT id,titulo FROM solicitudes WHERE usuario_id=? ORDER BY created_at DESC');
$requestsStmt->execute([$userId]);
$requests = $requestsStmt->fetchAll();
if ($preselectedRequestId) {
    $owned = false;
    foreach ($requests as $request) { if ((int)$request['id'] === $preselectedRequestId) { $owned = true; break; } }
    if (!$owned) abort_forbidden('La solicitud seleccionada no pertenece al cliente.');
}
if (request_is_post()) {
    verify_csrf();
    $subject = clean_text($_POST['asunto'] ?? '', 180);
    $message = clean_multiline($_POST['mensaje'] ?? '', 4000);
    $requestId = filter_input(INPUT_POST, 'solicitud_id', FILTER_VALIDATE_INT) ?: null;
    if (mb_strlen($subject) < 3) $errors[] = 'Escribe un asunto.';
    if (mb_strlen($message) < 2) $errors[] = 'El mensaje no puede estar vacío.';
    if ($requestId) {
        $check = db()->prepare('SELECT id FROM solicitudes WHERE id=? AND usuario_id=?');
        $check->execute([$requestId,$userId]);
        if (!$check->fetch()) abort_forbidden('Intento de relacionar un mensaje con una solicitud ajena.');
    }
    if (!$errors) {
        db()->prepare('INSERT INTO mensajes(remitente_id,destinatario_id,solicitud_id,asunto,mensaje,estado,leido) VALUES(?,?,?,?,?,"enviado",0)')
            ->execute([$userId,$admin['id'],$requestId,$subject,$message]);
        $sql = 'UPDATE mensajes SET estado="respondido" WHERE remitente_id=? AND destinatario_id=? AND estado IN ("enviado","leído")';
        $params = [$admin['id'],$userId];
        if ($requestId) { $sql .= ' AND solicitud_id=?'; $params[] = $requestId; }
        else { $sql .= ' AND solicitud_id IS NULL'; }
        db()->prepare($sql)->execute($params);
        flash('success', 'Mensaje enviado al administrador.');
        redirect('cliente/mensajes.php');
    }
}
db()->prepare('UPDATE mensajes SET leido=1,leido_at=NOW(),estado=IF(estado="enviado","leído",estado) WHERE destinatario_id=? AND leido=0')->execute([$userId]);
$stmt = db()->prepare('SELECT m.*,r.nombre remitente,d.nombre destinatario,s.titulo solicitud FROM mensajes m JOIN usuarios r ON r.id=m.remitente_id JOIN usuarios d ON d.id=m.destinatario_id LEFT JOIN solicitudes s ON s.id=m.solicitud_id WHERE m.remitente_id=? OR m.destinatario_id=? ORDER BY COALESCE(m.solicitud_id,0),m.created_at ASC');
$stmt->execute([$userId,$userId]);
$messages = $stmt->fetchAll();
$groups = [];
foreach ($messages as $message) {
    $key = $message['solicitud_id'] ? 's' . $message['solicitud_id'] : 'general';
    if (!isset($groups[$key])) $groups[$key] = ['title'=>$message['solicitud'] ?: 'Conversación general','items'=>[]];
    $groups[$key]['items'][] = $message;
}
require dirname(__DIR__) . '/includes/client_header.php';
?>
<div class="panel-heading"><div><h1>Mensajes</h1></div></div>
<div class="messages-layout"><form class="message-compose" method="post" data-validate><?= csrf_field() ?><h2>Nuevo mensaje</h2><?php if ($errors): ?><div class="error-list"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><div class="field"><label>Relacionar con solicitud</label><select name="solicitud_id"><option value="">Conversación general</option><?php foreach ($requests as $request): ?><option value="<?= (int)$request['id'] ?>" <?= $preselectedRequestId === (int)$request['id'] ? 'selected' : '' ?>><?= e($request['titulo']) ?></option><?php endforeach; ?></select></div><div class="field"><label>Asunto</label><input name="asunto" maxlength="180" required></div><div class="field"><label>Mensaje</label><textarea name="mensaje" maxlength="4000" required></textarea></div><button class="btn">Enviar</button></form>
<div class="conversation-list"><?php if (!$groups): ?><div class="empty-state card">Todavía no hay conversaciones.</div><?php endif; ?><?php foreach ($groups as $group): ?><section class="conversation"><h2><?= e($group['title']) ?></h2><?php foreach ($group['items'] as $item): ?><?php $mine = (int)$item['remitente_id'] === $userId; ?><article class="message-row <?= $mine ? 'mine' : 'other' ?>"><div class="message-meta"><strong><?= $mine ? 'Tú' : e($item['remitente']) ?></strong><span><?= format_date($item['created_at']) ?></span></div><h3><?= e($item['asunto']) ?></h3><p><?= nl2br(e($item['mensaje'])) ?></p><small>Estado: <?= e(ucfirst($item['estado'])) ?></small></article><?php endforeach; ?></section><?php endforeach; ?></div></div>
<?php require dirname(__DIR__) . '/includes/client_footer.php'; ?>
