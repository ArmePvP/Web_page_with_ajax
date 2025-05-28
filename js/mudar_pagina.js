document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("conteudo-dinamico");
  let ultimoConteudo = "";
  let carregando = false;

  async function carregarConteudo(url, method = "GET", body = null) {
    if (carregando) {
      console.log("Já está carregando, aguarde...");
      return;
    }
    carregando = true;

    try {
      const options = {
        method,
        headers: {
          "X-Requested-With": "XMLHttpRequest"
        }
      };

      if (method === "POST" && body) {
        options.body = body;
      }

      const response = await fetch(url, options);
      if (!response.ok) throw new Error("Erro ao carregar conteúdo.");

      const html = await response.text();
      const conteudoAtual = html.trim();
      const forcarRecarregamento = url.includes("casas_edit.php");

      if (conteudoAtual === ultimoConteudo && !forcarRecarregamento) {
        console.log("Conteúdo idêntico, carregamento cancelado.");
        return;
      }

      ultimoConteudo = conteudoAtual;

      container.innerHTML = conteudoAtual;
      animarFadeIn(container);
      ativarNavegacaoModal();
      ativarRemocaoDeImagem(); // <- ativa remoção após carregar novo conteúdo

    } catch (err) {
      container.innerHTML = `<p style="color: red;">Erro ao carregar o conteúdo.</p>`;
      console.error(err);
    } finally {
      carregando = false;
    }
  }

  function animarFadeIn(el) {
    el.classList.remove("fade-in");
    void el.offsetWidth;
    el.classList.add("fade-in");
  }

  function ativarNavegacaoModal() {
    const container = document.getElementById('imagem-container');
    if (!container) return;

    const imagens = JSON.parse(container.getAttribute('data-imagens'));
    const imgElemento = document.getElementById('imagem-zoom');
    const btnPrev = document.getElementById('prev-btn');
    const btnNext = document.getElementById('next-btn');

    let indiceAtual = 0;
    let timeoutZoom = null;
    let zoomAtivo = false;
    let interacaoTouch = false; // Flag para saber se já usou touch

    function atualizarImagem() {
      imgElemento.src = "../uploads/" + imagens[indiceAtual];
      if (!zoomAtivo) {
        container.classList.remove('zoomed');
      }
      if (btnPrev) btnPrev.style.display = indiceAtual === 0 ? 'none' : 'block';
      if (btnNext) btnNext.style.display = indiceAtual === imagens.length - 1 ? 'none' : 'block';
    }

    if (btnPrev) btnPrev.onclick = () => {
      if (indiceAtual > 0) {
        indiceAtual--;
        atualizarImagem();
      }
    };
    if (btnNext) btnNext.onclick = () => {
      if (indiceAtual < imagens.length - 1) {
        indiceAtual++;
        atualizarImagem();
      }
    };

    function ativarZoom() {
      container.classList.add('zoomed');
      zoomAtivo = true;
      console.log('Zoom ativado');
    }

    function desativarZoom() {
      container.classList.remove('zoomed');
      zoomAtivo = false;
      console.log('Zoom desativado');
    }

    // TOQUE: alterna o zoom e marca que foi toque
    imgElemento.addEventListener('pointerdown', (e) => {
      if (e.pointerType === 'touch') {
        e.preventDefault();
        interacaoTouch = true;
        if (zoomAtivo) desativarZoom();
        else ativarZoom();
      }
    });

    // SOMENTE MOUSE: se não for touch, ativa zoom com atraso
    imgElemento.addEventListener('mouseenter', () => {
      if (interacaoTouch) return;
      timeoutZoom = setTimeout(() => {
        ativarZoom();
      }, 1000);
      console.log('Mouse entrou, timer para zoom iniciado');
    });

    imgElemento.addEventListener('mouseleave', () => {
      if (interacaoTouch) return;
      if (timeoutZoom) {
        clearTimeout(timeoutZoom);
        timeoutZoom = null;
        console.log('Mouse saiu antes de 1s, timer cancelado');
      }
      if (zoomAtivo) {
        desativarZoom();
      }
    });

    atualizarImagem();
  }




  function ativarRemocaoDeImagem() {
    document.querySelectorAll(".form-remover-imagem").forEach(form => {
      form.addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const id = form.dataset.id;

        fetch("includes/remover_outra_imagem.php", {
          method: "POST",
          body: formData
        })
        .then(resp => resp.text())
        .then(resp => {
          if (resp.trim() === 'ok') {
            carregarConteudo(`includes/casas_edit.php?id=${id}`, "GET");
          } else {
            alert("Erro ao remover imagem: " + resp);
          }
        })

        .catch(() => alert("Erro na requisição."));
      });
    });
  }

  // Ativa também no conteúdo inicial da página
  ativarRemocaoDeImagem();

  container.addEventListener("submit", async (e) => {
    if (e.target.tagName.toLowerCase() !== "form") return;
    e.preventDefault();

    const form = e.target;
    let url = form.action || window.location.href;
    const method = (form.method || "GET").toUpperCase();
    const formData = new FormData(form);

    if (carregando) {
      console.log("Já está carregando, aguarde...");
      return;
    }
    carregando = true;

    try {
      const options = {
        method,
        headers: {
          "X-Requested-With": "XMLHttpRequest"
        }
      };

      // Corrige: GET não pode ter corpo
      if (method === "GET") {
        const query = new URLSearchParams(formData).toString();
        url += (url.includes("?") ? "&" : "?") + query;
      } else {
        options.body = formData;
      }

      const response = await fetch(url, options);
      if (!response.ok) throw new Error("Erro na requisição");

      const textoResposta = await response.text();

      if (textoResposta.includes("Login realizado com sucesso!")) {
        window.location.reload(true);
        return;
      }
      if (textoResposta.includes("Casa cadastrada com sucesso!")) {
        window.location.reload(true);
        return;
      }



      try {
        const json = JSON.parse(textoResposta);
        if (json.success) return;
        else {
          container.innerHTML = `<p style="color: red;">${json.message}</p>`;
          return;
        }
      } catch {
        const conteudoAtual = textoResposta.trim();
        const forcarRecarregamento = url.includes("casas_edit.php");

        if (conteudoAtual === ultimoConteudo && !forcarRecarregamento) {
          console.log("Resposta idêntica, não recarregando.");
          return;
        }

        ultimoConteudo = conteudoAtual;
        container.innerHTML = conteudoAtual;
        animarFadeIn(container);
        ativarNavegacaoModal();
        ativarRemocaoDeImagem();
      }

    } catch (error) {
      container.innerHTML = `<p style="color: red;">Erro ao enviar formulário.</p>`;
      console.error(error);
    } finally {
      carregando = false;
    }
  });


  document.body.addEventListener("click", (e) => {
    const target = e.target.closest("[data-url]");
    if (!target) return;

    e.preventDefault();
    const url = target.getAttribute("data-url");
    if (!url) return;

    carregarConteudo(url);
  });

  window.carregarConteudo = carregarConteudo;
});
