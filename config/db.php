<?php

include 'config.php';

$dsn = "pgsql:host=$db_server;port=$db_port;dbname=$db_name";

try {
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    //echo "Conexión exitosa a la base de datos: " . $db_name;

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
