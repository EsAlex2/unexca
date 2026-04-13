document.addEventListener('DOMContentLoaded', () => {
    const tablaPersonas = $('#tablaPersonas').DataTable({
        "ajax": {
            "url": "../api/administrador/datos_saime.php",
            "dataSrc": ""
        },
        "columns": [
            {
                "data": "cedula_identidad",
                "render": data => `<strong>${data}</strong>`
            },
            {
                "data": null,
                "render": (d) => `${d.nombres} ${d.apellidos}`
            },
            { "data": "correo_personal" },
            { "data": "telefono_personal" },
            {
                "data": "id_estatus",
                "render": function (id) {
                    let colorClass = "";
                    let texto = "";
                    
                    if (id == 1) {
                        colorClass = "bg-success text-white";
                        texto = "Activo";
                    } else {
                        colorClass = "bg-secondary text-white";
                        texto = "Inactivo";
                    }
                    return `<span class="badge ${colorClass}">${texto}</span>`;
                }
            },
            {
                "data": null,
                "render": (d) => `
            <button class="btn btn-sm btn-info text-white btn-editar" data-id="${d.cedula_identidad}"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-danger btn-eliminar" data-id="${d.cedula_identidad}"><i class="bi bi-trash"></i></button>`
            }
        ],
        "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
    });
});