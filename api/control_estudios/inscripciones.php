<?php
include_once '../../config/db.php';
include_once '../../auth/auth_helper.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            if (isset($_GET['id_estudiante'])) {
                $id_estudiante = filter_input(INPUT_GET, 'id_estudiante', FILTER_VALIDATE_INT);
                
                if (!$id_estudiante) {
                    http_response_code(400);
                    echo json_encode(["error" => "ID de estudiante no válido"]);
                    break;
                }

                // Consulta adaptada a la tabla inscripcion_nue_ingreso
                $stmt = $pdo->prepare("SELECT id_inscripcion, id_estudiante, id_periodo, id_seccion, id_pnf, id_sede, id_trayecto, id_estatus_inscripcion, fecha_formalizacion 
                                       FROM unexca_db.inscripcion_nue_ingreso 
                                       WHERE id_estudiante = :id_estudiante");

                $stmt->execute(['id_estudiante' => $id_estudiante]);
                $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($inscripciones) {
                    echo json_encode($inscripciones);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "No se encontraron inscripciones para el estudiante con ID: $id_estudiante"]);
                }

            } else {
                // Listar todas las inscripciones
                $stmt = $pdo->query("SELECT * FROM unexca_db.inscripcion_nue_ingreso");
                $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($inscripciones);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error de base de datos", "detalle" => $e->getMessage()]);
        }
        break;

    case 'POST':
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // 1. Campos requeridos según tu tabla
            $campos_requeridos = ['id_estudiante', 'id_periodo', 'id_seccion', 'id_pnf', 'id_sede', 'id_trayecto', 'id_estatus_inscripcion'];

            foreach ($campos_requeridos as $campo) {
                if (!isset($input[$campo]) || empty($input[$campo])) {
                    http_response_code(400);
                    echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                    exit;
                }
            }

            // 2. Validación unificada de existencia de llaves foráneas
            $sqlCheck = "SELECT 
                (SELECT COUNT(*) FROM unexca_db.datos_estudiantes WHERE id_estudiante = ?) as existe_estudiante,
                (SELECT COUNT(*) FROM unexca_db.periodos_academicos WHERE id = ?) as existe_periodo,
                (SELECT COUNT(*) FROM unexca_db.secciones WHERE id_seccion = ?) as existe_seccion,
                (SELECT COUNT(*) FROM unexca_db.pnf WHERE id_pnf = ?) as existe_pnf,
                (SELECT COUNT(*) FROM unexca_db.sedes_unexca WHERE id_sede = ?) as existe_sede,
                (SELECT COUNT(*) FROM unexca_db.trayectos WHERE id_trayecto = ?) as existe_trayecto,
                (SELECT COUNT(*) FROM unexca_db.estatus WHERE id_estatus = ?) as existe_estatus";

            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([
                $input['id_estudiante'], $input['id_periodo'], $input['id_seccion'], 
                $input['id_pnf'], $input['id_sede'], $input['id_trayecto'], $input['id_estatus_inscripcion']
            ]);

            $res = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            // Mapeo de errores de validación
            $validaciones = [
                'existe_estudiante' => "El estudiante no existe.",
                'existe_periodo'    => "El periodo académico no existe.",
                'existe_seccion'    => "La sección no existe.",
                'existe_pnf'        => "El PNF no existe.",
                'existe_sede'       => "La sede no existe.",
                'existe_trayecto'   => "El trayecto no existe.",
                'existe_estatus'    => "El estatus de inscripción no existe."
            ];

            foreach ($validaciones as $key => $mensaje) {
                if ((int) $res[$key] === 0) {
                    http_response_code(400);
                    echo json_encode(["error" => $mensaje]);
                    exit;
                }
            }

            // 3. Verificar restricción UNIQUE (estudiante + periodo)
            $checkDup = $pdo->prepare("SELECT 1 FROM unexca_db.inscripcion_nue_ingreso WHERE id_estudiante = ? AND id_periodo = ?");
            $checkDup->execute([$input['id_estudiante'], $input['id_periodo']]);
            if ($checkDup->fetch()) {
                http_response_code(409);
                echo json_encode(["error" => "El estudiante ya posee una inscripción para este periodo académico."]);
                exit;
            }

            // 4. Inserción
            $sqlInsert = "INSERT INTO unexca_db.inscripcion_nue_ingreso 
                          (id_estudiante, id_periodo, id_seccion, id_pnf, id_sede, id_trayecto, id_estatus_inscripcion)
                          VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                $input['id_estudiante'], $input['id_periodo'], $input['id_seccion'],
                $input['id_pnf'], $input['id_sede'], $input['id_trayecto'], $input['id_estatus_inscripcion']
            ]);

            http_response_code(201);
            echo json_encode(["message" => "Inscripción de nuevo ingreso registrada exitosamente"]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error interno", "detalle" => $e->getMessage()]);
        }
        break;

    case "PUT":
        $input = json_decode(file_get_contents('php://input'), true);
        $id_inscripcion = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id_inscripcion) {
            http_response_code(400);
            echo json_encode(["error" => "ID de inscripción no proporcionado."]);
            break;
        }

        try {
            // Actualización de campos (excepto llaves que violen la lógica de negocio si es necesario)
            $sql = "UPDATE unexca_db.inscripcion_nue_ingreso
                    SET id_seccion = :id_seccion,
                        id_pnf = :id_pnf,
                        id_sede = :id_sede,
                        id_trayecto = :id_trayecto,
                        id_estatus_inscripcion = :id_estatus
                    WHERE id_inscripcion = :id_inscripcion";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id_seccion' => $input['id_seccion'],
                'id_pnf' => $input['id_pnf'],
                'id_sede' => $input['id_sede'],
                'id_trayecto' => $input['id_trayecto'],
                'id_estatus' => $input['id_estatus_inscripcion'],
                'id_inscripcion' => $id_inscripcion
            ]);

            echo json_encode(["message" => "Inscripción actualizada con éxito"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al actualizar", "detalle" => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}