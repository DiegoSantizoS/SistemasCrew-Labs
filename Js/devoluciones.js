function initDevoluciones() {
  const lista = document.querySelector("#listaDevoluciones");
  const aviso = document.querySelector("#avisoDevoluciones");
  const modal = document.querySelector("#modalDevolucion");
  const form = document.querySelector("#formDevolucion");
  const panelDevoluciones = document.querySelector("#panelDevoluciones");
  const panelMultas = document.querySelector("#panelMultas");
  const tabDevoluciones = document.querySelector("#tabDevoluciones");
  const tabMultas = document.querySelector("#tabMultas");
  const listaVencidos = document.querySelector("#listaVencidos");
  if (!lista || !aviso || !modal || !form || !panelDevoluciones || !panelMultas || !tabDevoluciones || !tabMultas || !listaVencidos || form.dataset.inicializado) return;
  form.dataset.inicializado = "true";
  const api = "api/admin/Devoluciones.php";
  let csrf = "";
  let prestamos = [];
  let esAdmin = false;

  function activarTab(tabActiva) {
    const multasActivas = esAdmin && tabActiva === tabMultas;
    tabDevoluciones.setAttribute("aria-selected", String(!multasActivas));
    tabMultas.setAttribute("aria-selected", String(multasActivas));
    tabDevoluciones.classList.toggle("activa", !multasActivas);
    tabMultas.classList.toggle("activa", multasActivas);
    tabDevoluciones.tabIndex = multasActivas ? -1 : 0;
    tabMultas.tabIndex = multasActivas ? 0 : -1;
    panelDevoluciones.hidden = multasActivas;
    panelMultas.hidden = !multasActivas;
  }

  tabDevoluciones.addEventListener("click", () => activarTab(tabDevoluciones));
  tabMultas.addEventListener("click", () => activarTab(tabMultas));
  for (const tab of [tabDevoluciones, tabMultas]) {
    tab.addEventListener("keydown", event => {
      if (event.key !== "ArrowLeft" && event.key !== "ArrowRight") return;
      event.preventDefault();
      const destino = tab === tabDevoluciones ? tabMultas : tabDevoluciones;
      if (destino.hidden) return;
      activarTab(destino);
      destino.focus();
    });
  }

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
      ["Estado del préstamo", prestamo.estado_calculado || prestamo.estado_prestamo],
    ]) {
      const dt = document.createElement("dt");
      dt.textContent = label;
      const dd = document.createElement("dd");
      dd.textContent = valor ?? "—";
      datos.append(dt, dd);
    }
    tarjeta.append(titulo, datos);

    if (!prestamo.fecha_devolucion && Number(prestamo.dias_retraso) > 0) {
      const multa = document.createElement("p");
      multa.className = "multa-pendiente";
      multa.textContent = `${prestamo.dias_retraso} día(s) de retraso · Multa estimada: Q ${Number(prestamo.multa_estimada).toFixed(2)}`;
      tarjeta.appendChild(multa);
    }

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
    } else if (esAdmin && ["Pendiente de devolución", "Vencido"].includes(prestamo.estado_calculado || prestamo.estado_prestamo)) {
      const boton = document.createElement("button");
      boton.type = "button";
      boton.textContent = "Registrar devolución";
      boton.addEventListener("click", () => {
        form.reset();
        form.elements.detalle_id.value = prestamo.detalle_id;
        document.querySelector("#equipoDevolucion").textContent = `${prestamo.codigo} — ${prestamo.descripcion || "Equipo"}`;
        const resumen = document.querySelector("#resumenMulta");
        resumen.hidden = !(Number(prestamo.dias_retraso) > 0);
        resumen.textContent = Number(prestamo.dias_retraso) > 0
          ? `Vencido por ${prestamo.dias_retraso} día(s). Multa estimada: Q ${Number(prestamo.multa_estimada).toFixed(2)}. El total se confirma al guardar.`
          : "";
        modal.showModal();
      });
      tarjeta.appendChild(boton);
    } else if (["Pendiente de devolución", "Vencido"].includes(prestamo.estado_calculado || prestamo.estado_prestamo)) {
      const nota = document.createElement("p");
      nota.className = "devolucion-pendiente-admin";
      nota.textContent = "La devolución debe registrarla un administrador.";
      tarjeta.appendChild(nota);
    }
    return tarjeta;
  }

  function mostrarPrestamos() {
    lista.replaceChildren();
    listaVencidos.replaceChildren();
    const vencidos = esAdmin
      ? prestamos.filter(p => !p.fecha_devolucion && Number(p.dias_retraso) > 0 && p.estado_calculado === "Vencido")
      : [];
    tabMultas.hidden = !esAdmin;
    if (!esAdmin) activarTab(tabDevoluciones);
    if (esAdmin && vencidos.length === 0) {
      const vacio = document.createElement("p");
      vacio.className = "sin-vencidos";
      vacio.textContent = "No hay préstamos vencidos pendientes de devolución.";
      listaVencidos.appendChild(vacio);
    } else {
      vencidos.forEach(prestamo => listaVencidos.appendChild(crearTarjeta(prestamo)));
    }
    const prestamosVisibles = esAdmin ? prestamos.filter(p => !vencidos.includes(p)) : prestamos;
    if (!prestamos.length) {
      aviso.textContent = esAdmin ? "No hay préstamos registrados." : "Todavía no tienes préstamos registrados.";
      listaVencidos.replaceChildren();
      if (esAdmin) {
        const vacio = document.createElement("p");
        vacio.className = "sin-vencidos";
        vacio.textContent = "No hay préstamos vencidos pendientes de devolución.";
        listaVencidos.appendChild(vacio);
      }
      return;
    }
    aviso.textContent = esAdmin
      ? `${vencidos.length} préstamo(s) vencido(s) con devolución pendiente.`
      : `${prestamos.length} equipo(s) en tu historial de préstamos.`;
    document.querySelector("#tituloListaDevoluciones").textContent = esAdmin ? "Otros préstamos" : "Mis préstamos";
    if (!prestamosVisibles.length) {
      document.querySelector("#tituloListaDevoluciones").hidden = true;
      return;
    }
    document.querySelector("#tituloListaDevoluciones").hidden = false;
    prestamosVisibles.forEach(prestamo => lista.appendChild(crearTarjeta(prestamo)));
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
      activarTab(tabDevoluciones);
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
      const multa = Number(result.data.multa || 0).toFixed(2);
      aviso.textContent = result.data.dias_retraso > 0
        ? `${result.message} ${result.data.codigo}: multa aplicada Q ${multa} por ${result.data.dias_retraso} día(s) de retraso.`
        : `${result.message} ${result.data.codigo} ahora figura en el estado indicado.`;
    } catch (error) {
      aviso.textContent = error.message;
    } finally {
      boton.disabled = false;
    }
  });

  document.querySelector("#cancelarDevolucion").addEventListener("click", () => modal.close());
  cargarPrestamos();
}
