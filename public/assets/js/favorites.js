// Seleção de elementos do DOM
const favoritesBar = document.getElementById("favorites-bar");
const cleanFavoritesButton = document.getElementById("clean-favorites");

// Função para carregar os favoritos salvos do LocalStorage
function loadFavorites() {
    const savedFavorites =
        JSON.parse(localStorage.getItem("favoriteItems")) || [];
    favoritesBar.innerHTML = ""; // Limpa a barra

    if (savedFavorites.length === 0) {
        displayNoFavoritesMessage();
    } else {
        savedFavorites.forEach((item) => {
            if (window.location.href !== item.url) {
                addFavoriteToBar(item, false);
            }
        });
    }
}

// Função para exibir mensagem de "Favoritos" se não houver favoritos
function displayNoFavoritesMessage() {
    const noFavoritesMessage = document.createElement("span");
    noFavoritesMessage.textContent = "Favoritos";
    noFavoritesMessage.style.color = "#e5e5e5"; // cor cinza claro
    noFavoritesMessage.style.fontSize = "15px"; // tamanho da fonte
    noFavoritesMessage.style.display = "block"; // para centralização
    noFavoritesMessage.style.margin = "0 auto"; // centraliza horizontalmente
    favoritesBar.appendChild(noFavoritesMessage);
}

// Função para adicionar um favorito à barra
function addFavoriteToBar(item, save = true) {
    const { url, text, icon } = item;

    // Verifica se a URL já existe, é a URL atual ou é inválida
    if (
        isFavoriteExist(url) ||
        window.location.href === url ||
        isInvalidUrl(url)
    ) {
        console.log("Item não adicionado: URL inválida ou já existe.");
        return;
    }

    const newFavorite = createFavoriteElement(url, text, icon);
    favoritesBar.appendChild(newFavorite); // Insere na barra

    if (save) {
        saveFavoriteToLocalStorage(item); // Salva no LocalStorage
    }
    removeNoFavoritesMessage(); // Remove mensagem de "Favoritos" se existir
}

// Função para verificar se a URL é inválida
function isInvalidUrl(itemUrl) {
    const cleanedUrl = itemUrl.split(/[?#]/)[0];
    return cleanedUrl === "" || itemUrl.endsWith("#");
}

// Função para criar o elemento do favorito com o ícone da rota pai e o texto da tag <a>
function createFavoriteElement(itemUrl, text, iconClass) {
    const newFavorite = document.createElement("li");
    newFavorite.classList.add("favorite-item");

    const favoriteLink = document.createElement("a");
    favoriteLink.href = itemUrl;
    favoriteLink.textContent = text; // Usa o texto do <a> para o favorito

    // Adiciona o evento de clique para navegar na mesma janela
    favoriteLink.addEventListener("click", function (event) {
        event.preventDefault(); // Impede a navegação imediata
        window.location.href = itemUrl; // Navega para a URL na mesma janela
    });

    // Adiciona o ícone da rota pai
    const favoriteIcon = document.createElement("i");
    favoriteIcon.className = iconClass;

    newFavorite.appendChild(favoriteIcon); // Adiciona o ícone ao item
    newFavorite.appendChild(favoriteLink);
    return newFavorite;
}

// Função para verificar se o favorito já existe na barra
function isFavoriteExist(itemUrl) {
    return Array.from(favoritesBar.querySelectorAll("a")).some(
        (link) => link.href === itemUrl
    );
}

// Função para salvar o favorito no LocalStorage com URL, texto e ícone
function saveFavoriteToLocalStorage(item) {
    const savedFavorites =
        JSON.parse(localStorage.getItem("favoriteItems")) || [];
    savedFavorites.push(item); // Adiciona no final da lista
    localStorage.setItem("favoriteItems", JSON.stringify(savedFavorites));
}

// Função para remover um favorito da barra e do LocalStorage
function removeFavorite(itemUrl) {
    let savedFavorites =
        JSON.parse(localStorage.getItem("favoriteItems")) || [];
    savedFavorites = savedFavorites.filter((fav) => fav.url !== itemUrl);
    localStorage.setItem("favoriteItems", JSON.stringify(savedFavorites));
    loadFavorites(); // Recarrega a barra de favoritos
}

// Função para remover a mensagem "Favoritos" se existir
function removeNoFavoritesMessage() {
    const existingMessage = favoritesBar.querySelector("span");
    if (existingMessage) {
        favoritesBar.removeChild(existingMessage);
    }
}

// Adiciona eventos de arrastar em todos os níveis (incluindo os de terceiro nível gerados por Blade)
document
    .querySelectorAll(
        ".side-nav-item li, .side-nav-item a, .side-nav-sub-item, .side-nav-third-level-item, .side-nav-third-level li a"
    )
    .forEach((item) => {
        item.addEventListener("dragstart", function (e) {
            const url = this.getAttribute("href");
            const iconClass =
                this.closest(".side-nav-item").querySelector("i").className;
            const text = this.textContent.trim(); // Usa o texto do <a>
            if (url) {
                const itemData = { url, text, icon: iconClass };
                e.dataTransfer.setData("text", JSON.stringify(itemData));
            } else {
                console.log("Nenhum dado de URL encontrado.");
            }
        });
    });

// Eventos para a barra de favoritos
favoritesBar.addEventListener("dragover", (e) => {
    e.preventDefault();
});

favoritesBar.addEventListener("drop", (e) => {
    e.preventDefault();
    const itemData = JSON.parse(e.dataTransfer.getData("text"));
    addFavoriteToBar(itemData); // Adiciona o favorito à barra
});

// Adiciona um evento para detectar quando o item é arrastado para fora da barra
favoritesBar.addEventListener("dragleave", (e) => {
    const itemUrl = e.dataTransfer.getData("text");
    if (itemUrl) {
        removeFavorite(itemUrl); // Remove o item da barra e do LocalStorage
    }
});

// Carrega os favoritos ao iniciar a página
window.addEventListener("load", loadFavorites);

// Funções de navegação com as setas
document.getElementById("arrow-left").addEventListener("click", () => {
    favoritesBar.scrollBy({ top: 0, left: -300, behavior: "smooth" });
});

document.getElementById("arrow-right").addEventListener("click", () => {
    favoritesBar.scrollBy({ top: 0, left: 300, behavior: "smooth" });
});

// Função para verificar e ajustar a visibilidade das setas conforme o scroll
function checkScroll() {
    const arrowLeft = document.getElementById("arrow-left");
    const arrowRight = document.getElementById("arrow-right");

    arrowLeft.style.visibility =
        favoritesBar.scrollLeft === 0 ? "hidden" : "visible";
    arrowRight.style.visibility =
        favoritesBar.scrollLeft + favoritesBar.clientWidth >=
        favoritesBar.scrollWidth
            ? "hidden"
            : "visible";
}

// Verifica o scroll toda vez que houver rolagem
favoritesBar.addEventListener("scroll", checkScroll);
checkScroll(); // Checa o estado inicial das setas

// Evento para limpar os favoritos
cleanFavoritesButton.addEventListener("click", () => {
    localStorage.removeItem("favoriteItems"); // Limpa os favoritos do LocalStorage
    loadFavorites(); // Recarrega a barra de favoritos
});
