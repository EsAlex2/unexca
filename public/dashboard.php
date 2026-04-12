<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - UNEXCA</title>
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
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/auth.js"></script>
    <script src="js/sidebar.js"></script>
</body>
</html>