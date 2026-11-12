document.querySelectorAll(".perfil").forEach((tarjetaIntegrante) => {
    tarjetaIntegrante.querySelectorAll("a.perfil-boton").forEach((enlacePerfil) => {
        enlacePerfil.addEventListener("click", () => {
            tarjetaIntegrante.classList.add("sin-efecto");
        });
    });

    tarjetaIntegrante.addEventListener("pointerenter", () => {
        tarjetaIntegrante.classList.remove("sin-efecto");
    });

    tarjetaIntegrante.addEventListener("focusin", (eventoFoco) => {
        if (eventoFoco.target.matches(":focus-visible")) {
            tarjetaIntegrante.classList.remove("sin-efecto");
        }
    });
});
