<?php
/*
 * Gestión del módulo administrativo: Roles (RBAC)
 * Proyecto Socio-tecnológico - UNEXCA
 */
include_once '../../config/db.php';

header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            if (isset($_GET['id_tipo'])) {
                $id = filter_input(INPUT_GET, 'id_tipo', FILTER_VALIDATE_INT);
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(["error" => "ID de rol no válido"]);
                    break;
                }

                $stmt = $pdo->prepare("SELECT * FROM unexca_db.tipos_usuario WHERE id_tipo = :id");
                $stmt->execute(['id' => $id]);
                $rol = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($rol) {
                    echo json_encode($rol);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Rol no encontrado"]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM unexca_db.tipos_usuario ORDER BY id_tipo ASC");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al consultar roles"]);
        }
        break;

    case 'POST':
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['nombre_tipo']) || empty(trim($input['nombre_tipo']))) {
                http_response_code(400);
                echo json_encode(["error" => "El nombre del rol es obligatorio"]);
                exit;
            }

            // Validar duplicados
            $check = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.tipos_usuario WHERE nombre_tipo = :nom");
            $check->execute(['nom' => trim($input['nombre_tipo'])]);
            if ($check->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(["error" => "El rol ya existe"]);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO unexca_db.tipos_usuario (nombre_tipo) VALUES (:nom)");
            $stmt->execute(['nom' => trim($input['nombre_tipo'])]);

            http_response_code(201);
            echo json_encode(["message" => "Rol creado exitosamente"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error interno del servidor"]);
        }
        break;

    case 'PUT':
        try {
            $id = filter_input(INPUT_GET, 'id_tipo', FILTER_VALIDATE_INT);
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$id || !isset($input['nombre_tipo']) || empty(trim($input['nombre_tipo']))) {
                http_response_code(400);
                echo json_encode(["error" => "Datos incompletos o ID no válido"]);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE unexca_db.tipos_usuario SET nombre_tipo = :nom WHERE id_tipo = :id");
            $stmt->execute([
                'nom' => trim($input['nombre_tipo']),
                'id' => $id
            ]);

            echo json_encode(["message" => "Rol actualizado correctamente"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al actualizar"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}