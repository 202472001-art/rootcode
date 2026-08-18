# Arquitectura cliente-servidor y despliegue

## Recorrido de los datos

```text
Cliente
  ↓
Navegador web
  ↓ HTTPS
Servidor Apache + PHP 8
  ↓ PDO / consultas preparadas
Servidor MySQL
  ↓ resultados
Servidor PHP
  ↓ HTML o JSON
Navegador
  ↓
Cliente
```

Cuando una persona registra una solicitud, el navegador envía un formulario por HTTP POST. PHP valida la sesión, el token CSRF, los campos y los permisos. Después ejecuta una consulta preparada en MySQL. El servidor devuelve una respuesta HTML y el cliente ve el nuevo registro.

Las sesiones pertenecen a cada navegador. Por ello, varios clientes pueden utilizar simultáneamente la aplicación sin mezclar información. Todas las consultas del cliente contienen la condición `WHERE usuario_id = ?`. El administrador utiliza páginas distintas y consultas globales autorizadas por su rol.

## Separación de interfaces

- Las páginas de `cliente/` ejecutan `require_role('cliente')`.
- Las páginas de `admin/` ejecutan `require_role('administrador')`.
- Si el rol no coincide, la sesión se cierra, se registra el intento y la persona regresa al login.
- No existe un enlace público directo al panel administrativo.
- Escribir manualmente `/admin/` no evita la validación del servidor.

## Despliegue en Hostinger

### 1. Dominio

Desde hPanel, agrega o selecciona el dominio. Apunta sus DNS hacia Hostinger si el dominio fue comprado con otro proveedor. Espera la propagación antes de realizar las pruebas finales.

### 2. Archivos

1. Comprime el contenido del proyecto.
2. Abre **Administrador de archivos** en hPanel.
3. Entra a `public_html`.
4. Sube el archivo ZIP.
5. Extráelo.
6. Si quedó dentro de una carpeta adicional, mueve el contenido de esa carpeta directamente a `public_html`.

La ruta final debe tener `public_html/index.php`, no `public_html/rootcode_app/index.php`, salvo que se desee usar una subcarpeta.

### 3. Base de datos

1. En hPanel, crea una base MySQL y un usuario.
2. Guarda nombre de base, usuario y contraseña.
3. Abre phpMyAdmin de Hostinger.
4. Selecciona la base.
5. Importa `database/rootcode.sql`.
6. Cambia inmediatamente las credenciales de demostración.

### 4. Conexión

Edita `config/app.php` con los datos reales de Hostinger. Normalmente el host es `localhost`, pero debe utilizarse el valor mostrado en hPanel.

Configura:

- `APP_ENV = 'production'`
- `APP_BASE_URL = ''` si el proyecto está directamente en `public_html`
- `APP_KEY` con una cadena aleatoria nueva
- `DB_NAME`, `DB_USER` y `DB_PASS`

### 5. SSL

Activa SSL desde hPanel y fuerza HTTPS. El sistema detecta HTTPS y habilita cookies seguras y HSTS.

### 6. Permisos

- Carpetas: normalmente `755`.
- Archivos: normalmente `644`.
- `uploads/portfolio` y `uploads/images` necesitan permiso de escritura del servidor.
- No usar `777` salvo prueba temporal y nunca dejarlo en producción.

### 7. Recuperación de contraseña

La función utiliza `mail()`. En producción, revisa que el correo remitente de `SUPPORT_EMAIL` exista en el dominio. Para una implementación más robusta puede sustituirse posteriormente por SMTP autenticado.

### 8. Pruebas finales

- Abrir sitio con HTTPS.
- Crear un cliente nuevo.
- Iniciar sesión como cliente.
- Crear, editar y eliminar una solicitud pendiente.
- Iniciar sesión como administrador.
- Revisar la solicitud, cambiar su estado y enviar un mensaje.
- Volver al cliente y comprobar estado y mensaje.
- Intentar abrir una URL administrativa con cuenta de cliente.
- Intentar alterar el token de un registro.
- Verificar imágenes, formularios y diseño móvil.
