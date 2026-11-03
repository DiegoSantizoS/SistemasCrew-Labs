async function inventoryFetch(url, options = {}) {
  const headers = new Headers(options.headers || {});
  headers.set(
    "X-CSRF-Token",
    document.querySelector('meta[name="csrf-token"]').content,
  );
  const response = await fetch(url, { ...options, headers });
  if (response.status === 401) {
    location.assign("login.php");
    throw new Error("Tu sesión venció.");
  }
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || "No se pudo completar la solicitud.");
  }
  return response;
}
//Variables globales y constantes
let UltimoId = 0;
let paginaActual = 1;
let ultimaPagina = 1;
let action = "GUARDAR";
let form = document.querySelector("#equipoForm");

//Función flecha para devolver la url de la api
let API_RUTA = (controlador) => {
  return `api/admin/${controlador}.php`;
};

//Función para paginar la tabla
async function TablaPaginada(irAultimaPagina = false) {
  const URL = API_RUTA("PaginasEquipos");
  try {
    const response = await inventoryFetch(URL);
    const datos = await response.json();
    const paginaciondiv = document.querySelector("#Paginacion");

    paginaciondiv.innerHTML = "";
    for (let pagina of datos.data) {
      paginaciondiv.innerHTML += `
        <button
          class="Paginador"
          id="Pagina${pagina.id_Pagina}"
          data-ultimo-id="${pagina.Ultimo_id_pagina}">
          ${pagina.id_Pagina}
        </button>
      `;
    }

    const paginasbtn = document.querySelectorAll(".Paginador");
    const ultima = paginasbtn[paginasbtn.length - 1];
    ultimaPagina = ultima ? Number(ultima.textContent) : 1;

    paginasbtn.forEach((boton) => {
      boton.addEventListener("click", () => {
        paginaActual = Number(boton.textContent);
        UltimoId = Number(boton.dataset.ultimoId);
        ObtenerEquipos(UltimoId);
      });
    });

    const destino = irAultimaPagina ? ultimaPagina : paginaActual;
    const botonDestino = document.getElementById(`Pagina${destino}`) ?? ultima;
    paginaActual = botonDestino ? Number(botonDestino.textContent) : 1;
    UltimoId = Number(botonDestino?.dataset.ultimoId ?? 0);
    await ObtenerEquipos(UltimoId);
  } catch (error) {
    alert("Error al cargar los datos");
  }
}

//Función para mostrar los equipos y renderizarlos en la tabla
async function ObtenerEquipos(IdFinal = 0) {
  const URL = API_RUTA("Equipos");
  try {
    const response = await inventoryFetch(`${URL}?ultimo=${IdFinal}`);
    const datos = await response.json();
    const tabla = document.querySelector("#inventarioTable tbody");

    //Limpiar la tabla
    tabla.innerHTML = "";

    datos.data.forEach((equipo) => {
      let row = tabla.insertRow();
      const estado = String(equipo.Estado ?? "")
        .trim()
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-|-$/g, "");
      if (estado) {
        row.classList.add(`${estado}`);
      }
      row.insertCell().textContent = equipo.Codigo;
      row.insertCell().textContent = equipo.Descripcion;
      row.insertCell().textContent = equipo.Categoria;
      row.insertCell().textContent = equipo.Estado;

      const celdaAccion = row.insertCell();
      const btnDetalle = document.createElement("button");
      const btnEditar = document.createElement("button");
      const btnEliminar = document.createElement("button");

      btnDetalle.type = "button";
      btnDetalle.textContent = "Detalles";
      btnDetalle.classList.add("btn-preview");

      btnEditar.type = "button";
      btnEditar.textContent = "Editar";
      btnEditar.classList.add("btn-edit");

      btnEliminar.type = "button";
      btnEliminar.disabled = estado === "dado-de-baja";
      btnEliminar.textContent = "Eliminar";
      btnEliminar.classList.add("btn-delete");

      btnDetalle.addEventListener("click", () => {
        ObtenerPreview(equipo.Codigo);
      });
      btnEditar.addEventListener("click", () => {
        editarEquipo(equipo.Codigo);
      });
      btnEliminar.addEventListener("click", () => {
        EliminarEquipo(equipo.Codigo);
      });
      celdaAccion.appendChild(btnDetalle);
      celdaAccion.appendChild(btnEditar);
      celdaAccion.appendChild(btnEliminar);
    });
  } catch (error) {
    alert("Error al cargar los equipos");
  }
}

function MostrarDetallesEquipo(equipo) {
  const modal = document.querySelector("#ModalPreview");
  const contenedor = document.querySelector("#DetalleEquipo");

  contenedor.replaceChildren();

  Object.entries(equipo).forEach(([campo, valor]) => {
    const fila = document.createElement("p");
    const etiqueta = document.createElement("strong");
    const detalle = document.createElement("span");

    etiqueta.textContent = campo.replaceAll("_", " ") + ": ";
    detalle.textContent = valor ?? "No disponible";

    fila.append(etiqueta, detalle);
    contenedor.appendChild(fila);
  });

  modal.showModal();
}

function CerrarPreview() {
  document.querySelector("#ModalPreview").close();
}

async function ObtenerPreview(codigo) {
  const URL = API_RUTA("Equipos");
  try {
    const response = await inventoryFetch(
      `${URL}?codigo=${encodeURIComponent(codigo)}`,
    );

    const datos = await response.json();
    const equipo = datos.data;

    if (!equipo || Array.isArray(equipo)) {
      throw new Error("No se encontraron los detalles del equipo.");
    }

    MostrarDetallesEquipo(equipo);
  } catch (error) {
    alert("Error al consultar el equipo: " + error.message);
  }
}

async function ObtenerCategorias() {
  const API_URL_CATEGORIAS = API_RUTA("Categorias");
  try {
    const response = await inventoryFetch(API_URL_CATEGORIAS);
    const datos = await response.json();

    const selectCategoria = document.getElementById("categoria");

    datos.data.forEach((categoria) => {
      let option = document.createElement("option");

      option.value = categoria.Id_Categoria;
      option.textContent = categoria.Nombre_Categoria;

      selectCategoria.appendChild(option);
    });
  } catch (error) {
    alert("Error al conectar con la API:" + error);
  }
}

async function ObtenerUbicaciones() {
  const API_URL_UBICACIONES = API_RUTA("Ubicaciones");
  try {
    const response = await inventoryFetch(API_URL_UBICACIONES);
    const datos = await response.json();
    const ubicaciones = document.getElementById("ubicacion");

    datos.data.forEach((ubicacion) => {
      let option = document.createElement("option");
      option.value = ubicacion.Id_Ubicacion;
      option.textContent = ubicacion.Nombre_Ubicacion;
      ubicaciones.appendChild(option);
    });
  } catch (error) {
    alert("Error al conectar con la API:" + error);
  }
}

async function ObtenerEstadosEquipo() {
  const API_URL_ESTADOS = API_RUTA("Estados_Equipo");
  try {
    const response = await inventoryFetch(API_URL_ESTADOS);
    const datos = await response.json();
    const estados = document.getElementById("estado");
    datos.data.forEach((estado) => {
      let option = document.createElement("option");
      option.value = estado.id_estado_Equipo;
      option.textContent = estado.Nombre_Estado_Equipo;
      estados.appendChild(option);
    });
  } catch (error) {
    alert("Error al conectar con la API:" + error);
  }
}

function cargarModal() {
  form = document.querySelector("#equipoForm");

  const modal = document.querySelector("#ModalEquipos");
  const abrir = document.querySelector("#IngresarEquipo");
  const cerrar = document.querySelector("#cerrarModal");

  if (!form || !modal || !abrir || !cerrar) {
    console.error("No se encontraron todos los elementos del modal:", {
      form,
      modal,
      abrir,
      cerrar,
    });
    return;
  }

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    if (action === "GUARDAR") {
      await nuevoEquipo();
    } else {
      await actualizarEquipo();
    }
  });

  abrir.addEventListener("click", () => {
    action = "GUARDAR";
    form.reset();
    modal.showModal();
  });

  cerrar.addEventListener("click", () => {
    form.reset();
    modal.close();
  });
}

async function nuevoEquipo() {
  const URL = API_RUTA("Equipos");

  const data = new FormData(form);
  try {
    const response = await inventoryFetch(URL, {
      method: "POST",
      body: data, //body: JSON.STRINGFY(data); Esto es para PUT
    });
    const result = await response.json();
    alert(result.message);
    form.reset();
    await TablaPaginada(true);
  } catch (error) {
    alert("Error al enviar el formulario:" + error);
  }
}
async function editarEquipo(codigo) {
  const URL = API_RUTA("Equipos");
  let modal = document.querySelector("#ModalEquipos");

  try{
    const response = await inventoryFetch(`${URL}?codigo=${encodeURIComponent(codigo)}`);
    const datos = await response.json();
    if (!response.ok || !datos.data || Array.isArray(datos.data)) {
      alert("Error al obtener los datos del equipo");
      return;
    }
    form.reset();
    const equipo = datos.data;
    form.querySelector("#id_equipo").value = equipo.id;
    form.querySelector("#codigo").value = equipo.Codigo;
    form.querySelector("#descripcion").value = equipo.Descripcion;
    form.querySelector("#categoria").value = String(equipo.Id_Categoria ?? "");
    form.querySelector("#ubicacion").value = String(equipo.Id_Ubicacion ?? "");
    form.querySelector("#estado").value = String(equipo.Id_Estado_Equipo ?? "");
    form.querySelector("#marca").value = equipo.Marca;
    form.querySelector("#modelo").value = equipo.Modelo;
    form.querySelector("#serie").value = equipo.Serie;
    form.querySelector("#descripcion").value = equipo.Descripcion;

    modal.showModal();
    action = "EDITAR";
  } catch (error) {
    alert("Error al obtener los datos del equipo");
  }
}
async function actualizarEquipo() {
  const URL = API_RUTA("Equipos");
  const data = new URLSearchParams(new FormData(form));
  try {
    const response = await inventoryFetch(URL, {
      method: "PUT",
      body: data,
    });
    const result = await response.json();
    alert(result.message);
    await ObtenerEquipos(UltimoId);
  } catch (error) {
    alert("Error al actualizar el equipo" + error);
  }
}
async function EliminarEquipo(codigo) {
  const URL = API_RUTA("Equipos");
  if (!confirm("¿Estás seguro de que deseas eliminar este equipo?")) {
    return;
  }
  try {
    const response = await inventoryFetch(
      `${URL}?codigo=${encodeURIComponent(codigo)}`,
      { method: "DELETE" },
    );
    const result = await response.json();
    alert(result.message);
    await ObtenerEquipos(UltimoId);
  } catch (error) {
    alert("Error al eliminar el equipo");
  }
}
