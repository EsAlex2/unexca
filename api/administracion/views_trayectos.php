<?php
include_once '../../config/db.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $path = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : null;
    
    if ($path) {
        // 1. Validar que el ID sea numérico
        if (!is_numeric($path)) {
            http_response_code(400);
            echo json_encode(["error" => "El id del trayecto debe ser un número válido"]);
            exit;
        }

        $id_trayecto = (int)$path;

        try {
            $stmt = $pdo->prepare("SELECT * FROM unexca_db.trayectos
            WHERE id_trayecto = :id_trayecto");

            $stmt->execute(['id_trayecto' => $id_trayecto]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                echo json_encode([
                    "data" => $resultado
                ]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "No se encuentran registros para el trayecto seleccionado"]);
            }
        } catch (PDOException $e) {
            // 2. Manejo de error genérico si algo falla en la DB
            http_response_code(500);
            echo json_encode(["error" => "Error interno del servidor al consultar la base de datos"]);
        }
        
    } else {
        // Lógica para listar todos los trayectos
        try {
            $stmt = $pdo->query("SELECT * FROM unexca_db.trayectos");
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($resultados) {
                echo json_encode($resultados);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "No se encuentran trayectos registrados"]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al obtener la lista de trayectos"]);
        }
    }
} else {
    http_response_code(405); // Method Not Allowed es más preciso que 500 aquí
    echo json_encode(["error" => "Método no permitido"]);
}