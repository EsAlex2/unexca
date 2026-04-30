// ==========================================
// 1. VARIABLES Y CONFIGURACIÓN INICIAL
// ==========================================
$(document).ready(function () {

    $("#formRegistroCurso").on("submit", function (e) {
        e.preventDefault();

        const datos = {
            codigoAsignatura: $("#codigo").val(), 
            nombreAsignatura: $("#nombre").val(),
            pnfAsignatura: $("#pnf").val(),
            ucAsignatura: $("#unidades_credito").val(),
            trayectos: $("#trayectos").val(),
            caracterMateria: $("#caracterMateria").val(),
            descripcion: $("#descripcion"),
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
            data: JSON.stringify(datos),
            success: function (response) {
                Swal.fire({
                    icon: "success",
                    title: "¡Ha sido Registrado!",
                    text: response.message
                }).then(() => {
                    window.location.href = subject.php;
                });
            },
            error: function(xhr) {
                const error = xhr.responseJSON;
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error?.error || "No se pudo crear el usuario",
                    footer: error?.sugerencia || ""
                });
            },
        });
    });

});
