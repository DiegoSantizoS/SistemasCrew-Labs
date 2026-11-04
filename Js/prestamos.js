function initPrestamos() {
  const api = "api/admin/Prestamos.php";
  const lista = document.querySelector("#equiposDisponibles");
  const modal = document.querySelector("#modalPrestamo");
  const form = document.querySelector("#formPrestamo");
  const aviso = document.querySelector("#avisoPrestamos");
  const busqueda = document.querySelector("#buscarEquipo");
  if (!lista || !modal || !form || !busqueda || form.dataset.inicializado) return;
  form.dataset.inicializado = "true";
  let csrf = "";
  let equiposDisponibles = [];

  async function pedir(options = {}) {
    const headers = new Headers(options.headers || {});
    if (csrf && options.method === "POST") headers.set("X-CSRF-Token", csrf);
    const response = await fetch(api, { ...options, headers, credentials: "same-origin" });
    if (response.status === 401) {
      location.assign("login.php");
      throw new Error("Tu sesión venció. Inicia sesión nuevamente.");
    }
    const result = await response.json();
    if (!response.ok) throw new Error(result.message || "No se pudo completar la solicitud.");
    return result;
  }

  function notificar(texto, error = false) {
    aviso.textContent = texto;
    aviso.classList.toggle("error", error);
  }

  function crearTarjeta(equipo) {
    const tarjeta = document.createElement("article");
    tarjeta.className = "equipo-card";
    const titulo = document.createElement("h2");
    titulo.textContent = `${equipo.Codigo} — ${equipo.Descripcion || "Equipo sin descripción"}`;
    const detalle = document.createElement("p");
    detalle.textContent = `${equipo.Categoria} · ${equipo.Ubicacion}${equipo.Marca ? ` · ${equipo.Marca}` : ""}${equipo.Modelo ? ` ${equipo.Modelo}` : ""}`;
    const estado = document.createElement("span");
    estado.className = "estado-disponible";
    estado.textContent = equipo.Estado;
    const boton = document.createElement("button");
    boton.type = "button";
    boton.textContent = "Solicitar préstamo";
    boton.addEventListener("click", () => {
      form.reset();
      form.elements.equipo_id.value = equipo.Id_Equipo;
      document.querySelector("#equipoSeleccionado").textContent = `${equipo.Codigo} — ${equipo.Descripcion}`;
      modal.showModal();
    });
    tarjeta.append(titulo, detalle, estado, boton);
    return tarjeta;
  }

  async function cargarDisponibles() {
    lista.replaceChildren();
    notificar("Cargando equipos disponibles…");
    try {
      const result = await pedir();
      csrf = result.csrf;
      equiposDisponibles = result.data;
      mostrarFiltrados();
    } catch (error) {
      notificar(error.message, true);
    }
  }

  function mostrarFiltrados() {
    const termino = busqueda.value.trim().toLocaleLowerCase("es");
    const resultados = equiposDisponibles.filter(equipo =>
      [equipo.Descripcion, equipo.Codigo, equipo.Categoria, equipo.Marca, equipo.Modelo]
        .filter(Boolean)
        .some(valor => valor.toLocaleLowerCase("es").includes(termino)),
    );
    lista.replaceChildren();
    if (!equiposDisponibles.length) return notificar("No hay equipos disponibles en este momento.");
    if (!resultados.length) return notificar("No se encontraron equipos con ese nombre o término.");
    notificar(`${resultados.length} de ${equiposDisponibles.length} equipo(s) disponible(s).`);
    resultados.forEach(equipo => lista.appendChild(crearTarjeta(equipo)));
  }

  busqueda.addEventListener("input", mostrarFiltrados);

  form.addEventListener("submit", async event => {
    event.preventDefault();
    const boton = form.querySelector("button[type=submit]");
    boton.disabled = true;
    try {
      const result = await pedir({
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(Object.fromEntries(new FormData(form))),
      });
      modal.close();
      await cargarDisponibles();
      notificar(`${result.message} Número de préstamo: ${result.data.id_prestamo}.`);
    } catch (error) {
      notificar(error.message, true);
    } finally {
      boton.disabled = false;
    }
  });
  document.querySelector("#cerrarPrestamo").addEventListener("click", () => modal.close());
  cargarDisponibles();
}

window.initPrestamos = initPrestamos;
