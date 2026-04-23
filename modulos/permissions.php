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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <?php include '../public/includes/sidebar.php'; ?>

    <main id="content">
        <div class="top-nav">
            <h5 class="m-0"><i class="bi bi-person-fill-gear"></i> Gestión de Permisos</h5>
            <div class="text-muted small">Administrador: <?php echo $_SESSION['nombre_completo']; ?></div>
        </div>

        <div class="container-fluid pt-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Permisos definidos</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalRol">
                        <i class="bi bi-plus-circle-fill"></i> Nuevo Permiso
                    </button>
                </div>
                <div class="card-body">
                    <table id="tablePermissions" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>Nombre del Permiso</th>
                                <th>Descripción</th>
                                <th>Modulo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../public/js/permissions.js"></script>
    <script src="../public/js/sidebar.js"></script>
    <script src="../public/js/auth.js"></script>
</body>
</html>