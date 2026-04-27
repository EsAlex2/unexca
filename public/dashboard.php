<?php
include '../config/init.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <main id="content">
        <div class="top-nav">
            <h5 class="m-0">Panel de Control</h5>
            <div class="text-muted">
                <i class="bi bi-person-circle"></i> <?php echo $_SESSION['nombre_completo']; ?>
            </div>
        </div>

        <div class="container-fluid">
            <h1>Bienvenido al Sistema</h1>
            <?php if (isset($_SESSION['id_tipo']) && $_SESSION['id_tipo'] == 1): ?>
                <p>Como administrador, tienes acceso completo a todas las funcionalidades del sistema.</p>
            <?php else: ?>
                <p>Explora las secciones disponibles en el menú lateral para gestionar tus tareas.</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/sidebar.js"></script>
</body>

</html>