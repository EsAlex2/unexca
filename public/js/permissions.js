document.addEventListener("DOMContentLoaded", () => {
    const tablePermissions = $("#tablePermissions").DataTable({
        ajax: {
            url: "../api/administrador/permisos.php", //ruta para mostrar los permisos registrados en la base de datos
            dataSrc: "",
            error: function (xhr, error, code) {
                console.log("Error: ", xhr.reponseText);
            },
        },
        columns: [
            { data: "nombre_permiso" },
            { data: "descripcion" },
            {
                data: null,
                render: function (data) {
                    return `${data.nombre_modulo}`;
                },
            },
            {
                data: null,
                render: function (data) {
                    const isActive = data.id_estatus == 1 ? "checked" : "";
                    const textEstatus = data.id_estatus == 1 ? "Activo" : "Inactivo";

                    return `
                    
            <div class="form-check form-switch d-flex gap-2">
                <input class="form-check-input switch-estatus" type="checkbox" role="switch"
                id="switch_${data.id_permiso}"
                data-id="${data.id_permiso}"
                ${isActive}>
                
                <label class="form-check-label fw-bold" for="switch_${data.id_permiso}">
                    ${textEstatus}
                </label>

                <button class="btn btn-sm btn-secondary text-white btn-edir_permissions" data-id="${data.id_permiso}">
                    <i class="bi bi-pencil-square"></i>
                </button>
            </div>
            
            `;
                },
            }
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
    });
});

/*
activacion y suspension de los permisos
*/
$(document).on("change", ".switch-estatus", function () {
    const checkBox = $(this);
    const permisoId = checkBox.data("id");
    const newEstatus = checkBox.is(":checked") ? 1 : 2;
    const label = checkBox.next("label");

    checkBox.prop("disabled", true);

    $.ajax({
        url: "../api/administrador/permisos.php?id_permiso=" + permisoId,
        method: "PUT",
        contentType: "application/json",
        data: JSON.stringify({
            id_estatus: newEstatus
        }),
        success: function (response) {
            checkBox.prop("disabled", false);
            label.text(newEstatus === 1 ? "Activo" : "Inactivo");

            Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
            }).fire({
                icon: "success",
                title: "Estatus Actualizado",
            });
        },
        error: function (xhr) {
            checkBox.prop("disabled", false);
            checkBox.prop("checked", !checkBox.is(":checked"));
            console.error(xhr.responseText);
            Swal.fire("Error", "No se pudo cambiar el estatus", "error");
        }
    });
});
