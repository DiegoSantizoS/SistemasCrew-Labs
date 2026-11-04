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
cargarInventario();
CargarInicio();
cargarHistorial();