<?php
// auth_helper.php
/**
 * Verificacion para saber si un tipo de usuario, tiene un permiso específico asignado.
 */
class AuthMiddleware
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Valida sesión, estado de la cuenta (activo) y permisos.
     */
    public function protegerRuta($nombre_permiso)
    {
        $id_usuario = $_SESSION['id_usuario'] ?? null;
        $id_tipo_usuario = $_SESSION['id_tipo'] ?? null;

        //Verificación de sesión
        if (!$id_usuario || !$id_tipo_usuario) {
            http_response_code(401);
            echo json_encode(["error" => "Sesión no iniciada."]);
            exit;
        }

        try {
            //Verificación combinada: ¿El usuario está activo? ¿El rol tiene el permiso?
            $sql = "SELECT u.activo, 
                    (SELECT COUNT(*) 
                     FROM unexca_db.roles_permisos rp
                     INNER JOIN unexca_db.permisos p ON rp.id_permiso = p.id_permiso
                     WHERE rp.id_tipo_usuario = u.id_tipo 
                     AND p.nombre_permiso = :nombre) as tiene_permiso
                    FROM unexca_db.usuarios u
                    WHERE u.id_usuario = :id_user";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id_user' => $id_usuario,
                'nombre' => $nombre_permiso
            ]);

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            //Validar si el usuario existe
            if (!$resultado) {
                http_response_code(404);
                echo json_encode(["error" => "Usuario no encontrado."]);
                exit;
            }

            // Validar si el usuario está activo
            if ($resultado['activo'] == false) {
                session_destroy();
                http_response_code(403);
                echo json_encode(["error" => "Tu cuenta está desactivada. Contacta al administrador."]);
                exit;
            }

            // 5. Validar el permiso específico
            if ($resultado['tiene_permiso'] == 0) {
                http_response_code(403);
                echo json_encode(["error" => "No tienes permiso para '$nombre_permiso'."]);
                exit;
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error de validación: " . $e->getMessage()]);
            exit;
        }
    }
}

