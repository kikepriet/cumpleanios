<?php
// phpmailer.php
// Envía por correo la misma información que muestra index.php (lista de cumpleaños del mes)

require_once 'vendor/autoload.php'; // Composer autoload (si existe)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargar variables de entorno (si existe .env)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Variables de BD (igual que index.php)
$dbHost = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
$dbUser = $_ENV['DB_USER'] ?? getenv('DB_USER');
$dbPass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');
$dbName = $_ENV['DB_NAME'] ?? getenv('DB_NAME');

// Variables de correo (configúralas en .env)
$mailTo = $_ENV['MAIL_TO'] ?? 'destinatario@example.com';
$mailFrom = $_ENV['MAIL_FROM'] ?? 'no-reply@example.com';
$mailFromName = $_ENV['MAIL_FROM_NAME'] ?? 'Cumpleaños';
$smtpHost = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST');
$smtpPort = $_ENV['SMTP_PORT'] ?? 587;
$smtpUser = $_ENV['SMTP_USER'] ?? getenv('SMTP_USER');
$smtpPass = $_ENV['SMTP_PASS'] ?? getenv('SMTP_PASS');
$smtpSecure = $_ENV['SMTP_SECURE'] ?? 'tls';

// Conectar a MySQL
$hoy = date('Y-m-d');
$conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

$sql = "SELECT nombre, fecha FROM usuarios WHERE DATE_FORMAT(fecha, '%m') = DATE_FORMAT('$hoy', '%m') ORDER BY DATE_FORMAT(fecha, '%m-%d') ASC";
$resultado = mysqli_query($conn, $sql);

// Construir HTML de la tabla (misma estructura que index.php)
$table_html = '';
if ($resultado && mysqli_num_rows($resultado) > 0) {
    $table_html .= '<div class="mt-4 overflow-x-auto">';
    $table_html .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;">';
    $table_html .= '<thead><tr style="background:#f7fafc"><th>Name</th><th>Day</th></tr></thead><tbody>';

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $table_html .= '<tr>';
        $table_html .= '<td>' . htmlspecialchars($fila['nombre']) . '</td>';
        $table_html .= '<td>' . date('d', strtotime($fila['fecha'])) . '</td>';
        $table_html .= '</tr>';
    }

    $table_html .= '</tbody></table></div>';
} elseif ($resultado === false) {
    $table_html = '<div style="color:#c00">Error en la consulta: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
} else {
    $table_html = '<div>No hay usuarios registrados este mes.</div>';
}

if ($resultado && $resultado !== false) {
    mysqli_free_result($resultado);
}
mysqli_close($conn);

// Verificar disponibilidad de PHPMailer
if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    // Intentar cargar la copia local en ../PHPMailer-master
    $local = __DIR__ . '/../PHPMailer-master/src/';
    if (file_exists($local . 'PHPMailer.php')) {
        require_once $local . 'Exception.php';
        require_once $local . 'PHPMailer.php';
        require_once $local . 'SMTP.php';
    } else {
        // Instrucciones claras para instalar
        die("PHPMailer no está disponible. Instálalo con: composer require phpmailer/phpmailer o coloca PHPMailer en ../PHPMailer-master\n");
    }
}

// Preparar y enviar correo
$mail = new PHPMailer(true);
try {
    // Configuración SMTP (si se proporcionó)
    if ($smtpHost) {
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = !empty($smtpUser);
        if (!empty($smtpUser)) {
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
        }
        $mail->SMTPSecure = $smtpSecure;
        $mail->Port = (int)$smtpPort;
    }

    $mail->setFrom($mailFrom, $mailFromName);
    $mail->addAddress($mailTo);

    $mail->isHTML(true);
    $mail->Subject = "Birthday's of - " . date('F Y');

    $emailBody = '<h1>Birthday\'s of ' . date('F') . '</h1>' . $table_html;
    $mail->Body = $emailBody;
    $mail->AltBody = strip_tags(str_replace('</tr>', "\n", strip_tags($emailBody)));

    $mail->send();
    echo "Correo enviado correctamente a: " . htmlspecialchars($mailTo) . "\n";
} catch (Exception $e) {
    echo "No se pudo enviar el correo. Mailer Error: " . $mail->ErrorInfo . "\n";
}
