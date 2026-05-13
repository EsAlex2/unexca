<?php
include_once '../../config/db.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $path = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : null;
    
    if ($path) {

        if (!is_numeric($path)) {
            http_response_code(400);
            echo json_encode(["error" => "El id del pnf debe ser un número válido"]);
            exit;
        }

        $id_pnf = (int)$path;

        try {
            $stmt = $pdo->prepare("SELECT * FROM unexca_db.pnf
            WHERE id_pnf = :id_pnf");

            $stmt->execute(['id_pnf' => $id_pnf]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                echo json_encode([
                    "data" => $resultado
                ]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "No se encuentran registros para el pnf seleccionado"]);
            }
        } catch (PDOException $e) {

            http_response_code(500);
            echo json_encode(["error" => "Error interno del servidor al consultar la base de datos"]);
        }
        
    } else {

        try {
            $stmt = $pdo->query("SELECT * FROM unexca_db.pnf");
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($resultados) {
                echo json_encode($resultados);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "No se encuentran pnfs registrados"]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al obtener la lista de pnfs"]);
        }
    }
} else {
    http_response_code(405); //
    echo json_encode(["error" => "Método no permitido"]);
}