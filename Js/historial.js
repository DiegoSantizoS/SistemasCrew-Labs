function initHistorial() {
    const buscador = document.getElementById("buscarHistorial");
    const mensaje = document.getElementById("avisoHistorial");
    const filas = document.getElementById("filasHistorial");
    const anterior = document.getElementById("historialAnterior");
    const siguiente = document.getElementById("historialSiguiente");
    const indicador = document.getElementById("paginaHistorial");
    const formularioFiltro = document.getElementById("filtroHistorial");
    let solicitudActual = 0;
    let temporizador;

    async function cargarHistorial(pagina = 1) {
        if (!buscador.isConnected) return;
        const solicitud = ++solicitudActual;
        const busqueda = buscador.value.trim();
        const vigente = () => solicitud === solicitudActual && buscador === document.getElementById("buscarHistorial");
        anterior.disabled = siguiente.disabled = true;
        indicador.textContent = "";
        mensaje.textContent = "Cargando historial...";
        filas.replaceChildren();
        try {
            const parametros = new URLSearchParams({ pagina, buscar: busqueda });
            const respuesta = await fetch("api/admin/Historial.php?" + parametros, { credentials: "same-origin", cache: "no-store" });
            if (!vigente()) return;
            if (respuesta.status === 401) {
                location.assign("login.php");
                return;
            }
            const resultado = await respuesta.json();
            if (!vigente()) return;
            if (!respuesta.ok) throw new Error(resultado.message || "No se pudo cargar el historial.");
            document.getElementById("tituloHistorial").textContent = resultado.es_admin ? "Todos los pr\u00e9stamos" : "Mis pr\u00e9stamos";
            document.getElementById("columnaUsuarioHistorial").hidden = !resultado.es_admin;
            for (const prestamo of resultado.data) {
                const fila = document.createElement("tr");
                const valores = [prestamo.id, ...(resultado.es_admin ? [prestamo.usuario] : []), prestamo.fecha, prestamo.vencimiento, prestamo.estado, prestamo.motivo];
                for (const valor of valores) {
                    const celda = document.createElement("td");
                    celda.textContent = valor ?? "";
                    fila.appendChild(celda);
                }
                filas.appendChild(fila);
            }
            mensaje.textContent = busqueda
                ? `${resultado.total} pr\u00e9stamo(s) coinciden con "${busqueda}".`
                : (resultado.data.length ? `${resultado.total} pr\u00e9stamo(s) en total.` : "No hay prestamos para mostrar.");
            indicador.textContent = `P\u00e1gina ${resultado.pagina} de ${resultado.paginas}`;
            anterior.disabled = resultado.pagina <= 1;
            siguiente.disabled = resultado.pagina >= resultado.paginas;
            anterior.onclick = () => cargarHistorial(resultado.pagina - 1);
            siguiente.onclick = () => cargarHistorial(resultado.pagina + 1);
        } catch (error) {
            if (vigente()) mensaje.textContent = error.message;
        }
    }

    formularioFiltro.addEventListener("submit", (evento) => {
        evento.preventDefault();
        clearTimeout(temporizador);
        cargarHistorial(1);
    });

    buscador.addEventListener("input", () => {
        clearTimeout(temporizador);
        solicitudActual++;
        filas.replaceChildren();
        anterior.disabled = siguiente.disabled = true;
        indicador.textContent = "";
        mensaje.textContent = "Buscando...";
        temporizador = setTimeout(() => cargarHistorial(1), 250);
    });
    return cargarHistorial();
}
