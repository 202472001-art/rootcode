<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('cliente');
$pageTitle = 'Mi perfil';
$userId = (int)user()['id'];
$errors = [];
$stmt = db()->prepare('SELECT * FROM usuarios WHERE id=? LIMIT 1');$stmt->execute([$userId]);$account=$stmt->fetch();

if(request_is_post()){
    verify_csrf();
    $formAction=clean_text($_POST['form_action']??'profile',20);
    if($formAction==='profile'){
        $nombre=clean_text($_POST['nombre']??'',120);$email=mb_strtolower(clean_text($_POST['email']??'',180));$telefono=clean_text($_POST['telefono']??'',30);$empresa=clean_text($_POST['empresa']??'',150);
        if(mb_strlen($nombre)<3)$errors[]='Escribe un nombre válido.';if(!valid_email($email))$errors[]='Escribe un correo válido.';
        $check=db()->prepare('SELECT id FROM usuarios WHERE email=? AND id<>? LIMIT 1');$check->execute([$email,$userId]);if($check->fetch())$errors[]='Ese correo ya está registrado.';
        if(!$errors){db()->prepare('UPDATE usuarios SET nombre=?,email=?,telefono=?,empresa=?,updated_at=NOW() WHERE id=?')->execute([$nombre,$email,$telefono?:null,$empresa?:null,$userId]);$_SESSION['user']['nombre']=$nombre;$_SESSION['user']['email']=$email;flash('success','Perfil actualizado.');redirect('cliente/perfil.php');}
    }
    if($formAction==='password'){
        $current=(string)($_POST['current_password']??'');$new=(string)($_POST['new_password']??'');$confirmation=(string)($_POST['password_confirmation']??'');
        if(!password_verify($current,$account['password_hash']))$errors[]='La contraseña actual es incorrecta.';if(strlen($new)<8||!preg_match('/[A-Z]/',$new)||!preg_match('/[a-z]/',$new)||!preg_match('/\d/',$new))$errors[]='La nueva contraseña no cumple los requisitos.';if($new!==$confirmation)$errors[]='Las contraseñas nuevas no coinciden.';
        if(!$errors){db()->prepare('UPDATE usuarios SET password_hash=?,updated_at=NOW() WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),$userId]);db()->prepare('DELETE FROM remember_tokens WHERE user_id=?')->execute([$userId]);flash('success','Contraseña actualizada.');redirect('cliente/perfil.php');}
    }
}
require dirname(__DIR__) . '/includes/client_header.php';
?>
<div class="panel-heading"><div><h1>Mi perfil</h1></div></div><?php if($errors):?><div class="error-list"><ul><?php foreach($errors as $error):?><li><?=e($error)?></li><?php endforeach;?></ul></div><?php endif;?><div class="grid-2"><form class="card" method="post"><?=csrf_field()?><input type="hidden" name="form_action" value="profile"><h2>Datos personales</h2><div class="field"><label>Nombre</label><input name="nombre" value="<?=e($account['nombre'])?>" required></div><div class="field"><label>Correo</label><input name="email" type="email" value="<?=e($account['email'])?>" required></div><div class="field"><label>Teléfono</label><input name="telefono" value="<?=e($account['telefono'])?>"></div><div class="field"><label>Empresa</label><input name="empresa" value="<?=e($account['empresa'])?>"></div><button class="btn" type="submit" style="margin-top:18px">Guardar perfil</button></form><form class="card" method="post"><?=csrf_field()?><input type="hidden" name="form_action" value="password"><h2>Cambiar contraseña</h2><div class="field"><label>Contraseña actual</label><input name="current_password" type="password" required></div><div class="field"><label>Nueva contraseña</label><input name="new_password" type="password" required></div><div class="field"><label>Confirmar nueva contraseña</label><input name="password_confirmation" type="password" required></div><p class="help">Mínimo 8 caracteres, mayúscula, minúscula y número.</p><button class="btn" type="submit">Actualizar contraseña</button></form></div>
<?php require dirname(__DIR__) . '/includes/client_footer.php'; ?>
