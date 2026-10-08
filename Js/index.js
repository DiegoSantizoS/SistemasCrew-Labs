const PerfilImg = document.getElementById("PerfilImg");
const PerfilNav = document.getElementById("PerfilNav");

PerfilImg.addEventListener("click", function () {
  PerfilNav.classList.toggle("mostrar");
});
let $ = (doc) => document.querySelector(doc);
let $$ = (doc) => document.querySelectorAll(doc);

function cargarInventario() {
  let InventarioNav = $("#InventarioNav");
  InventarioNav.addEventListener("click", function () {
    $(".Bienvenida").style.display = "none";
    fetch("Sites/inventario.html")
      .then((response) => response.text())
      .then((html) => {
        $(".Contenido").innerHTML = html;
        TablaPaginada();
        ObtenerCategorias();
        ObtenerUbicaciones();
        ObtenerEstadosEquipo();
        cargarModal();
        document.title = "Inventario";
      })
      .catch((error) => {
        alert("Error al cargar el contenido:" + error);
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
