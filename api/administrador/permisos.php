<?php
/*
 * Gestión del módulo administrativo: Permisos y Roles (RBAC)
 * Proyecto Socio-tecnológico - UNEXCA
 */
include_once '../../config/db.php';

header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];


switch ($method) {
    case 'GET':
        try {

            if (isset($_GET['id_permiso'])) {
                $id = filter_input(INPUT_GET, 'id_permiso', FILTER_VALIDATE_INT);

                $stmt = $pdo->prepare("SELECT * FROM unexca_db.permisos WHERE id_permiso = :id");
                $stmt->execute(['id' => $id]);
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($resultado ?: ["error" => "Permiso no encontrado"]);

            } else if (isset($_GET['listar_modulos'])) {
                $sql = "SELECT id_modulo, nombre_modulo FROM unexca_db.modulos ORDER BY nombre_modulo ASC";
                $stmt = $pdo->query($sql);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } else if (isset($_GET['id_tipo'])) {
                $id_tipo = filter_input(INPUT_GET, 'id_tipo', FILTER_VALIDATE_INT);
                $sql = "SELECT p.* FROM unexca_db.permisos p
                        INNER JOIN unexca_db.roles_permisos rp ON p.id_permiso = rp.id_permiso
                        WHERE rp.id_tipo_usuario = :id_tipo";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id_tipo' => $id_tipo]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                
            } else {
                $sql = "SELECT p.*, m.nombre_modulo 
                        FROM unexca_db.permisos p
                        LEFT JOIN unexca_db.modulos m ON p.id_modulos = m.id_modulo
                        ORDER BY p.id_permiso ASC";
                $stmt = $pdo->query($sql);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al consultar"]);
        }
        break;

    case 'POST':
        try {
            $json = file_get_contents('php://input');
            $input = json_decode($json, true);

            $nombrePermiso = isset($input['nombre_permiso']) ? trim($input['nombre_permiso']) : null;
            $descripcion = trim($input['descripcion'] ?? '');
            $idModulo = $input['id_modulos'] ?? null;

            if ($nombrePermiso && $idModulo) {
                $checkP = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.permisos WHERE LOWER(nombre_permiso) = LOWER(:nom)");
                $checkP->execute(['nom' => $nombrePermiso]);

                if ($checkP->fetchColumn() > 0) {
                    http_response_code(409);
                    echo json_encode(["error" => "El permiso '$nombrePermiso' ya existe"]);
                    exit;
                }

                $checkM = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.modulos WHERE id_modulo = :mod");
                $checkM->execute(['mod' => $idModulo]);

                if ($checkM->fetchColumn() == 0) {
                    http_response_code(404);
                    echo json_encode(["error" => "El módulo especificado no existe"]);
                    exit;
                }

                $sql = "INSERT INTO unexca_db.permisos (id_estatus, nombre_permiso, descripcion, id_modulos) 
                        VALUES (:estatus, :nom, :des, :mod)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'estatus' => 1,
                    'nom' => $nombrePermiso,
                    'des' => $descripcion,
                    'mod' => $idModulo
                ]);

                http_response_code(201);
                echo json_encode([
                    "message" => "Permiso creado con éxito",
                    "id_permiso" => $pdo->lastInsertId()
                ]);
            } else if (isset($input['id_tipo_usuario'], $input['id_permiso'], $input['id_usuario']) && is_array($input['id_permiso'])) {

                $id_rol = $input['id_tipo_usuario'];
                $permisosArray = $input['id_permiso'];
                $id_usuario = $input['id_usuario'];

                $checkRol = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.tipos_usuario WHERE id_tipo = :r");
                $checkRol->execute(['r' => $id_rol]);

                if ($checkRol->fetchColumn() == 0) {
                    http_response_code(404);
                    echo json_encode(["error" => "El tipo de usuario especificado no existe"]);
                    exit;
                }

                $checkUser = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.usuarios WHERE id_usuario = :u");
                $checkUser->execute(['u' => $id_usuario]);

                if ($checkUser->fetchColumn() == 0) {
                    http_response_code(404);
                    echo json_encode(["error" => "El usuario especificado no existe"]);
                    exit;
                }

                // Iniciamos una transacción para asegurar que todos los permisos se asignen correctamente
                $pdo->beginTransaction();

                try {
                    // Preparamos la sentencia de inserción con ON CONFLICT para evitar errores si ya existe la relación 
                    $sqlAsignar = "INSERT INTO unexca_db.roles_permisos (id_tipo_usuario, id_permiso, id_usuario) 
                                   VALUES (:rol, :perm, :usuario) 
                                   ON CONFLICT (id_tipo_usuario, id_permiso, id_usuario) DO NOTHING";
                    $stmtAsignar = $pdo->prepare($sqlAsignar);

                    foreach ($permisosArray as $id_permiso) {
                        $stmtAsignar->execute([
                            'rol' => $id_rol,
                            'perm' => $id_permiso,
                            'usuario' => $id_usuario
                        ]);
                    }

                    $pdo->commit();
                    echo json_encode(["message" => "Asignación de permisos procesada exitosamente"]);

                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Datos incompletos o formato de permisos no válido (debe ser un arreglo)"]);
            }
        } catch (PDOException $e) {
            error_log("Error en POST permisos.php: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Error interno del servidor al procesar la solicitud"]);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id_permiso = filter_input(INPUT_GET, 'id_permiso', FILTER_VALIDATE_INT);

        if (!$id_permiso) {
            http_response_code(400);
            echo json_encode(["error" => "El id del permiso no es válido"]);
            break;
        }

        // 1. Obtener los datos actuales del permiso de la base de datos
        $stmt = $pdo->prepare("SELECT * FROM unexca_db.permisos WHERE id_permiso = :id");
        $stmt->execute(['id' => $id_permiso]);
        $permisoActual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$permisoActual) {
            http_response_code(404);
            echo json_encode(["error" => "Permiso no encontrado"]);
            break;
        }

        // 2. Fusionar datos: Si el campo no viene en el JSON ($input), usamos el que ya existe en la DB ($permisoActual)
        $estatus = $input['id_estatus'] ?? $permisoActual['id_estatus'];
        $nombre = $input['nombre_permiso'] ?? $permisoActual['nombre_permiso'];
        $desc = $input['descripcion'] ?? $permisoActual['descripcion'];
        $modulo = $input['id_modulos'] ?? $permisoActual['id_modulos'];

        try {
            $sql = "UPDATE unexca_db.permisos SET 
                id_estatus = :st,
                nombre_permiso = :np,
                descripcion = :d,
                id_modulos = :id_m 
                WHERE id_permiso = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'st' => $estatus,
                'np' => trim($nombre),
                'd' => trim($desc),
                'id_m' => $modulo,
                'id' => $id_permiso
            ]);

            echo json_encode(["message" => "¡Actualización exitosa!"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}
?>