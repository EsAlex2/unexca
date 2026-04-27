<?php
include '../config/init.php';
include '../config/db.php';

//====================================================//
// Obtenemos las carreras y las mostramos en la tabla //
//====================================================//
$spnf = $pdo->prepare("SELECT cod_pnf, nombre_pnf, descripcion, duracion_pnf, unidad_total_creditos FROM unexca_db.pnf ORDER BY unidad_total_creditos DESC");
$spnf->execute();
$pnfs = $spnf->fetchAll();

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
            <h5 class="m-0"><i class="bi bi-ui-radios"></i> Programas Nacional de Formacion </h5>
            <div class="text-muted small"><?php echo $_SESSION['nombre_completo']; ?></div>
        </div>

        <div class="container-fluid pt-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Programas registrados en sistema</h6>
                </div>
                <div class="card-body">
                    <table id="tablePnf" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>Cod_pnf</th>
                                <th>Nombre del PNF</th>
                                <th>Descripcion</th>
                                <th>Duracion del PNF</th>
                                <th>Unidades de Creditos (TOTAL)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pnfs)): ?>
                                <?php foreach ($pnfs as $pnf): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pnf['cod_pnf']); ?></td>
                                        <td><?php echo htmlspecialchars($pnf['nombre_pnf']); ?></td>
                                        <td><?php echo htmlspecialchars($pnf['descripcion']); ?></td>
                                        <td><?php echo htmlspecialchars($pnf['duracion_pnf']); ?></td>
                                        <td><?php echo htmlspecialchars($pnf['unidad_total_creditos']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay programas registrados.</td>
                                </tr>
                            <?php endif; ?>
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
    <script src="/unexca/public/js/sidebar.js"></script>
    <script src="/unexca/public/js/auth.js"></script>
</body>

</html>