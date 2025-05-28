<?php
$casasFile = __DIR__ . '/../casas.json';
$casas = file_exists($casasFile) ? json_decode(file_get_contents($casasFile), true) : [];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<title>Mosaico de Casas com Busca</title>
<style>
/* --- SEU CSS EXISTENTE --- */
.mosaico-container {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  padding: 10px;
  justify-content: center;
}

.casa-card {
  border: 1px solid #ccc;
  padding: 10px;
  width: 220px; /* desktop */
  max-width: 100%;
  cursor: pointer;
  text-align: center;
  background: #fff;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  border-radius: 8px;
  transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.casa-card:hover {
  box-shadow: 0 6px 15px rgba(0,0,0,0.2);
  transform: scale(1.05);
  z-index: 10;
}

.casa-card img {
  width: 100%;
  height: 140px;
  object-fit: cover;
  border-radius: 6px;
  transition: transform 0.3s ease;
}

.casa-card:hover img {
  transform: scale(1.05);
}

.modal {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
  padding: 10px;
}

.modal.visible {
  opacity: 1;
  pointer-events: auto;
}

.modal-content {
  background: white;
  padding: 20px;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  border-radius: 8px;
  transform: scale(0.8);
  transition: transform 0.3s ease;
  box-shadow: 0 10px 40px rgba(0,0,0,0.3);
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

.modal.visible .modal-content {
  transform: scale(1);
}

#close-modal {
  position: absolute;
  right: 10px;
  top: 5px;
  cursor: pointer;
  font-size: 26px;
  font-weight: bold;
  user-select: none;
}

.fade-in {
  animation: fadeIn 0.5s ease forwards;
}

@keyframes fadeIn {
  from {opacity: 0;}
  to {opacity: 1;}
}

@media (max-width: 600px) {
  .mosaico-container {
    gap: 10px;
  }
  .casa-card {
    width: 100%;
    padding: 8px;
  }
  .casa-card img {
    height: 120px;
  }
  .modal-content {
    padding: 15px;
    max-height: 85vh;
  }
  #close-modal {
    font-size: 28px;
    top: 8px;
    right: 15px;
  }
}

/* Estilo simples para a busca */
#busca-casas {
  display: block;
  margin: 20px auto;
  padding: 10px 15px;
  width: 90%;
  max-width: 400px;
  font-size: 16px;
  border: 1px solid #aaa;
  border-radius: 6px;
  box-sizing: border-box;
}
#form-busca {
  display: flex;
  justify-content: center;
  margin: 30px 0;
}

.search-box {
  display: flex;
  gap: 0;
  width: 90%;
  max-width: 500px;
  border: 1px solid #ccc;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  background: white;
}

.search-box input[type="text"] {
  flex: 1;
  padding: 12px 16px;
  font-size: 16px;
  border: none;
  outline: none;
  background: transparent;
}

.search-box button {
  background: #3498db;
  color: white;
  border: none;
  padding: 0 20px;
  cursor: pointer;
  font-weight: bold;
  font-size: 16px;
  transition: background 0.3s ease;
}

.search-box button:hover {
  background: #2980b9;
}

</style>
</head>
<body>

<h2>Mosaico de Casas</h2>

<!-- Caixa de busca -->
<form id="form-busca" action="includes/casas_busca.php" method="GET">
  <div class="search-box">
    <input type="text" name="termo" placeholder="🔍 Buscar casas por título ou descrição..." required />
    <button type="submit">Buscar</button>
  </div>
</form>




<div class="mosaico-container" id="mosaico-casas">
  <?php foreach ($casas as $index => $casa): ?>
    <div
      class="casa-card"
      data-url="includes/modal.php?index=<?= $index ?>"
    >
      <img src="../uploads/<?= htmlspecialchars($casa['imagem_destaque']) ?>" alt="Destaque da casa <?= htmlspecialchars($casa['titulo']) ?>">
      <h3><?= htmlspecialchars($casa['titulo']) ?></h3>
      <p><strong>R$ <?= number_format($casa['preco'], 2, ',', '.') ?></strong></p>
    </div>
  <?php endforeach; ?>
</div>

<!-- Modal -->
<div id="modal" class="modal" aria-hidden="true">
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <span id="close-modal" aria-label="Fechar modal" role="button" tabindex="0">×</span>
    <div id="conteudo-dinamico">
      <p>Clique numa casa para ver detalhes</p>
    </div>
  </div>
</div>



</body>
</html>
