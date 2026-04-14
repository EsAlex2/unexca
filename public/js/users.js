document.addEventListener('DOMContentLoaded', () => {
    cargarRolesEnSelect();
    const tabla = $('#tablaUsuarios').DataTable({
        "ajax": {
            "url": "../api/administrador/gestion_usuarios.php",
            "dataSrc": "",
            "cache": false,
            "error": function (xhr, error, code) {
                console.log("Error detallado:", xhr.responseText);
            }
        },
        "columns": [
            { "data": "cedula" },
            {
                "data": null,
                "render": function (data) {
                    // Ahora que agregamos el JOIN, estos campos estarán disponibles
                    return `${data.nombres} ${data.apellidos}`;
                }
            },
            { "data": "correo_institucional" },
            {
                "data": "nombre_tipo",
                "render": function (nombre) {
                    let colorClass = "";
                    switch (nombre) {
                        case "Administrador":
                            colorClass = "bg-danger text-white"; // Rojo
                            break;
                        case "Administrativo":
                            colorClass = "bg-primary text-white"; // Azul
                            break;
                        case "Coordinador":
                            colorClass = "bg-warning text-dark"; // Amarillo
                            break;
                        case "Docente":
                            colorClass = "bg-success text-white"; // Verde
                            break;
                        case "Estudiante":
                            colorClass = "bg-info text-dark"; // Celeste
                            break;
                        case "Invitado":
                            colorClass = "bg-secondary text-white"; // Gris
                            break;
                        default:
                            colorClass = "bg-light text-dark"; // Color por defecto
                    }
                    return `<span class="badge ${colorClass}">${nombre}</span>`;
                }
            },
            {
                "data": "nombre_estatus",
                "render": function (estatus) {
                    let badgeClass = estatus === "Activo" ? "bg-success" : "bg-secondary";
                    return `<span class="badge ${badgeClass}">${estatus}</span>`;
                }
            },
            {
                "data": null,
                "render": function (data) {
                    return `
                <button class="btn btn-sm btn-info text-white btn-editar" data-id="${data.id_usuario}">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-eliminar" data-id="${data.id_usuario}">
                    <i class="bi bi-trash"></i>
                </button>`;
                }
            }
        ],
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        }
    });

    //Cargar Roles dinámicamente al abrir la página o el modal  
    async function cargarRolesEnSelect() {
        const select = document.getElementById('id_tipo_usuario');
        if (!select) return;

        try {
            //ruta relativa para acceder al API desde el JS ubicado en /public/js/
            const response = await fetch('../api/administrador/gestion_roles.php');
            const roles = await response.json();

            // Limpiar opciones previas
            select.innerHTML = '<option value="" selected disabled>Seleccione un rol...</option>';

            roles.forEach(rol => {
                const option = document.createElement('option');
                option.value = rol.id_tipo;
                option.textContent = rol.nombre_tipo;
                select.appendChild(option);
            });
        } catch (error) {
            console.error("Error cargando roles:", error);
            select.innerHTML = '<option value="">Error al cargar roles</option>';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        cargarRolesEnSelect();
    });
});

$(document).ready(function () {
    let cedulaValidada = "";

    // Cargar roles dinámicamente desde el API
    async function cargarRoles() {
        const select = $('#id_tipo_select');
        try {
            const response = await fetch('../api/administrador/gestion_roles.php');
            const roles = await response.json();

            select.empty().append('<option value="" selected disabled>Seleccione un rol...</option>');
            roles.forEach(rol => {
                select.append(`<option value="${rol.id_tipo}">${rol.nombre_tipo}</option>`);
            });
        } catch (error) {
            console.error("Error cargando roles:", error);
            select.html('<option value="">Error al cargar roles</option>');
        }
    }

    cargarRoles();

    // Verificar si la persona existe en datos_personas
    $('#formVerificarPersona').on('submit', function (e) {
        e.preventDefault();
        const cedula = $('#v_cedula').val().trim();
        const btn = $('#btnVerificar');
        const feedback = $('#feedbackPersona');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: `../api/administrador/gestion_usuarios.php/${cedula}`, // Llamada GET
            method: 'GET',
            success: function (res) {
                // Caso: Persona encontrada sin usuario
                cedulaValidada = cedula;
                $('#nombrePersonaLabel').html(`<strong>${res.nombres} ${res.apellidos}</strong>`);
                $('#formCrearUsuario').slideDown();
                feedback.html('<span class="text-success"><i class="bi bi-check-circle"></i> Persona verificada exitosamente.</span>');
                btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> Cédula Fijada');
            },
            error: function (xhr) {
                $('#formCrearUsuario').slideUp();
                let msg = "Error al verificar";
                let icon = "error";

                if (xhr.status === 404) {
                    msg = "La cédula no está registrada en el sistema de datos básicos.";
                    icon = "warning";
                } else if (xhr.status === 409) {
                    msg = "Esta persona ya posee una cuenta de acceso.";
                    icon = "info";
                }

                Swal.fire({
                    icon: icon,
                    title: 'Atención',
                    text: msg,
                    confirmButtonText: 'Entendido'
                });

                btn.prop('disabled', false).html('<i class="bi bi-search"></i> Verificar Persona');
                feedback.html(`<span class="text-danger">${msg}</span>`);
            }
        });
    });

    // Guardar el usuario en la base de datos a través de la API
    $('#formCrearUsuario').on('submit', function (e) {
        e.preventDefault();

        const datos = {
            cedula: cedulaValidada,
            correo_institucional: $('#correo').val(),
            id_tipo: $('#id_tipo_select').val(),
            password_hash: $('#password').val()
        };

        Swal.fire({
            title: 'Guardando Usuario',
            text: 'Espere un momento...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: '../api/administrador/gestion_usuarios.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(datos),
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Registrado!',
                    text: res.message,
                    confirmButtonText: 'Ver Lista'
                }).then(() => {
                    window.location.href = 'users.php';
                });
            },
            error: function (xhr) {
                const error = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo crear',
                    text: error.error || 'Error desconocido',
                    footer: error.sugerencia || ''
                });
            }
        });
    });
});

function togglePassword() {
    const pass = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pass.type === "password") {
        pass.type = "text";
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pass.type = "password";
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}