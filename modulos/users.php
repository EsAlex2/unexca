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
            <h5 class="m-0"><i class="bi bi-people-fill me-2"></i> Gestión de Usuarios</h5>
            <div class="text-muted small"><?php echo $_SESSION['nombre_completo']; ?></div>
        </div>

        <div class="container-fluid pt-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Usuarios Registrados</h6>
                </div>
                <div class="card-body">
                    <table id="tablaUsuarios" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Nombre Completo</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="listaUsuarios">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalEditarUsuarioLabel">
                        <i class="bi bi-person-gear me-2"></i>Editar Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarUsuario">
                    <div class="modal-body">
                        <input type="hidden" id="edit_user_id">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="edit_cedula" class="form-label fw-bold small">Cédula de Identidad</label>
                                <input type="text" class="form-control bg-light" id="edit_cedula" readonly>
                            </div>

                            <div class="col-md-12">
                                <label for="edit_nombre" class="form-label fw-bold small">Nombre Completo</label>
                                <input type="text" class="form-control" id="edit_nombre" required>
                            </div>

                            <div class="col-md-12">
                                <label for="edit_email" class="form-label fw-bold small">Correo Electrónico</label>
                                <input type="email" class="form-control" id="edit_email" required>
                            </div>

                            <div class="col-md-12">
                                <label for="edit_rol" class="form-label fw-bold small">Rol de Sistema</label>
                                <select class="form-select" id="edit_rol" required>
                                    <option value="" selected disabled>Seleccione un rol...</option>
                                    <option value="1">Administrador</option>
                                    <option value="2">Operador</option>
                                    <option value="3">Consultor</option>
                                </select>
                            </div>

                            <div class="col-md-12 pt-2">
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="edit_estado_user"
                                        checked>
                                    <label class="form-check-label fw-bold text-success" for="edit_estado_user"
                                        id="label_estado_user">
                                        Usuario Activo
                                    </label>
                                </div>
                                <div id="estado_help" class="form-text mt-0">
                                    Los usuarios inactivos no pueden iniciar sesión.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss=\"modal\">Cerrar</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">
                            <i class="bi bi-save me-2"></i>Guardar Cambios
                        </button>
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
    <script src="/unexca/public/js/sidebar.js"></script>
    <script src="/unexca/public/js/auth.js"></script>
    <script src="/unexca/public/js/users.js"></script>
</body>

</html>