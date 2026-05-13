
$(document).ready(function () {

    // ==========================================
    // Creación de la tabla de asignaturas
    // ==========================================

    const tablaAsignaturas = $("#tablaAsignaturas").DataTable({
        ajax: {
            url: "../api/administrador/asignaturas_pnf.php",
            dataSrc: "",
            cache: false,
            error: function (xhr) {
                console.error("Error en la carga de la tabla:", xhr.responseText);
            },
        },
        columns: [
            { data: null, render: (data) => `<strong>${data.codigo}</strong>` },
            { data: null, render: (data) => `<span class="text-uppercase">${data.nombre}</span>` },
            { data: "pnf" },
            { data: "trayecto" },
            { data: "unidades_credito" },
            {
                data: null, render: function (data, type, row) {
                    return `
                    <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${row.id_asignatura}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${row.id_asignatura}">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
                }
            }
        ]
    });

    // ==========================================
    // FUNCIONES PARA CARGAR PNF Y TRAYECTOS
    // ==========================================

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

    async function cargarTrayectosBd() {
        const select2 = $("#trayectos");

        try {
            const res2 = await fetch("../api/administracion/views_trayectos.php");
            const trayectos = await res2.json();

            select2.empty().append('<option value="" selected disabled>Seleccione un Trayecto...</option>');
            trayectos.forEach((trayecto) => {
                select2.append(`<option value="${trayecto.id_trayecto}">${trayecto.descripcion}</option>`);
            });
        } catch (error) {
            console.error("Error para cargar trayectos:", error);
            select2.html('<option value="">Error al cargar trayectos</option>');

        }
    }

    //ejecucion de los pnf y trayectos
    cargarPnf();
    cargarTrayectosBd();

    // ==========================================
    // Registro de asignaturas
    // ==========================================

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
