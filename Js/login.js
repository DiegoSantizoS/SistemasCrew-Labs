(() => {
    'use strict';
    const contenedorAcceso = document.querySelector('.acceso');
    const pestanas = [...contenedorAcceso.querySelectorAll('[role="tab"]')];
    const panelesFormulario = pestanas.map(pestana => document.getElementById(pestana.getAttribute('aria-controls')));
    const avisoAccesible = contenedorAcceso.querySelector('[data-aviso-accesible]');
    let solicitudEnCurso = false;
    function seleccionarPestana(indicePestana, enfocarPestana = false) {
        if (solicitudEnCurso) return;
        const indiceAnterior = pestanas.findIndex(pestana => pestana.getAttribute('aria-selected') === 'true');
        pestanas.forEach((pestana, indiceActual) => {
            pestana.setAttribute('aria-selected', String(indiceActual === indicePestana));
            pestana.tabIndex = indiceActual === indicePestana ? 0 : -1;
            panelesFormulario[indiceActual].hidden = indiceActual !== indicePestana;
            panelesFormulario[indiceActual].classList.remove('entra-derecha', 'entra-izquierda');
        });
        if (indiceAnterior !== indicePestana) panelesFormulario[indicePestana].classList.add(indicePestana > indiceAnterior ? 'entra-derecha' : 'entra-izquierda');
        contenedorAcceso.querySelector('[data-lista-pestanas]').classList.toggle('registro-seleccionado', indicePestana === 1);
        if (enfocarPestana) pestanas[indicePestana].focus();
    }
    pestanas.forEach((pestana, indicePestana) => {
        pestana.addEventListener('click', () => seleccionarPestana(indicePestana));
        pestana.addEventListener('keydown', evento => {
            const indicesPorTecla = { ArrowRight: 1 - indicePestana, ArrowLeft: 1 - indicePestana, Home: 0, End: 1 };
            if (evento.key in indicesPorTecla) {
                evento.preventDefault();
                seleccionarPestana(indicesPorTecla[evento.key], true);
            }
        });
    });
    contenedorAcceso.querySelectorAll('[data-destino-formulario]').forEach(botonCambio => botonCambio.addEventListener('click', () => {
        if (solicitudEnCurso) return;
        const indicePestana = botonCambio.dataset.destinoFormulario === 'signup' ? 1 : 0;
        seleccionarPestana(indicePestana);
        panelesFormulario[indicePestana].querySelector('input').focus();
    }));
    function validarCampo(grupoCampo) {
        const entrada = grupoCampo.querySelector('input');
        const formulario = entrada.form;
        const esContrasena = ['password', 'confirm'].includes(entrada.name);
        const valorCampo = esContrasena ? entrada.value : entrada.value.trim();
        let mensajeError = '';
        if (entrada.required && !valorCampo) mensajeError = 'Completa este campo.';
        else if (entrada.validity.typeMismatch) mensajeError = 'Introduce un correo válido, como nombre@ejemplo.com.';
        else if (valorCampo.length > entrada.maxLength) mensajeError = `Usa como máximo ${entrada.maxLength} caracteres.`;
        else if (formulario.dataset.formulario === 'signup' && entrada.name === 'password' && (Array.from(valorCampo).length < 8 || new TextEncoder().encode(valorCampo).length > 72)) mensajeError = 'Usa al menos 8 caracteres y como máximo 72 bytes.';
        else if (entrada.name === 'confirm' && valorCampo !== formulario.elements.password.value) mensajeError = 'Las contraseñas no coinciden.';
        grupoCampo.querySelector('[data-error-campo]').textContent = mensajeError;
        grupoCampo.classList.toggle('campo-invalido', !!mensajeError);
        entrada.setAttribute('aria-invalid', String(!!mensajeError));
        return !mensajeError;
    }
    contenedorAcceso.querySelectorAll('[data-formulario]').forEach(formulario => {
        const gruposCampos = [...formulario.querySelectorAll('[data-campo]')];
        gruposCampos.forEach(grupoCampo => {
            grupoCampo.addEventListener('focusout', evento => {
                if (!grupoCampo.contains(evento.relatedTarget)) {
                    grupoCampo.dataset.revisado = '1';
                    validarCampo(grupoCampo);
                }
            });
            grupoCampo.addEventListener('input', () => {
                if (grupoCampo.dataset.revisado) validarCampo(grupoCampo);
            });
        });
        formulario.addEventListener('submit', async evento => {
            evento.preventDefault();
            if (solicitudEnCurso) return;
            const mensajeServidor = formulario.querySelector('[data-mensaje-servidor]');
            mensajeServidor.textContent = '';
            const camposInvalidos = gruposCampos.filter(grupoCampo => {
                grupoCampo.dataset.revisado = '1';
                return !validarCampo(grupoCampo);
            });
            if (camposInvalidos.length) {
                avisoAccesible.textContent = 'Revisa los campos indicados.';
                camposInvalidos[0].querySelector('input').focus();
                return;
            }
            const datosSolicitud = Object.fromEntries(new FormData(formulario));
            datosSolicitud.csrf = document.querySelector('meta[name="csrf-token"]').content;
            const controlesFormulario = [...contenedorAcceso.querySelectorAll('button,input')];
            solicitudEnCurso = true;
            controlesFormulario.forEach(control => control.disabled = true);
            formulario.setAttribute('aria-busy', 'true');
            const botonEnviar = formulario.querySelector('[type="submit"]');
            const textoBoton = botonEnviar.textContent;
            botonEnviar.textContent = 'Procesando…';
            let registroExitoso = false;
            let accesoFallido = false;
            try {
                const respuestaHttp = await fetch('api/auth/' + (formulario.dataset.formulario === 'signup' ? 'register' : 'login') + '.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datosSolicitud)
                });
                const resultadoSolicitud = await respuestaHttp.json();
                if (!respuestaHttp.ok) throw new Error(resultadoSolicitud.message || 'No se pudo completar la solicitud.');
                if (formulario.dataset.formulario === 'login') location.assign(resultadoSolicitud.data.redirect);
                else {
                    registroExitoso = true;
                    formulario.reset();
                    const formularioAcceso = panelesFormulario[0].querySelector('form');
                    formularioAcceso.elements.email.value = datosSolicitud.email;
                    formularioAcceso.querySelector('[data-mensaje-servidor]').textContent = resultadoSolicitud.message;
                }
            } catch (errorSolicitud) {
                mensajeServidor.textContent = errorSolicitud.message || 'No se pudo conectar con el servidor.';
                if (formulario.dataset.formulario === 'login') {
                    formulario.elements.password.value = '';
                    accesoFallido = true;
                }
            }
            finally {
                solicitudEnCurso = false;
                controlesFormulario.forEach(control => control.disabled = false);
                formulario.removeAttribute('aria-busy');
                botonEnviar.textContent = textoBoton;
                if (registroExitoso) {
                    seleccionarPestana(0);
                    panelesFormulario[0].querySelector('[name="password"]').focus();
                }
                if (accesoFallido) formulario.elements.password.focus();
            }
        });
    });
    contenedorAcceso.querySelectorAll('[data-alternar-contrasena]').forEach(botonVisibilidad => {
        const entrada = document.getElementById(botonVisibilidad.getAttribute('aria-controls'));
        botonVisibilidad.addEventListener('click', () => {
            const mostrarContrasena = entrada.type === 'password';
            entrada.type = mostrarContrasena ? 'text' : 'password';
            botonVisibilidad.setAttribute('aria-pressed', String(mostrarContrasena));
            botonVisibilidad.setAttribute('aria-label', mostrarContrasena ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });
})();
