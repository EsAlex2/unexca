<?php
// modulos/auth/logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_unset();
session_destroy();

header("Content-Type: application/json");
echo json_encode(["message" => "Sesión cerrada correctamente"]);
exit;
