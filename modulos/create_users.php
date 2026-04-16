<?php
include '../config/init.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Usuario - <?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/css/style.css">
</head>

<body>

    <?php include '../public/includes/sidebar.php'; ?>

    <main id="content">
        <div class="top-nav">
            <h5 class="m-0"><i class="bi bi-person-plus-fill me-2"></i> Nuevo Registro de Usuario</h5>
            <div class="text-muted small"><?php echo $_SESSION['nombre_completo']; ?></div>
        </div>

        <div class="container-fluid pt-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold text-primary">Vincular Persona a Cuenta de Acceso</h6>
                        </div>
                        <div class="card-body p-4">
                            <form id="formVerificarPersona" class="mb-4">
                                <label class="form-label fw-bold">1. Verificar Identidad en Sistema</label>
                                <div class="input-group">
                                    <span class="input-group-text">V/E</span>
                                    <input type="text" id="v_cedula" class="form-control"
                                        placeholder="Ingrese cédula sin puntos" required>
                                    <button class="btn btn-outline-primary" type="submit" id="btnVerificar">
                                        <i class="bi bi-search"></i> Verificar Persona
                                    </button>
                                </div>
                                <div id="feedbackPersona" class="form-text mt-2"></div>
                            </form>

                            <hr>

                            <form id="formCrearUsuario" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="alert alert-info border-0 shadow-sm">
                                            <i class="bi bi-person-check-fill me-2"></i>
                                            Registrando acceso para: <strong id="nombrePersonaLabel"></strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="correo" class="form-label">Correo Institucional</label>
                                        <input type="email" class="form-control" id="correo" name="correo_institucional"
                                            required placeholder="usuario@unexca.edu.ve">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="id_tipo_select" class="form-label">Rol de Usuario</label>
                                        <select class="form-select" id="id_tipo_select" name="id_tipo" required>
                                            <option value="" selected disabled>Cargando roles...</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="password" class="form-label">Contraseña Temporal</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password"
                                                name="password_hash" required>
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="togglePassword()">
                                                <i class="bi bi-eye" id="eyeIcon"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4 d-flex justify-content-between">
                                        <a href="users.php" class="btn btn-light border">Cancelar</a>
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bi bi-save me-1"></i> Guardar Usuario
                                        </button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/unexca/public/js/sidebar.js"></script>
    <script src="/unexca/public/js/auth.js"></script>
    <script src="/unexca/public/js/users.js"></script>
</body>

</html>