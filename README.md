# RootCode simplificado

Aplicación web académica desarrollada con PHP 8, MySQL, HTML, CSS y JavaScript.

## Módulos disponibles

### Sitio público
- Inicio rediseñado con estadísticas y carrusel.
- Servicios como contenido fijo, sin tabla administrativa.
- Portafolio consultado desde MySQL.
- Formulario de contacto.
- Registro, inicio de sesión y recuperación de contraseña.

### Administrador
- Dashboard.
- Solicitudes.
- Mensajes organizados por cliente o solicitud.
- Portafolio.
- Contactos.

El panel administrativo ya no incluye el módulo para crear, editar o eliminar usuarios. La tabla `usuarios` se conserva porque es indispensable para el registro, inicio de sesión, solicitudes, mensajes y perfiles.

### Cliente
- Inicio con accesos a Solicitudes, Mensajes y Chatbot.
- Solicitudes propias.
- Mensajes con el administrador.
- Perfil.
- Chatbot local mediante un botón flotante.

La antigua página independiente **Asistente de preguntas frecuentes** fue eliminada. El chatbot se abre únicamente desde el botón circular flotante de ayuda dentro del panel del cliente.

## Tablas que permanecen
`roles`, `usuarios`, `solicitudes`, `mensajes`, `portafolio`, `contacto`, `remember_tokens`, `password_resets`, `login_attempts`, `security_logs`.

## Tablas eliminadas
`cotizaciones`, `servicios`, `categorias`, `contenido_sitio`, `imagenes`, `chatbot_respuestas`.

## Instalación nueva
1. Copia `rootcode_app` dentro de `C:\xampp\htdocs`.
2. Enciende Apache y MySQL.
3. Importa `database/rootcode_xampp.sql` desde el nivel del servidor, o selecciona la base `rootcode` e importa `database/rootcode.sql`.
4. Abre `http://localhost/rootcode_app/`.

## Actualizar una instalación anterior
1. Realiza un respaldo de la base.
2. Elimina o renombra la carpeta anterior `C:\xampp\htdocs\rootcode_app`.
3. Copia la nueva carpeta `rootcode_app` completa. No la combines con la anterior, porque los archivos `admin/usuarios.php` y `cliente/chatbot.php` fueron eliminados.
4. No es necesario importar SQL para esta actualización visual y de navegación.
5. Recarga la aplicación con `Ctrl + F5`.

## Credenciales de demostración
- Administrador: `admin@rootcode.mx` / `AdminRoot2026!`
- Cliente: `cliente@rootcode.mx` / `ClienteRoot2026!`

## Mensajes
El cliente envía un formulario al administrador y puede relacionarlo con una solicitud. El administrador abre la conversación, lee los mensajes y responde. Cada registro muestra remitente, fecha, hora y estado: enviado, leído o respondido.

## Chatbot
Las respuestas se editan en `assets/js/chatbot.js`, dentro del arreglo `answers`. Cada elemento tiene palabras clave (`words`) y una respuesta (`answer`). El chatbot solo se carga en las páginas privadas del cliente y se abre como ventana flotante.

## SQL para Hostinger
Selecciona la base creada en hPanel y después importa `database/rootcode.sql`. Este archivo no intenta crear ni cambiar el nombre de la base.
