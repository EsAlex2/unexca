<nav id="sidebar">
    <div class="sidebar-header">
        <span class="logo-text"><i class="bi bi-mortarboard-fill me-2"></i> UNEXCA</span>
        <button id="sidebarCollapse" class="btn text-white ms-auto">
            <i class="bi bi-list"></i>
        </button>
    </div>
    
    <div class="mt-3 nav flex-column">
        <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="bi bi-house-door"></i> <span class="link-text">Home</span>
        </a>
        <a href="estudiantes.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'estudiantes.php' ? 'active' : ''; ?>">
            <i class="bi bi-people"></i> <span class="link-text">Estudiantes</span>
        </a>
        
        <?php if (isset($_SESSION['id_tipo']) && $_SESSION['id_tipo'] == 1): ?>
        <a href="admin.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
            <i class="bi bi-gear"></i> <span class="link-text">Administración</span>
        </a>
        <?php endif; ?>

        <div class="mt-auto border-top border-secondary">
            <a href="#" id="btnLogout" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left"></i> <span class="link-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>
</nav>