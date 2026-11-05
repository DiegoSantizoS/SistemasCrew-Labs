function initDevoluciones() {
  const lista = document.querySelector("#listaDevoluciones");
  const aviso = document.querySelector("#avisoDevoluciones");
  const modal = document.querySelector("#modalDevolucion");
  const form = document.querySelector("#formDevolucion");
  if (!lista || !aviso || !modal || !form || form.dataset.inicializado) return;
  form.dataset.inicializado = "true";
  const api = "api/admin/Devoluciones.php";
  let csrf = "";
  let prestamos = [];
  let esAdmin = false;

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

  function fecha(valor) {
    if (!valor) return "—";
    const parsed = new Date(String(valor).replace(" ", "T"));
    return Number.isNaN(parsed.getTime()) ? valor : parsed.toLocaleDateString("es-GT");
  }

  function crearTarjeta(prestamo) {
    const tarjeta = document.createElement("article");
    tarjeta.className = "devolucion-card";
    const titulo = document.createElement("h2");
    titulo.textContent = `${prestamo.codigo} — ${prestamo.descripcion || "Equipo"}`;
    const datos = document.createElement("dl");
    for (const [label, valor] of [
      ["Préstamo", `#${prestamo.prestamo_id}`],
      ["Categoría", prestamo.categoria],
      ["Ubicación", prestamo.ubicacion],
      ["Fecha del préstamo", fecha(prestamo.fecha_prestamo)],
      ["Fecha prevista de devolución", fecha(prestamo.fecha_vencimiento)],
      ["Estado del préstamo", prestamo.estado_prestamo],
    ]) {
      const dt = document.createElement("dt");
      dt.textContent = label;
      const dd = document.createElement("dd");
      dd.textContent = valor ?? "—";
      datos.append(dt, dd);
    }
    tarjeta.append(titulo, datos);

    if (esAdmin && prestamo.usuario) {
      const solicitante = document.createElement("p");
      solicitante.className = "solicitante-devolucion";
      solicitante.textContent = `Solicitante: ${prestamo.usuario}`;
      tarjeta.appendChild(solicitante);
    }

    if (prestamo.fecha_devolucion) {
      const estado = document.createElement("p");
      estado.className = "devolucion-completada";
      estado.textContent = `Devuelto el ${fecha(prestamo.fecha_devolucion)} · Estado: ${prestamo.estado_retorno || "registrado"}`;
      tarjeta.appendChild(estado);
    } else if (esAdmin && ["Activo", "Vencido"].includes(prestamo.estado_prestamo)) {
      const boton = document.createElement("button");
      boton.type = "button";
      boton.textContent = "Registrar devolución";
      boton.addEventListener("click", () => {
        form.reset();
        form.elements.detalle_id.value = prestamo.detalle_id;
        document.querySelector("#equipoDevolucion").textContent = `${prestamo.codigo} — ${prestamo.descripcion || "Equipo"}`;
        modal.showModal();
      });
      tarjeta.appendChild(boton);
    } else if (["Activo", "Vencido"].includes(prestamo.estado_prestamo)) {
      const nota = document.createElement("p");
      nota.className = "devolucion-pendiente-admin";
      nota.textContent = "La devolución debe registrarla un administrador.";
      tarjeta.appendChild(nota);
    }
    return tarjeta;
  }

  function mostrarPrestamos() {
    lista.replaceChildren();
    if (!prestamos.length) {
      aviso.textContent = esAdmin ? "No hay préstamos registrados." : "Todavía no tienes préstamos registrados.";
      return;
    }
    aviso.textContent = esAdmin
      ? `${prestamos.length} equipo(s) en el historial de préstamos.`
      : `${prestamos.length} equipo(s) en tu historial de préstamos.`;
    prestamos.forEach(prestamo => lista.appendChild(crearTarjeta(prestamo)));
  }

  async function cargarPrestamos() {
    aviso.textContent = "Cargando tus préstamos…";
    lista.replaceChildren();
    try {
      const result = await pedir();
      if (!lista.isConnected) return;
      csrf = result.csrf;
      esAdmin = result.es_admin === true;
      prestamos = result.data;
      document.querySelector(".devoluciones-page h1").textContent = esAdmin ? "Préstamos por devolver" : "Mis préstamos";
      mostrarPrestamos();
    } catch (error) {
      aviso.textContent = error.message;
    }
  }

  form.addEventListener("submit", async event => {
    event.preventDefault();
    const boton = form.querySelector('button[type="submit"]');
    boton.disabled = true;
    try {
      const result = await pedir({
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(Object.fromEntries(new FormData(form))),
      });
      modal.close();
      await cargarPrestamos();
      aviso.textContent = `${result.message} ${result.data.codigo} ahora figura en el estado indicado.`;
    } catch (error) {
      aviso.textContent = error.message;
    } finally {
      boton.disabled = false;
    }
  });

  document.querySelector("#cancelarDevolucion").addEventListener("click", () => modal.close());
  cargarPrestamos();
}
