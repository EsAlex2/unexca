<?php include '../config/init.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="bg-light">

    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center p-0">
        <div class="row g-0 shadow-lg login-container w-100 m-0 m-md-3">

            <div
                class="col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center text-white p-5 login-banner">
                <i class="bi bi-mortarboard-fill display-1 mb-4 text-primary"></i>
                <h1 class="fw-bold fs-3">Sistema Académico UNEXCA</h1>
                <p class="text-white-50 fs-6 text-center">Gestión integral de procesos universitarios con tecnología de
                    vanguardia.</p>
            </div>

            <div class="col-lg-6 col-md-12 bg-white d-flex flex-column justify-content-center p-4 p-md-5">

                <div class="mb-4">
                    <h2 class="fw-bold text-dark">¡Bienvenido!</h2>
                    <p class="text-muted">Por favor, ingresa tus credenciales institucionales.</p>
                </div>

                <form id="loginForm">
                    <div class="mb-3">
                        <label for="correo" class="form-label fw-semibold">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" id="correo" class="form-control bg-light border-start-0 py-2"
                                placeholder="usuario@unexca.edu.ve" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" id="password" class="form-control bg-light border-start-0 py-2"
                                placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                        Ingresar
                    </button>

                    <div id="mensaje" class="mt-3"></div>
                </form>

                <div class="mt-5 text-center text-muted small">
                    &copy; <?php echo date('Y'); ?> Universidad Nacional Experimental de la Gran Caracas.
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/auth.js"></script>
</body>

</html>