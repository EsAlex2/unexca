<?php
// api/config/db.php
require_once __DIR__ . '/config.php'; // Asegura la ruta correcta

$dsn = "pgsql:host=$db_server;port=$db_port;dbname=$db_name";

try {
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // En producción, es mejor loguear esto y no mostrarlo al usuario
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión a la base de datos"]);
    exit;
}