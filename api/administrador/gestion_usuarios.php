<?php
include_once '../../config/db.php';
include_once '../../auth/auth_helper.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$auth = new AuthMiddleware($pdo);

switch ($method) {
    case 'GET':
        try {
            $path = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : null;

            if ($path) {

                $cedula = filter_var($path, FILTER_UNSAFE_RAW);

                if (!$cedula) {
                    http_response_code(400);
                    echo json_encode(["error" => "Cédula no válida"]);
                    break;
                }

                // Query con INNER JOIN para un usuario específico
                $sql = "SELECT u.id_usuario, u.id_persona, u.cedula, u.correo_institucional,
                               u.password_hash, u.id_tipo, u.id_estatus, e.nombre_estatus, t.nombre_tipo 
                        FROM unexca_db.usuarios u
                        INNER JOIN unexca_db.tipos_usuario t ON u.id_tipo = t.id_tipo
                        INNER JOIN unexca_db.estatus e ON u.id_estatus = e.id_estatus
                        WHERE u.cedula = :cedula";

                $stmt = $pdo->prepare($sql);
                $stmt->execute(['cedula' => $cedula]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    echo json_encode(["mensaje" => "Usuario encontrado", 
                    "cedula" => $usuario['cedula'],
                    "correo" => $usuario['correo_institucional'],
                    "tipo" => $usuario['nombre_tipo'],
                    "estatus" => $usuario['nombre_estatus']]);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Usuario no encontrado"]);
                }

            } else {
                // Query con INNER JOIN para todos los usuarios
                $sql = "SELECT u.id_usuario, u.cedula, u.correo_institucional, 
                                u.id_tipo, u.id_estatus, e.nombre_estatus, t.nombre_tipo,
                                p.nombres, p.apellidos 
                        FROM unexca_db.usuarios u
                        INNER JOIN unexca_db.tipos_usuario t ON u.id_tipo = t.id_tipo
                        INNER JOIN unexca_db.estatus e ON u.id_estatus = e.id_estatus
                        INNER JOIN unexca_db.datos_personas p ON u.id_persona = p.id_persona";

                $stmt = $pdo->query($sql);
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($usuarios);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "error" => "Error de base de datos",
                "detalles" => $e->getMessage()
            ]);
        }
        break;

    case 'POST':
        try {
            $json = file_get_contents('php://input');
            $input = json_decode($json, true);

            if (!isset($input['cedula']) || empty(trim($input['cedula']))) {
                http_response_code(400);
                echo json_encode(["error" => "La cédula es necesaria para verificar la identidad."]);
                exit;
            }

            $cedula = trim($input['cedula']);

            $stmtPersona = $pdo->prepare("SELECT id_persona, nombres, apellidos 
            FROM unexca_db.datos_personas WHERE cedula_identidad = :c");
            $stmtPersona->execute(['c' => $cedula]);
            $persona = $stmtPersona->fetch(PDO::FETCH_ASSOC);

            if (!$persona) {
                http_response_code(404);
                echo json_encode([
                    "error" => "La persona no está registrada en el sistema.",
                    "sugerencia" => "Debe registrar primero los datos básicos en el módulo de Personas/SAIME."
                ]);
                exit;
            }

            $id_persona = $persona['id_persona'];

            $checkUser = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.usuarios WHERE id_persona = :idp");
            $checkUser->execute(['idp' => $id_persona]);
            if ($checkUser->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(["error" => "Esta persona ya tiene una cuenta de usuario activa."]);
                exit;
            }

            $campos_acceso = ['correo_institucional', 'password_hash', 'id_tipo'];
            foreach ($campos_acceso as $campo) {
                if (!isset($input[$campo]) || empty(trim((string) $input[$campo]))) {
                    http_response_code(400);
                    echo json_encode(["error" => "El campo '$campo' es necesario para el acceso."]);
                    exit;
                }
            }

            $password_hash = password_hash($input['password_hash'], PASSWORD_DEFAULT);

            $sql = "INSERT INTO unexca_db.usuarios (id_persona, cedula, correo_institucional, password_hash, id_tipo, id_estatus)
                VALUES (:id_persona, :cedula, :correo, :pass, :tipo, :id_estatus)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id_persona' => $id_persona,
                'cedula' => $cedula,
                'correo' => trim($input['correo_institucional']),
                'pass' => $password_hash,
                'tipo' => $input['id_tipo'],
                'id_estatus' => 2 // Estatus inactivo por defecto, se puede cambiar luego
            ]);

            http_response_code(201);
            echo json_encode(["message" => "Usuario vinculado y creado exitosamente para " . $persona['nombres']]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error interno", "detalle" => $e->getMessage()]);
        }
        break;

    case "PUT":
        $path = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : null;
        $cedula_url = filter_var($path, FILTER_UNSAFE_RAW);
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$cedula_url) {
            http_response_code(400);
            die(json_encode(["error" => "No hay cédula en la URL"]));
        }

        try {
            $stmt = $pdo->prepare("SELECT id_usuario FROM unexca_db.usuarios WHERE cedula = :cedula");
            $stmt->execute(['cedula' => $cedula_url]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                http_response_code(404);
                die(json_encode(["error" => "El usuario con cédula $cedula_url no existe en la base de datos"]));
            }

            $n_cedula = trim($input['cedula'] ?? '');
            $n_correo = trim($input['correo_institucional'] ?? '');
            $n_tipo = $input['id_tipo'] ?? null;
            $n_estatus = $input['id_estatus'] ?? null;
            $n_pass = !empty($input['password_hash']) ? password_hash($input['password_hash'], PASSWORD_DEFAULT) : null;

            $sql = "UPDATE unexca_db.usuarios SET 
                cedula = :c, 
                correo_institucional = :m, 
                id_tipo = :t, 
                id_estatus = :e";

            $params = [
                'c' => $n_cedula,
                'm' => $n_correo,
                't' => $n_tipo,
                'e' => $n_estatus,
                'id_usuario' => $user['id_usuario']
            ];

            if ($n_pass) {
                $sql .= ", password_hash = :p";
                $params['p'] = $n_pass;
            }

            $sql .= " WHERE id_usuario = :id_usuario";

            $update = $pdo->prepare($sql);
            $update->execute($params);

            echo json_encode(["message" => "¡Usuario actualizado con éxito!"]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => "Error de base de datos",
                "detalle" => $e->getMessage()
            ]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}
