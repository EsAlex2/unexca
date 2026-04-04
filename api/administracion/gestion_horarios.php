<?php
include_once '../../config/db.php';
include_once '../../auth/auth_helper.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            if (isset($_GET['cod_horario'])) {
                $cod_horario = filter_input(INPUT_GET, 'cod_horario', FILTER_SANITIZE_STRING);
                /*
                si el get envia un codigo de horario no valido, mostrara un error en pantalla.
                */
                if (!$cod_horario) {
                    http_response_code(400);
                    echo json_encode(["error" => "Código de horario no válido"]);
                    break;
                }
                /*
                creamos un query para buscar y mostrar el horario almacenado en base de datos mientras exista el horario seleccionado.
                */
                $stmt = $pdo->prepare("SELECT id_horario, id_asignatura, id_seccion, id_docente, id_aula, id_turno, id_trayecto, cod_hora_inicio, hora_fin, creado_en, actualizado_en
                                       FROM unexca_db.horarios
                                       WHERE cod_horario = :cod_horario");

                $stmt->execute(['cod_horario' => $cod_horario]);
                $checkExist = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($checkExist) {
                    echo json_encode($checkExist);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Horario no encontrado"]);
                }

                /*
                sino ha sido seleccionado el codigo de horario, muestrame todos los horarios almacenados en la base de datos.
                */

            } else {
                $stmt = $pdo->query("SELECT id_horario, id_asignatura, id_seccion, id_docente, id_aula, id_turno, id_trayecto, cod_horario, hora_inicio, hora_fin, creado_en, actualizado_en FROM unexca_db.horarios");
                $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($horarios);
            }
        } catch (PDOException $e) {

            http_response_code(500);
            echo json_encode([
                "error" => "Error de base de datos"
            ]);
        }
        break;

    case 'POST':
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // 1. Validaciones iniciales de campos y formato
            $campos_requeridos = ['id_asignatura', 'id_seccion', 'id_docente', 'id_aula', 'id_turno', 'id_trayecto', 'cod_horario', 'hora_inicio', 'hora_fin'];

            foreach ($campos_requeridos as $campo) {
                $valor = trim((string) ($input[$campo] ?? ''));
                if (empty($valor)) {
                    http_response_code(400);
                    echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                    exit;
                }
            }

            // 2. Validación unificada de existencia (Corregida)
            // Nota: Asegúrate que los nombres sean: secciones, asignaturas, datos_docentes, aulas, turnos, trayectos
            $sqlCheck = "SELECT 
            (SELECT COUNT(*) FROM unexca_db.secciones WHERE id_seccion = ?) as existe_seccion,
            (SELECT COUNT(*) FROM unexca_db.asignatura WHERE id_asignatura = ?) as existe_asignatura,
            (SELECT COUNT(*) FROM unexca_db.datos_docentes WHERE id_docente = ?) as existe_docente,
            (SELECT COUNT(*) FROM unexca_db.aulas WHERE id_aula = ?) as existe_aula,
            (SELECT COUNT(*) FROM unexca_db.turnos WHERE id_turno = ?) as existe_turno,
            (SELECT COUNT(*) FROM unexca_db.trayectos WHERE id_trayecto = ?) as existe_trayecto";

            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([
                $input['id_seccion'],
                $input['id_asignatura'],
                $input['id_docente'],
                $input['id_aula'],
                $input['id_turno'],
                $input['id_trayecto']
            ]);

            $res = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            $validaciones = [
                'existe_seccion' => "La sección seleccionada no existe.",
                'existe_asignatura' => "La asignatura seleccionada no existe.",
                'existe_docente' => "El docente seleccionado no existe.",
                'existe_aula' => "El aula seleccionada no existe.",
                'existe_turno' => "El turno seleccionado no existe.",
                'existe_trayecto' => "El trayecto seleccionado no existe."
            ];

            foreach ($validaciones as $key => $mensaje) {
                if ((int) $res[$key] === 0) {
                    http_response_code(400);
                    echo json_encode(["error" => $mensaje]);
                    exit;
                }
            }

            // 3. Verificar si el código de horario ya existe
            $checkDup = $pdo->prepare("SELECT 1 FROM unexca_db.horarios WHERE cod_horario = ?");
            $checkDup->execute([$input['cod_horario']]);
            if ($checkDup->fetch()) {
                http_response_code(409);
                echo json_encode(["error" => "El código de horario ya existe."]);
                exit;
            }

            // 4. Inserción Final
            $sqlInsert = "INSERT INTO unexca_db.horarios (id_asignatura, id_seccion, id_docente, id_aula, id_turno, id_trayecto, cod_horario, hora_inicio, hora_fin)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                $input['id_asignatura'],
                $input['id_seccion'],
                $input['id_docente'],
                $input['id_aula'],
                $input['id_turno'],
                $input['id_trayecto'],
                trim($input['cod_horario']),
                $input['hora_inicio'],
                $input['hora_fin']
            ]);

            http_response_code(201);
            echo json_encode(["message" => "Horario creado exitosamente"]);

        } catch (PDOException $e) {
            error_log("Error en POST horario: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "error" => "Error interno al crear el horario",
                "detalle" => $e->getMessage() // Esto te dirá exactamente qué tabla o columna falla
            ]);
        }
        break;

    case "PUT":

        $input = json_decode(file_get_contents('php://input'), true);

        $cod_horario = filter_input(INPUT_GET, 'cod_horario', FILTER_SANITIZE_STRING);

        if (!$cod_horario) {
            http_response_code(400);
            echo json_encode(["error" => "El código de horario no es válido."]);
            break;
        }

        $checkExist = $pdo->prepare("SELECT cod_horario FROM unexca_db.horarios WHERE cod_horario = :cod_horario");
        $checkExist->execute(['cod_horario' => $cod_horario]);
        if (!$checkExist->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Horario no encontrado"]);
            break;
        }


        $campos_requeridos = [
            'id_asignatura',
            'id_docente',
            'id_aula',
            'id_turno',
            'id_trayecto',
            'cod_horario',
            'hora_inicio',
            'hora_fin'
        ];

        foreach ($campos_requeridos as $campo) {
            $valor = trim((string) ($input[$campo] ?? ''));

            if (empty($valor)) {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                exit;
            }

            if ($campo === 'hora_inicio' || $campo === 'hora_fin') {
                $formato = 'H:i:s';
                $date = DateTime::createFromFormat($formato, $valor);

                if (!($date && $date->format($formato) === $valor)) {
                    http_response_code(400);
                    echo json_encode(["error" => "El formato de '$campo' debe ser HH:MM:SS (24 horas)."]);
                    exit;
                }
            }
        }

        if (strtotime($input['hora_inicio']) >= strtotime($input['hora_fin'])) {
            http_response_code(400);
            echo json_encode(["error" => "La hora de inicio debe ser menor a la hora de fin."]);
            exit;
        }

        $checkDup = $pdo->prepare("SELECT cod_horario FROM unexca_db.horarios
                                   WHERE (cod_horario = :c OR hora_inicio = :h)
                                   AND cod_horario != :id");
        $checkDup->execute([
            'c' => $input['cod_horario'],
            'h' => $input['hora_inicio'],
            'id' => $cod_horario
        ]);

        if ($checkDup->fetch()) {
            http_response_code(409);
            echo json_encode(["error" => "El código de horario o la hora de inicio ya están en uso por otro horario."]);
            break;
        }

        try {

            $sql = "UPDATE unexca_db.horarios
                    SET id_asignatura = :id_asignatura,
                        id_docente = :id_docente,
                        id_aula = :id_aula,
                        id_turno = :id_turno,
                        id_trayecto = :id_trayecto,
                        cod_horario = :cod_horario,
                        hora_inicio = :hora_inicio,
                        hora_fin = :hora_fin
                    WHERE cod_horario = :cod_horario";

            $params = [
                'id_asignatura' => trim($input['id_asignatura']),
                'id_docente' => trim($input['id_docente']),
                'id_aula' => trim($input['id_aula']),
                'id_turno' => trim($input['id_turno']),
                'id_trayecto' => trim($input['id_trayecto']),
                'cod_horario' => trim($input['cod_horario']),
                'hora_inicio' => trim($input['hora_inicio']),
                'hora_fin' => trim($input['hora_fin'])
            ];

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo json_encode(["message" => "Horario ha sido actualizado con éxito"]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en la base de datos", "detalle" => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        $cod_horario = filter_input(INPUT_GET, 'cod_horario', FILTER_SANITIZE_STRING);

        if (!$cod_horario) {
            http_response_code(400);
            echo json_encode(["error" => "El código de horario no es válido."]);
            break;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM unexca_db.horarios WHERE cod_horario = :cod_horario");
            $stmt->execute(['cod_horario' => $cod_horario]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(["message" => "Horario eliminado exitosamente"]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Horario no encontrado"]);
            }
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

