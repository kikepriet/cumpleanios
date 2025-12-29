<?php
require_once 'vendor/autoload.php'; // Carga Composer

// Cargar las variables del archivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Acceder a las variables
$dbHost = $_ENV['DB_HOST']; // o getenv('DB_HOST')
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASSWORD'];
$dbName = $_ENV['DB_NAME'];

$hoy = date('Y-m-d');

// Paso 4: Conectar a MySQL
$conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Aquí puedes ejecutar tus consultas SQL
$sql = "SELECT nombre, fecha FROM usuarios WHERE DATE_FORMAT(fecha, '%m') = DATE_FORMAT('$hoy', '%m') ORDER BY DATE_FORMAT(fecha, '%m-%d') ASC";

// 3. Ejecutar la consulta
$resultado = mysqli_query($conn, $sql);

// 4. Procesar resultados y construir HTML de la tabla
$table_html = '';
if ($resultado && mysqli_num_rows($resultado) > 0) {
    $table_html .= '<div class="mt-4 overflow-x-auto">';
    $table_html .= '<table class="min-w-full divide-y divide-gray-200 border-collapse">';
    $table_html .= '<thead class="bg-gray-50"><tr>';
    $table_html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>';
    $table_html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>';
    $table_html .= '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

    // Recorrer cada fila
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $table_html .= '<tr>';
        $table_html .= '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($fila['nombre']) . '</td>';
        $table_html .= '<td class="px-6 py-4 whitespace-nowrap">' . date('d', strtotime($fila['fecha'])) . '</td>';
        $table_html .= '</tr>';
    }

    $table_html .= '</tbody></table></div>';
} elseif ($resultado === false) {
    $table_html = '<div class="mt-4 text-red-700">Error en la consulta: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
} else {
    $table_html = '<div class="mt-4 text-gray-600">No hay usuarios registrados.</div>';
}

// 5. Cerrar conexión
if ($resultado && $resultado !== false) {
    mysqli_free_result($resultado); // Liberar memoria del resultado
}

mysqli_close($conn);

// Imprimir página completa con Tailwind
echo "<!doctype html>\n";
echo "<html lang=\"es\">\n";
echo "<head>\n";
echo "<meta charset=\"utf-8\">\n";
echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
echo "<title>Lista de Cumpleaños</title>\n";
echo "<script src=\"https://cdn.tailwindcss.com\"></script>\n";
echo "</head>\n";
echo "<body class=\"bg-gray-50 min-h-screen flex items-start md:items-center justify-center py-10\">\n";
echo "<div class=\"w-full max-w-4xl bg-white shadow-md rounded-lg p-6\">\n";
echo "<h1 class=\"text-2xl font-semibold\">" . date('F') . " birtday's</h1>\n";
echo "<div class=\"mt-4\">" . $table_html . "</div>\n";
echo "<div class=\"mt-4\"><a href=\"insert.php\" class=\"text-indigo-600 hover:underline\">Add user</a></div>\n";
echo "</div>\n";
echo "</body>\n";
echo "</html>\n";
