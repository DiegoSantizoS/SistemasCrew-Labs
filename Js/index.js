const PerfilImg = document.getElementById("PerfilImg");
const PerfilNav = document.getElementById("PerfilNav");

PerfilImg.addEventListener("click", function () {
  PerfilNav.classList.toggle("mostrar");
});
let $ = (doc) => document.querySelector(doc);
let $$ = (doc) => document.querySelectorAll(doc);
const historialNav = $("#HistorialNav");

function cargarInventario() {
  let InventarioNav = $("#InventarioNav");

  InventarioNav.addEventListener("click", function () {
    $(".Bienvenida").style.display = "none";

    fetch("Sites/inventario.html")
      .then((response) => response.text())
      .then((html) => {
        $(".Contenido").innerHTML = html;

        // Conecta el buscador una vez que su campo existe en el documento.
        inicializarBuscadorInventario();
        TablaPaginada();
        ObtenerCategorias();
        ObtenerUbicaciones();
        ObtenerEstadosEquipo();
        cargarModal();

        // Evento para cerrar el modal de preview
        const cerrarPreview = $("#cerrarPreview");

        if (cerrarPreview) {
          cerrarPreview.addEventListener("click", CerrarPreview);
        }

        document.title = "Inventario";
      })
      .catch((error) => {
        alert("Error al cargar el contenido: " + error);
      });
  });
}

function cargarHistorial() {
  historialNav.addEventListener("click", function () {
    $(".Bienvenida").style.display = "none";
    fetch("Sites/historial.html")
      .then((response) => response.text())
      .then((html) => {
        $(".Contenido").innerHTML = html;
        document.title = "Historial";
      })
      .catch((error) => {
        alert("Error al cargar el contenido: " + error);
      });
  });
}

function CargarInicio() {
  let InicioNav = $("#InicioNav");
  InicioNav.addEventListener("click", function () {
    $(".Contenido").innerHTML = "";
    $(".Bienvenida").style.display = "block";
    document.title = "Inicio";
  });
}

function cargarPrestamos() {
  const prestamosNav = $("#PrestamosNav");
  prestamosNav.addEventListener("click", async function (event) {
    event.preventDefault();
    $(".Bienvenida").style.display = "none";
    const contenido = $(".Contenido");
    contenido.textContent = "Cargando préstamos…";
    try {
      const response = await fetch("Sites/prestamos.html");
      if (!response.ok) throw new Error("No se pudo cargar la página de préstamos.");
      contenido.innerHTML = await response.text();
      document.title = "Préstamos";
      if (!document.querySelector('script[data-prestamos]')) {
        const script = document.createElement("script");
        script.src = "Js/prestamos.js";
        script.dataset.prestamos = "true";
        document.body.appendChild(script);
      } else if (typeof initPrestamos === "function") {
        initPrestamos();
      }
    } catch (error) {
      contenido.textContent = error.message;
    }
  });
}
cargarInventario();
CargarInicio();
cargarPrestamos();
cargarHistorial();
