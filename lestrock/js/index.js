document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("lista-produtos");
  const btnMais = document.getElementById("carregar-mais");
  const botaoBuscar = document.getElementById("botao-buscar");
  const campoBusca = document.getElementById("busca");

  if (!container || !btnMais) return;

  let pagina = 1;
  let termoAtual = "brasil"; // termo inicial
  const key = "CdGODaeIwjOSStGpHleL";
  const secret = "vSgbqlAacxUTOCXAKXKgaBuTUEoEXBRs";

  function carregarDiscosComTermo(termoBusca) {
    fetch(`https://api.discogs.com/database/search?q=${encodeURIComponent(termoBusca)}&type=release&page=${pagina}&per_page=12&key=${key}&secret=${secret}`)
      .then(res => res.json())
      .then(data => {
        if (!data.results || data.results.length === 0) {
          btnMais.style.display = "none";
          if (pagina === 1) {
            container.innerHTML = "<p>Nenhum disco encontrado.</p>";
          }
          return;
        }

        data.results.forEach(item => {
          const link = document.createElement("a");
link.href = `detalhesprod.php?id=${item.id}`;
link.className = "produto";

link.innerHTML = `
  <img src="${item.cover_image}" alt="${item.title}">
  <h3>${item.title}</h3>
  <p>${item.genre ? item.genre.join(', ') : 'Gênero desconhecido'}</p>
`;

container.appendChild(link);

        });

        pagina++;
        btnMais.style.display = "block";
      })
      .catch(err => {
        console.error("Erro ao buscar produtos:", err);
        container.innerHTML = "<p>Erro ao carregar produtos.</p>";
      });
  }

  // Primeira carga com o termo inicial
  carregarDiscosComTermo(termoAtual);

  // Clique no botão "Carregar mais"
  btnMais.addEventListener("click", () => {
    carregarDiscosComTermo(termoAtual);
  });

  // Clique no botão de buscar
  if (botaoBuscar && campoBusca) {
    botaoBuscar.addEventListener("click", () => {
      const termoDigitado = campoBusca.value.trim();
      if (termoDigitado !== "") {
        container.innerHTML = "";
        pagina = 1;
        termoAtual = termoDigitado;
        carregarDiscosComTermo(termoAtual);
      }
    });

    // Enter também dispara a busca
    campoBusca.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        botaoBuscar.click();
      }
    });
  }
});

