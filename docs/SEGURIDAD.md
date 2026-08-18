# Controles de seguridad implementados

## Autenticación

Las contraseñas se almacenan con `password_hash()` y se validan con `password_verify()`. El inicio de sesión registra intentos fallidos y bloquea temporalmente después de cinco intentos desde el mismo correo e IP en quince minutos.

## Autorización

Cada archivo administrativo exige el rol `administrador`. Cada archivo del cliente exige el rol `cliente`. Una sesión con rol incorrecto es destruida y el intento se almacena en `security_logs`.

## Propiedad de registros

Las operaciones del cliente siempre incluyen `usuario_id`. Aunque alguien copie o altere una URL, el servidor comprueba el propietario. Los identificadores se envían firmados con HMAC usando `APP_KEY`.

## Formularios

Todos los formularios que modifican datos utilizan un token CSRF. Las entradas se validan en PHP y también se revisan en JavaScript para mejorar la experiencia.

## Base de datos

PDO utiliza prepared statements y desactiva la emulación de consultas preparadas. Esto reduce el riesgo de inyección SQL.

## Salida HTML

La función `e()` aplica `htmlspecialchars()` antes de mostrar datos proporcionados por usuarios, reduciendo el riesgo de XSS almacenado o reflejado.

## Archivos

Las imágenes se validan por MIME real, tamaño máximo y extensión generada por el servidor. La carpeta `uploads` bloquea la ejecución de PHP y no permite listar archivos.
