<nav id="sidebar">
    <div class="sidebar-header">
        <span class="logo-text"><i class="bi bi-mortarboard-fill me-2"></i> UNEXCA</span>
        <button id="sidebarCollapse" class="btn text-white ms-auto">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="mt-3 nav flex-column">
        <a href="/unexca/public/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="bi bi-house-door"></i> <span class="link-text">Home</span>
        </a>

        <?php if (isset($_SESSION['id_tipo']) && $_SESSION['id_tipo'] == 1): ?>
            <div>
                <a href="#gestionUsuarios" data-bs-toggle="collapse" aria-expanded="false" class="nav-link dropdown-toggle shadow-none">
                    <i class="bi bi-people"></i> <span class="link-text">Gestion de Usuarios</span>
                </a>
                <ul class="collapse list-unstyled mb-0 bg-dark" id="gestionUsuarios">
                    <li>
                        <a href="/unexca/modulos/users.php" class="nav-link ps-5 py-2">
                            <i class="bi bi-person-lines-fill"></i><span class="link-text">Lista de Usuarios</span>
                        </a>
                    </li>

                    <li>
                        <a href="/unexca/modulos/create_users.php" class="nav-link ps-5 py-2">
                            <i class="bi bi-person-fill-add"></i><span class="link-text">Creacion de Usuarios</span>
                        </a>
                    </li>
                </ul>

                <a href="/unexca/modulos/carrers.php" class="nav-link shadow-none">
                    <i class="bi bi-ui-radios"></i>
                    <span class="link-text">Programas Nacional de Formación</span>
                </a>

                <a href="#adminSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="nav-link dropdown-toggle shadow-none">
                    <i class="bi bi-shield-lock"></i> <span class="link-text">Administración</span>
                </a>
                <ul class="collapse list-unstyled mb-0 bg-dark" id="adminSubmenu">
                    <li>
                        <a href="/unexca/modulos/persons_saime.php" class="nav-link ps-5 py-2">
                            <i class="bi bi-person-lines-fill small"></i> <span class="link-text">Personas</span>
                        </a>
                    </li>
                    <li>
                        <a href="/unexca/modulos/roles.php" class="nav-link ps-5 py-2">
                            <i class="bi bi-person-badge small"></i> <span class="link-text">Roles</span>
                        </a>
                    </li>
                    <li>
                        <a href="/unexca/modulos/permissions.php" class="nav-link ps-5 py-2">
                            <i class="bi bi-person-fill-gear"></i> <span class="link-text">Permisos</span>
                        </a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>

        <div class="mt-auto border-top border-secondary">
            <a href="#" id="btnLogout" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left"></i> <span class="link-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>
</nav>