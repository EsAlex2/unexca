document.addEventListener("DOMContentLoaded", () => {
  //funcion para cargar los roles en el datatable y la vista de creacion de usuarios
  cargarRolesEnSelect();
  const tabla = $("#tablaUsuarios").DataTable({
    ajax: {
      url: "../api/administrador/gestion_usuarios.php", //endpoint para mostrar los datos de los usuarios en el datatable
      dataSrc: "",
      cache: false,
      error: function (xhr, error, code) {
        console.log("Error detallado:", xhr.responseText);
      },
    },
    columns: [
      { data: "cedula" },
      {
        data: null,
        render: function (data) {
          return `${data.nombres} ${data.apellidos}`;
        },
      },
      { data: "correo_institucional" },
      {
        data: "nombre_tipo",
        render: function (nombre) {
          let colorClass = "";
          switch (nombre) {
            case "Administrador":
              colorClass = "bg-primary text-white";
              break;
            case "Control de Estudios":
              colorClass = "bg-danger text-white";
              break;
            case "Docente":
              colorClass = "bg-warning text-dark";
              break;
            case "Estudiante":
              colorClass = "bg-info text-dark";
              break;
            case "Finanzas":
              colorClass = "bg-success   text-white";
              break;
            default:
              colorClass = "bg-light text-dark";
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

  /*
  creacion de funcion para la carga de roles (method: GET)
  */
  async function cargarRolesEnSelect() {
    const select = document.getElementById("id_tipo_usuario");
    if (!select) return;

    try {
      const response = await fetch("../api/administrador/gestion_roles.php");
      const roles = await response.json();
      select.innerHTML =
        '<option value="" selected disabled>Seleccione un rol...</option>'; //CARGA DE ROLES EN SELECT

      roles.forEach((rol) => {
        const option = document.createElement("option");
        option.value = rol.id_tipo;
        option.textContent = rol.nombre_tipo;
        select.appendChild(option);
      });
    } catch (error) {
      console.error("Error cargando roles:", error);
      select.innerHTML = '<option value="">Error al cargar roles</option>';
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    cargarRolesEnSelect();
  });
});

$(document).ready(function () {
  let cedulaValidada = "";

  // Cargar roles dinámicamente desde el API
  async function cargarRoles() {
    const select = $("#id_tipo_select");
    try {
      const response = await fetch("../api/administrador/gestion_roles.php");
      const roles = await response.json();

      select
        .empty()
        .append(
          '<option value="" selected disabled>Seleccione un rol...</option>',
        );
      roles.forEach((rol) => {
        select.append(
          `<option value="${rol.id_tipo}">${rol.nombre_tipo}</option>`,
        );
      });
    } catch (error) {
      console.error("Error cargando roles:", error);
      select.html('<option value="">Error al cargar roles</option>');
    }
  }

  cargarRoles();

  // Verificar si la persona existe en datos_personas
  $("#formVerificarPersona").on("submit", function (e) {
    e.preventDefault();
    const cedula = $("#v_cedula").val().trim();
    const btn = $("#btnVerificar");
    const feedback = $("#feedbackPersona");

    btn
      .prop("disabled", true)
      .html('<span class="spinner-border spinner-border-sm"></span>');

    $.ajax({
      url: `../api/administrador/gestion_usuarios.php/${cedula}`, // Llamada GET
      method: "GET",
      success: function (res) {
        cedulaValidada = cedula;
        $("#nombrePersonaLabel").html(
          `<strong>${res.nombres} ${res.apellidos}</strong>`,
        );
        $("#formCrearUsuario").slideDown();
        feedback.html(
          '<span class="text-success"><i class="bi bi-check-circle"></i> Persona verificada exitosamente.</span>',
        );
        btn
          .prop("disabled", false)
          .html('<i class="bi bi-check-lg"></i> Cédula Fijada');
      },
      error: function (xhr) {
        $("#formCrearUsuario").slideUp();
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
          title: "Atención",
          text: msg,
          confirmButtonText: "Entendido",
        });

        btn
          .prop("disabled", false)
          .html('<i class="bi bi-search"></i> Verificar Persona');
        feedback.html(`<span class="text-danger">${msg}</span>`);
      },
    });
  });

  // Guardar el usuario en la base de datos a través de la API
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
      text: "Espere un momento...",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
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
          text: res.message,
          confirmButtonText: "Ver Usuarios",
        }).then(() => {
          window.location.href = "users.php";
        });
      },
      error: function (xhr) {
        const error = xhr.responseJSON;
        Swal.fire({
          icon: "error",
          title: "No se pudo crear",
          text: error.error || "Error desconocido",
          footer: error.sugerencia || "",
        });
      },
    });
  });
});

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

/*
acivar / desactivar usuarios
*/
$(document).on("change", ".switch-estatus", function () {
  const checkbox = $(this);
  const idUsuario = checkbox.data("id");
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
    success: function (res) {
      checkbox.prop("disabled", false);
      label.text(nuevoEstatus === 1 ? "Activo" : "Inactivo");

      const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 2000,
      });
      Toast.fire({
        icon: "success",
        title: "Estatus actualizado",
      });
    },
    error: function (xhr) {
      checkbox.prop("disabled", false);
      checkbox.prop("checked", !checkbox.is(":checked"));
      console.error(xhr.responseText);
      Swal.fire("Error", "No se pudo cambiar el estatus", "error");
    },
  });
});
