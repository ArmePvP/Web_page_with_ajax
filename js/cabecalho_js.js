document.addEventListener("DOMContentLoaded", function () {
  const menuBtn = document.getElementById("menu-btn");
  const closeBtn = document.getElementById("close-menu");
  const mobileMenu = document.getElementById("mobile-menu");
  const overlay = document.getElementById("overlay");
  const header = document.querySelector('.cabecalho');

  menuBtn.addEventListener("click", () => {
    mobileMenu.classList.add("ativo");
    overlay.classList.add("ativo");
    header.classList.add("escurecido");
  });

  closeBtn.addEventListener("click", fecharMenu);
  overlay.addEventListener("click", fecharMenu);

  function fecharMenu() {
    mobileMenu.classList.remove("ativo");
    overlay.classList.remove("ativo");
    header.classList.remove("escurecido");
  }

  // Logout
  function logout() {
    fetch('includes/logout.php')
      .then(res => res.text())
      .then(() => {
        // Após logout, recarrega a página para atualizar menu
        location.reload();
      });
  }

  const logoutBtn = document.getElementById("logoutBtn");
  const logoutBtnMobile = document.getElementById("logoutBtnMobile");

  if (logoutBtn) {
    logoutBtn.addEventListener("click", (e) => {
      e.preventDefault();
      logout();
    });
  }

  if (logoutBtnMobile) {
    logoutBtnMobile.addEventListener("click", (e) => {
      e.preventDefault();
      logout();
    });
  }
});
