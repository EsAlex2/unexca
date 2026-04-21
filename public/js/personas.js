document.addEventListener("DOMContentLoaded", () => {

  //cargar los datos de las personas en el datatable asignando la informacion por columna  
  const tablaPersonas = $("#tablaPersonas").DataTable({
    ajax: {
      url: "../api/administrador/datos_saime.php",
      dataSrc: "",
    },
    columns: [
      {
        data: "cedula_identidad",
        render: (data) => `<strong>${data}</strong>`,
      },
      {
        data: null,
        render: (d) => `${d.nombres} ${d.apellidos}`,
      },
      { data: "correo_personal" },
      { data: "telefono_personal" },
      {
        data: "id_estatus",
        render: function (id) {
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
        },
      },
      {
        data: null,
        render: (d) => {
          const esActivo = d.id_estatus == 1;
          const textoBoton = esActivo ? "Desactivar" : "Activar";
          const iconoBoton = esActivo
            ? "bi-person-x-fill text-danger"
            : "bi-person-check-fill text-success";

          return `
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-secondary text-white btn-mostrar_datos" data-id="${d.cedula_identidad}" title="Ver detalles">
                    <i class="bi bi-eye-fill"></i>
                </button>

                <button class="btn btn-sm btn-info text-white btn-editar" data-id="${d.cedula_identidad}" title="Editar Datos">
                <i class="bi bi-pencil-square text-white"></i>
                </button>
            </div>`;
        },
      },
    ],
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
    },
  });
});

$(document).on("click", ".btn-mostrar_datos", function () {
  const cedula = $(this).data("id");

  $("#modalDetallePersona").modal("show");
  $(".modal-body span[id^='det_']").text("Cargando...");

  $.ajax({
    url: `../api/administrador/datos_saime.php/${cedula}`,
    method: "GET",
    dataType: "json",
    success: function (data) {
      $("#det_cedula").text(data.cedula_identidad);
      $("#det_nombre").text(`${data.nombres} ${data.apellidos}`);
      $("#det_correo").text(data.correo_personal);
      $("#det_telefono").text(data.telefono_personal || "No registrado");
      $("#det_fecha_na").text(data.fecha_nacimiento);
      $("#det_direccion").text(
        data.direccion_habitacion || "Sin dirección registrada",
      );
      if ($("#det_genero").length)
        $("#det_genero").text(data.genero == 1 ? "Masculino" : "Femenino");
      if ($("#det_ingreso").length) $("#det_ingreso").text(data.fecha_ingreso);
    },
    error: function (xhr) {
      console.error(xhr);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudieron obtener los datos detallados del servidor.",
      });
      $("#modalDetallePersona").modal("hide");
    },
  });
});

$(document).ready(function () {
  $("#formPersona").on("submit", function (e) {
    e.preventDefault();

    const datos = {
      cedula_identidad: $("#identificacion").val(),
      nombres: $("#nombres").val(),
      apellidos: $("#apellidos").val(),
      genero: $("#genero").val(),
      fecha_nacimiento: $("#fecha_na").val(),
      correo_personal: $("#correo").val(),
      telefono_personal: $("#telefono").val(),
      fecha_ingreso: $("#fecha_in").val(),
      direccion_habitacion: $("#direccion").val(),
    };

    Swal.fire({
      title: "Guardando Datos personales",
      text: "Espere un momento",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    $.ajax({
      url: "../api/administrador/datos_saime.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(datos),
      success: function (response) {
        Swal.fire({
          icon: "success",
          title: "¡Datos Registrados!",
          text: response.message,
          confirmButtonText: "Aceptar",
        }).then(() => {
          window.location.href = "persons_saime.php";
        });

        $("#formPersona")[0].reset();
        $("#modalPersona").modal("hide");

        tablaPersonas.ajax.reload();
      },
      error: function (xhr) {
        Swal.close();
        const errorData = xhr.responseJSON;
        Swal.fire({
          icon: "error",
          title: "No se pudo registrar",
          text:
            errorData && errorData.error
              ? errorData.error
              : "Error interno del servidor",
          footer:
            errorData && errorData.detalle
              ? `<code>${errorData.detalle}</code>`
              : "",
        });
      },
    });
  });
});

$(document).on("click", ".btn-editar", function () {
  const cedula = $(this).data("id");
  $.get(`../api/administrador/datos_saime.php/${cedula}`, function (data) {
    $("#edit_cedula_original").val(data.cedula_identidad);
    $("#edit_identificacion").val(data.cedula_identidad);
    $("#edit_nombres").val(data.nombres);
    $("#edit_apellidos").val(data.apellidos);
    $("#edit_correo").val(data.correo_personal);
    $("#edit_telefono").val(data.telefono_personal);
    $("#edit_fecha_na").val(data.fecha_nacimiento);
    $("#edit_direccion").val(data.direccion_habitacion);

    const esActivo = data.id_estatus == 1;
    $("#edit_estatus_toggle").prop("checked", esActivo);
    actualizarLabelEstatus(esActivo);

    $("#modalEditarPersona").modal("show");
  });
});

function actualizarLabelEstatus(esActivo) {
  const label = $("#label_estatus");
  if (esActivo) {
    label
      .text("Activo")
      .addClass("text-success")
      .removeClass("text-secondary");
  } else {
    label
      .text("Inactivo")
      .addClass("text-secondary")
      .removeClass("text-success");
  }
}

$("#edit_estatus_toggle").on("change", function () {
  actualizarLabelEstatus($(this).is(":checked"));
});

$("#formEditarPersona").on("submit", function (e) {
  e.preventDefault();

  const cedulaOriginal = $("#edit_cedula_original").val();
  const nuevoEstatus = $("#edit_estatus_toggle").is(":checked") ? 1 : 2;
  const datosActualizados = {
    cedula_identidad: $("#edit_identificacion").val(),
    id_estatus: nuevoEstatus,
    nombres: $("#edit_nombres").val(),
    apellidos: $("#edit_apellidos").val(),
    correo_personal: $("#edit_correo").val(),
    telefono_personal: $("#edit_telefono").val(),
    fecha_nacimiento: $("#edit_fecha_na").val(),
    direccion_habitacion: $("#edit_direccion").val(),
  };

  $.ajax({
    url: `../api/administrador/datos_saime.php/${cedulaOriginal}`,
    method: "PUT",
    dataType: "json", 
    contentType: "application/json",
    data: JSON.stringify(datosActualizados),
    success: function (response) {
      Swal.fire("¡Actualizado!", response.message, "success");
      $("#modalEditarPersona").modal("hide");
      $("#tablaPersonas").DataTable().ajax.reload();
    },
    error: function (xhr) {
      console.error(xhr.responseText);
      Swal.fire("Error", "No se pudo actualizar la información.", "error");
    },
  });
});
