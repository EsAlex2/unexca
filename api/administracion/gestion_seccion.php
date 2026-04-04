<?php
include_once '../../config/db.php';
include_once '../../auth/auth_helper.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            if (isset($_GET['cod_seccion'])) {
                $cod_seccion = filter_input(INPUT_GET, 'cod_seccion', FILTER_SANITIZE_STRING);

                if (!$cod_seccion) {
                    http_response_code(400);
                    echo json_encode(["error" => "Código de sección no válido"]);
                    break;
                }

                $stmt = $pdo->prepare("SELECT id_seccion, cod_seccion, capacidad_max, creado_en, actualizado_en
                                       FROM unexca_db.secciones
                                       WHERE cod_seccion = :cod_seccion");

                $stmt->execute(['cod_seccion' => $cod_seccion]);
                $checkExist = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($checkExist) {
                    echo json_encode($checkExist);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Sección no encontrada"]);
                }


            } else {
                $stmt = $pdo->query("SELECT id_seccion, cod_seccion, capacidad_max, creado_en, actualizado_en FROM unexca_db.secciones ORDER BY id_seccion ASC");
                $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($secciones);
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

            $campos_requeridos = ['cod_seccion', 'capacidad_max'];
            foreach ($campos_requeridos as $campo) {
                
                $valor = isset($input[$campo]) ? trim((string) $input[$campo]) : '';

                if ($valor === '') {
                    http_response_code(400);
                    echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                    exit;
                }
            }

            if (!is_numeric($input['capacidad_max'])) {
                http_response_code(400);
                echo json_encode(["error" => "La capacidad máxima debe ser un número entero."]);
                exit;
            }

            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM unexca_db.secciones WHERE cod_seccion = ?");
            $checkStmt->execute([trim($input['cod_seccion'])]);
            if ($checkStmt->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(["error" => "El código de sección ya existe."]);
                exit;
            }

            $sql = "INSERT INTO unexca_db.secciones (cod_seccion, capacidad_max)
                VALUES (:cod_seccion, :capacidad_max)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'cod_seccion' => trim($input['cod_seccion']),
                'capacidad_max' => (int) $input['capacidad_max']
            ]);

            http_response_code(201);
            echo json_encode(["message" => "Sección creada exitosamente"]);

        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Error interno al crear la sección", "detalle" => $e->getMessage()]);
        }
        break;

    case "PUT":
        $input = json_decode(file_get_contents('php://input'), true);
        $cod_seccion = filter_input(INPUT_GET, 'cod_seccion', FILTER_SANITIZE_STRING);

        if (!$cod_seccion) {
            http_response_code(400);
            echo json_encode(["error" => "El código de sección es obligatorio."]);
            break;
        }

        $checkExist = $pdo->prepare("SELECT id_seccion FROM unexca_db.secciones WHERE cod_seccion = :cod");
        $checkExist->execute(['cod' => $cod_seccion]);
        if (!$checkExist->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Sección no encontrada"]);
            break;
        }

        $campos_requeridos = ['cod_seccion'];
        foreach ($campos_requeridos as $campo) {
            if (empty(trim((string) ($input[$campo] ?? '')))) {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$campo' es obligatorio."]);
                exit;
            }
        }

        $checkDup = $pdo->prepare("SELECT cod_seccion FROM unexca_db.secciones 
                               WHERE cod_seccion = :nuevo_cod 
                               AND cod_seccion != :cod_actual");
        $checkDup->execute([
            'nuevo_cod' => $input['cod_seccion'],
            'cod_actual' => $cod_seccion
        ]);

        if ($checkDup->fetch()) {
            http_response_code(409);
            echo json_encode(["error" => "El nuevo código de sección ya está en uso."]);
            break;
        }

        try {
            $sql = "UPDATE unexca_db.secciones 
                SET cod_seccion = :nuevo_cod, 
                    capacidad_max = :cap,
                    actualizado_en = CURRENT_TIMESTAMP
                WHERE cod_seccion = :cod_url";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nuevo_cod' => $input['cod_seccion'],
                'cap' => $input['capacidad_max'],
                'cod_url' => $cod_seccion
            ]);

            echo json_encode(["message" => "Sección actualizada con éxito"]);

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

