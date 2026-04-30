<?php
include __DIR__ . '/../config/init.php';
include __DIR__ . '/../config/db.php';

$sql = $pdo->prepare("SELECT * FROM unexca_db.trayectos");
$sql->execute();
$trayectos = $sql->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../public/css/style.css">
</head>

<body>

    <?php include '../public/includes/sidebar.php'; ?>

    <main id="content">
        <div class="top-nav">
            <h5 class="m-0"><i class="bi bi-card-checklist"></i> Gestion de Cursos</h5>
            <div class="text-muted small"><?php echo $_SESSION['nombre_completo']; ?></div>
        </div>

        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 text-primary"><i class="bi bi-plus-circle me-2"></i>Registrar Nueva
                                Asignatura</h6>
                        </div>
                        <div class="card-body p-4">
                            <form id="formRegistroCurso" action="../controllers/CursosController.php?action=save"
                                method="POST">

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="codigo" class="form-label">Código de la asignatura</label>
                                        <input type="text" class="form-control" id="codigo" name="codigo"
                                            placeholder="Ej: MAT-101" required>
                                    </div>

                                    <div class="col-md-8">
                                        <label for="nombre" class="form-label">Nombre de la Asignatura</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre"
                                            placeholder="Ej: Cálculo Diferencial" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="unidades_credito" class="form-label">U.C. (Créditos)</label>
                                        <input type="number" class="form-control" id="unidades_credito"
                                            name="unidades_credito" min="1" max="10" value="3" required>
                                    </div>

                                    <div class="col-md-4">

                                        <label for="semestre" class="form-label">Semestre / Trayecto</label>
                                        <?php if (!empty($trayectos)): ?>
                                            <select class="form-select" id="trayectos" name="semestre" required>
                                                <option value="" selected disabled>Seleccione...</option>
                                                <?php foreach ($trayectos as $items): ?>
                                                    <option value="<?php $items['id_trayecto'] ?>"><?php echo $items['descripcion'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="caracterMateria" class="form-label">Carácter de la Materia</label>
                                        <select class="form-select" name="tipo" id="caracterMateria">
                                            <option value="" selected disabled>Cargando..</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label for="descripcion" class="form-label">Descripción o Contenido Sinóptico
                                            (Opcional)</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                            placeholder="Breve resumen del contenido programático..."></textarea>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <hr>
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="reset" class="btn btn-light border">
                                                <i class="bi bi-eraser me-1"></i> Limpiar
                                            </button>
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="bi bi-save me-1"></i> Guardar Curso
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>


    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="/unexca/public/js/sidebar.js"></script>
    <script src="/unexca/public/js/auth.js"></script>
</body>

</html>