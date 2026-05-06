<?php
include_once '../../config/db.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if($method === 'GET'){
    $path = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : null;
    if ($path) {
        $cod_pnf = htmlspecialchars($path);

        $stmt = "SELECT id_pnf, id_sede, cod_pnf, nombre_pnf, descripcion FROM ";

    }

}