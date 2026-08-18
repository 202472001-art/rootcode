# Cambios de la versión simplificada

## Módulos eliminados

- Cotizaciones del cliente y del administrador.
- Administración de servicios.
- Administración de categorías.
- Administración de contenido.
- Administración de imágenes.
- Administración del chatbot.

Los archivos, rutas y enlaces correspondientes fueron retirados.

## Tablas eliminadas

- `cotizaciones`
- `servicios`
- `categorias`
- `contenido_sitio`
- `imagenes`
- `chatbot_respuestas`

## Tablas que permanecen

- `roles`
- `usuarios`
- `solicitudes`
- `mensajes`
- `portafolio`
- `contacto`
- `remember_tokens`
- `password_resets`
- `login_attempts`
- `security_logs`

## Adaptaciones realizadas antes de eliminar relaciones

- `solicitudes.servicio_id` fue sustituido por `tipo_pagina`.
- `portafolio.categoria_id` fue sustituido por `categoria` de texto.
- `contacto.servicio_id` fue sustituido por `tipo_servicio` de texto.
- `mensajes.cotizacion_id` fue eliminado.

## Mensajes

Los mensajes se almacenan con remitente, destinatario, solicitud opcional, asunto, contenido, fecha y estado. Al abrir un mensaje pasa a leído. Cuando la otra persona responde, el mensaje anterior pasa a respondido.

## Chatbot

No existe una tabla ni un CRUD administrativo. Las preguntas y respuestas se encuentran en `assets/js/chatbot.js`. Esto mantiene el proyecto sencillo y permite agregar nuevas respuestas copiando un elemento del arreglo.

## Ajuste de navegación del cliente

- Se eliminó `cliente/chatbot.php` y la interfaz “Asistente de preguntas frecuentes”.
- Se retiró la opción Chatbot del menú lateral.
- El chatbot se abre mediante el botón flotante circular.
- El inicio del cliente incluye tres bloques con título, instrucción y botón: Solicitudes, Mensajes y Chatbot.
- El botón del bloque Chatbot abre directamente la ventana flotante, sin cambiar de página.

## Eliminación del módulo Usuarios del administrador

- Se eliminó `admin/usuarios.php`.
- Se retiró Usuarios del menú lateral administrativo.
- Se eliminó la métrica de clientes del dashboard y se sustituyó por Contactos nuevos.
- No se eliminó la tabla `usuarios`, porque es necesaria para el registro, login, perfiles, solicitudes y mensajes.
- Esta actualización no requiere cambios en la base de datos.
