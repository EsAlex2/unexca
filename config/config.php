<?php 
//config/config.php
$db_server = "127.0.0.1";
$db_name = "admin_sql";
$db_user = "postgres";
$db_password = "qwerty2801**";
$db_port = 5432;
$db_database = "pgsql";

define('SITE_NAME', 'UNEXCA - Sistema de Gestión Académica');
define('BASE_URL', 'http://localhost/unexca');

//DEFINICION DE RUTAS A LOS MODULOS
define('MODULO_ESTUDIANTES', 'modules/estudiantes/');
define('MODULO_DOCENTES', 'modules/docentes/');
define('MODULO_CALIFICACIONES', 'modules/calificaciones/');
define('MODULO_ADMIN', 'modules/administrativo/');
define('MODULO_REPORTES', 'modules/reportes/');
define('MODULO_COMUNICACION', 'modules/comunicacion/');


