<?php
include_once '../../config/db.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            if (isset($_GET['id_asignatura'])) {
                $asignatura = filter_input(INPUT_GET, 'id_asignatura', FILTER_VALIDATE_INT);
                /*
                si el get envia una el id no valido, mostrara un error en pantalla.
                */
                if (!$asignatura) {
                    http_response_code(400);
                    echo json_encode(["error" => "ID de asignatura no válido"]);
                    break;
                }
                /*
                creamos un query para buscar y mostrar la asignatura almacenada en base de datos mientras exista la asignatura seleccionada.
                */
                $stmt = $pdo->prepare("SELECT id_asignatura, id_pnf, id_trayecto, codigo, nombre, unidades_credito
                                       FROM unexca_db.asignatura
                                       WHERE id_asignatura = :id_asignatura");

                $stmt->execute(['id_asignatura' => $asignatura]);
                $checkExist = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($checkExist) {
                    echo json_encode($checkExist);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Asignatura no encontrada"]);
                }

                /*
                sino ha sido seleccionado la cedula del estudiante, muestrame todos los estudiantes almacenados en la base de datos.
                */

            } else {
                $stmt = $pdo->query("SELECT id_asignatura, id_pnf, id_trayecto, codigo, nombre, unidades_credito FROM unexca_db.asignatura");
                $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($asignaturas);
            }
        } catch (PDOException $e) {
            /*
            en caso de un error en la busqueda de las asignaturas, captura el error y muestralo en pantalla.
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

            $campos_requeridos = ['id_pnf', 'id_trayecto', 'codigo', 'nombre', 'unidades_credito'];
            foreach ($campos_requeridos as $campo) {
                if (empty(trim((string) ($input[$campo] ?? '')))) {
                    http_response_code(400);
                    echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                    exit;
                }
            }

            /*
             * validacion de codigo y correo que no esten registrados en la base de datos
             * prepraramos una consulta a la base de datos, seleccionamos y contamos los usuarios mientras la cedula o correo ingresada por el usuario
             * no esten registradas en la base de datos
             */
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.asignatura WHERE codigo = ?");
            $checkStmt->execute([$input['codigo']]);
            if ($checkStmt->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(["error" => "La asignatura ya existe."]);
                exit;
            }


            /*
             * insertamos lo datos enviados del usuario a la base de datos y enviamos un mensaje de exito si fue un 201.
             */
            $sql = "INSERT INTO unexca_db.asignatura (id_pnf, id_trayecto, codigo, nombre, unidades_credito)
                VALUES (:id_pnf, :id_trayecto, :codigo, :nombre, :unidades_credito)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id_pnf' => trim($input['id_pnf']),
                'id_trayecto' => trim($input['id_trayecto']),
                'codigo' => trim($input['codigo']),
                'nombre' => trim($input['nombre']),
                'unidades_credito' => trim($input['unidades_credito'])
            ]);

            http_response_code(201);
            echo json_encode(["message" => "Asignatura creada exitosamente"]);

        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Error interno al crear la asignatura"]);
        }
        break;

    case "PUT":

        $input = json_decode(file_get_contents('php://input'), true);

        $id_asignatura = filter_input(INPUT_GET, 'id_asignatura', FILTER_VALIDATE_INT);

        if (!$id_asignatura) {
            http_response_code(400);
            echo json_encode(["error" => "El ID de la asignatura no es válido"]);
            break;
        }

        $checkExist = $pdo->prepare("SELECT id_asignatura FROM unexca_db.asignatura WHERE id_asignatura = :id");
        $checkExist->execute(['id' => $id_asignatura]);
        if (!$checkExist->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Asignatura no encontrada"]);
            break;
        }

        $campos_requeridos = [
            'id_pnf',
            'id_trayecto',
            'codigo',
            'nombre',
            'unidades_credito'
        ];
        foreach ($campos_requeridos as $campo) {
            if (!isset($input[$campo]) || strlen(trim((string) $input[$campo])) === 0) {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                exit;
            }
        }

        $checkDup = $pdo->prepare("SELECT id_asignatura FROM unexca_db.asignatura 
                           WHERE (codigo = :c OR nombre = :n) 
                           AND id_asignatura != :id");
        $checkDup->execute([
            'c' => $input['codigo'],
            'n' => $input['nombre'],
            'id' => $id_asignatura
        ]);

        if ($checkDup->fetch()) {
            http_response_code(409);
            echo json_encode(["error" => "El código ya está en uso por otra asignatura."]);
            break;
        }

        try {

            $sql = "UPDATE unexca_db.asignatura
                    SET id_pnf = :id_pnf,
                        id_trayecto = :id_trayecto,
                        codigo = :codigo,
                        nombre = :nombre,
                        unidades_credito = :unidades_credito
                    WHERE id_asignatura = :id";

            $params = [
                'id_pnf' => (int) $input['id_pnf'],
                'id_trayecto' => (int) $input['id_trayecto'],
                'codigo' => trim($input['codigo']),
                'nombre' => trim($input['nombre']),
                'unidades_credito' => (int) $input['unidades_credito'],
                'id' => $id_asignatura
            ];

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo json_encode(["message" => "Asignatura ha sido actualizada con éxito"]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en la base de datos"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;

}

