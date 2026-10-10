// const API_URL =
//   "http://localhost/SistemasCrew-Labs/api/admin/Equipos.php";
let UltimoId = 0;
let API_RUTA = (controlador) => {
  return `http://localhost/SistemasCrew-Labs/api/admin/${controlador}.php`;
};
async function TablaPaginada() {
  const URL = API_RUTA("PaginasEquipos");
  try {
    const response = await fetch(URL);
    const datos = await response.json();
    const paginaciondiv = document.querySelector("#Paginacion");
    let paginas = Number(datos["0"].paginas);

    /* for (let i = 0; i < paginas; i++) {
      paginaciondiv.innerHTML += `
        <button class="Paginador" id="Pagina${i + 1}">
          ${i + 1}
        </button>
      `;
    }*/
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
    paginasbtn.forEach((boton) => {
      boton.addEventListener("click", () => {
        const ultimoId = boton.dataset.ultimoId;
        ObtenerEquipos(ultimoId);
      });
    });
    ObtenerEquipos(0);
  } catch (error) {
    alert("Error al cargar los datos" + error);
  }
}
async function ObtenerEquipos(IdFinal = 0) {
  const URL = API_RUTA("Equipos");
  try {
    const response = await fetch(`${URL}?ultimo=${IdFinal}`);

    const datos = await response.json();

    const tabla = document.querySelector("#inventarioTable tbody");
    tabla.innerHTML = "";
    datos.data.forEach((equipo) => {
      let row = tabla.insertRow();
      row.insertCell().textContent = equipo.Codigo;
      row.insertCell().textContent = equipo.Categoria;
      row.insertCell().textContent = equipo.Ubicacion;
      row.insertCell().textContent = equipo.Estado;
      row.insertCell().textContent = equipo.Marca;
      row.insertCell().textContent = equipo.Modelo;
      row.insertCell().textContent = equipo.Serie;
      row.insertCell().textContent = equipo.Descripcion;
      row.insertCell().textContent = equipo.Fecha_Registro_Equipo;
      row.insertCell().textContent = equipo.Fecha_Baja ?? "No disponible";
    });
  } catch (error) {
    alert("Error al conectar con la API:", error);
  }
}

const API_URL_CATEGORIAS =
  "http://localhost/SistemasCrew-Labs/api/admin/Categorias.php";

async function ObtenerCategorias() {
  try {
    const response = await fetch(API_URL_CATEGORIAS);
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
  const API_URL_UBICACIONES =
    "http://localhost/SistemasCrew-Labs/api/admin/Ubicaciones.php";
  try {
    const response = await fetch(API_URL_UBICACIONES);
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
  const API_URL_ESTADOS =
    "http://localhost/SistemasCrew-Labs/api/admin/Estados_Equipo.php";
  try {
    const response = await fetch(API_URL_ESTADOS);
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
  const URL = API_RUTA("Equipos");
  const form = document.querySelector("#equipoForm");
  const modal = document.querySelector("#ModalEquipos");
  const abrir = document.querySelector("#IngresarEquipo");
  const cerrar = document.querySelector("#cerrarModal");
  const tbody = document.querySelector("#inventarioTable tbody");
  abrir.addEventListener("click", () => {
    modal.showModal();
  });
  cerrar.addEventListener("click", () => {
    form.reset();
    modal.close();
  });
  form.addEventListener("submit", async (event) => {
      event.preventDefault();
      const data = new FormData(form);
      try {
        const response = await fetch(URL, {
          method: "POST",
          body: data,
        });
        const result = await response.json();
        alert(result.message);
      } catch (error) {
        alert("Error al enviar el formulario:" + error);
      }
      form.reset();
      tbody.innerHTML = "";
      TablaPaginada();  
    });
}
