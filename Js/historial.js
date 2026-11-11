function initHistorial() {
  obtenerMiHistorial();
}

async function obtenerMiHistorial(pagina = 1) {
  const aviso = document.querySelector("#avisoHistorial");
  const filas = document.querySelector("#filasHistorial");
  const anterior = document.querySelector("#historialAnterior");
  const siguiente = document.querySelector("#historialSiguiente");
  const indicador = document.querySelector("#paginaHistorial");
  anterior.disabled = true;
  siguiente.disabled = true;
  indicador.textContent = "";

  aviso.textContent = "Cargando historial…";
  filas.replaceChildren();

  try {
    const response = await fetch(`api/admin/Historial.php?pagina=${pagina}`, {
      credentials: "same-origin",
    });

    if (response.status === 401) {
      location.assign("login.php");
      return;
    }

    const result = await response.json();

    if (!response.ok) {
      throw new Error(result.message || "No se pudo cargar el historial.");
    }
    if (!filas.isConnected) return;
    document.querySelector("#tituloHistorial").textContent = result.es_admin
      ? "Todos los préstamos" : "Mis préstamos";
    document.querySelector("#columnaUsuarioHistorial").hidden = !result.es_admin;

    for (const prestamo of result.data) {
      const fila = document.createElement("tr");

      for (const valor of [
        prestamo.id,
        ...(result.es_admin ? [prestamo.usuario] : []),
        prestamo.fecha,
        prestamo.vencimiento,
        prestamo.estado,
        prestamo.motivo,
      ]) {
        const celda = document.createElement("td");
        celda.textContent = valor ?? "—";
        fila.appendChild(celda);
      }

      filas.appendChild(fila);
    }

    aviso.textContent = result.data.length
      ? `${result.total} préstamo(s) en total.`
      : "No hay préstamos para mostrar.";
    indicador.textContent = `Página ${result.pagina} de ${result.paginas}`;
    anterior.disabled = result.pagina <= 1;
    siguiente.disabled = result.pagina >= result.paginas;
    anterior.onclick = () => obtenerMiHistorial(result.pagina - 1);
    siguiente.onclick = () => obtenerMiHistorial(result.pagina + 1);
  } catch (error) {
    aviso.textContent = error.message;
  }
}
