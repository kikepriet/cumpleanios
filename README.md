# Cumpleanios 🎂

Pequeña aplicación PHP que lista los cumpleaños del mes y permite enviar esa lista por correo electrónico usando PHPMailer.

---

## 🔎 Descripción

- Muestra una tabla con los usuarios que cumplen años en el mes actual (consulta a MySQL).
- Incluye `phpmailer.php` para enviar la lista por correo (configurable desde `.env`).
- Usa `vlucas/phpdotenv` para cargar variables de entorno.

---

## ✅ Características

- Consulta MySQL similar a `index.php` para extraer `nombre` y `fecha` de la tabla `usuarios`.
- Envío HTML por correo con PHPMailer (soporte SMTP).
- Manejo básico de errores y mensajes por consola.

---

## Requisitos

- PHP 7.4 o superior
- Composer
- MySQL
- Extensiones PHP: `mysqli`, `openssl` (si usas TLS/SSL en SMTP)

---

## Instalación

1. Clona o copia el repositorio:

```bash
git clone git@github.com:kikepriet/cumpleanios.git
cd cumpleanios
```

2. Instala dependencias con Composer:

```bash
composer install
```

3. Crea el archivo `.env` y configúralo (ejemplo abajo). Si tu contraseña contiene espacios, colócala entre comillas:

```dotenv
DB_HOST=localhost
DB_USER=usuario
DB_PASSWORD=secreto
DB_NAME=cumpleanios
MAIL_TO=destinatario@example.com
MAIL_FROM=no-reply@example.com
MAIL_FROM_NAME=Cumpleaños
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=smtp_user
SMTP_PASS="contraseña con espacios si aplica"
SMTP_SECURE=tls
```

> Si usas Gmail, usa una App Password y ponla en `SMTP_PASS`.

4. Asegúrate de que la base de datos contiene la tabla `usuarios` con al menos las columnas:

```sql
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  fecha DATE NOT NULL
);
```

5. (Opcional) Crea carpeta `logs` y ajusta permisos:

```bash
mkdir -p logs
chown -R www-data:www-data logs
chmod 750 logs
```

---

## Uso

- Ver la lista en el navegador: abre `index.php` (`http://<tu-servidor>/cumpleanios/`).
- Enviar la lista por correo manualmente:

```bash
php phpmailer.php
```

El script mostrará si el envío fue exitoso o el error generado.

---

## 🕒 Programar envío (cron)

Ejemplo: enviar todos los días a las 08:00 y guardar logs:

```cron
0 8 * * * /usr/bin/php /var/www/html/cumpleanios/phpmailer.php >> /var/www/html/cumpleanios/logs/phpmailer.log 2>&1
```

Para añadirlo al crontab del usuario actual:

```bash
crontab -e
# pegar la línea de arriba
```

---

## 🔧 Depuración y comprobaciones

- Comprobar sintaxis PHP:

```bash
php -l phpmailer.php
```

- Ejecutar manualmente y revisar mensajes de error en consola.
- Revisar `logs/phpmailer.log` si utilizas cron.

---

## 🔐 Buenas prácticas y seguridad

- No subas `.env` al repositorio (está incluido en `.gitignore`).
- Usa contraseñas seguras y App Passwords para cuentas como Gmail.
- Asegura permisos de archivos sensibles y directorios de log.

---

## Contribuciones

Si deseas contribuir, crea un fork, añade cambios y abre un Pull Request.

---

## Licencia

Sin licencia explícita (añade una si deseas compartirlo públicamente).
