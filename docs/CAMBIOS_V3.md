# RootCode - cambios v3

## Rutas limpias
Las rutas visibles privadas son /panel, /solicitudes, /mensajes, /perfil, /gestion-portafolio y /gestion-contactos. Las carpetas físicas admin/ y cliente/ siguen existiendo por organización, pero no se muestran en los enlaces normales.

## Imágenes del portafolio
Las imágenes subidas se guardan como archivos dentro de uploads/portfolio/. MySQL no guarda el archivo binario: la columna imagen guarda únicamente la ruta relativa. El límite es 5 MB.

## Imágenes de servicios
Los nombres se definen en servicios.php, dentro del arreglo $services en el valor imagen. Los archivos están en assets/img/. Se puede copiar una imagen JPG, PNG, WEBP o SVG a esa carpeta y escribir su nombre en el arreglo.

## Recuperación por correo
La aplicación incluye envío SMTP autenticado. Está preparada para Gmail SMTP. En config/app.php coloca una contraseña de aplicación de Google en SMTP_PASS. No uses la contraseña normal de Gmail. Para Hostinger Email también se puede cambiar SMTP_HOST a smtp.hostinger.com, puerto 465, SSL, y usar una cuenta de correo creada en Hostinger.

## Hora
PHP usa America/Mexico_City y la conexión MySQL ejecuta SET time_zone = '-06:00' para que NOW() y los mensajes coincidan con la hora de CDMX.
