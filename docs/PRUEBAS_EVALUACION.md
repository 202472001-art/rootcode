# Guion de pruebas para la evaluación

## Sitio público

1. Abrir Inicio y comprobar la navegación horizontal.
2. Probar el carrusel con flechas e indicadores.
3. Abrir Servicios, Portafolio y Contacto.
4. Reducir el navegador para comprobar el menú móvil y una tarjeta por vista.

## Cliente

1. Registrar una cuenta e iniciar sesión.
2. Crear una solicitud y editarla mientras esté pendiente.
3. Enviar un mensaje al administrador, relacionado o no con una solicitud.
4. Abrir el chatbot y probar preguntas sobre servicios, tiempos, estados, pagos y hosting.
5. Verificar que no aparecen cotizaciones ni datos de otros clientes.

## Administrador

1. Iniciar sesión y comprobar el bloqueo de acceso para clientes.
2. Revisar la solicitud y modificar su estado.
3. Abrir Mensajes, leer la conversación y responder.
4. Gestionar usuarios, portafolio y contactos.
5. Verificar que no existen opciones de cotizaciones, servicios, categorías, contenido, imágenes ni chatbot.

## Consultas SQL de apoyo

```sql
SELECT id, nombre, email, password_hash FROM usuarios;
SELECT id, usuario_id, titulo, tipo_pagina, estado FROM solicitudes ORDER BY id DESC;
SELECT id, remitente_id, destinatario_id, solicitud_id, estado, created_at FROM mensajes ORDER BY id DESC;
SELECT id, titulo, categoria, activo FROM portafolio;
```
