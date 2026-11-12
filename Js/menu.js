const botonMenu = document.getElementById("botonMenu");
const menuPrincipal = document.getElementById("menuPrincipal");
const vistasMenu = {
    inicio: "InicioNav",
    inventario: "InventarioNav",
    prestamos: "PrestamosNav",
    devoluciones: "DevolucionesNav",
    historial: "HistorialNav"
};

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

document.querySelectorAll("[data-vista]").forEach((enlace) => {
    enlace.addEventListener("click", (evento) => {
        evento.preventDefault();
        const destino = document.getElementById(vistasMenu[enlace.dataset.vista]);
        if (!destino) return;
        if (enlace !== destino) {
            destino.click();
            return;
        }
        menuPrincipal.querySelectorAll("[data-vista]").forEach((opcion) => {
            if (opcion === destino) opcion.setAttribute("aria-current", "page");
            else opcion.removeAttribute("aria-current");
        });
        if (window.matchMedia("(max-width: 700px)").matches) cambiarMenu(false);
    });
});

document.getElementById("PerfilImg").addEventListener("click", (evento) => {
    const visible = document.getElementById("PerfilNav").classList.contains("mostrar");
    evento.currentTarget.setAttribute("aria-expanded", String(visible));
});
