(() => {
    const contenido = document.getElementById("contenido");
    if (!contenido) return;

    function adaptarTablas() {
        contenido.querySelectorAll(".tabla-desplazable table").forEach((tabla) => {
            tabla.classList.add("tabla-adaptable");
            tabla.setAttribute("role", "table");
            const encabezados = [...tabla.querySelectorAll("thead th")].filter(encabezado => !encabezado.hidden);
            tabla.querySelectorAll("thead, tbody").forEach(grupo => grupo.setAttribute("role", "rowgroup"));
            tabla.querySelectorAll("thead tr").forEach(fila => fila.setAttribute("role", "row"));
            encabezados.forEach(encabezado => {
                encabezado.setAttribute("role", "columnheader");
                encabezado.setAttribute("scope", "col");
            });
            tabla.querySelectorAll("tbody tr").forEach((fila) => {
                fila.setAttribute("role", "row");
                fila.querySelectorAll("td").forEach((celda, indice) => {
                    celda.setAttribute("role", "cell");
                    celda.removeAttribute("data-resumen");
                    celda.removeAttribute("data-acciones");
                    if (celda.colSpan > 1) {
                        celda.setAttribute("data-resumen", "");
                        celda.removeAttribute("data-columna");
                        return;
                    }
                    const etiqueta = encabezados[indice]?.textContent.trim() ?? "";
                    celda.setAttribute("data-columna", etiqueta);
                    if (etiqueta === "Acciones" || etiqueta === "Detalles") {
                        celda.setAttribute("data-acciones", "");
                    }
                });
            });
        });
    }

    const observador = new MutationObserver(adaptarTablas);
    observador.observe(contenido, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ["hidden"]
    });
    adaptarTablas();
})();
