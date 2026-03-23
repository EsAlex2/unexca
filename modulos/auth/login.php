<?php
// login.php
include_once '../../config/db.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

// 1. Obtener datos del input
$json = file_get_contents('php://input');
$input = json_decode($json, true);

$correo = isset($input['correo']) ? trim($input['correo']) : null;
$password = isset($input['password']) ? $input['password'] : null;

// 2. Validaciones básicas
if (!$correo || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Correo y contraseña son obligatorios"]);
    exit;
}

try {
    // 3. Buscar al usuario por correo
    // Nota: Seleccionamos password_hash para verificarlo y el id_tipo para la sesión
    $sql = "SELECT id_usuario, correo_institucional, password_hash, id_tipo, nombres, apellidos, activo 
            FROM unexca_db.usuarios 
            WHERE correo_institucional = :correo";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch();

    // 4. Verificar existencia y contraseña
    if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
        http_response_code(401);
        echo json_encode(["error" => "Credenciales incorrectas"]);
        exit;
    }

    // 5. Verificar si el usuario está activo
    if ($usuario['activo'] == false) {
        http_response_code(403);
        echo json_encode(["error" => "Cuenta desactivada. Contacte al administrador"]);
        exit;
    }

    // 6. Iniciar Sesión (los datos que usará AuthMiddleware)
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['id_tipo']    = $usuario['id_tipo'];
    $_SESSION['nombre_completo'] = $usuario['nombres'] . " " . $usuario['apellidos'];

    // 7. Actualizar último login
    $updateStmt = $pdo->prepare("UPDATE unexca_db.usuarios SET ultimo_login = NOW() WHERE id_usuario = :id");
    $updateStmt->execute(['id' => $usuario['id_usuario']]);

    echo json_encode([
        "message" => "Inicio de sesión exitoso",
        "usuario" => [
            "nombres" => $usuario['nombres'],
            "apellidos" => $usuario['apellidos'],
            "id_tipo" => $usuario['id_tipo']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error en Login: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Error interno del servidor"]);
}