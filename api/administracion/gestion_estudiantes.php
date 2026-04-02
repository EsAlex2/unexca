<?php
include_once '../../config/db.php';
include_once '../../auth/auth_helper.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            if (isset($_GET['cedula_identidad'])) {
                $cedula = filter_input(INPUT_GET, 'cedula_identidad', FILTER_SANITIZE_STRING);
                /*
                si el get envia una cedula no valida, mostrara un error en pantalla.
                */
                if (!$cedula) {
                    http_response_code(400);
                    echo json_encode(["error" => "Cédula no válida"]);
                    break;
                }
                /*
                creamos un query para buscar y mostrar el usuario almacenado en base de datos mientras exista la cedula del estudiante y mostrar el estudiante seleccionado.
                */
                $stmt = $pdo->prepare("SELECT id_estudiante, cedula_identidad, nombres_estudiante, apellidos_estudiante, fecha_nacimiento, correo_personal, direccion_habitacion, telefono_personal, fecha_ingreso, id_estatus
                                       FROM unexca_db.datos_estudiantes
                                       WHERE cedula_identidad = :cedula");

                $stmt->execute(['cedula' => $cedula]);
                $checkExist = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($checkExist) {
                    echo json_encode($checkExist);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Estudiante no encontrado"]);
                }

                /*
                sino ha sido seleccionado la cedula del estudiante, muestrame todos los estudiantes almacenados en la base de datos.
                */

            } else {
                $stmt = $pdo->query("SELECT id_estudiante, cedula_identidad, nombres_estudiante, apellidos_estudiante, fecha_nacimiento, correo_personal, direccion_habitacion, telefono_personal, fecha_ingreso, id_estatus FROM unexca_db.datos_estudiantes");
                $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($estudiantes);
            }
        } catch (PDOException $e) {
            /*
            en caso de un error en la busqueda de los estudiantes, captura el error y muestralo en pantalla.
            */
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

            $campos_requeridos = ['cedula_identidad', 'nombres_estudiante', 'apellidos_estudiante', 'genero', 'correo_personal'];
            foreach ($campos_requeridos as $campo) {
                if (empty(trim((string) ($input[$campo] ?? '')))) {
                    http_response_code(400);
                    echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                    exit;
                }
            }

            if (!in_array($input['genero'], ['M', 'F', 'O'])) {
                http_response_code(400);
                echo json_encode(["error" => "Género no válido."]);
                exit;
            }

            /*
            luego, creamos una validacion para saber si existe el campo de correos y verificamos si el correo que el usuario
            esta suministrandoen en el campo es valido.
            */
            $email = isset($input['correo_personal']) ? trim($input['correo_personal']) : '';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(["error" => "El formato del correo electrónico no es válido."]);
                exit;
            }

            /*
             * validacion de cedula y correo que no esten registrados en la base de datos
             * prepraramos una consulta a la base de datos, seleccionamos y contamos los usuarios mientras la cedula o correo ingresada por el usuario
             * no esten registradas en la base de datos
             */
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.datos_estudiantes WHERE cedula_identidad = ? OR correo_personal = ?");
            $checkStmt->execute([$input['cedula_identidad'], $input['correo_personal']]);
            if ($checkStmt->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(["error" => "La cédula o el correo ya existen."]);
                exit;
            }


            /*
             * insertamos lo datos enviados del usuario a la base de datos y enviamos un mensaje de exito si fue un 201.
             */
            $sql = "INSERT INTO unexca_db.datos_estudiantes (cedula_identidad, nombres_estudiante, apellidos_estudiante, genero, fecha_nacimiento, correo_personal, telefono_personal, direccion_habitacion, fecha_ingreso)
                VALUES (:cedula_identidad, :nombres_estudiante, :apellidos_estudiante, :genero, :fecha_nacimiento, :correo_personal, :telefono_personal, :direccion_habitacion, :fecha_ingreso)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'cedula_identidad' => trim($input['cedula_identidad']),
                'nombres_estudiante' => trim($input['nombres_estudiante']),
                'apellidos_estudiante' => trim($input['apellidos_estudiante']),
                'genero' => trim($input['genero']),
                'fecha_nacimiento' => trim($input['fecha_nacimiento']),
                'correo_personal' => trim($input['correo_personal']),
                'telefono_personal' => trim($input['telefono_personal']),
                'direccion_habitacion' => trim($input['direccion_habitacion']),
                'fecha_ingreso' => trim($input['fecha_ingreso'])
            ]);

            http_response_code(201);
            echo json_encode(["message" => "Estudiante creado exitosamente"]);

        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Error interno al crear el estudiante"]);
        }
        break;

    case "PUT":

        $input = json_decode(file_get_contents('php://input'), true);

        $id_estudiante = filter_input(INPUT_GET, 'cedula_identidad', FILTER_SANITIZE_STRING);

        if (!$id_estudiante) {
            http_response_code(400);
            echo json_encode(["error" => "la cédula del estudiante no es válida"]);
            break;
        }

        $checkExist = $pdo->prepare("SELECT cedula_identidad FROM unexca_db.datos_estudiantes WHERE cedula_identidad = :cedula");
        $checkExist->execute(['cedula' => $id_estudiante]);
        if (!$checkExist->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Estudiante no encontrado"]);
            break;
        }


        $campos_requeridos = [
            'cedula_identidad',
            'nombres_estudiante',
            'apellidos_estudiante',
            'genero',
            'fecha_nacimiento',
            'correo_personal',
            'telefono_personal',
            'direccion_habitacion',
            'fecha_ingreso',
        ];
        foreach ($campos_requeridos as $campo) {
            if (!isset($input[$campo]) || strlen(trim((string) $input[$campo])) === 0) {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                exit;
            }
        }

        $checkDup = $pdo->prepare("SELECT cedula_identidad FROM unexca_db.datos_estudiantes
                                   WHERE (cedula_identidad = :c OR correo_personal = :e)
                                   AND cedula_identidad != :id");
        $checkDup->execute([
            'c' => $input['cedula_identidad'],
            'e' => $input['correo_personal'],
            'id' => $id_estudiante
        ]);

        if ($checkDup->fetch()) {
            http_response_code(409);
            echo json_encode(["error" => "La cédula o el correo ya están en uso por otro estudiante."]);
            break;
        }

        try {

            $sql = "UPDATE unexca_db.datos_estudiantes
                    SET cedula_identidad = :cedula,
                        nombres_estudiante = :nombres,
                        apellidos_estudiante = :apellidos,
                        genero = :genero,
                        fecha_nacimiento = :fecha_nacimiento,
                        correo_personal = :correo,
                        telefono_personal = :telefono,
                        direccion_habitacion = :direccion,
                        fecha_ingreso = :fecha,
                        id_estatus = :estatus,
                        actualizado_en = NOW()
                    WHERE cedula_identidad = :id";

            $params = [
                'cedula' => trim($input['cedula_identidad']),
                'nombres' => trim($input['nombres_estudiante']),
                'apellidos' => trim($input['apellidos_estudiante']),
                'genero' => trim($input['genero']),
                'fecha_nacimiento' => trim($input['fecha_nacimiento']),
                'correo' => trim($input['correo_personal']),
                'telefono' => trim($input['telefono_personal']),
                'direccion' => trim($input['direccion_habitacion']),
                'fecha' => trim($input['fecha_ingreso']),
                'estatus' => isset($input['id_estatus']) ? trim($input['id_estatus']) : null,
                'id' => $id_estudiante
            ];

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo json_encode(["message" => "Estudiante ha sido actualizado con éxito"]);

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

