const inicio = document.getElementById("inicio");
const contenido = document.getElementById("contenido");
const menu = document.getElementById("menuPrincipal");
const botonMenu = document.getElementById("botonMenu");
let cargaActual = 0;

function cambiarMenu(expandido) {
  document.body.classList.toggle("menu-expandido", expandido);
  botonMenu.setAttribute("aria-expanded", String(expandido));
  botonMenu.setAttribute("aria-label", expandido ? "Contraer men\u00fa" : "Expandir men\u00fa");
}

botonMenu.addEventListener("click", () => {
  cambiarMenu(!document.body.classList.contains("menu-expandido"));
});

document.addEventListener("keydown", (evento) => {
  if (evento.key === "Escape" && document.body.classList.contains("menu-expandido")) {
    cambiarMenu(false);
    botonMenu.focus();
  }
});

async function mostrarVista(vista) {
  const carga = ++cargaActual;
  if (window.matchMedia("(max-width: 700px)").matches) cambiarMenu(false);
  menu.querySelectorAll("[data-vista]").forEach((enlace) => {
    if (enlace.dataset.vista === vista) enlace.setAttribute("aria-current", "page");
    else enlace.removeAttribute("aria-current");
  });
  inicio.hidden = vista !== "inicio";
  contenido.hidden = vista === "inicio";
  if (vista === "inicio") {
    contenido.replaceChildren();
    document.title = "Inicio | SistemasCrew Labs";
    return;
  }
  contenido.textContent = "Cargando...";
  try {
    const respuesta = await fetch(`Sites/${vista}.html`);
    if (!respuesta.ok) throw new Error("No se pudo cargar la vista.");
    const html = await respuesta.text();
    if (carga !== cargaActual) return;
    contenido.innerHTML = html;
    document.title = `${vista.charAt(0).toUpperCase() + vista.slice(1)} | SistemasCrew Labs`;
    if (vista === "inventario") {
      inicializarBuscadorInventario();
      TablaPaginada();
      ObtenerCategorias();
      ObtenerUbicaciones();
      ObtenerEstadosEquipo();
      cargarModal();
      const cerrarDetalle = document.getElementById("cerrarPreview");
      if (cerrarDetalle) cerrarDetalle.addEventListener("click", CerrarPreview);
    }
    if (vista === "prestamos") {
      if (!document.querySelector("script[data-prestamos]")) {
        const archivo = document.createElement("script");
        archivo.src = "Js/prestamos.js";
        archivo.dataset.prestamos = "true";
        document.body.appendChild(archivo);
      } else if (typeof initPrestamos === "function") {
        initPrestamos();
      }
    }
  } catch (error) {
    if (carga === cargaActual) contenido.textContent = error.message;
  }
}

document.querySelectorAll("[data-vista]").forEach((enlace) => {
  enlace.addEventListener("click", (evento) => {
    evento.preventDefault();
    mostrarVista(enlace.dataset.vista);
  });
});
