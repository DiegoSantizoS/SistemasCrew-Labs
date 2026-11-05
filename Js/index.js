// Navegación y elementos compartidos del panel.
const PerfilImg = document.getElementById("PerfilImg");
const PerfilNav = document.getElementById("PerfilNav");
PerfilImg.addEventListener("click", () => PerfilNav.classList.toggle("mostrar"));

let navegacionActual = 0;

function conectarSeccion(id, ruta, titulo, inicializar) {
  document.getElementById(id).addEventListener("click", async (event) => {
    event.preventDefault();
    const solicitud = ++navegacionActual;
    const contenido = document.querySelector(".Contenido");
    document.querySelector(".Bienvenida").style.display = "none";
    contenido.textContent = "Cargando…";
    try {
      const response = await fetch(ruta);
      if (!response.ok) throw new Error(`No se pudo cargar ${titulo}.`);
      const html = await response.text();
      if (solicitud !== navegacionActual) return;
      contenido.innerHTML = html;
      document.title = titulo;
      inicializar();
    } catch (error) {
      if (solicitud === navegacionActual) contenido.textContent = error.message;
    }
  });
}

document.getElementById("InicioNav").addEventListener("click", () => {
  navegacionActual++;
  document.querySelector(".Contenido").replaceChildren();
  document.querySelector(".Bienvenida").style.display = "block";
  document.title = "Inicio";
});

conectarSeccion("InventarioNav", "Sites/inventario.html", "Inventario", initInventario);
conectarSeccion("PrestamosNav", "Sites/prestamos.html", "Préstamos", initPrestamos);
conectarSeccion("DevolucionesNav", "Sites/devoluciones.html", "Devoluciones", initDevoluciones);
conectarSeccion("HistorialNav", "Sites/historial.html", "Historial", initHistorial);
