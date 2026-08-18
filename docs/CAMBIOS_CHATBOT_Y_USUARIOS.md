# Cambios: chatbot y usuarios

## Archivos eliminados

- `cliente/chatbot.php`
- `admin/usuarios.php`

## Archivos modificados

- `cliente/dashboard.php`
- `admin/dashboard.php`
- `includes/client_header.php`
- `includes/client_footer.php`
- `includes/admin_header.php`
- `assets/js/chatbot.js`
- `assets/css/styles.css`
- `README.md`
- `docs/MATRIZ_REQUISITOS.md`
- `docs/CAMBIOS_VERSION_SIMPLIFICADA.md`

## Funcionamiento actual del chatbot

El chatbot aparece únicamente en el panel del cliente como un botón circular flotante con el símbolo `?`. Al pulsarlo se abre la ventana de conversación. Se eliminó cualquier bloque o botón adicional del inicio del cliente.

Las respuestas se modifican en `assets/js/chatbot.js`, dentro del arreglo `answers`.

## Base de datos

No se elimina ninguna tabla en esta actualización. La tabla `usuarios` debe permanecer porque almacena las cuentas que permiten el registro, inicio de sesión, perfiles y la relación con solicitudes y mensajes. Solo se eliminó el CRUD administrativo de usuarios.
