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
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNewPermissions">
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

    <!--MODAL PARA REGISTRAR LOS PERMISOS NUEVOS Y ASIGNARLOS A LOS ROLES Y USUARIOS -->
    <div class="modal fade" id="modalNewPermissions" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Registrar Nuevos Permisos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPersona">
                    <div class="modal-body">
                        <input type="hidden" id="id_persona" name="id_persona">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre del Permiso</label>
                                <input type="text" class="form-control" id="name_permission" name="name_permission"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="name_modulo" class="form-label">Modulo asignado</label>
                                <select class="form-select" id="name_modulo" name="name_modulo" required>
                                    <option value="" selected disabled>Cargando modulos...</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Descripcion</label>
                                <textarea class="form-control" id="description" name="description" row="10"></textarea>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Información</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



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