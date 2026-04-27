$(document).ready(function () {
    // ==========================================
    // 1. VARIABLES Y CONFIGURACIÓN INICIAL
    // ==========================================
    let cedulaValidada = "";

    // Inicialización de DataTable
    const tablaUsuarios = $("#tablaUsuarios").DataTable({
        ajax: {
            url: "../api/administrador/gestion_usuarios.php",
            dataSrc: "",
            cache: false,
            error: function (xhr) {
                console.error("Error en la carga de la tabla:", xhr.responseText);
            },
        },
        columns: [
            { data: "cedula" },
            {
                data: null,
                render: (data) => `${data.nombres} ${data.apellidos}`
            },
            { data: "correo_institucional" },
            {
                data: "nombre_tipo",
                render: function (nombre) {
                    let colorClass = "";
                    switch (nombre) {
                        case "Administrador": colorClass = "bg-primary text-white"; break;
                        case "Control de Estudios": colorClass = "bg-danger text-white"; break;
                        case "Docente": colorClass = "bg-warning text-dark"; break;
                        case "Estudiante": colorClass = "bg-info text-dark"; break;
                        case "Finanzas": colorClass = "bg-success text-white"; break;
                        default: colorClass = "bg-light text-dark";
                    }
                    return `<span class="badge ${colorClass}">${nombre}</span>`;
                },
            },
            {
                data: null,
                render: function (data) {
                    const isActive = data.nombre_estatus === "Activo" ? "checked" : "";
                    return `
                        <div class="form-check form-switch">
                            <input class="form-check-input switch-estatus" type="checkbox" role="switch" 
                                id="switch_${data.id_usuario}" 
                                data-id="${data.id_usuario}" 
                                data-cedula="${data.cedula}"
                                ${isActive}>
                            <label class="form-check-label fw-bold" for="switch_${data.id_usuario}">
                                ${data.nombre_estatus}
                            </label>
                        </div>`;
                },
            },
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
    });

    // ==========================================
    // 2. FUNCIONES CORE
    // ==========================================

    // Cargar roles dinámicamente
    async function cargarRoles() {
        const select = $("#id_tipo_select");
        try {
            const response = await fetch("../api/administrador/gestion_roles.php");
            const roles = await response.json();

            select.empty().append('<option value="" selected disabled>Seleccione un rol...</option>');
            roles.forEach((rol) => {
                select.append(`<option value="${rol.id_tipo}">${rol.nombre_tipo}</option>`);
            });
        } catch (error) {
            console.error("Error cargando roles:", error);
            select.html('<option value="">Error al cargar roles</option>');
        }
    }

    // Ejecutar carga de roles al iniciar
    cargarRoles();

    // ==========================================
    // 3. EVENTOS (FORMULARIOS Y ACCIONES)
    // ==========================================

    // Paso 1: Verificar si la persona existe
    $("#formVerificarPersona").on("submit", function (e) {
        e.preventDefault();
        const cedula = $("#v_cedula").val().trim();
        const btn = $("#btnVerificar");
        const feedback = $("#feedbackPersona");

        btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: `../api/administrador/gestion_usuarios.php/${cedula}`,
            method: "GET",
            success: function (res) {
                cedulaValidada = cedula;
                $("#nombrePersonaLabel").html(`<strong>${res.nombres} ${res.apellidos}</strong>`);
                $("#formCrearUsuario").slideDown();
                feedback.html('<span class="text-success"><i class="bi bi-check-circle"></i> Persona verificada.</span>');
                btn.prop("disabled", false).html('<i class="bi bi-check-lg"></i> Cédula Fijada');
            },
            error: function (xhr) {
                $("#formCrearUsuario").slideUp();
                let msg = xhr.status === 404 ? "Cédula no registrada en datos básicos." : 
                          xhr.status === 409 ? "Esta persona ya posee una cuenta." : "Error al verificar.";
                
                Swal.fire({ icon: "warning", title: "Atención", text: msg });
                btn.prop("disabled", false).html('<i class="bi bi-search"></i> Verificar Persona');
                feedback.html(`<span class="text-danger">${msg}</span>`);
            },
        });
    });

    // Paso 2: Guardar el nuevo usuario
    $("#formCrearUsuario").on("submit", function (e) {
        e.preventDefault();

        const datos = {
            cedula: cedulaValidada,
            correo_institucional: $("#correo").val(),
            id_tipo: $("#id_tipo_select").val(),
            password_hash: $("#password").val(),
        };

        Swal.fire({
            title: "Guardando Usuario",
            didOpen: () => { Swal.showLoading(); },
        });

        $.ajax({
            url: "../api/administrador/gestion_usuarios.php",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify(datos),
            success: function (res) {
                Swal.fire({
                    icon: "success",
                    title: "¡Registrado!",
                    text: res.message
                }).then(() => {
                    window.location.href = "users.php";
                });
            },
            error: function (xhr) {
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

    // Evento para los Switches de estatus (Delegado)
    $(document).on("change", ".switch-estatus", function () {
        const checkbox = $(this);
        const cedula = checkbox.data("cedula");
        const label = checkbox.next("label");
        const nuevoEstatus = checkbox.is(":checked") ? 1 : 2;

        checkbox.prop("disabled", true);

        $.ajax({
            url: `../api/administrador/gestion_usuarios.php/${cedula}`,
            method: "PUT",
            contentType: "application/json",
            data: JSON.stringify({
                cedula: cedula,
                id_estatus: nuevoEstatus,
            }),
            success: function () {
                checkbox.prop("disabled", false);
                label.text(nuevoEstatus === 1 ? "Activo" : "Inactivo");
                
                Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                }).fire({ icon: "success", title: "Estatus actualizado" });
            },
            error: function () {
                checkbox.prop("disabled", false);
                checkbox.prop("checked", !checkbox.is(":checked"));
                Swal.fire("Error", "No se pudo cambiar el estatus", "error");
            },
        });
    });
});

// ==========================================
// 4. FUNCIONES GLOBALES (FUERA DEL READY)
// ==========================================
function togglePassword() {
    const pass = document.getElementById("password");
    const icon = document.getElementById("eyeIcon");
    if (pass.type === "password") {
        pass.type = "text";
        icon.classList.replace("bi-eye", "bi-eye-slash");
    } else {
        pass.type = "password";
        icon.classList.replace("bi-eye-slash", "bi-eye");
    }
}