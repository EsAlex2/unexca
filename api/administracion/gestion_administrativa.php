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
            // Si se pide un ID específico
            if (isset($_GET['id_permiso'])) {
                $id = filter_input(INPUT_GET, 'id_permiso', FILTER_VALIDATE_INT);
                $stmt = $pdo->prepare("SELECT * FROM unexca_db.permisos WHERE id_permiso = :id");
                $stmt->execute(['id' => $id]);
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($resultado ?: ["error" => "Permiso no encontrado"]);
            }
            // Si se piden los permisos de un ROL específico (útil para el frontend)
            else if (isset($_GET['id_tipo'])) {
                $id_tipo = filter_input(INPUT_GET, 'id_tipo', FILTER_VALIDATE_INT);
                $sql = "SELECT p.* FROM unexca_db.permisos p
                        INNER JOIN unexca_db.roles_permisos rp ON p.id_permiso = rp.id_permiso
                        WHERE rp.id_tipo_usuario = :id_tipo";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id_tipo' => $id_tipo]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            // Listado general
            else {
                $stmt = $pdo->query("SELECT * FROM unexca_db.permisos ORDER BY modulo ASC");
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

            // ACCIÓN 1: Crear nuevo permiso
            if (isset($input['nombre_permiso'], $input['modulo'])) {
                // Opcional: Validar si el nombre del permiso ya existe para evitar duplicados manuales
                $checkP = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.permisos WHERE nombre_permiso = :nom");
                $checkP->execute(['nom' => trim($input['nombre_permiso'])]);

                if ($checkP->fetchColumn() > 0) {
                    http_response_code(409);
                    echo json_encode(["error" => "El permiso ya existe en el sistema"]);
                    exit;
                }

                $sql = "INSERT INTO unexca_db.permisos (nombre_permiso, descripcion, modulo) 
                        VALUES (:nom, :des, :mod)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'nom' => trim($input['nombre_permiso']),
                    'des' => trim($input['descripcion'] ?? ''),
                    'mod' => trim($input['modulo'])
                ]);
                http_response_code(201);
                echo json_encode(["message" => "Permiso creado con éxito"]);
            }

            // ACCIÓN 2: Asignar permiso a un Rol (Con validación previa)
            else if (isset($input['id_tipo_usuario'], $input['id_permiso'])) {

                // 1. Verificar si el TIPO DE USUARIO (Rol) existe 
                $checkRol = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.tipos_usuario WHERE id_tipo = :r");
                $checkRol->execute(['r' => $input['id_tipo_usuario']]);


                if ($checkRol->fetchColumn() == 0) {
                    http_response_code(404);
                    echo json_encode(["error" => "El tipo de usuario especificado no existe"]);
                    exit;
                }

                // 2. Verificar si el PERMISO existe 
                $checkPerm = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.permisos WHERE id_permiso = :p");
                $checkPerm->execute(['p' => $input['id_permiso']]);

                if ($checkPerm->fetchColumn() == 0) {
                    http_response_code(404);
                    echo json_encode(["error" => "El permiso especificado no existe"]);
                    exit;
                }

                $checkAsig = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.roles_permisos 
                               WHERE id_tipo_usuario = :rol AND id_permiso = :perm");
                $checkAsig->execute([
                    'rol' => $input['id_tipo_usuario'],
                    'perm' => $input['id_permiso']
                ]);

                if ($checkAsig->fetchColumn() > 0) {
                    // Si el conteo es mayor a 0, significa que ya está asignado
                    http_response_code(409);
                    echo json_encode(["error" => "Este rol ya tiene asignado ese permiso actualmente."]);
                    exit;
                }

                // 3. Proceder con la asignación si ambos existen
                $sql = "INSERT INTO unexca_db.roles_permisos (id_tipo_usuario, id_permiso) 
                        VALUES (:rol, :perm) ON CONFLICT DO NOTHING";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'rol' => $input['id_tipo_usuario'],
                    'perm' => $input['id_permiso']
                ]);

                echo json_encode(["message" => "Permiso asignado al rol exitosamente"]);
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Datos incompletos para procesar la solicitud"]);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Error interno del servidor", "detalle" => $e->getMessage()]);
        }
        break;

    case 'PUT':
            /*
            *creamos validaciones para mantener la seguridad e integridad de los datos ingresados por los usuarios
            */
            $input = json_decode(file_get_contents('php://input'), true);
            $id_permiso = filter_input(INPUT_GET, 'id_permiso', FILTER_VALIDATE_INT);
            /*
            verificamos que el permiso sea valido
            */
            if (!$id_permiso) {
                http_response_code(400);
                echo json_encode(["error" => "El id del permiso no es válido"]);
                break;
            }
            /*
            validamos que el permiso exista, prepraramos un query con pdo y arrojamos un mensaje de permiso no encontrado si no se encuantra el permiso
            */
            $permisoExistente = $pdo->prepare("SELECT id_permiso FROM unexca_db.permisos WHERE id_permiso = :id");
            $permisoExistente->execute(['id' => $id_permiso]);
            if (!$permisoExistente->fetch()) {
                http_response_code(404);
                echo json_encode(["error" => "Permiso no encontrado"]);
                break;
            }
            /*
            validamos que los campos no esten vacios y sean obligatorios para actualizar
            */
            $camposRequeridos = [
                'nombre_permiso',
                'descripcion',
                'modulo'
            ];
            foreach ($camposRequeridos as $requerido) {
                if (!isset($input[$requerido]) || strlen(trim((string) $input[$requerido])) === 0) {
                    http_response_code(400);
                    echo json_encode(["error" => "El campo '$requerido' es obligatorio."]);
                    exit;
                }
            }
            /*
            chequeamos que no existan valores duplicados en los permisos registrados previamente
            */
            $valoresDuplicados = $pdo->prepare("SELECT id_permiso FROM unexca_db.permisos WHERE (nombre_permiso = :np AND id_permiso != :id)");
            $valoresDuplicados->execute([
                'np' => $input['nombre_permiso'],
                'id' => $id_permiso
            ]);

            if ($valoresDuplicados->fetch()) {
                http_response_code(409);
                echo json_encode([
                    "error" => "Permiso registrado previamente"
                ]);
            }
        /*
        *realizamos captura de informacion, si cumple con los parametros hacemos la actualizacion de los datos 
        */
        try {


        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en la base de datos", "detalle" => $e->getMessage()]);
        }
        break;


    case 'DELETE':
        try {
            // Revocar un permiso de un rol (quitar la relación)
            if (isset($_GET['revocar'], $_GET['id_tipo'], $_GET['id_permiso'])) {
                $stmt = $pdo->prepare("DELETE FROM unexca_db.roles_permisos 
                            WHERE id_tipo_usuario = :r AND id_permiso = :p");
                $stmt->execute([
                    'r' => $_GET['id_tipo'],
                    'p' => $_GET['id_permiso']
                ]);
                echo json_encode(["message" => "Permiso revocado del rol"]);
            }
            // Eliminar el permiso por completo de la base de datos
            else if (isset($_GET['id_permiso'])) {
                $id = filter_input(INPUT_GET, 'id_permiso', FILTER_VALIDATE_INT);
                $stmt = $pdo->prepare("DELETE FROM unexca_db.permisos WHERE id_permiso = :id");
                $stmt->execute(['id' => $id]);
                echo json_encode(["message" => "Permiso eliminado del sistema"]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo revocar el permiso. Verifique si el permiso está en uso."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}
?>