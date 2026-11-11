document.querySelectorAll(".perfil").forEach((tarjeta) => {
    tarjeta.querySelectorAll("a.perfil-boton").forEach((enlace) => {
        enlace.addEventListener("click", () => {
            tarjeta.classList.add("sin-efecto");
        });
    });

    tarjeta.addEventListener("pointerenter", () => {
        tarjeta.classList.remove("sin-efecto");
    });

    tarjeta.addEventListener("focusin", (evento) => {
        if (evento.target.matches(":focus-visible")) {
            tarjeta.classList.remove("sin-efecto");
        }
    });
});
