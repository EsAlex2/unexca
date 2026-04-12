document.addEventListener('DOMContentLoaded', () => {
    // 1. Inicializar DataTable para Roles
    const tablaRoles = $('#tablaRoles').DataTable({
        "ajax": {
            "url": "../api/administrador/gestion_roles.php",
            "dataSrc": ""
        },
        "columns": [
            { 
                "data": "nombre_tipo",
                "render": data => `<strong>${data}</strong>`
            },
            { "data": "descripcion" },
            {
                "data": null,
                "render": (d) => `
                    <button class="btn btn-sm btn-info text-white btn-editar" data-id="${d.id_tipo}"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-danger btn-eliminar" data-id="${d.id_tipo}"><i class="bi bi-trash"></i></button>`
            }
        ],
        "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
    });

    // 2. Lógica para Guardar Nuevo Rol
    const form = document.getElementById('addRolForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = {
            nombre_tipo: document.getElementById('nombre_tipo').value,
            descripcion: document.getElementById('descripcion').value
        };

        try {
            const response = await fetch('api/administrador/gestion_roles.php', { // Ajusta la ruta a tu API de roles
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const res = await response.json();

            if (response.ok) {
                Swal.fire('¡Éxito!', 'El rol ha sido creado.', 'success');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('modalRol')).hide();
                tablaRoles.ajax.reload();
            } else {
                Swal.fire('Error', res.error || 'No se pudo crear el rol', 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
        }
    });
});