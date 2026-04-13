<?php
// login.php
session_start();
include_once '../../config/db.php';

header("Content-Type: application/json");


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$json = file_get_contents('php://input');
$input = json_decode($json, true);

$correo = isset($input['correo']) ? trim($input['correo']) : null;
$password = isset($input['password']) ? $input['password'] : null;

if (!$correo || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Correo y contraseña son obligatorios"]);
    exit;
}

try {
    $sql = "SELECT u.id_usuario, u.id_persona, u.cedula, u.correo_institucional, u.password_hash, 
            u.id_tipo, u.id_estatus, p.nombres, p.apellidos
            FROM unexca_db.usuarios u
            INNER JOIN unexca_db.datos_personas p ON u.id_persona = p.id_persona
            WHERE u.correo_institucional = :correo";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
        http_response_code(401);
        echo json_encode(["error" => "Credenciales incorrectas"]);
        exit;
    }

    //Verificar si el usuario está activo
    if ($usuario['id_estatus'] == 2) { // 2 es el estatus de "Inactivo"
        http_response_code(403);
        echo json_encode(["error" => "Cuenta desactivada. Contacte al administrador"]);
        exit;
    }

    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['id_tipo']    = $usuario['id_tipo'];
    $_SESSION['nombre_completo'] = $usuario['nombres'] . " " . $usuario['apellidos'];

    $updateStmt = $pdo->prepare("UPDATE unexca_db.usuarios SET ultimo_login = NOW() WHERE id_usuario = :id");
    $updateStmt->execute(['id' => $usuario['id_usuario']]);

    echo json_encode([
        "message" => "Inicio de sesión exitoso",
        "usuario" => [
            "cedula" => $usuario['cedula'],
            "nombres" => $usuario['nombres'],
            "apellidos" => $usuario['apellidos'],
            "correo" => $usuario['correo_institucional']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error en Login: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Error interno del servidor" . $e->getMessage()]);
}
?>