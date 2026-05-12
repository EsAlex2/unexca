<?php
include_once '../../config/db.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            $path = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : null;

            if ($path) {
                $asignatura_codigo = htmlspecialchars($path);

                $stmt = $pdo->prepare("SELECT id_asignatura, id_pnf, id_trayecto, codigo, nombre, unidades_credito, id_caracter
                                   FROM unexca_db.asignatura
                                   WHERE codigo = :codigo");

                $stmt->execute(['codigo' => $asignatura_codigo]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Asignatura no encontrada"]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM unexca_db.asignatura");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'POST':
        try {
            $json = file_get_contents('php://input');
            $input = json_decode($json, true);

            $campos_requeridos = ['id_pnf', 'id_trayecto', 'codigo', 'nombre', 'unidades_credito', 'id_caracter'];
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
            $sql = "INSERT INTO unexca_db.asignatura (id_pnf, id_trayecto, codigo, nombre, unidades_credito, id_caracter)
                VALUES (:id_pnf, :id_trayecto, :codigo, :nombre, :unidades_credito, :id_caracter)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id_pnf' => trim($input['id_pnf']),
                'id_trayecto' => trim($input['id_trayecto']),
                'codigo' => trim($input['codigo']),
                'nombre' => trim($input['nombre']),
                'unidades_credito' => trim($input['unidades_credito']),
                'id_caracter' => trim($input['id_caracter'])
            ]);

            http_response_code(201);
            echo json_encode(["message" => "Asignatura creada exitosamente"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "error" => "Error de base de datos",
                "detalles" => $e->getMessage() // Esto te dirá si falta una columna o hay error de FK
            ]);
        }
        break;

    case "PUT":
        // 1. Obtener el código de la asignatura desde el final de la URL
        $path = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : null;
        $json = file_get_contents('php://input');
        $input = json_decode($json, true);

        // Validar que el código esté presente en la URL
        if (!$path) {
            http_response_code(400);
            echo json_encode(["error" => "Debe proporcionar el código de la asignatura"]);
            break;
        }

        $codigo_url = htmlspecialchars($path);

        try {
            // 2. Verificar si la asignatura existe y obtener su ID primario
            $checkExist = $pdo->prepare("SELECT id_asignatura FROM unexca_db.asignatura WHERE codigo = :cod_url");
            $checkExist->execute(['cod_url' => $codigo_url]);
            $asignatura_actual = $checkExist->fetch(PDO::FETCH_ASSOC);

            if (!$asignatura_actual) {
                http_response_code(404);
                echo json_encode(["error" => "La asignatura con código '$codigo_url' no existe."]);
                break;
            }

            $id_asignatura_interna = $asignatura_actual['id_asignatura'];

            // 3. Validar que todos los campos necesarios vengan en el JSON
            $campos_requeridos = ['id_pnf', 'id_trayecto', 'codigo', 'nombre', 'unidades_credito', 'id_caracter'];
            foreach ($campos_requeridos as $campo) {
                if (!isset($input[$campo]) || strlen(trim((string) $input[$campo])) === 0) {
                    http_response_code(400);
                    echo json_encode(["error" => "El campo '$campo' es obligatorio para la actualización."]);
                    exit;
                }
            }

            // 4. Verificar si el NUEVO código o nombre ya lo tiene OTRA asignatura (evitar duplicados)
            $checkDup = $pdo->prepare("SELECT id_asignatura FROM unexca_db.asignatura 
                                       WHERE (codigo = :c OR nombre = :n) 
                                       AND id_asignatura != :id");
            $checkDup->execute([
                'c' => trim($input['codigo']),
                'n' => trim($input['nombre']),
                'id' => $id_asignatura_interna
            ]);

            if ($checkDup->fetch()) {
                http_response_code(409);
                echo json_encode(["error" => "El nuevo código o nombre ya pertenece a otra asignatura."]);
                break;
            }

            // 5. Ejecutar la actualización final
            $sql = "UPDATE unexca_db.asignatura
                    SET id_pnf = :id_pnf,
                        id_trayecto = :id_trayecto,
                        codigo = :codigo_nuevo,
                        nombre = :nombre,
                        unidades_credito = :uc,
                        id_caracter = :caracter,
                        actualizado_en = NOW()
                    WHERE id_asignatura = :id_original";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id_pnf'         => (int) $input['id_pnf'],
                'id_trayecto'    => (int) $input['id_trayecto'],
                'codigo_nuevo'   => trim($input['codigo']),
                'nombre'         => trim($input['nombre']),
                'uc'             => (int) $input['unidades_credito'],
                'caracter'       => (int) $input['id_caracter'],
                'id_original'    => $id_asignatura_interna
            ]);

            echo json_encode([
                "message" => "Asignatura '$codigo_url' actualizada con éxito",
                "detalles" => "Se ha guardado como " . $input['codigo']
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "error" => "Error de base de datos al actualizar",
                "detalles" => $e->getMessage()
            ]);
        }
        break;
}
