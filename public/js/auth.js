// public/js/auth.js

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const correo = document.getElementById('correo').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('../modulos/auth/login.php',
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            correo: correo,
                            password: password
                        })
                    });

                const data = await response.json();

                if (response.ok) {
                    const nombreCompleto = `${data.usuario.nombres} ${data.usuario.apellidos}`;

                    Swal.fire({
                        icon: 'success',
                        title: '¡Inicio de sesión exitoso!',
                        text: `Bienvenido al sistema, ${nombreCompleto}`,
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    }).then(() => {
                        window.location.href = 'dashboard.php';
                    });
                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error de acceso',
                        text: data.error || 'Credenciales incorrectas',
                        confirmButtonColor: '#0d6efd'
                    });
                }

            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo establecer contacto con el servidor.',
                });
            }
        });
    }

    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', async () => {
            try {
                const res = await fetch('/unexca/modulos/auth/logout.php');

                if (res.ok) {
                    window.location.href = 'index.php';
                } else {
                    console.error('No se pudo encontrar el archivo de logout');
                }
            } catch (error) {
                console.error('Error al intentar cerrar sesión:', error);
            }
        });
    }
});