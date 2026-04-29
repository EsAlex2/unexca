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

                $sql = "SELECT id_persona, cedula_identidad, id_estatus,
                    nombres, apellidos, genero, fecha_nacimiento, correo_personal,
                    telefono_personal, direccion_habitacion, fecha_ingreso, creado_en, actualizado_en
                    FROM unexca_db.datos_personas WHERE cedula_identidad = :cedula";

                $stmt = $pdo->prepare($sql);
                $stmt->execute(['cedula' => $cedula]);
                $persona = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($persona) {
                    echo json_encode($persona);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Los datos del ciudadano no fueron encontrados"]);
                }

            } else {

                $sql = "SELECT id_persona, cedula_identidad, id_estatus,
                    nombres, apellidos, genero, fecha_nacimiento, correo_personal,
                    telefono_personal, direccion_habitacion, fecha_ingreso, creado_en, actualizado_en
                    FROM unexca_db.datos_personas";

                $stmt = $pdo->query($sql);
                $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($personas);
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
            //auth->protegerRuta('crear_usuario');
            $json = file_get_contents('php://input');
            $input = json_decode($json, true);

            $campos_requeridos = [
                'cedula_identidad',
                'nombres',
                'apellidos',
                'genero',
                'fecha_nacimiento',
                'correo_personal',
                'telefono_personal',
                'direccion_habitacion',
                'fecha_ingreso'
            ];
            foreach ($campos_requeridos as $campo) {
                if (!isset($input[$campo]) || empty(trim((string) $input[$campo]))) {
                    http_response_code(400);
                    echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                    exit;
                }
            }

            /*
            luego, creamos una validacion para saber si existe el campo de correos y verificamos si el correo que el usuario
            esta suministrando en en el campo es valido.
            */
            $email = isset($input['correo_personal']) ? trim($input['correo_personal']) : '';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(["error" => "El formato del correo electrónico no es válido."]);
                exit;
            }

            /*
             * --validacion de cedula y correo que no esten registrados en la base de datos--
             * prepraramos una consulta a la base de datos, seleccionamos y contamos los registros mientras la cedula o correo ingresada por el usuario
             * no esten registradas en la base de datos
             */
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.datos_personas WHERE cedula_identidad = :c OR correo_personal = :e");
            $checkStmt->execute(['c' => $input['cedula_identidad'], 'e' => $input['correo_personal']]);
            if ($checkStmt->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(["error" => "La cedula o el correo ya están registrados."]);
                exit;
            }

            /*
             * insertamos lo datos enviados de la persona a la base de datos y enviamos un mensaje de exito si fue un 201.
             */
            $sql = "INSERT INTO unexca_db.datos_personas (cedula_identidad, id_estatus,
            nombres, apellidos, genero,
            correo_personal, 
            fecha_nacimiento, telefono_personal, 
            direccion_habitacion, fecha_ingreso)
            VALUES (:cedula_identidad, :id_estatus, :nombres, :apellidos, :genero, :correo_personal, :fecha_nacimiento, :telefono_personal, :direccion_habitacion, :fecha_ingreso)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'cedula_identidad' => trim($input['cedula_identidad']),
                'id_estatus' => 2,
                'nombres' => trim($input['nombres']),
                'apellidos' => trim($input['apellidos']),
                'genero' => trim($input['genero']),
                'correo_personal' => trim($input['correo_personal']),
                'fecha_nacimiento' => $input['fecha_nacimiento'],
                'telefono_personal' => $input['telefono_personal'],
                'direccion_habitacion' => $input['direccion_habitacion'],
                'fecha_ingreso' => $input['fecha_ingreso']
            ]);

            http_response_code(201);
            echo json_encode(["message" => "Datos registrados exitosamente"]);

        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode([
                "error" => "Error interno al crear el usuario",
                "detalle" => $e->getMessage()
            ]);
        }
        break;

    case "PUT":
        //$auth->protegerRuta('editar_usuario');

        $cedula_identidad = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : null;

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$cedula_identidad) {
            http_response_code(400);
            echo json_encode(["error" => "Debe proporcionar la cédula"]);
            break;
        }

        $checkExist = $pdo->prepare("SELECT cedula_identidad FROM unexca_db.datos_personas WHERE cedula_identidad = :cedula");
        $checkExist->execute(['cedula' => $cedula_identidad]);
        if (!$checkExist->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Los datos del ciudadano no fueron encontrados"]);
            break;
        }

        $camposRequeridos = [
            'cedula_identidad',
            'nombres',
            'apellidos',
            'fecha_nacimiento',
            'correo_personal',
            'telefono_personal',
            'direccion_habitacion'
        ];

        foreach ($camposRequeridos as $campo) {
            if (!isset($input[$campo]) || strlen(trim((string) $input[$campo])) === 0) {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                exit;
            }
        }

        $checkDup = $pdo->prepare("SELECT cedula_identidad FROM unexca_db.datos_personas 
                               WHERE (cedula_identidad = :c OR correo_personal = :e) 
                               AND cedula_identidad != :cedula_original");
        $checkDup->execute([
            'c' => $input['cedula_identidad'],
            'e' => $input['correo_personal'],
            'cedula_original' => $cedula_identidad
        ]);

        if ($checkDup->fetch()) {
            http_response_code(409);
            echo json_encode(["error" => "La nueva cédula o el correo ya están registrados en otro ciudadano"]);
            break;
        }

        try {

            $sql = "UPDATE unexca_db.datos_personas
                SET cedula_identidad = :nueva_cedula,
                    id_estatus = :estatus, 
                    nombres = :nombres,
                    apellidos = :apellidos,
                    correo_personal = :correo,
                    fecha_nacimiento = :fecha_nacimiento,
                    telefono_personal = :telefono_personal,
                    direccion_habitacion = :direccion_habitacion,
                    actualizado_en = NOW()
                WHERE cedula_identidad = :cedula_url";

            $params = [
                'nueva_cedula' => trim($input['cedula_identidad']),
                'estatus' => $input['id_estatus'],
                'nombres' => trim($input['nombres']),
                'apellidos' => trim($input['apellidos']),
                'correo' => trim($input['correo_personal']),
                'fecha_nacimiento' => $input['fecha_nacimiento'],
                'telefono_personal' => $input['telefono_personal'],
                'direccion_habitacion' => $input['direccion_habitacion'],
                'cedula_url' => $cedula_identidad
            ];

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            http_response_code(200);
            echo json_encode(["message" => "Datos del ciudadano actualizados con éxito"]);
            exit;

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
