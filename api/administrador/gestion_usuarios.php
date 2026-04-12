<?php
include_once '../../config/db.php';
include_once '../../auth/auth_helper.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$auth = new AuthMiddleware($pdo);

switch ($method) {
    case 'GET':
        try {
            $auth->protegerRuta('mostrar_usuarios');

            if (isset($_GET['id_usuario'])) {
                $id = filter_input(INPUT_GET, 'id_usuario', FILTER_VALIDATE_INT);

                if (!$id) {
                    http_response_code(400);
                    echo json_encode(["error" => "ID no válido"]);
                    break;
                }

                // Query con INNER JOIN para un usuario específico
                $sql = "SELECT u.id_usuario, u.cedula, u.nombres, u.apellidos, 
                               u.correo_institucional, u.activo, u.id_tipo, 
                               u.ultimo_login, u.creado_en, t.nombre_tipo 
                        FROM unexca_db.usuarios u
                        INNER JOIN unexca_db.tipos_usuario t ON u.id_tipo = t.id_tipo
                        WHERE u.id_usuario = :id";

                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id' => $id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    echo json_encode($usuario);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Usuario no encontrado"]);
                }

            } else {
                // Query con INNER JOIN para todos los usuarios
                $sql = "SELECT u.id_usuario, u.cedula, u.nombres, u.apellidos, 
                               u.correo_institucional, u.activo, u.id_tipo, 
                               u.ultimo_login, u.creado_en, t.nombre_tipo 
                        FROM unexca_db.usuarios u
                        INNER JOIN unexca_db.tipos_usuario t ON u.id_tipo = t.id_tipo";

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
            /*
            convierte la informacion ingrasada por el usuario en input, nos servira para recibir y procesar
            los datos enviados en un formato JSON desde nuestra aplicacion(cliente API) a traves de una
            peticion.
            */

            $auth->protegerRuta('crear_usuario');

            $json = file_get_contents('php://input');
            $input = json_decode($json, true);

            /*
            posteriormente, iteramos en los campos atraves de un array para verificar cual es el campo que esta vacio
            y si no existe ese campo, enviamos un mensaje indicando que esta faltando ese campo especificamente.
            */
            $campos_requeridos = ['cedula', 'nombres', 'apellidos', 'correo_institucional', 'password_hash', 'id_tipo'];
            foreach ($campos_requeridos as $campo) {
                if (!isset($input[$campo]) || empty(trim((string) $input[$campo]))) {
                    http_response_code(400);
                    echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                    exit;
                }
            }

            /*
            luego, creamos una validacion para saber si existe el campo de correos y verificamos si el correo que el usuario
            esta suministrandoen en el campo es valido.
            */
            $email = isset($input['correo_institucional']) ? trim($input['correo_institucional']) : '';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(["error" => "El formato del correo electrónico no es válido."]);
                exit;
            }

            /*
            añadimos una validacion para que el campo de contraseñas posea minimo 8 caracteres
            */
            if (strlen($input['password_hash']) < 8) {
                http_response_code(400);
                echo json_encode(["error" => "La contraseña debe tener al menos 8 caracteres."]);
                exit;
            }

            /*
             * --validacion de cedula y correo que no esten registrados en la base de datos--
             * prepraramos una consulta a la base de datos, seleccionamos y contamos los usuarios mientras la cedula o correo ingresada por el usuario
             * no esten registradas en la base de datos
             */
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.usuarios WHERE cedula = :c OR correo_institucional = :e");
            $checkStmt->execute(['c' => $input['cedula'], 'e' => $input['correo_institucional']]);
            if ($checkStmt->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(["error" => "La cedula o el correo ya están registrados."]);
                exit;
            }

            /*
             * validacion de la contraseña hasheada
             */
            $password_hash = password_hash($input['password_hash'], PASSWORD_DEFAULT);

            /*
             * insertamos lo datos enviados del usuario a la base de datos y enviamos un mensaje de exito si fue un 201.
             */
            $sql = "INSERT INTO unexca_db.usuarios (cedula, nombres, apellidos, correo_institucional, password_hash, id_tipo)
                VALUES (:cedula, :nombres, :apellidos, :correo_institucional, :password_hash, :id_tipo)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'cedula' => trim($input['cedula']),
                'nombres' => trim($input['nombres']),
                'apellidos' => trim($input['apellidos']),
                'correo_institucional' => trim($input['correo_institucional']),
                'password_hash' => $password_hash,
                'id_tipo' => $input['id_tipo']
            ]);

            http_response_code(201);
            echo json_encode(["message" => "Usuario creado exitosamente"]);

        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Error interno al crear el usuario"]);
        }
        break;

    case "PUT":


        $input = json_decode(file_get_contents('php://input'), true);

        $id_usuario = filter_input(INPUT_GET, 'id_usuario', FILTER_VALIDATE_INT);

        $auth->protegerRuta('editar_usuario');

        if (!$id_usuario) {
            http_response_code(400);
            echo json_encode(["error" => "el id del usuario no es válido"]);
            break;
        }

        $checkExist = $pdo->prepare("SELECT id_usuario FROM unexca_db.usuarios WHERE id_usuario = :id");
        $checkExist->execute(['id' => $id_usuario]);
        if (!$checkExist->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Usuario no encontrado"]);
            break;
        }


        $camposRequeridos = ['id_tipo', 'cedula', 'nombres', 'apellidos', 'correo_institucional'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($input[$campo]) || strlen(trim((string) $input[$campo])) === 0) {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                exit;
            }
        }

        $checkDup = $pdo->prepare("SELECT id_usuario FROM unexca_db.usuarios
                                   WHERE (cedula = :c OR correo_institucional = :e)
                                   AND id_usuario != :id");
        $checkDup->execute([
            'c' => $input['cedula'],
            'e' => $input['correo_institucional'],
            'id' => $id_usuario
        ]);

        if ($checkDup->fetch()) {
            http_response_code(409);
            echo json_encode(["error" => "La cédula o el correo ya están en uso por otro usuario"]);
            break;
        }

        try {
            $pass_to_update = !empty($input['password_hash'])
                ? password_hash($input['password_hash'], PASSWORD_DEFAULT)
                : null;

            $sql = "UPDATE unexca_db.usuarios
                    SET cedula = :cedula,
                        nombres = :nombres,
                        apellidos = :apellidos,
                        correo_institucional = :correo,
                        id_tipo = :tipo" .
                ($pass_to_update ? ", password_hash = :pass" : "") . "
                    WHERE id_usuario = :id";

            $params = [
                'cedula' => trim($input['cedula']),
                'nombres' => trim($input['nombres']),
                'apellidos' => trim($input['apellidos']),
                'correo' => trim($input['correo_institucional']),
                'tipo' => $input['id_tipo'],
                'id' => $id_usuario
            ];

            if ($pass_to_update) {
                $params['pass'] = $pass_to_update;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo json_encode(["message" => "Usuario actualizado con éxito"]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en la base de datos", "detalle" => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}
