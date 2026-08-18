# Matriz de cumplimiento simplificada

| Requisito | Implementación |
|---|---|
| PHP 8, MySQL, HTML, CSS y JavaScript | Proyecto sin frameworks |
| Interfaz Administrador | Sidebar verde y módulos exclusivos en `admin/` |
| Interfaz Cliente | Sidebar independiente y datos filtrados por usuario |
| Protección de rutas | `require_role()`, sesiones y redirección al login |
| Registro, login y recuperación | Carpeta `auth/` y contraseñas cifradas |
| SQL Injection | PDO y consultas preparadas |
| CSRF y XSS | Tokens en formularios y escape con `e()` |
| Solicitudes | CRUD del cliente sobre registros pendientes y gestión administrativa |
| Mensajes | Envío y respuesta con fecha, remitente y estado |
| Administración de usuarios | Eliminada del panel; registro y autenticación permanecen |
| Portafolio | CRUD administrativo y consulta pública |
| Contactos | Formulario público y gestión de estado |
| Chatbot | Solo como ventana flotante en el cliente, con respuestas locales en JavaScript |
| Inicio del cliente | Tres bloques con Solicitudes, Mensajes y Chatbot |
| Diseño público | Navbar horizontal, estadísticas y carrusel responsivo |
| Diseño privado | Sidebar fijo, tarjetas rectangulares y tablas |
| Hostinger | Configuración PHP/MySQL y documentación de despliegue |
