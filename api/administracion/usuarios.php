<?php
include_once '../../config/db.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            /*
            si existe un id_usuario, almacena esa informacion en una variable
            donde se va a filtrar pata validar que solo sea un entero
            */
            if (isset($_GET['id_usuario'])) {
                $id = filter_input(INPUT_GET, 'id_usuario', FILTER_VALIDATE_INT);
                
                /*
                si el get envia un id no valido, mostrara un error en pantalla
                */
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(["error" => "ID no válido"]);
                    break;
                }
                $stmt = $pdo->prepare("SELECT id_usuario, cedula, nombres, apellidos, correo_institucional, activo, ultimo_login, creado_en 
                                       FROM unexca_db.usuarios 
                                       WHERE id_usuario = :id");

                $stmt->execute(['id' => $id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    echo json_encode($usuario);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Usuario no encontrado"]);
                }

            } else {
                $stmt = $pdo->query("SELECT id_usuario, cedula, nombres, apellidos, correo_institucional, activo, ultimo_login, creado_en FROM unexca_db.usuarios");
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

            $cedula = isset($input['cedula']) ? trim($input['cedula']) : '';
            $nombres = isset($input['nombres']) ? trim($input['nombres']) : '';
            $email = isset($input['correo_institucional']) ? trim($input['correo_institucional']) : '';
            $pass = isset($input['password_hash']) ? $input['password_hash'] : '';

            if (empty($cedula) || empty($email) || empty($pass)) {
                http_response_code(400);
                echo json_encode(["error" => "Faltan campos obligatorios o están vacíos."]);
                exit;
            }

            // 1. Verificar que existan todos los campos necesarios
            $campos_requeridos = ['cedula', 'nombres', 'apellidos', 'correo_institucional', 'password_hash'];
            foreach ($campos_requeridos as $campo) {
                if (!isset($input[$campo]) || empty(trim((string) $input[$campo]))) {
                    http_response_code(400);
                    echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                    exit;
                }
            }
            $email = isset($input['correo_institucional']) ? trim($input['correo_institucional']) : '';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(["error" => "El formato del correo electrónico no es válido."]);
                exit;
            }
            // 3. Validar longitud de la contraseña (ejemplo: mínimo 8 caracteres)
            if (strlen($input['password_hash']) < 8) {
                http_response_code(400);
                echo json_encode(["error" => "La contraseña debe tener al menos 8 caracteres."]);
                exit;
            }
            // 4. Verificar si la cedula o email ya existen (Evitar duplicados)
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.usuarios WHERE cedula = :c OR correo_institucional = :e");
            $checkStmt->execute(['c' => $input['cedula'], 'e' => $input['correo_institucional']]);
            if ($checkStmt->fetchColumn() > 0) {
                http_response_code(409); // Conflict
                echo json_encode(["error" => "La cedula o el correo ya están registrados."]);
                exit;
            }

            // 5. Si todo está bien, procedemos al registro
            $password_hash = password_hash($input['password_hash'], PASSWORD_DEFAULT);

            $sql = "INSERT INTO unexca_db.usuarios (cedula, nombres, apellidos, correo_institucional, password_hash) 
                VALUES (:cedula, :nombres, :apellidos, :correo_institucional, :password_hash)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'cedula' => trim($input['cedula']),
                'nombres' => trim($input['nombres']),
                'apellidos' => trim($input['apellidos']),
                'correo_institucional' => trim($input['correo_institucional']),
                'password_hash' => $password_hash,
            ]);

            http_response_code(201);
            echo json_encode(["message" => "Usuario creado exitosamente"]);

        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Error interno al crear el usuario"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}