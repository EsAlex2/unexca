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
                    // Usamos el ID numérico que viene de la base de datos (1 = Activo)
                    const isActive = data.id_estatus == 1 ? "checked" : "";
                    const textEstatus = data.id_estatus == 1 ? "Activo" : "Inactivo";

                    return `
            <div class="form-check form-switch">
                <input class="form-check-input switch-estatus" type="checkbox" role="switch"
                id="switch_${data.id_permiso}"
                data-id="${data.id_permiso}"
                ${isActive}>
                <label class="form-check-label fw-bold" for="switch_${data.id_permiso}">
                    ${textEstatus}
                </label>
            </div>`;
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
    const row = checkBox.closest('tr');
    const label = checkBox.next("label");
    const newEstatus = checkBox.is(":checked") ? 1 : 2;

    checkBox.prop("disabled", true);

    const nombre = row.find('td:eq(0)').text();
    const descripcion = row.find('td:eq(1)').text();
    const table = $('#tablePermissions').DataTable();
    const rowData = table.row(row).data();

    checkBox.prop("disbled", true);

    $.ajax({
        url: "../api/administrador/permisos.php?id_permiso=" + permisoId,
        method: "PUT",
        contentType: "application/json",
        data: JSON.stringify({
            id_estatus: newEstatus,
            id_permiso: permisoId,
        }),
        success: function (response) {
            checkBox.prop("disabled", false);
            label.text(newEstatus === 1 ? "Activo" : "Inactivo");

            const toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
            });
            toast.fire({
                icon: "info",
                title: "Estatus Actualizado",
            });
        },
        error: function (xhr) {
            checkBox.prop("disabled", false);
            checkBox.prop("checked", !checkBox.is(":checked"));
            console.error(xhr.responseText);
            Swal.fire("Error", "No se pudo cambiar el estatus", "error");
        },
    });
});
