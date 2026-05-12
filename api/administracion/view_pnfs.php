<?php
include_once '../../config/db.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $path = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : null;
    if ($path) {
        $cod_pnf = htmlspecialchars($path);

        $stmt = $pdo->prepare("SELECT * 
        FROM unexca_db.pnf 
        WHERE id_pnf = :id_pnf");

        $stmt->execute(['id_pnf' => $cod_pnf]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if (isset($resultado)) {
            echo json_encode([
                "message" => "PNF encontrado!",
                "data" => $resultado
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "No se encuentran registros para el PNF seleccionado"]);
        }
    } else {
        $stmt = $pdo->query("SELECT * 
        FROM unexca_db.pnf");
        if (isset($stmt)) {
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            http_response_code(404);
            echo json_encode(["error" => "No se encuentran ningun PNF registrado"]);
        }
    }
} else {
    
    http_response_code(500);
    echo json_encode([
        "error" => "Error en consultas a base de datos"
    ]);
}
