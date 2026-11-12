function initUsuarios() {
    const buscador = document.getElementById("buscarUsuario");
    const mensaje = document.getElementById("mensajeUsuarios");
    const filas = document.getElementById("filasUsuarios");
    const paginasListado = document.getElementById("paginasUsuarios");
    const paginasBusqueda = document.getElementById("paginasBusquedaUsuarios");

    if (!buscador || !mensaje || !filas || !paginasListado || !paginasBusqueda) {
        throw new Error("La vista de usuarios está incompleta. Recarga la página.");
    }

    const botonIngresar = document.getElementById("IngresarUsuario");
    const modalUsuario = document.getElementById("modalUsuario");
    const formulario = document.getElementById("formularioUsuario");
    const mensajeFormulario = document.getElementById("mensajeFormularioUsuario");
    const botonGuardar = document.getElementById("guardarUsuario");
    const modalDetalle = document.getElementById("detalleUsuario");
    const elementosFormulario = [botonIngresar, modalUsuario, formulario, mensajeFormulario, botonGuardar, modalDetalle];
    if (elementosFormulario.some(elemento => !elemento)) {
        throw new Error("La vista de usuarios está incompleta. Recarga la página.");
    }
    let permisos = {};
    let idActual = 0;
    let solicitudFormulario = 0;

    async function consultarUsuarios(parametros = "", opciones = {}) {
        const cabeceras = new Headers(opciones.headers || {});
        if (opciones.method && opciones.method !== "GET") {
            cabeceras.set("X-CSRF-Token", document.querySelector('meta[name="csrf-token"]').content);
        }
        const respuesta = await fetch("api/admin/Usuarios.php" + parametros, { ...opciones, headers: cabeceras });
        if (respuesta.status === 401) {
            location.assign("login.php");
            throw new Error("Tu sesión venció.");
        }
        const resultado = await respuesta.json();
        if (!respuesta.ok) throw new Error(resultado.message || "No se pudo completar la operación.");
        return resultado;
    }

    function completarCatalogo(selector, opciones, seleccionado) {
        selector.replaceChildren();
        for (const opcion of opciones) {
            const elemento = document.createElement("option");
            elemento.value = opcion.id;
            elemento.textContent = opcion.nombre;
            selector.appendChild(elemento);
        }
        selector.value = seleccionado ?? opciones[0]?.id ?? "";
    }

    async function abrirFormulario(idUsuario = null) {
        const solicitud = ++solicitudFormulario;
        try {
            const catalogos = await consultarUsuarios("?catalogos=1");
            const usuario = idUsuario ? (await consultarUsuarios("?id=" + idUsuario)).data : null;
            if (!formulario.isConnected || solicitud !== solicitudFormulario) return;
            formulario.reset();
            mensajeFormulario.textContent = "";
            document.getElementById("idUsuario").value = usuario?.id ?? "";
            for (const campo of ["nombres", "apellidos", "carnet", "correo", "usuario", "telefono"]) {
                formulario.elements[campo].value = usuario?.[campo] ?? "";
            }
            const rol = document.getElementById("usuario-rol");
            const estado = document.getElementById("usuario-estado");
            const rolInicial = catalogos.data.roles.find(opcion => opcion.nombre === "Usuario")?.id;
            const estadoInicial = catalogos.data.estados.find(opcion => opcion.nombre === "Activo")?.id;
            completarCatalogo(rol, catalogos.data.roles, usuario?.id_rol ?? rolInicial);
            completarCatalogo(estado, catalogos.data.estados, usuario?.id_estado ?? estadoInicial);
            rol.disabled = estado.disabled = Boolean(usuario && Number(usuario.id) === idActual);
            formulario.elements.contrasena.required = !usuario;
            formulario.elements.confirmacion.required = !usuario;
            botonGuardar.textContent = usuario ? "Guardar cambios" : "Agregar usuario";
            document.getElementById("tituloFormularioUsuario").textContent = usuario ? "Editar usuario" : "Agregar usuario";
            if (!modalUsuario.open) modalUsuario.showModal();
        } catch (error) {
            if (mensaje.isConnected && solicitud === solicitudFormulario) mensaje.textContent = error.message;
        }
    }

    async function mostrarDetalles(idUsuario) {
        try {
            const resultado = await consultarUsuarios("?id=" + idUsuario);
            if (!modalDetalle.isConnected) return;
            const contenedor = document.getElementById("datosDetalleUsuario");
            contenedor.replaceChildren();
            const etiquetas = { id: "ID", nombres: "Nombres", apellidos: "Apellidos", carnet: "Carnet", correo: "Correo", usuario: "Usuario", telefono: "Teléfono", rol: "Rol", estado: "Estado" };
            for (const [campo, etiqueta] of Object.entries(etiquetas)) {
                const linea = document.createElement("p");
                const titulo = document.createElement("strong");
                const valor = document.createElement("span");
                titulo.textContent = etiqueta + ": ";
                valor.textContent = resultado.data[campo] ?? "No disponible";
                linea.append(titulo, valor);
                contenedor.appendChild(linea);
            }
            if (!modalDetalle.open) modalDetalle.showModal();
        } catch (error) {
            if (mensaje.isConnected) mensaje.textContent = error.message;
        }
    }

    async function eliminarUsuario(usuario, boton) {
        if (!confirm(`Eliminar a ${usuario.usuario}? La cuenta quedará inactiva y conservará su historial.`)) return;
        boton.disabled = true;
        try {
            const resultado = await consultarUsuarios("?id=" + usuario.id, { method: "DELETE" });
            if (!filas.isConnected) return;
            await cargarUsuarios();
            if (mensaje.isConnected) mensaje.textContent = resultado.message;
        } catch (error) {
            if (mensaje.isConnected) mensaje.textContent = error.message;
            if (boton.isConnected) boton.disabled = false;
        }
    }

    botonIngresar.addEventListener("click", () => abrirFormulario());
    document.getElementById("cerrarFormularioUsuario").addEventListener("click", () => modalUsuario.close());
    document.getElementById("cerrarDetalleUsuario").addEventListener("click", () => modalDetalle.close());
    modalUsuario.addEventListener("close", () => {
        formulario.elements.contrasena.value = "";
        formulario.elements.confirmacion.value = "";
    });
    formulario.addEventListener("submit", async (evento) => {
        evento.preventDefault();
        if (botonGuardar.disabled || !formulario.reportValidity()) return;
        const datos = Object.fromEntries(new FormData(formulario));
        datos.id_rol = document.getElementById("usuario-rol").value;
        datos.id_estado = document.getElementById("usuario-estado").value;
        const nuevo = !datos.id;
        botonGuardar.disabled = true;
        mensajeFormulario.textContent = "Guardando...";
        try {
            const resultado = await consultarUsuarios("", {
                method: nuevo ? "POST" : "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(datos)
            });
            if (!formulario.isConnected) return;
            modalUsuario.close();
            buscador.value = "";
            paginaActual = 1;
            paginaBusqueda = 0;
            cursoresBusqueda = [0];
            clearTimeout(temporizador);
            const listado = await cargarUsuarios();
            if (nuevo && listado?.paginas > 1 && filas.isConnected) {
                paginaActual = listado.paginas;
                await cargarUsuarios();
            }
            if (mensaje.isConnected) mensaje.textContent = resultado.message;
        } catch (error) {
            if (mensajeFormulario.isConnected) mensajeFormulario.textContent = error.message;
        } finally {
            botonGuardar.disabled = false;
        }
    });

    let temporizador;
    let solicitudActual = 0;
    let paginaActual = 1;
    let paginaBusqueda = 0;
    let cursoresBusqueda = [0];

    buscador.addEventListener("input", () => {
        clearTimeout(temporizador);
        solicitudActual++;
        paginaActual = 1;
        paginaBusqueda = 0;
        cursoresBusqueda = [0];
        filas.replaceChildren();
        paginasListado.replaceChildren();
        paginasBusqueda.replaceChildren();
        mensaje.textContent = "Buscando usuarios...";
        temporizador = setTimeout(cargarUsuarios, 250);
    });

    function crearBoton(texto, accion) {
        const boton = document.createElement("button");
        boton.type = "button";
        boton.textContent = texto;
        boton.addEventListener("click", accion);
        return boton;
    }

    function mostrarPaginas(resultado, busqueda) {
        paginasListado.replaceChildren();
        paginasBusqueda.replaceChildren();
        paginasListado.hidden = busqueda !== "";
        paginasBusqueda.hidden = busqueda === "";

        if (busqueda) {
            const anterior = crearBoton("Anterior", () => {
                paginaBusqueda--;
                cargarUsuarios();
            });
            anterior.disabled = paginaBusqueda === 0;
            const indicador = document.createElement("span");
            indicador.textContent = ` Página ${paginaBusqueda + 1} `;
            const siguiente = crearBoton("Siguiente", () => {
                cursoresBusqueda[paginaBusqueda + 1] = resultado.paginacion.siguiente;
                paginaBusqueda++;
                cargarUsuarios();
            });
            siguiente.disabled = !resultado.paginacion.hayMas;
            paginasBusqueda.append(anterior, indicador, siguiente);
            return;
        }

        paginaActual = resultado.pagina;
        for (let numeroPagina = 1; numeroPagina <= resultado.paginas; numeroPagina++) {
            const boton = crearBoton(String(numeroPagina), () => {
                paginaActual = numeroPagina;
                cargarUsuarios();
            });
            boton.classList.add("Paginador");
            if (numeroPagina === paginaActual) boton.setAttribute("aria-current", "page");
            paginasListado.appendChild(boton);
        }
    }

    async function cargarUsuarios() {
        if (!buscador.isConnected) return;
        const solicitud = ++solicitudActual;
        const busqueda = buscador.value.trim();
        const sigueVigente = () => solicitud === solicitudActual && buscador === document.getElementById("buscarUsuario");
        mensaje.textContent = busqueda ? "Buscando usuarios..." : "Cargando usuarios...";
        filas.replaceChildren();
        paginasListado.querySelectorAll("button").forEach(boton => boton.disabled = true);
        paginasBusqueda.querySelectorAll("button").forEach(boton => boton.disabled = true);

        try {
            const parametros = new URLSearchParams({
                buscar: busqueda,
                ultimo: busqueda ? cursoresBusqueda[paginaBusqueda] : 0,
                pagina: paginaActual
            });
            const resultado = await consultarUsuarios("?" + parametros);
            if (!sigueVigente()) return;
            permisos = resultado.permisos;
            idActual = Number(resultado.id_actual);
            botonIngresar.hidden = !permisos.crear;

            if (resultado.data.length === 0) {
                const fila = document.createElement("tr");
                const celda = document.createElement("td");
                celda.colSpan = 9;
                celda.textContent = busqueda ? "No se encontraron usuarios que coincidan." : "No hay usuarios para mostrar.";
                fila.appendChild(celda);
                filas.appendChild(fila);
            }

            for (const usuario of resultado.data) {
                const fila = document.createElement("tr");
                const valores = [usuario.id, usuario.nombres, usuario.apellidos, usuario.carnet, usuario.correo, usuario.usuario, usuario.rol, usuario.estado];
                for (const valor of valores) {
                    const celda = document.createElement("td");
                    celda.textContent = valor ?? "";
                    fila.appendChild(celda);
                }
                const acciones = document.createElement("td");
                const verDetalle = crearBoton("Detalles", () => mostrarDetalles(usuario.id));
                verDetalle.classList.add("btn-preview");
                acciones.appendChild(verDetalle);
                if (permisos.editar) {
                    const editar = crearBoton("Editar", () => abrirFormulario(usuario.id));
                    editar.classList.add("btn-edit");
                    acciones.appendChild(editar);
                }
                if (permisos.eliminar) {
                    const eliminar = crearBoton("Eliminar", () => eliminarUsuario(usuario, eliminar));
                    eliminar.classList.add("btn-delete");
                    eliminar.disabled = Number(usuario.id) === idActual || usuario.estado === "Inactivo";
                    acciones.appendChild(eliminar);
                }
                fila.appendChild(acciones);
                filas.appendChild(fila);
            }

            mensaje.textContent = busqueda
                ? `${resultado.data.length} usuario(s) en esta página${resultado.has_more ? "; hay más resultados" : ""}.`
                : "";
            mostrarPaginas(resultado, busqueda);
            return resultado;
        } catch (error) {
            if (sigueVigente()) {
                mensaje.textContent = error.message;
                paginasListado.querySelectorAll("button").forEach(boton => boton.disabled = false);
                paginasBusqueda.replaceChildren();
                if (busqueda) {
                    paginasBusqueda.appendChild(crearBoton("Reintentar", cargarUsuarios));
                }
            }
        }
    }

    return cargarUsuarios();
}
