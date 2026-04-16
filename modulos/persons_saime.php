<?php
include '../config/init.php';

$genders = [
    "1" => "Masculino",
    "2" => "Femenino",
    "3" => "Prefiero no Decirlo"
];

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
            <h5 class="m-0"><i class="bi bi-people-fill me-2"></i> Gestión de Personas</h5>
            <div class="text-muted small"><?php echo $_SESSION['nombre_completo']; ?></div>
        </div>

        <div class="container-fluid pt-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Listado de Personas</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPersona">
                        <i class="bi bi-person-plus-fill"></i> Registrar Persona
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaPersonas" class="table table-striped table-hover w-100">
                            <thead>
                                <tr>
                                    <th>Identificación</th>
                                    <th>Nombre Completo</th>
                                    <th>Correo Electrónico</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalPersona" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Registrar Nueva Persona</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPersona">
                    <div class="modal-body">
                        <input type="hidden" id="id_persona" name="id_persona">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Cedula de Identidad</label>
                                <input type="text" class="form-control" id="identificacion" name="identificacion"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombres</label>
                                <input type="text" class="form-control" id="nombres" name="nombres" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Género</label>
                                <select class="form-select" id="genero" name="genero">
                                    <option value="" disabled selected>Seleccione el Genero</option>
                                    <?php foreach ($genders as $id => $name): ?>
                                        <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_na" name="fecha_na" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Direccion</label>
                                <textarea class="form-control" id="direccion" name="direccion" row="10"></textarea>
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

    <script src="../public/js/personas.js"></script>
    <script src="../public/js/sidebar.js"></script>
    <script src="../public/js/auth.js"></script>
</body>

</html>