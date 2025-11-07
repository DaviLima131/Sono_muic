document.addEventListener("DOMContentLoaded", () => {
    const favBtn = document.getElementById("fav-btn");

    if (favBtn) {
        favBtn.addEventListener("click", () => {
            const musicaId = favBtn.dataset.musicaId;

            fetch("favoritar.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "musica_id=" + musicaId
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "adicionado") {
                    favBtn.classList.add("active");
                    favBtn.innerText = "💜 Favoritada";
                } else if (data.status === "removido") {
                    favBtn.classList.remove("active");
                    favBtn.innerText = "❤️ Favoritar";
                }
            });
        });
    }
});
