// ==========================================
// 1. VARIABLES Y CONFIGURACIÓN INICIAL
// ==========================================
$(document).ready(function () {

    async function cargarPnf() {
        const select = $("#pnf");
        try {
            const res = await fetch("../api/administracion/view_pnfs.php");
            const pnfs = await res.json();

            select.empty().append('<option value="" selected disabled>Seleccione una PNF...</option>');
            pnfs.forEach((pnf) => {
                select.append(`<option value="${pnf.id_pnf}">${pnf.nombre_pnf}</option>`);
            });
        } catch (error) {
            console.error("Error para cargar pnfs:", error);
            select.html('<option value="">Error al cargar pnfs</option>');
        }
    }

    //ejecucion de los pnf
    cargarPnf()


    $("#formRegistroCurso").on("submit", function (e) {
        e.preventDefault();

        const data = {
            id_pnf: $("#pnf").val(),
            id_trayecto: $("#trayectos").val(),
            codigo: $("#codigo").val(),
            nombre: $("#nombre").val(),
            unidades_credito: $("#unidades_credito").val(),
            id_caracter: $("#caracterMateria").val()
        };

        Swal.fire({
            title: "Guardando la Asignatura",
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            url: "../api/administracion/asignaturas_pnf.php",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify(data),
            success: function (response) {
                Swal.fire({
                    icon: "success",
                    title: "¡Ha sido Registrado!",
                    text: response.message
                }).then(() => {
                    window.location.href = "subject.php";
                });
            },
            error: function (xhr) {
                const error = xhr.responseJSON;
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error?.error || "No se pudo crear la asignatura",
                    footer: error?.sugerencia || ""
                });
            },
        });
    });

});
