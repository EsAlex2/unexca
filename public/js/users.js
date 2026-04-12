document.addEventListener('DOMContentLoaded', () => {
    cargarRolesEnSelect();
    const tabla = $('#tablaUsuarios').DataTable({
        "ajax": {
            "url": "../api/administrador/gestion_usuarios.php", // Agregamos ../ para salir de /modulos/
            "dataSrc": "",
            "error": function (xhr, error, code) {
                console.log("Error detallado:", xhr.responseText);
            }
        },
        "columns": [
            { "data": "cedula" },
            {
                "data": null,
                "render": function (data) {
                    return `${data.nombres} ${data.apellidos}`;
                }
            },
            { "data": "correo_institucional" },
            {
                "data": "nombre_tipo",
                "render": function (nombre) {
                    let colorClass = "";

                    // Asignamos una clase de Bootstrap según el nombre del rol
                    switch (nombre) {
                        case "Administrador":
                            colorClass = "bg-dark text-white"; // Rojo
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
                "data": "activo",
                "render": function (activo) {
                    return activo
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-secondary">Inactivo</span>';
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
                        </button>
                    `;
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


    // 3. Evento para crear nuevo usuario
    const form = document.getElementById('addUserForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = {
            cedula: document.getElementById('cedula').value,
            nombres: document.getElementById('nombres').value,
            apellidos: document.getElementById('apellidos').value,
            correo_institucional: document.getElementById('correo').value,
            password_hash: document.getElementById('password').value,
            id_tipo: document.getElementById('rol').value
        };

        try {
            const response = await fetch('../api/administrador/gestion_usuarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const res = await response.json();

            if (response.ok) {
                Swal.fire('¡Creado!', 'El usuario ha sido registrado con éxito.', 'success');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('modalUsuario')).hide();
                tabla.ajax.reload(); // Recarga la tabla sin refrescar la página
            } else {
                Swal.fire('Error', res.error || 'No se pudo crear el usuario', 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Error de conexión con el servidor', 'error');
        }
    });
});