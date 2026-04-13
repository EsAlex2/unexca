<?php 
session_start();

$current_page = basename($_SERVER['PHP_SELF']);
$login_page = 'index.php';


if ($current_page !== $login_page) {
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: index.php");
        exit;
    }
}

define('SITE_NAME', 'UNEXCA - Sistema de Gestión Académica');
define('BASE_URL', 'http://localhost/unexca/public/');
define('MODULO_ESTUDIANTES', 'modulos/students.php');
define('MODULO_ROLES', 'modulos/roles.php');
define('MODULO_USUARIOS', 'modulos/users.php');
define('DASHBOARD_PRINCIPAL', 'dashboard.php'); 
define('LOGIN_PAGE', 'index.php');
define('MODULO_PERSONAS', 'modulos/persons_saime.php');


$pages = [
    MODULO_ESTUDIANTES => 'Gestión de Estudiantes',
    MODULO_ROLES => 'Gestión de Roles',
    MODULO_USUARIOS => 'Gestión de Usuarios',
    DASHBOARD_PRINCIPAL => 'Panel de Control - UNEXCA',
    LOGIN_PAGE => 'Inicio de Sesión - UNEXCA',
    MODULO_PERSONAS => 'Gestión de Personas'
];

$page_title = SITE_NAME;

foreach ($pages as $ruta => $titulo) {
    if (strpos($_SERVER['PHP_SELF'], $ruta) !== false) {
        $page_title = $titulo;
        break;
    }
}