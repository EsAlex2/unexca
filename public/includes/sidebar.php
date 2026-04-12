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

        <a href="/unexca/public/estudiantes.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'estudiantes.php' ? 'active' : ''; ?>">
            <i class="bi bi-person"></i> <span class="link-text">Estudiantes</span>
        </a>
        
        <?php if (isset($_SESSION['id_tipo']) && $_SESSION['id_tipo'] == 1): ?>
        <div>
            <a href="#adminSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="nav-link dropdown-toggle shadow-none">
                <i class="bi bi-shield-lock"></i> <span class="link-text">Administración</span>
            </a>
            <ul class="collapse list-unstyled mb-0 bg-dark" id="adminSubmenu">
                <li>
                    <a href="/unexca/modulos/users.php" class="nav-link ps-5 py-2">
                        <i class="bi bi-people small"></i> <span class="link-text">Usuarios</span>
                    </a>
                </li>
                <li>
                    <a href="/unexca/modulos/roles.php" class="nav-link ps-5 py-2">
                        <i class="bi bi-person-badge small"></i> <span class="link-text">Roles</span>
                    </a>
                </li>
                <li>
                    <a href="/unexca/modulos/permisos.php" class="nav-link ps-5 py-2">
                        <i class="bi bi-key small"></i> <span class="link-text">Permisos</span>
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