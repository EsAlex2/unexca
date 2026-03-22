<?php
include_once '../../config/db.php';
/*
 * Gestión del módulo configuraciones: Configuraciones Generales del Sistema
 * Proyecto Socio-tecnológico - UNEXCA
 */
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            if (isset($_GET['id'])) {
                $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(["error" => "id de condiguracion no valida"]);
                    break;
                }

                $stmt = $pdo->prepare("SELECT * FROM unexca_db.configuraciones WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $config = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($config) {
                    echo json_encode($config);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "configuracion no encontrado"]);
                }

            } else {
                $stmt = $pdo->query("SELECT * FROM unexca_db.configuraciones ORDER BY id");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al consultar configuraciones"]);
        }
        break;
    case 'POST':
        try {
            $input = json_decode(file_get_contents("php://input"), true);

            if (!isset($input['clave'], $input['valor'])) {
                http_response_code(400);
                echo json_encode(["error" => "Faltan datos obligatorios (clave, valor)"]);
                break;
            }

            // 1. Validar si la CLAVE ya existe (usamos AND en el WHERE)
            $checkConfig = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.configuraciones WHERE clave = :c OR valor = :v");
            $checkConfig->execute(['c' => $input['clave'], 'v' => $input['valor']]);

            if ($checkConfig->fetchColumn() > 0) {
                http_response_code(409); // 409 Conflict es mejor para duplicados
                echo json_encode(["error" => "Esta configuracion ya existe en el sistema"]);
                break;
            }

            // 2. Si no existe, procedemos a insertar
            $sql = "INSERT INTO unexca_db.configuraciones (clave, valor, descripcion, id_categoria) 
                VALUES (:clave, :valor, :descripcion, :categoria)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'clave' => $input['clave'],
                'valor' => $input['valor'],
                'descripcion' => $input['descripcion'] ?? null,
                'categoria' => $input['id_categoria'] ?? 'general'
            ]);

            http_response_code(201);
            echo json_encode(["mensaje" => "Configuración creada con éxito"]);

        } catch (PDOException $e) {
            http_response_code(500);
            // Concatenamos con un espacio para leer mejor el error
            echo json_encode(["error" => "Error al guardar"]);
        }
        break;
    case 'PUT':
        try {
            $input = json_decode(file_get_contents("php://input"), true);

            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(["error" => "El ID es obligatorio"]);
                break;
            }

            $sql = "UPDATE unexca_db.configuraciones 
                SET clave = :clave, 
                    valor = :valor, 
                    descripcion = :desc,
                    id_categoria = :id_cat, 
                    actualizado_en = NOW() 
                WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'clave' => $input['clave'] ?? null,
                'valor' => $input['valor'] ?? null,
                'desc' => $input['descripcion'] ?? null,
                'id_cat' => $input['id_categoria'] ?? null,
                'id' => $input['id']
            ]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(["mensaje" => "Configuración {$input['clave']} actualizada"]);
            } else {
                echo json_encode(["mensaje" => "No hubo cambios o el id no existe"]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error de SQL: " . $e->getMessage()]);
        }
        break;
}
?>