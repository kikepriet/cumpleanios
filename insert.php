<?php
require_once 'vendor/autoload.php'; // Carga Composer y Dotenv

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dbHost = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
$dbUser = $_ENV['DB_USER'] ?? getenv('DB_USER');
$dbPass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');
$dbName = $_ENV['DB_NAME'] ?? getenv('DB_NAME');

$message = '';
$nombre = '';
$fecha = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');

    // Validaciones básicas
    if ($nombre === '' || $fecha === '') {
        $message = 'Por favor complete ambos campos.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $message = 'Fecha inválida. Use formato YYYY-MM-DD.';
    } else {
        // Conectar a la base de datos usando variables de entorno
        $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

        if (!$conn) {
            $message = 'Error de conexión: ' . mysqli_connect_error();
        } else {
            // Preparar INSERT para evitar inyección SQL
            $stmt = mysqli_prepare($conn, "INSERT INTO usuarios (nombre, fecha) VALUES (?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ss', $nombre, $fecha);
                if (mysqli_stmt_execute($stmt)) {
                    $message = 'Usuario insertado correctamente.';
                    // Limpiar valores para evitar reenvío accidental
                    $nombre = '';
                    $fecha = '';
                } else {
                    $message = 'Error al insertar: ' . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = 'Error al preparar la consulta: ' . mysqli_error($conn);
            }

            mysqli_close($conn);
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Insertar usuario - Cumpleaños</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-start md:items-center justify-center py-10">
    <div class="w-full max-w-xl bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-semibold mb-4">Agregar usuario</h1>

        <?php if ($message): ?>
            <div class="<?php echo ($message === 'Usuario insertado correctamente.') ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'; ?> px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="space-y-4">
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 p-2">
            </div>

            <div>
                <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($fecha); ?>" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 p-2">
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Enviar
                </button>
                <a href="index.php" class="text-sm text-indigo-600 hover:underline">Volver a la lista</a>
            </div>
        </form>
    </div>

</body>
</html>
