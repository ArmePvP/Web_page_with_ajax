<?php session_start(); ?>
<header class="cabecalho">
  <button id="menu-btn" class="btn-menu-mobile">☰</button>

  <nav class="menu-desktop">
    <div class="meu-esnquerda">
        <a href="#" data-url='includes/mosaico.php'>Início</a>
        <a href="#" data-url="includes/i_adm.php">Sobre</a>
        <a href="#" data-url="includes/lista_casas.php">Contato</a>
    </div>
    <div class="menu-direita">
      <?php if (isset($_SESSION['logado']) && $_SESSION['logado']): ?>
          <?php if (!empty($_SESSION['nome'])): ?>
            <span>Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?></span>
          <?php endif; ?>

          <a href="includes/logout.php" class="btn-secundario">Logout</a>
      <?php else: ?>
          <a href="#" id="loginBtn" data-url='includes/login.php'>Login</a>
      <?php endif; ?>
    </div>
  </nav>

  <div class="logo">MeuSite</div>
</header>


<div id="overlay" class="overlay"></div>

<!-- Sidebar Menu Mobile -->
<div id="mobile-menu" class="menu-mobile">
  <button id="close-menu" class="btn-fechar">✕</button>
  <?php if (!empty($_SESSION['nome'])): ?>
    <span>Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?></span>
  <?php endif; ?>
  <a href="#" data-url='includes/mosaico.php'><span>🏠</span> Início</a>
  <a href="#" data-url='includes/i_adm.php'><span>ℹ️</span> Sobre</a>
  <a href="#" data-url='includes/lista_casas.php'><span>📞</span> Contato</a>
  <?php if (!empty($_SESSION['logado']) && $_SESSION['logado']): ?>
    <a href="#" id="logoutBtnMobile"><span>🔓</span> Logout</a>
  <?php else: ?>
    <a href="#" data-url='includes/login.php'><span>🔒</span> Login</a>
  <?php endif; ?>
</div>
